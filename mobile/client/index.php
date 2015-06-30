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

require_once( '../../system/bootstrap.inc.php' );

class Mobile_Client_Index extends System_Web_Component
{
    protected function __construct()
    {
        parent::__construct();
    }

    protected function execute()
    {
        $this->view->setDecoratorClass( 'Mobile_ThreePanes' );

        $itemId = (int)$this->request->getQueryString( 'item' );
        if ( $itemId ) {
            $helper = new Common_Tools_ItemHelper();
            $helper->findItem( $itemId );
        }

        $issueManager = new System_Api_IssueManager();
        $projectManager = new System_Api_ProjectManager();

        $issueId = (int)$this->request->getQueryString( 'issue' );
        $folderId = (int)$this->request->getQueryString( 'folder' );
        $projectId = (int)$this->request->getQueryString( 'project' );
        $typeId = (int)$this->request->getQueryString( 'type' );

        $this->view->setSlot( 'page_title', $this->tr( 'Web Client' ) );

        $this->leftPaneClass = 'Mobile_Client_ProjectsTree';
        if ( $folderId || $issueId || $typeId )
            $this->topPaneClass = 'Mobile_Client_IssuesList';
        else if ( $projectId )
            $this->topPaneClass = 'Mobile_Client_Project';

        if ( $issueId )
            $this->bottomPaneClass = 'Mobile_Client_IssueDetails';
    }
}

System_Bootstrap::run( 'Common_Application', 'Mobile_Client_Index' );
