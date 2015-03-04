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

class Client_Projects_MoveFolder extends System_Web_Component
{
    protected function __construct()
    {
        parent::__construct();
    }

    protected function execute()
    {
        $projectManager = new System_Api_ProjectManager();
        $folderId = (int)$this->request->getQueryString( 'folder' );
        $this->folder = $projectManager->getFolder( $folderId, System_Api_ProjectManager::RequireAdministrator );

        $breadcrumbs = new Common_Breadcrumbs( $this );
        $breadcrumbs->initialize( Common_Breadcrumbs::ManageProjects );

        $this->form = new System_Web_Form( 'projects', $this );
        $this->form->addField( 'project', $this->folder[ 'project_id' ] );

        $projects = $projectManager->getProjects();

        $this->projects = array();
        $this->canMove = false;

        foreach ( $projects as $project ) {
            if ( $project[ 'project_access' ] == System_Const::AdministratorAccess ) {
                $this->projects[ $project[ 'project_id' ] ] = $project[ 'project_name' ];
                if ( $project[ 'project_id' ] != $this->folder[ 'project_id' ] )
                    $this->canMove = true;
            }
        }

        if ( $this->canMove )
            $this->view->setDecoratorClass( 'Common_FixedBlock' );
        else
            $this->view->setDecoratorClass( 'Common_MessageBlock' );
        $this->view->setSlot( 'page_title', $this->tr( 'Move Folder' ) );

        $this->form->addItemsRule( 'project', $this->projects );

        if ( $this->form->loadForm() ) {
            if ( $this->form->isSubmittedWith( 'cancel' ) )
                $this->response->redirect( $breadcrumbs->getParentUrl() );

            $this->form->validate();

            if ( $this->form->isSubmittedWith( 'ok' ) && !$this->form->hasErrors() ) {
                if ( $this->canMove )
                    $this->submit();
                if ( !$this->form->hasErrors() ) {
                    if ( $this->canMove ) {
                        $grid = new System_Web_Grid();
                        $grid->addExpandCookieId( 'wi_projects', $this->project );
                    }
                    $this->response->redirect( $breadcrumbs->getParentUrl() );
                }
            }
        }
    }
    
    private function submit()
    {
        $projectManager = new System_Api_ProjectManager();
        $project = $projectManager->getProject( $this->project, System_Api_ProjectManager::RequireAdministrator );

        try {
            $projectManager->moveFolder( $this->folder, $project );
        } catch ( System_Api_Error $ex ) {
            $this->form->getErrorHelper()->handleError( 'project', $ex );
        }
    }
}

System_Bootstrap::run( 'Common_Application', 'Client_Projects_MoveFolder' );
