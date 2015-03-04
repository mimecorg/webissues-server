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

class Client_Projects_Access extends System_Web_Component
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
        $this->view->setSlot( 'page_title', $this->tr( 'User Access' ) );

        $breadcrumbs = new Common_Breadcrumbs( $this );
        $breadcrumbs->initialize( Common_Breadcrumbs::ProjectMembers, $this->project );

        $userManager = new System_Api_UserManager();
        $userId = (int)$this->request->getQueryString( 'user' );
        $this->user = $userManager->getUser( $userId );

        $principal = System_Api_Principal::getCurrent();
        if ( $userId == $principal->getUserId() && $principal->getUserAccess() != System_Const::AdministratorAccess )
            throw new System_Api_Error( System_Api_Error::AccessDenied );

        $member = $userManager->getMember( $this->user, $this->project );

        $this->form = new System_Web_Form( 'projects', $this );
        $this->form->addField( 'accessLevel', $member[ 'project_access' ] );

        $this->accessLevels = array(
            System_Const::NormalAccess => $this->tr( 'Regular member' ),
            System_Const::AdministratorAccess => $this->tr( 'Project administrator' ) );

        $this->form->addItemsRule( 'accessLevel', $this->accessLevels );

        if ( $this->form->loadForm() ) {
            if ( $this->form->isSubmittedWith( 'cancel' ) )
                $this->response->redirect( $breadcrumbs->getParentUrl() );

            $this->form->validate();

            if ( $this->form->isSubmittedWith( 'ok' ) && !$this->form->hasErrors() ) {
                if ( $this->accessLevel != $member[ 'project_access' ] )
                    $userManager->grantMember( $this->user, $this->project, $this->accessLevel );
                $this->response->redirect( $breadcrumbs->getParentUrl() );
            }
        }
    }
}

System_Bootstrap::run( 'Common_Application', 'Client_Projects_Access' );
