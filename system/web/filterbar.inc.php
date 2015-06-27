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
* Helper class for rendering a filter bar.
*
* A filter bar constists of a number mutually exclusive links.
*/
class System_Web_FilterBar extends System_Web_Base
{
    private $param = null;
    private $value = null;
    private $mergeParams = array();

    /**
    * Constructor
    */
    public function __construct()
    {
        parent::__construct();
    }

    /**
    * Set the name of the parameter used to pass selected filter.
    */
    public function setParameter( $param )
    {
        $this->param = $param;
        $this->value = $this->request->getQueryString( $param );
    }

    /**
    * Set query parameters to be merged with URLs of filter links.
    */
    public function setMergeParameters( $params )
    {
        $this->mergeParams = $params;
    }

    /**
    * Render the no filter option.
    * @param $text Text of the no filter option.
    */
    public function renderNoFilter( $text )
    {
        if ( $this->value == null )
            echo $text;
        else
            echo $this->link( $this->mergeQueryString( WI_SCRIPT_URL, array_merge( array( $this->param => null ), $this->mergeParams ) ), $text );
    }

    /**
    * Render the filter options.
    * @param $filters An array of filter options.
    */
    public function renderFilters( $filters )
    {
        foreach ( $filters as $value => $text ) {
            echo ' | ';
            if ( $value == $this->value )
                echo $text;
            else
                echo $this->link( $this->mergeQueryString( WI_SCRIPT_URL, array_merge( array( $this->param => $value ), $this->mergeParams ) ), $text );
        }
    }

    /**
    * Render filter options with a default filter.
    * @param $filters An array of filter options.
    * @param $defaultFilter The default filter.
    */
    public function renderDefaultFilters( $filters, $defaultFilter )
    {
        $first = true;
        foreach ( $filters as $value => $text ) {
            if ( !$first )
                echo ' | ';
            if ( $value == $this->value || $value == $defaultFilter && $this->value == null )
                echo $text;
            else if ( $value == $defaultFilter )
                echo $this->link( $this->mergeQueryString( WI_SCRIPT_URL, array_merge( array( $this->param => null ), $this->mergeParams ) ), $text );
            else
                echo $this->link( $this->mergeQueryString( WI_SCRIPT_URL, array_merge( array( $this->param => $value ), $this->mergeParams ) ), $text );
            $first = false;
        }
    }

    /**
    * Render filter options with a default filter as list items.
    * @param $filters An array of filter options.
    * @param $defaultFilter The default filter.
    */
    public function renderListItems( $filters, $defaultFilter )
    {
        foreach ( $filters as $value => $text ) {
            if ( $value == $this->value || $value == $defaultFilter && $this->value == null )
                echo '<li>' . $text . '</li>';
            else if ( $value == $defaultFilter )
                echo '<a href="' . $this->url( $this->mergeQueryString( WI_SCRIPT_URL, array_merge( array( $this->param => null ), $this->mergeParams ) ) ) . '"><li>' . $text . '</li></a>';
            else
                echo '<a href="' . $this->url( $this->mergeQueryString( WI_SCRIPT_URL, array_merge( array( $this->param => $value ), $this->mergeParams ) ) ) . '"><li>' . $text . '</li></a>';
        }
    }
}
