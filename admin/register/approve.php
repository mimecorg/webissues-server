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

class Admin_Register_Approve extends System_Web_Component
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

        $breadcrumbs = new Common_Breadcrumbs( $this );
        $breadcrumbs->initialize( Common_Breadcrumbs::RegistrationRequests );

        $projectManager = new System_Api_ProjectManager();
        $projects = $projectManager->getProjects();
        $this->allProjects = array();
        foreach ( $projects as $project )
            $this->allProjects[ $project[ 'project_id' ] ] = $project;

        $this->form = new System_Web_Form( 'register', $this );
        foreach ( $this->allProjects as $projectId => $project )
            $this->form->addField( 'project' . $projectId, false );

        $this->view->setDecoratorClass( 'Common_FixedBlock' );
        $this->view->setSlot( 'page_title', $this->tr( 'Approve Request' ) );

        if ( $this->form->loadForm() ) {
            if ( $this->form->isSubmittedWith( 'cancel' ) )
                $this->response->redirect( $breadcrumbs->getParentUrl() );

            $this->form->validate();

            if ( $this->form->isSubmittedWith( 'ok' ) && !$this->form->hasErrors() ) {
                $userId = $registrationManager->approveRequest( $this->register );

                $userManager = new System_Api_UserManager();
                $user = $userManager->getUser( $userId );

                foreach ( $this->allProjects as $projectId => $project ) {
                    $fieldName = 'project' . $projectId;
                    if ( $this->$fieldName == 1 )
                        $userManager->grantMember( $user, $project, System_Const::NormalAccess );
                }

                $mail = System_Web_Component::createComponent( 'Common_Mail_Approve', null, $this->register );
                $body = $mail->run();
                $subject = $mail->getView()->getSlot( 'subject' );

                $engine = new System_Mail_Engine();
                $engine->loadSettings();
                $engine->send( $this->register[ 'user_email' ], $this->register[ 'user_name' ], $subject, $body );

                $this->response->redirect( $breadcrumbs->getParentUrl() );
            }
        }

        $javaScript = new System_Web_JavaScript( $this->view );
        $javaScript->registerCheckOnOff( '#project-select', '#project-choices :checkbox', true );
        $javaScript->registerCheckOnOff( '#project-unselect', '#project-choices :checkbox', false );
    }
}

System_Bootstrap::run( 'Common_Application', 'Admin_Register_Approve' );
