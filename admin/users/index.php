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

require_once( '../../system/bootstrap.inc.php' );

class Admin_Users_Index extends System_Web_Component
{
    protected function __construct()
    {
        parent::__construct();
    }

    protected function execute()
    {
        $this->view->setDecoratorClass( 'Common_FixedBlock' );
        $this->view->setSlot( 'page_title', $this->tr( 'User Accounts' ) );

        $this->grid = new System_Web_Grid();
        $this->grid->setPageSize( 20 );
        $this->grid->setMergeParameters( array( 'id' => null ) );

        $userManager = new System_Api_UserManager();
        $this->grid->setColumns( $userManager->getUsersColumns() );
        $this->grid->setDefaultSort( 'name', System_Web_Grid::Ascending );
        $this->grid->setRowsCount( $userManager->getUsersCount() );

        $page = $userManager->getUsersPage( $this->grid->getOrderBy(), $this->grid->getPageSize(), $this->grid->getOffset() );

        $accessLevels = array(
            System_Const::NoAccess => $this->tr( 'Disabled' ),
            System_Const::NormalAccess => $this->tr( 'Regular user' ),
            System_Const::AdministratorAccess => $this->tr( 'System administrator' ) );
 
        $principal = System_Api_Principal::getCurrent();

        $this->users = array();
        foreach ( $page as $row ) {
            $row[ 'access_level' ] = $accessLevels[ $row[ 'user_access' ] ];
            $row[ 'classes' ] = array();
            if ( $row[ 'user_id' ] != $principal->getUserId() )
                $row[ 'classes' ][] = 'access';
            $this->users[ $row[ 'user_id' ] ] = $row;
        }

        $selectedId = (int)$this->request->getQueryString( 'id' );

        $this->grid->setSelection( $selectedId );

        $this->toolBar = new System_Web_ToolBar();

        $classes = array();
        if ( $selectedId != $principal->getUserId() )
            $classes[] = 'access';
        $this->toolBar->setSelection( $selectedId, null, $classes );

        $this->toolBar->addFixedCommand( '/admin/users/add.php', '/common/images/user-new-16.png', $this->tr( 'Add User' ) );
        $this->toolBar->addItemCommand( '/admin/users/access.php', '/common/images/edit-access-16.png', $this->tr( 'Change Access' ), array( 'access' ) );
        $this->toolBar->addItemCommand( '/admin/users/password.php', '/common/images/edit-password-16.png', $this->tr( 'Change Password' ) );
        $this->toolBar->addItemCommand( '/admin/users/rename.php', '/common/images/edit-rename-16.png', $this->tr( 'Rename User' ) );
        $this->toolBar->addItemCommand( '/admin/users/preferences.php', '/common/images/preferences-16.png', $this->tr( 'User Preferences' ) );

        $javaScript = new System_Web_JavaScript( $this->view );
        $javaScript->registerSelection( $this->toolBar );
    }
}

System_Bootstrap::run( 'Common_Application', 'Admin_Users_Index' );