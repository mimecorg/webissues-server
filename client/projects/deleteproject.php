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

class Client_Projects_DeleteProject extends System_Web_Component
{
    protected function __construct()
    {
        parent::__construct();
    }

    protected function execute()
    {
        if ( !System_Api_Principal::getCurrent()->isAdministrator() )
            throw new System_Api_Error( System_Api_Error::AccessDenied );

        $projectManager = new System_Api_ProjectManager();
        $projectId = (int)$this->request->getQueryString( 'project' );
        $this->project = $projectManager->getProject( $projectId );

        $this->view->setDecoratorClass( 'Common_MessageBlock' );
        $this->view->setSlot( 'page_title', $this->tr( 'Delete Project' ) );

        $breadcrumbs = new Common_Breadcrumbs( $this );
        $breadcrumbs->initialize( Common_Breadcrumbs::ManageProjects );

        $this->form = new System_Web_Form( 'projects', $this );
        $this->form->addViewState( 'warning', false );

        if ( $this->form->loadForm() ) {
            if ( $this->form->isSubmittedWith( 'cancel' ) )
                $this->response->redirect( $breadcrumbs->getParentUrl() );

            if ( $this->form->isSubmittedWith( 'ok' ) ) {
                if ( $this->submit() )
                    $this->response->redirect( $breadcrumbs->getParentUrl() );
            }
        } else {
            $this->warning = $projectManager->checkProjectNotEmpty( $this->project );
        }
    }

    private function submit()
    {
        $projectManager = new System_Api_ProjectManager();
        try {
            return $projectManager->deleteProject( $this->project, $this->warning ? System_Api_ProjectManager::ForceDelete : 0 );
        } catch ( System_Api_Error $ex ) {
            if ( $ex->getMessage() == System_Api_Error::CannotDeleteProject ) {
                $this->warning = true;
                return false;
            }
            throw $ex;
        }
    }
}

System_Bootstrap::run( 'Common_Application', 'Client_Projects_DeleteProject' );
