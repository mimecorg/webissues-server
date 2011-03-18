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

class Client_ProjectsTree extends System_Web_Component
{
    protected function __construct()
    {
        parent::__construct();
    }

    protected function execute()
    {
        $projectManager = new System_Api_ProjectManager();

        $issueId = (int)$this->request->getQueryString( 'issue' );
        if ( $issueId ) {
            $issueManager = new System_Api_IssueManager();
            $issue = $issueManager->getIssue( $issueId );
            $folderId = $issue[ 'folder_id' ];
            $projectId = $issue[ 'project_id' ];
        } else {
            $folderId = (int)$this->request->getQueryString( 'folder' );
            if ( $folderId ) {
                $folder = $projectManager->getFolder( $folderId );
                $projectId = $folder[ 'project_id' ];
            } else {
                $projectId = (int)$this->request->getQueryString( 'project' );
            }
        }

        $projects = $projectManager->getProjects();
        $folders = $projectManager->getFolders();

        $this->grid = new System_Web_Grid();
        $this->grid->setSelection( $folderId, $projectId );

        $this->projects = array();
        foreach ( $projects as $project ) {
            $project[ 'folders' ] = array();
            $this->projects[ $project[ 'project_id' ] ] = $project;
        }
        foreach ( $folders as $folder )
            $this->projects[ $folder[ 'project_id' ] ][ 'folders' ][ $folder[ 'folder_id' ] ] = $folder;

        $emptyProjects = array();
        foreach ( $this->projects as $id => $project ) {
            if ( empty( $project[ 'folders' ] ) )
                $emptyProjects[] = $id;
        }
        $this->grid->removeExpandCookieIds( 'wi_projects', $emptyProjects );

        $javaScript = new System_Web_JavaScript( $this->view );
        $javaScript->registerExpandCookie( 'wi_projects' );

        $this->toolBar = new System_Web_ToolBar();
        $this->toolBar->setFilterParameters( array() );

        if ( System_Api_Principal::getCurrent()->isAdministrator() )
            $this->toolBar->addFixedCommand( '/client/projects/addproject.php', '/common/images/project-new-16.png', $this->tr( 'Add Project' ) );
    }
}
