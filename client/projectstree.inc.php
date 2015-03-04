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

class Client_ProjectsTree extends System_Web_Component
{
    protected function __construct()
    {
        parent::__construct();
    }

    protected function execute()
    {
        $projectManager = new System_Api_ProjectManager();

        $typeId = (int)$this->request->getQueryString( 'type' );
        if ( !$typeId ) {
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
        }

        $preferencesManager = new System_Api_PreferencesManager();
        $pageSize = $preferencesManager->getPreferenceOrSetting( 'project_page_size' );

        $this->grid = new System_Web_Grid();
        $this->grid->setPageSize( $pageSize );
        $this->grid->setParameters( 'ppg', 'po', 'ps' );
        if ( $typeId )
            $this->grid->setSelection( $typeId, 'T' );
        else
            $this->grid->setSelection( $folderId, $projectId );

        $this->grid->setColumns( $projectManager->getProjectsColumns() );
        $this->grid->setDefaultSort( 'name', System_Web_Grid::Ascending );
        $this->grid->setRowsCount( $projectManager->getProjectsCount() );

        $projects = $projectManager->getProjectsPage( $this->grid->getOrderBy(), $this->grid->getPageSize(), $this->grid->getOffset() );
        $folders = $projectManager->getFoldersForProjects( $projects );

        $this->projects = array();
        foreach ( $projects as $project ) {
            $project[ 'folders' ] = array();
            $this->projects[ $project[ 'project_id' ] ] = $project;
        }
        foreach ( $folders as $folder )
            $this->projects[ $folder[ 'project_id' ] ][ 'folders' ][ $folder[ 'folder_id' ] ] = $folder;

        $emptyProjects = array();
        $anyProjectAdmin = false;
        foreach ( $this->projects as $id => $project ) {
            if ( empty( $project[ 'folders' ] ) )
                $emptyProjects[] = $id;
            if ( $project[ 'project_access' ] == System_Const::AdministratorAccess )
                $anyProjectAdmin = true;
        }
        $this->grid->removeExpandCookieIds( 'wi_projects', $emptyProjects );

        $typeManager = new System_Api_TypeManager();
        $types = $typeManager->getAvailableIssueTypes();

        $this->types = array();
        foreach ( $types as $type )
            $this->types[ $type[ 'type_id' ] ] = $type;

        $javaScript = new System_Web_JavaScript( $this->view );
        $javaScript->registerExpandCookie( 'wi_projects' );

        $this->toolBar = new System_Web_ToolBar();
        $this->toolBar->setFilterParameters( array() );

        if ( System_Api_Principal::getCurrent()->isAdministrator() || $anyProjectAdmin )
            $this->toolBar->addFixedCommand( '/client/projects/index.php', '/common/images/project-admin-16.png', $this->tr( 'Manage Projects' ) );
    }
}
