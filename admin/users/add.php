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

class Admin_Users_Add extends System_Web_Component
{
    protected function __construct()
    {
        parent::__construct();
    }

    protected function execute()
    {
        $this->view->setDecoratorClass( 'Common_FixedBlock' );
        $this->view->setSlot( 'page_title', $this->tr( 'Add User' ) );

        $breadcrumbs = new Common_Breadcrumbs( $this );
        $breadcrumbs->initialize( Common_Breadcrumbs::UserAccounts );

        $this->form = new System_Web_Form( 'users', $this );
        $this->form->addField( 'userName', '' );
        $this->form->addField( 'login', '' );
        $this->form->addField( 'password', '' );
        $this->form->addField( 'passwordConfirm', '' );
        $this->form->addField( 'isTemp', false );

        $this->form->addTextRule( 'userName', System_Const::NameMaxLength );
        $this->form->addTextRule( 'login', System_Const::LoginMaxLength );
        $this->form->addTextRule( 'password', System_Const::PasswordMaxLength );
        $this->form->addTextRule( 'passwordConfirm', System_Const::PasswordMaxLength );
        $this->form->addPasswordRule( 'passwordConfirm', 'password' );

        if ( $this->form->loadForm() ) {
            if ( $this->form->isSubmittedWith( 'cancel' ) )
                $this->response->redirect( $breadcrumbs->getParentUrl() );

            $this->form->validate();

            if ( $this->form->isSubmittedWith( 'ok' ) && !$this->form->hasErrors() ) {
                $this->submit();
                if ( !$this->form->hasErrors() )
                    $this->response->redirect( $breadcrumbs->getParentUrl() );
            }
        }
    }
    
    private function submit()
    {
        $userManager = new System_Api_UserManager();
        try {
            $userManager->addUser( $this->login, $this->userName, $this->password, $this->isTemp );
        } catch ( System_Api_Error $ex ) {
            $this->form->getErrorHelper()->handleError( 'userName', $ex );
        }
    }
}

System_Bootstrap::run( 'Common_Application', 'Admin_Users_Add' );
