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

class Admin_Users_AddProjects extends System_Web_Component
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

        $breadcrumbs = new Common_Breadcrumbs( $this );
        $breadcrumbs->initialize( Common_Breadcrumbs::UserProjects, $this->user );

        $projectManager = new System_Api_ProjectManager();
        $projects = $projectManager->getProjects();
        $allProjects = array();
        foreach ( $projects as $project )
            $allProjects[ $project[ 'project_id' ] ] = $project;
        $memberProjects = $userManager->getUserProjects( $this->user );
        $allMemberProjects = array();
        foreach ( $memberProjects as $project )
            $allMemberProjects[ $project[ 'project_id' ] ] = $project;
        $this->memberProjects = array_diff_key( $allProjects, $allMemberProjects );

        $this->form = new System_Web_Form( 'projects', $this );
        foreach ( $this->memberProjects as $projectId => $project )
            $this->form->addField( 'project' . $projectId, false );
        $this->form->addField( 'accessLevel', System_Const::NormalAccess );

        if ( empty( $this->memberProjects ) )
            $this->view->setDecoratorClass( 'Common_MessageBlock' );
        else
            $this->view->setDecoratorClass( 'Common_FixedBlock' );
        $this->view->setSlot( 'page_title', $this->tr( 'Add Projects' ) );

        $this->accessLevels = array(
            System_Const::NormalAccess => $this->tr( 'Regular member' ),
            System_Const::AdministratorAccess => $this->tr( 'Project administrator' ) );

        $this->form->addItemsRule( 'accessLevel', $this->accessLevels );

        if ( $this->form->loadForm() ) {
            if ( $this->form->isSubmittedWith( 'cancel' ) || $this->form->isSubmittedWith( 'close' ) )
                $this->response->redirect( $breadcrumbs->getParentUrl() );

            $this->form->validate();

            if ( $this->form->isSubmittedWith( 'ok' ) && !$this->form->hasErrors() ) {
                foreach ( $this->memberProjects as $projectId => $project ) {
                    $fieldName = 'project' . $projectId;
                    if ( $this->$fieldName == 1 )
                        $userManager->grantMember( $this->user, $project, $this->accessLevel );
                }
                $this->response->redirect( $breadcrumbs->getParentUrl() );
            }
        }

        $javaScript = new System_Web_JavaScript( $this->view );
        $javaScript->registerCheckOnOff( '#project-select', '#project-choices :checkbox', true );
        $javaScript->registerCheckOnOff( '#project-unselect', '#project-choices :checkbox', false );
    }
}

System_Bootstrap::run( 'Common_Application', 'Admin_Users_AddProjects' );
