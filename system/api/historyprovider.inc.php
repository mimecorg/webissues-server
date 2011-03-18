<?php
/**************************************************************************
* This file is part of the WebIssues Server program
* Copyright (C) 2006 Michał Męciński
* Copyright (C) 2007-2011 WebIssues Team
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
* Extract the history of an issue from the database.
*
* This class can be used to build SQL queries for retrieving the issue history
* which appropriate paging and sorting. Queries can be executed using appropriate
* methods of System_Db_Connection with arguments provided by getQueryArguments().
*
* Issue history consists of changes, comments and files sorted by date.
* Changes made by the same user within a short period of time are grouped
* together.
*/
class System_Api_HistoryProvider
{
    private $issueId = 0;

    private $arguments = null;

    /**
    * Constructor.
    */
    public function __construct()
    {
    }

    /**
    * Set the identifier of the issue.
    */
    public function setIssueId( $issueId )
    {
        $this->issueId = $issueId;
    }

    /**
    * Return a query for calculating the number of items.
    * @param $itemType The type of history items.
    */
    public function generateCountQuery( $itemType = null )
    {
        $this->arguments = array( $this->issueId );

        $query = 'SELECT COUNT(*) FROM {changes} WHERE issue_id = %d';

        if ( $itemType != null ) {
            $this->arguments[] = $itemType;

            $query .= ' AND change_type = %d';
        }

        return $query;
    }

    /**
    * Return a query for extracting item identifiers only.
    */
    public function generateSimpleSelectQuery()
    {
        $this->arguments = array( $this->issueId );

        return 'SELECT change_id FROM {changes} WHERE issue_id = %d';
    }

    /**
    * Return a query for extracting item details.
    * @param $itemType The type of history items.
    */
    public function generateSelectQuery( $itemType = null )
    {
        $this->arguments = array( $this->issueId, System_Const::CommentAdded, System_Const::FileAdded );

        $query = 'SELECT ch.change_id, ch.change_type, ch.stamp_id,'
            . ' sc.stamp_time AS created_date, uc.user_id AS created_user, uc.user_name AS created_by,'
            . ' sm.stamp_time AS modified_date, um.user_id AS modified_user, um.user_name AS modified_by';
        if ( $itemType == null )
            $query .= ', ch.attr_id, ch.value_old, ch.value_new, a.attr_name, a.attr_def, ff.folder_name AS from_folder_name, tf.folder_name AS to_folder_name';
        if ( $itemType == null || $itemType == System_Const::CommentAdded )
            $query .= ', c.comment_text';
        if ( $itemType == null || $itemType == System_Const::FileAdded )
            $query .= ', f.file_name, f.file_size, f.file_descr';
        $query .= ' FROM {changes} AS ch'
            . ' JOIN {stamps} AS sc ON sc.stamp_id = ch.change_id'
            . ' JOIN {users} AS uc ON uc.user_id = sc.user_id'
            . ' JOIN {stamps} AS sm ON sm.stamp_id = ch.stamp_id'
            . ' JOIN {users} AS um ON um.user_id = sm.user_id';
        if ( $itemType == null ) {
            $query .= ' LEFT OUTER JOIN {attr_types} AS a ON a.attr_id = ch.attr_id'
                . ' LEFT OUTER JOIN {folders} AS ff ON ff.folder_id = ch.from_folder_id'
                . ' LEFT OUTER JOIN {folders} AS tf ON tf.folder_id = ch.to_folder_id';
        }
        if ( $itemType == null || $itemType == System_Const::CommentAdded ) {
            if ( $itemType == null )
                $query .= ' LEFT OUTER';
            $query .= ' JOIN {comments} AS c ON c.comment_id = ch.change_id AND ch.change_type = %2d';
        }
        if ( $itemType == null || $itemType == System_Const::FileAdded ) {
            if ( $itemType == null )
                $query .= ' LEFT OUTER';
            $query .= ' JOIN {files} AS f ON f.file_id = ch.change_id AND ch.change_type = %3d';
        }
        $query .= ' WHERE ch.issue_id = %1d';

        return $query;
    }

    /**
    * Return the arguments to be passed when executing the query.
    */
    public function getQueryArguments()
    {
        return $this->arguments;
    }

    /**
    * Return the sorting order specifier. Items are sorted by creation date
    * from oldest to newest.
    */
    public function getOrderBy()
    {
        return 'ch.change_id ASC';
    }

    /**
    * Process a page of items to group changes together.
    * @param $page The page of rows returned from the database.
    * @return Items with changes made by the same user within a short period
    * of time grouped together.
    */
    public function processPage( $page )
    {
        $items = array();

        $change = null;

        foreach ( $page as $row ) {
            if ( $row[ 'change_type' ] <= System_Const::ValueChanged && $change != null ) {
                if ( $row[ 'created_user' ] == $change[ 'changes' ][ 0 ][ 'created_user' ]
                     && ( $row[ 'created_date' ] - $change[ 'changes' ][ 0 ][ 'created_date' ] ) < 180 ) {
                    $change[ 'changes' ][] = $row;
                    continue;
                }
            }

            if ( $change != null ) {
                $items[ $change[ 'change_id' ] ] = $change;
                $change = null;
            }

            if ( $row[ 'change_type' ] <= System_Const::ValueChanged ) {
                $change = $row;
                $change[ 'changes' ][ 0 ] = $row;
            } else {
                $items[ $row[ 'change_id' ] ] = $row;
            }
        }

        if ( $change != null )
            $items[ $change[ 'change_id' ] ] = $change;

        return $items;
    }
}
