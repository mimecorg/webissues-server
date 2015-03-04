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

class Common_Tools_Password extends System_Web_Component
{
    protected function __construct()
    {
        parent::__construct();
    }

    protected function execute()
    {
        $this->view->setDecoratorClass( 'Common_FixedBlock' );
        $this->view->setSlot( 'page_title', $this->tr( 'Change Password' ) );

        $helper = new Common_Tools_Helper();
        $breadcrumbs = $helper->getBreadcrumbs( $this );

        $this->user = $helper->getUser();
        if ( $this->user != null )
            $this->isSelf = $this->user[ 'user_id' ] == System_Api_Principal::getCurrent()->getUserId();
        else
            $this->isSelf = true;

        $this->form = new System_Web_Form( 'password', $this );
        if ( $this->isSelf )
            $this->form->addField( 'password' );
        $this->form->addField( 'newPassword' );
        $this->form->addField( 'newPasswordConfirm' );
        if ( !$this->isSelf )
            $this->form->addField( 'isTemp' );

        if ( $this->isSelf )
            $this->form->addTextRule( 'password', System_Const::PasswordMaxLength );
        $this->form->addTextRule( 'newPassword', System_Const::PasswordMaxLength );
        $this->form->addTextRule( 'newPasswordConfirm', System_Const::PasswordMaxLength );
        $this->form->addPasswordRule( 'newPasswordConfirm', 'newPassword' );

        if ( $this->form->loadForm() ) {
            if ( $this->form->isSubmittedWith( 'cancel' ) )
                $this->response->redirect( $breadcrumbs->getParentUrl() );

            $this->form->validate();

            if ( $this->form->isSubmittedWith( 'ok' ) && !$this->form->hasErrors() ) {
                if ( $this->submit() )
                    $this->response->redirect( $breadcrumbs->getParentUrl() );
            }
        }
    }

    private function submit()
    {
        $userManager = new System_Api_UserManager();
        try {
            if ( $this->isSelf )
                $userManager->changePassword( $this->password, $this->newPassword );
            else
                $userManager->setPassword( $this->user, $this->newPassword, $this->isTemp );
            return true;
        } catch ( System_Api_Error $ex ) {
            if ( $this->isSelf && $ex->getMessage() == System_Api_Error::IncorrectLogin )
                $this->form->getErrorHelper()->handleError( 'password', $ex );
            else
                $this->form->getErrorHelper()->handleError( 'newPassword', $ex );
            return false;
        }
    }
}
