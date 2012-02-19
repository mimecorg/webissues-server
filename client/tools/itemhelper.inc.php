<?php
/**************************************************************************
* This file is part of the WebIssues Server program
* Copyright (C) 2006 Michał Męciński
* Copyright (C) 2007-2012 WebIssues Team
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

class Client_Tools_ItemHelper extends System_Web_Base
{
    public function __construct()
    {
        parent::__construct();
    }

    public function findItem( $itemId )
    {
        $issueManager = new System_Api_IssueManager();

        $issueId = $issueManager->findItem( $itemId );

        if ( $itemId == $issueId )
            $this->response->redirect( $this->appendQueryString( '/client/index.php', array( 'issue' => $issueId ) ) );

        $issue = $issueManager->getIssue( $issueId );

        $historyProvider = new System_Api_HistoryProvider();
        $historyProvider->setIssueId( $issueId );

        $connection = System_Core_Application::getInstance()->getConnection();

        $query = $historyProvider->generateSimpleSelectQuery();
        $history = $connection->queryPageArgs( $query, $historyProvider->getOrderBy(), System_Const::INT_MAX, 0, $historyProvider->getQueryArguments() );

        $index = -1;
        foreach ( $history as $i => $item ) {
            if ( $item[ 'change_id' ] == $itemId ) {
                $index = $i;
                break;
            }
        }

        if ( $index < 0 )
            throw new System_Api_Error( System_Api_Error::ItemNotFound );

        $page = floor( ( $index + 1 ) / 20 ) + 1;
        if ( $page == 1 )
            $page = null;

        $this->response->redirect( $this->appendQueryString( '/client/index.php', array( 'issue' => $issueId, 'hpg' => $page ) ) . '#item' . $itemId );
    }
}
