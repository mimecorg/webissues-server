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

class Admin_Users_Projects extends System_Web_Component
{
    protected function __construct()
    {
        parent::__construct();
    }

    protected function execute()
    {
        $userManager = new System_Api_UserManager();
        $userId = (int)$this->request->getQueryString( 'id' );
        $this->user = $userManager->getUser( $userId );

        $this->view->setDecoratorClass( 'Common_FixedBlock' );
        $this->view->setSlot( 'page_title', $this->tr( 'Manage Permissions' ) );

        $breadcrumbs = new Common_Breadcrumbs( $this );
        $breadcrumbs->initialize( Common_Breadcrumbs::UserAccounts );

        $this->form = new System_Web_Form( 'projects', $this );
        if ( $this->form->loadForm() )
            $this->response->redirect( $breadcrumbs->getParentUrl() );

        $this->grid = new System_Web_Grid();
        $this->grid->setPageSize( 20 );
        $this->grid->setParameters( 'ppage', 'porder', 'psort' );
        $this->grid->setMergeParameters( array( 'project' => null ) );

        $this->grid->setColumns( $userManager->getUserProjectsColumns() );
        $this->grid->setDefaultSort( 'name', System_Web_Grid::Ascending );
        $this->grid->setRowsCount( $userManager->getUserProjectsCount( $this->user ) );

        $page = $userManager->getUserProjectsPage( $this->user, $this->grid->getOrderBy(), $this->grid->getPageSize(), $this->grid->getOffset() );

        $systemLevels = array(
            System_Const::NoAccess => $this->tr( 'Disabled' ),
            System_Const::NormalAccess => $this->tr( 'Regular user' ),
            System_Const::AdministratorAccess => $this->tr( 'System administrator' ) );

        $this->systemLevel = $systemLevels[ $this->user[ 'user_access' ] ];

        $principal = System_Api_Principal::getCurrent();
        if ( $userId != $principal->getUserId() )
            $this->canChangeAccess = true;

        $accessLevels = array(
            System_Const::NormalAccess => $this->tr( 'Regular member' ),
            System_Const::AdministratorAccess => $this->tr( 'Project administrator' ) );

        $this->projects = array();
        foreach ( $page as $row ) {
            $row[ 'access_level' ] = $accessLevels[ $row[ 'project_access' ] ];
            $this->projects[ $row[ 'project_id' ] ] = $row;
        }

        $selectedId = (int)$this->request->getQueryString( 'project' );

        $this->grid->setSelection( $selectedId );

        $this->toolBar = new System_Web_ToolBar();
        $this->toolBar->setParameters( 'project' );

        $this->toolBar->setSelection( $selectedId );

        $this->toolBar->addFixedCommand( '/admin/users/addprojects.php', '/common/images/project-new-16.png', $this->tr( 'Add Projects' ) );
        $this->toolBar->addItemCommand( '/admin/users/projectaccess.php', '/common/images/edit-access-16.png', $this->tr( 'Change Access' ) );
        $this->toolBar->addItemCommand( '/admin/users/removeproject.php', '/common/images/edit-delete-16.png', $this->tr( 'Remove Project' ) );

        $javaScript = new System_Web_JavaScript( $this->view );
        $javaScript->registerSelection( $this->toolBar );
    }
}

System_Bootstrap::run( 'Common_Application', 'Admin_Users_Projects' );
