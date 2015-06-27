<?php
/**************************************************************************
* This file is part of the WebIssues Server program
* Copyright (C) 2006 Michał Męciński
* Copyright (C) 2007-2015 WebIssues Team
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU Affero General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU Affero General Public License for more details.
*
* You should have received a copy of the GNU Affero General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
**************************************************************************/

if ( !defined( 'WI_VERSION' ) ) die( -1 );

/**
* Helper class for handling and rendering grids with sortable columns.
*
* This class provides support for the components for handling query string
* parameters and determining sorting and paging parameters to be used with
* System_Db_Connection::queryPage().
*
* It also provides support for the views for rendering grid headers with
* sortable columns and pager controls. It can also be used for handling
* stand-alone pager controls.
*
* The controller must at least pass the total number of rows and - if the
* grid has sortable columns - pass an array containing internal identifiers
* of columns which are used in the URLs and expressions which can be used
* to build the ORDER BY clause in SQL. It can then use getOffset() and
* getOrderBy() to calculate parameters for the System_Db_Connection::queryPage()
* method.
*
* Note that this is not a fully functional grid and retrieving and rendering
* data must be implemented in the component and the view.
*/
class System_Web_Grid extends System_Web_Base
{
    /**
    * Sort in ascending order.
    */
    const Ascending = 'asc';
    /**
    * Sort in descending order.
    */
    const Descending = 'desc';

    private $pageParam = 'page';
    private $orderParam = 'order';
    private $sortParam = 'sort';

    private $pageSize = 10;
    private $rowsCount = 0;
    private $columns = array();
    private $defaultOrder = null;
    private $defaultSort = null;
    private $filterParams = null;
    private $mergeParams = array();
    private $rowId = null;
    private $parentId = null;

    /**
    * Constructor
    */
    public function __construct()
    {
        parent::__construct();
    }

    /**
    * Change names of the query string parameters used by this grid.
    * The default names are 'page', 'order' and 'sort'. The names can be changed
    * to avoid conflicts when there are multiple grids or pagers on one page.
    * @param $page Parameter containing the page number.
    * @param $order Optional parameter containing the identifier of column
    * used for sorting.
    * @param $sort Optional parameter containing the order of sorting.
    */
    public function setParameters( $page, $order = null, $sort = null )
    {
        $this->pageParam = $page;
        $this->orderParam = $order;
        $this->sortParam = $sort;
    }

    /**
    * Set query parameters to be filtered in URLs of paging and sorting links.
    */
    public function setFilterParameters( $params )
    {
        $this->filterParams = $params;
    }

    /**
    * Set query parameters to be merged with URLs of paging and sorting links.
    */
    public function setMergeParameters( $params )
    {
        $this->mergeParams = $params;
    }

    /**
    * Set the identifiers of the currently selected row.
    * @param $rowId Identifier of the row.
    * @param $parentId Optional identifier of the parent row.
    */
    public function setSelection( $rowId, $parentId = null )
    {
        $this->rowId = $rowId;
        $this->parentId = $parentId;
    }

    /**
    * Change the size of the page. The default size is 10 rows per page.
    */
    public function setPageSize( $size )
    {
        $this->pageSize = $size;
    }

    /**
    * Return the size of the page.
    */
    public function getPageSize()
    {
        return $this->pageSize;
    }

    /**
    * Set the total number of rows. It must be calculated by the controller
    * using appropriate query.
    */
    public function setRowsCount( $count )
    {
        $this->rowsCount = $count;
    }

    /**
    * Return the total number of rows.
    */
    public function getRowsCount()
    {
        return $this->rowsCount;
    }

    /**
    * Set the columns which can be used for sorting. The array should contain
    * all sortable columns whith identifiers as keys and SQL expressions
    * as values.
    */
    public function setColumns( $columns )
    {
        $this->columns = $columns;
    }

    /**
    * Set the default sort order used if no query string arguments are passed.
    * @param $order The identifier of the column used for sorting.
    * @param $sort The sorting order (Ascending or Descending).
    */
    public function setDefaultSort( $order, $sort )
    {
        $this->defaultOrder = $order;
        $this->defaultSort = $sort;
    }

    /**
    * Calculate the total number of pages.
    */
    public function getPagesCount()
    {
        return ceil( $this->rowsCount / $this->pageSize );
    }

    /**
    * Retrieve the index of the current page. The index is always between
    * 1 and getPagesCount(), inclusively.
    */
    public function getCurrentPage()
    {
        $page = $this->request->getQueryString( $this->pageParam, 1 );
        $count = $this->getPagesCount();
        return max( 1, min( $page, $count ) );
    }

    /**
    * Calculate the zero-based index of the first row.
    */
    public function getOffset()
    {
        $page = $this->getCurrentPage();
        return ( $page - 1 ) * $this->pageSize;
    }

    /**
    * Calculate the sorting order specifier. It is calculated based on the
    * expression associated with the current column and the sorting order.
    */
    public function getOrderBy()
    {
        $order = $this->request->getQueryString( $this->orderParam, $this->defaultOrder );
        $sort = $this->request->getQueryString( $this->sortParam, $this->defaultSort );

        if ( isset( $order ) && isset( $this->columns[ $order ] ) ) {
            $column = $this->columns[ $order ];

            return self::makeOrderBy( $column, $sort );
        }

        throw new System_Core_Exception( 'Invalid sort order' );
    }

    /**
    * Create the sorting order specifier for given column and sorting order.
    */
    public static function makeOrderBy( $column, $sort )
    {
        if ( $sort == self::Ascending )
            $order = ' ASC';
        else if ( $sort == self::Descending )
            $order = ' DESC';
        else
            throw new System_Core_Exception( 'Invalid sort order' );

        $parts = explode( ', ', $column );

        foreach ( $parts as &$part ) {
            if ( substr( $part, -4 ) != ' ASC' && substr( $part, -5 ) != ' DESC' )
                $part .= $order;
        }

        return implode( ', ', $parts );
    }

    /**
    * Print a column header for the grid.
    * The header is printed as a @c th tag containing the title of the column
    * and a sort indicator and link if the column is sortable.
    * @param $title The title of the column.
    * @param $order The identifier of the column or @c null if the column is not
    * sortable.
    */
    public function renderHeader( $title, $order = null )
    {
        if ( isset( $order ) ) {
            if ( !isset( $this->columns[ $order ] ) )
                throw new System_Core_Exception( 'Invalid sort order' );
            $sort = self::Ascending;
            $currentOrder = $this->request->getQueryString( $this->orderParam, $this->defaultOrder );
            if ( $order == $currentOrder ) {
                $currentSort = $this->request->getQueryString( $this->sortParam, $this->defaultSort );
                $sort = ( $currentSort == self::Ascending ) ? self::Descending : self::Ascending;
            }
            $url = $this->makeUrl( array( $this->orderParam => $order, $this->sortParam => $sort, $this->pageParam => null ) );
            $header = $this->link( $url, $title );
            if ( $order == $currentOrder ) {
                $text = ( $currentSort == self::Ascending ) ? $this->tr( 'Ascending' ) : $this->tr( 'Decending' );
                $image = $this->image( "/common/images/sort-$currentSort.png", $text, array( 'width' => 13, 'height' => 13, 'class' => null ) );
                $header .= $this->link( $url, "\n" . $image );
            }
        } else {
            $header = $title;
        }

        echo '<th>' . $header . '</th>';
    }

    /**
    * Print a pager control for switching pages.
    * The pager is printed as a @c div tag with @c pager class containing
    * links to the first, previous, next and last page and the 9 nearest pages.
    * The pager is not printer if there is only one page.
    */
    public function renderPager()
    {
        $pagesCount = $this->getPagesCount();
        if ( $pagesCount <= 1 )
            return '';

        $currentPage = $this->getCurrentPage();
        $fromPage = $currentPage - 4;
        $toPage = $currentPage + 4;

        if ( $toPage > $pagesCount ) {
            $fromPage -= $toPage - $pagesCount;
            $toPage = $pagesCount;
        }
        if ( $fromPage <= 0 ) {
            $toPage += 1 - $fromPage;
            $fromPage = 1;
        }
        if ( $toPage > $pagesCount )
            $toPage = $pagesCount;

        $pages = array();

        if ( $currentPage > 1 ) {
            $pages[] = $this->link( $this->makeUrl( array( $this->pageParam => 1 ) ), $this->tr( '&laquo; first' ) );
            $pages[] = $this->link( $this->makeUrl( array( $this->pageParam => $currentPage - 1 ) ), $this->tr( '&lt; previous' ) );
        }

        if ( $fromPage > 1 )
            $pages[] = "...\n";

        for ( $i = $fromPage; $i <= $toPage; $i++ ) {
            if ( $i == $currentPage )
                $pages[] = $this->buildTag( 'strong', array( 'class' => 'pager-current' ), $i );
            else
                $pages[] = $this->link( $this->makeUrl( array( $this->pageParam => $i ) ), $i );
        }

        if ( $toPage < $pagesCount )
            $pages[] = "...\n";

        if ( $currentPage < $pagesCount ) {
            $pages[] = $this->link( $this->makeUrl( array( $this->pageParam => $currentPage + 1 ) ), $this->tr( 'next &gt;' ) );
            $pages[] = $this->link( $this->makeUrl( array( $this->pageParam => $pagesCount ) ), $this->tr( 'last &raquo;' ) );
        }

        echo '<div class="pager">' . join( '', $pages ) . '</div>';
    }

    /**
    * Print a mobile pager control for switching pages.
    */
    public function renderMobilePager()
    {
        $pagesCount = $this->getPagesCount();
        if ( $pagesCount <= 1 )
            return '';

        $currentPage = $this->getCurrentPage();

        $pages = array();

        if ( $currentPage > 1 )
            $pages[] = $this->link( $this->makeUrl( array( $this->pageParam => $currentPage - 1 ) ), $this->tr( '&lt; previous' ), array( 'class' => 'pager-previous' ) );

        if ( $currentPage < $pagesCount )
            $pages[] = $this->link( $this->makeUrl( array( $this->pageParam => $currentPage + 1 ) ), $this->tr( 'next &gt;' ), array( 'class' => 'pager-next' ) );

        $pages[] = $this->buildTag( 'div', array( 'class' => 'pager-current' ), $this->tr( '%1 of %2', null, $currentPage, $pagesCount ) );

        echo '<div class="pager">' . join( '', $pages ) . '</div>';
    }

    private function makeUrl( $params )
    {
        if ( is_array( $this->filterParams ) )
            return $this->filterQueryString( WI_SCRIPT_URL, $this->filterParams, array_merge( $params, $this->mergeParams ) );
        else
            return $this->mergeQueryString( WI_SCRIPT_URL, array_merge( $params, $this->mergeParams ) );
    }

    /**
    * Update an expand cookie by adding an item.
    * @param $cookieName The name of the cookie.
    * @param $id Identifier of the item to add.
    */
    public function addExpandCookieId( $cookieName, $id )
    {
        $session = System_Core_Application::getInstance()->getSession();
        $items = explode( '|', $session->getCookie( $cookieName, '' ) );
        if ( !in_array( $id, $items ) ) {
            $items[] = $id;
            $session->setCookie( $cookieName, implode( '|', $items ), 90 );
        }
    }

    /**
    * Update an expand cookie by removing items.
    * @param $cookieName The name of the cookie.
    * @param $ids Identifiers of items to remove.
    */
    public function removeExpandCookieIds( $cookieName, $ids )
    {
        $session = System_Core_Application::getInstance()->getSession();
        $items = explode( '|', $session->getCookie( $cookieName, '' ) );
        $count = count( $items );
        $items = array_diff( $items, $ids );
        if ( count( $items ) < $count )
            $session->setCookie( $cookieName, implode( '|', $items ), 90 );
    }

    /**
    * Render a button for expanding and collapsing rows.
    * @param $empty If @c true a blank placeholder is rendered instead.
    */
    public function renderExpandButton( $empty = false )
    {
        if ( !$empty ) {
            echo $this->imageLink( '#', '/common/images/plus.png', $this->tr( 'Expand' ), array( 'class' => null, 'width' => 9, 'height' => 9 ), array( 'class' => 'expand', 'style' => 'display: none; cursor: default' ) );
            echo $this->imageLink( '#', '/common/images/minus.png', $this->tr( 'Collapse' ), array( 'class' => null, 'width' => 9, 'height' => 9 ), array( 'class' => 'collapse', 'style' => 'display: none; cursor: default' ) );
        } else {
            echo $this->image( '/common/images/blank.png', $this->tr( 'Blank' ), array( 'class' => 'blank', 'style' => 'display: none', 'width' => 9, 'height' => 9, 'title' => null ) );
        }
    }

    /**
    * Render the opening tag of a row for use with dynamic selection.
    * @param $rowId Identifier of the row.
    * @param $classes Optional array of custom classes to be added to the row.
    * @param $tag Tag to use (default is 'tr').
    */
    public function renderRowOpen( $rowId, $classes = array(), $tag = 'tr' )
    {
        if ( is_object( $classes ) && is_a( $classes, 'System_Web_ArrayEscaper' ) )
            $classes = $classes->getRawValue();

        $classes[] = 'row-' . $rowId;

        if ( $this->rowId == $rowId )
            $classes[] = 'selected';

        echo $this->buildTag( $tag, array( 'class' => join( ' ', $classes ) ), true );
    }

    /**
    * Render the opening tag of a parent row in a tree.
    * @param $parentId Identifier of the parent row.
    * @param $classes Optional array of custom classes to be added to the row.
    * @param $tag Tag to use (default is 'tr').
    */
    public function renderParentRowOpen( $parentId, $classes = array(), $tag = 'tr' )
    {
        if ( is_object( $classes ) && is_a( $classes, 'System_Web_ArrayEscaper' ) )
            $classes = $classes->getRawValue();

        $classes[] = 'parent';
        $classes[] = 'parent-' . $parentId;

        if ( $this->rowId == null && $this->parentId === $parentId )
            $classes[] = 'selected';

        echo $this->buildTag( $tag, array( 'class' => join( ' ', $classes ) ), true );
    }

    /**
    * Render the opening tag of a child row in a tree.
    * @param $rowId Identifier of the row.
    * @param $parentId Identifier of the parent row.
    * @param $classes Optional array of custom classes to be added to the row.
    * @param $tag Tag to use (default is 'tr').
    */
    public function renderChildRowOpen( $rowId, $parentId, $classes = array(), $tag = 'tr' )
    {
        if ( is_object( $classes ) && is_a( $classes, 'System_Web_ArrayEscaper' ) )
            $classes = $classes->getRawValue();

        $classes[] = 'child';
        $classes[] = 'row-' . $rowId;
        $classes[] = 'parent-' . $parentId;

        if ( $this->rowId === $rowId && $this->parentId === $parentId )
            $classes[] = 'selected';

        echo $this->buildTag( $tag, array( 'class' => join( ' ', $classes ) ), true );
    }

    /**
    * Render the closing tag of a row.
    * @param $tag Tag to use (default is 'tr').
    */
    public function renderRowClose( $tag = 'tr' )
    {
        echo '</' . $tag . ">\n";
    }
}
