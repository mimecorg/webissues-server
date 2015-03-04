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

class Client_Projects_ProjectAccess extends System_Web_Component
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
        $this->view->setSlot( 'page_title', $this->tr( 'Global Access' ) );

        $breadcrumbs = new Common_Breadcrumbs( $this );
        $breadcrumbs->initialize( Common_Breadcrumbs::ProjectMembers );

        $this->form = new System_Web_Form( 'projects', $this );
        $this->form->addField( 'isPublic', $this->project[ 'is_public' ] );

        $this->accessLevels = array(
            0 => $this->tr( 'Regular project' ),
            1 => $this->tr( 'Public project' )
        );

        $this->form->addItemsRule( 'isPublic', $this->accessLevels );

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
        $projectManager = new System_Api_ProjectManager();
        try {
            $projectManager->setProjectAccess( $this->project, $this->isPublic );
        } catch ( System_Api_Error $ex ) {
            $this->form->getErrorHelper()->handleError( 'isPublic', $ex );
        }
    }
}

System_Bootstrap::run( 'Common_Application', 'Client_Projects_ProjectAccess' );
