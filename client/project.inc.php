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

class Client_Project extends System_Web_Component
{
    protected function __construct()
    {
        parent::__construct();
    }

    protected function execute()
    {
        $projectManager = new System_Api_ProjectManager();
        $projectId = (int)$this->request->getQueryString( 'project' );
        $project = $projectManager->getProject( $projectId );

        $this->projectName = $project[ 'project_name' ];

        $this->view->setSlot( 'page_title', $project[ 'project_name' ] );

        if ( $project[ 'project_access' ] == System_Const::AdministratorAccess ) {
            $this->toolBar = new System_Web_ToolBar();
            $this->toolBar->addFixedCommand( '/client/projects/addfolder.php', '/common/images/folder-new-16.png', $this->tr( 'Add Folder' ) );
            $this->toolBar->addFixedCommand( '/client/projects/renameproject.php', '/common/images/edit-rename-16.png', $this->tr( 'Rename Project' ) );
            $this->toolBar->addFixedCommand( '/client/projects/deleteproject.php', '/common/images/edit-delete-16.png', $this->tr( 'Delete Project' ) );
            $this->toolBar->addFixedCommand( '/client/projects/members.php', '/common/images/view-members-16.png', $this->tr( 'Project Members' ) );
        }
    }
}
