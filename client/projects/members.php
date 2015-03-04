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

class Client_Projects_Members extends System_Web_Component
{
    protected function __construct()
    {
        parent::__construct();
    }

    protected function execute()
    {
        $projectManager = new System_Api_ProjectManager();
        $projectId = (int)$this->request->getQueryString( 'project' );
        $this->project = $projectManager->getProject( $projectId, System_Api_ProjectManager::RequireAdministrator );

        $this->view->setDecoratorClass( 'Common_FixedBlock' );
        $this->view->setSlot( 'page_title', $this->tr( 'Manage Permissions' ) );

        $breadcrumbs = new Common_Breadcrumbs( $this );
        $breadcrumbs->initialize( Common_Breadcrumbs::ManageProjects );

        $this->form = new System_Web_Form( 'members', $this );
        if ( $this->form->loadForm() )
            $this->response->redirect( $breadcrumbs->getParentUrl() );

        $userManager = new System_Api_UserManager();

        $this->grid = new System_Web_Grid();
        $this->grid->setPageSize( 20 );
        $this->grid->setParameters( 'mpage', 'morder', 'msort' );
        $this->grid->setMergeParameters( array( 'user' => null ) );

        $this->grid->setColumns( $userManager->getMembersColumns() );
        $this->grid->setDefaultSort( 'name', System_Web_Grid::Ascending );
        $this->grid->setRowsCount( $userManager->getMembersCount( $this->project ) );

        $page = $userManager->getMembersPage( $this->project, $this->grid->getOrderBy(), $this->grid->getPageSize(), $this->grid->getOffset() );

        $systemLevels = array(
            0 => $this->tr( 'Regular project' ),
            1 => $this->tr( 'Public project' )
        );

        $this->systemLevel = $systemLevels[ $this->project[ 'is_public' ] ];

        $accessLevels = array(
            System_Const::NormalAccess => $this->tr( 'Regular member' ),
            System_Const::AdministratorAccess => $this->tr( 'Project administrator' ) );

        $principal = System_Api_Principal::getCurrent();

        $this->members = array();
        foreach ( $page as $row ) {
            $row[ 'access_level' ] = $accessLevels[ $row[ 'project_access' ] ];
            $row[ 'classes' ] = array();
            if ( $row[ 'user_id' ] != $principal->getUserId() || $principal->getUserAccess() == System_Const::AdministratorAccess )
                $row[ 'classes' ][] = 'access';
            $this->members[ $row[ 'user_id' ] ] = $row;
        }

        $selectedId = (int)$this->request->getQueryString( 'user' );

        $this->grid->setSelection( $selectedId );

        $this->toolBar = new System_Web_ToolBar();
        $this->toolBar->setParameters( 'user' );

        $classes = array();
        if ( $selectedId != $principal->getUserId() || $principal->getUserAccess() == System_Const::AdministratorAccess )
            $classes[] = 'access';
        $this->toolBar->setSelection( $selectedId, null, $classes );

        if ( $this->project[ 'project_access' ] == System_Const::AdministratorAccess ) {
            $this->toolBar->addFixedCommand( '/client/projects/addmembers.php', '/common/images/user-new-16.png', $this->tr( 'Add Members' ) );
            $this->toolBar->addItemCommand( '/client/projects/access.php', '/common/images/edit-access-16.png', $this->tr( 'Change Access' ), array( 'access' ) );
            $this->toolBar->addItemCommand( '/client/projects/removemember.php', '/common/images/edit-delete-16.png', $this->tr( 'Remove Member' ), array( 'access' ) );
        }

        $javaScript = new System_Web_JavaScript( $this->view );
        $javaScript->registerSelection( $this->toolBar );
    }
}

System_Bootstrap::run( 'Common_Application', 'Client_Projects_Members' );
