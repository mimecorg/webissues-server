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

class Admin_Register_Reject extends System_Web_Component
{
    protected function __construct()
    {
        parent::__construct();
    }

    protected function execute()
    {
        $registrationManager = new System_Api_RegistrationManager();
        $requestId = (int)$this->request->getQueryString( 'id' );
        $this->register = $registrationManager->getRequest( $requestId );

        $this->view->setDecoratorClass( 'Common_MessageBlock' );
        $this->view->setSlot( 'page_title', $this->tr( 'Reject Request' ) );

        $breadcrumbs = new Common_Breadcrumbs( $this );
        $breadcrumbs->initialize( Common_Breadcrumbs::RegistrationRequests );

        $this->form = new System_Web_Form( 'register', $this );

        if ( $this->form->loadForm() ) {
            if ( $this->form->isSubmittedWith( 'cancel' ) )
                $this->response->redirect( $breadcrumbs->getParentUrl() );

            if ( $this->form->isSubmittedWith( 'ok' ) ) {
                $registrationManager->rejectRequest( $this->register );

                $mail = System_Web_Component::createComponent( 'Common_Mail_Reject', null, $this->register );
                $body = $mail->run();
                $subject = $mail->getView()->getSlot( 'subject' );

                $engine = new System_Mail_Engine();
                $engine->loadSettings();
                $engine->send( $this->register[ 'user_email' ], $this->register[ 'user_name' ], $subject, $body );

                $this->response->redirect( $breadcrumbs->getParentUrl() );
            }
        }
    }
}

System_Bootstrap::run( 'Common_Application', 'Admin_Register_Reject' );
