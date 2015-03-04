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

class Client_Projects_Index extends System_Web_Component
{
    protected function __construct()
    {
        parent::__construct();
    }

    protected function execute()
    {
        $this->view->setDecoratorClass( 'Common_FixedBlock' );
        $this->view->setSlot( 'page_title', $this->tr( 'Manage Projects' ) );

        $this->form = new System_Web_Form( 'projects', $this );
        if ( $this->form->loadForm() )
            $this->response->redirect( '/client/index.php' );

        $projectManager = new System_Api_ProjectManager();

        $preferencesManager = new System_Api_PreferencesManager();
        $pageSize = $preferencesManager->getPreferenceOrSetting( 'project_page_size' );

        $this->grid = new System_Web_Grid();
        $this->grid->setPageSize( $pageSize );
        $this->grid->setMergeParameters( array( 'project' => null, 'folder' => null ) );

        $this->grid->setColumns( $projectManager->getProjectsColumns() );
        $this->grid->setDefaultSort( 'name', System_Web_Grid::Ascending );
        $this->grid->setRowsCount( $projectManager->getProjectsCount( System_Api_ProjectManager::RequireAdministrator ) );

        $projects = $projectManager->getProjectsPage( $this->grid->getOrderBy(), $this->grid->getPageSize(), $this->grid->getOffset(), System_Api_ProjectManager::RequireAdministrator );
        $folders = $projectManager->getFoldersForProjects( $projects );

        $accessLevels = array(
            0 => $this->tr( 'Regular project' ),
            1 => $this->tr( 'Public project' )
        );

        $this->projects = array();
        foreach ( $projects as $project ) {
            $project[ 'folders' ] = array();
            $project[ 'project_access' ] = $accessLevels[ $project[ 'is_public' ] ];
            $this->projects[ $project[ 'project_id' ] ] = $project;
        }
        foreach ( $folders as $folder )
            $this->projects[ $folder[ 'project_id' ] ][ 'folders' ][ $folder[ 'folder_id' ] ] = $folder;

        $emptyProjects = array();
        foreach ( $this->projects as $id => $project ) {
            if ( empty( $project[ 'folders' ] ) )
                $emptyProjects[] = $id;
        }

        $folderId = (int)$this->request->getQueryString( 'folder' );
        if ( $folderId ) {
            $folder = $projectManager->getFolder( $folderId );
            $projectId = $folder[ 'project_id' ];
        } else {
            $projectId = (int)$this->request->getQueryString( 'project' );
        }

        $this->grid->setSelection( $folderId, $projectId );

        $this->toolBar = new System_Web_ToolBar();
        $this->toolBar->setParameters( 'folder', 'project' );
        $this->toolBar->setSelection( $folderId, $projectId );

        $this->isAdministrator = System_Api_Principal::getCurrent()->isAdministrator();

        if ( $this->isAdministrator )
            $this->toolBar->addFixedCommand( '/client/projects/addproject.php', '/common/images/project-new-16.png', $this->tr( 'Add Project' ) );
        $this->toolBar->addItemCommand( '/client/projects/addfolder.php', '/common/images/folder-new-16.png', $this->tr( 'Add Folder' ) );
        if ( $this->isAdministrator ) {
            $this->toolBar->addParentCommand( '/client/projects/renameproject.php', '/common/images/edit-rename-16.png', $this->tr( 'Rename Project' ) );
            $this->toolBar->addParentCommand( '/client/projects/archiveproject.php', '/common/images/archive-16.png', $this->tr( 'Archive Project' ) );
            $this->toolBar->addParentCommand( '/client/projects/deleteproject.php', '/common/images/edit-delete-16.png', $this->tr( 'Delete Project' ) );
        }
        $this->toolBar->addChildCommand( '/client/projects/renamefolder.php', '/common/images/edit-rename-16.png', $this->tr( 'Rename Folder' ) );
        $this->toolBar->addChildCommand( '/client/projects/movefolder.php', '/common/images/folder-move-16.png', $this->tr( 'Move Folder' ) );
        $this->toolBar->addChildCommand( '/client/projects/deletefolder.php', '/common/images/edit-delete-16.png', $this->tr( 'Delete Folder' ) );
        $this->toolBar->addParentCommand( '/client/projects/members.php', '/common/images/edit-access-16.png', $this->tr( 'Manage Permissions' ) );

        $javaScript = new System_Web_JavaScript( $this->view );
        $javaScript->registerExpandCookie( 'wi_projects' );
        $javaScript->registerSelection( $this->toolBar );

        $this->grid->removeExpandCookieIds( 'wi_projects', $emptyProjects );
    }
}

System_Bootstrap::run( 'Common_Application', 'Client_Projects_Index' );
