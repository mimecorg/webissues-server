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

class Client_Projects_AddMembers extends System_Web_Component
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

        $breadcrumbs = new System_Web_Breadcrumbs( $this );
        $breadcrumbs->initialize( System_Web_Breadcrumbs::ProjectMembers, $this->project );

        $userManager = new System_Api_UserManager();

        $this->members = array();
        $users = $userManager->getUsers();
        foreach ( $users as $user ) {
            if ( $user[ 'user_access' ] != System_Const::NoAccess )
                $this->members[ $user[ 'user_id' ] ] = $user;
        }
        $members = $userManager->getMembers( $this->project );
        foreach ( $members as $member )
            unset( $this->members[ $member[ 'user_id' ] ] );

        $this->form = new System_Web_Form( 'projects', $this );
        foreach ( $this->members as $userId => $user )
            $this->form->addField( 'member' . $userId, false );
        $this->form->addField( 'accessLevel', System_Const::NormalAccess );

        if ( empty( $this->members ) )
            $this->view->setDecoratorClass( 'Common_MessageBlock' );
        else
            $this->view->setDecoratorClass( 'Common_FixedBlock' );
        $this->view->setSlot( 'page_title', $this->tr( 'Add Members' ) );

        $this->accessLevels = array(
            System_Const::NormalAccess => $this->tr( 'Regular Member' ),
            System_Const::AdministratorAccess => $this->tr( 'Project Administrator' ) );

        $this->form->addItemsRule( 'accessLevel', $this->accessLevels );

        if ( $this->form->loadForm() ) {
            if ( $this->form->isSubmittedWith( 'cancel' ) || $this->form->isSubmittedWith( 'close' ) )
                $this->response->redirect( $breadcrumbs->getParentUrl() );

            $this->form->validate();

            if ( $this->form->isSubmittedWith( 'ok' ) && !$this->form->hasErrors() ) {
                foreach ( $this->members as $userId => $user ) {
                    $fieldName = 'member' . $userId;
                    if ( $this->$fieldName == 1 )
                        $userManager->grantMember( $user, $this->project, $this->accessLevel );
                }
                $this->response->redirect( $breadcrumbs->getParentUrl() );
            }
        }
    }
}

System_Bootstrap::run( 'Common_Application', 'Client_Projects_AddMembers' );
