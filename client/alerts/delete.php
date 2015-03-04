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

class Client_Alerts_Delete extends System_Web_Component
{
    protected function __construct()
    {
        parent::__construct();
    }

    protected function execute()
    {
        $folderId = (int)$this->request->getQueryString( 'folder' );
        if ( $folderId != 0 ) {
            $projectManager = new System_Api_ProjectManager();
            $folder = $projectManager->getFolder( $folderId );
        } else {
            $typeId = (int)$this->request->getQueryString( 'type' );
            $typeManager = new System_Api_TypeManager();
            $type = $typeManager->getIssueType( $typeId );
        }

        $this->view->setDecoratorClass( 'Common_MessageBlock' );
        $this->view->setSlot( 'page_title', $this->tr( 'Delete Alert' ) );

        $breadcrumbs = new Common_Breadcrumbs( $this );
        if ( $folderId != 0 )
            $breadcrumbs->initialize( Common_Breadcrumbs::ManageAlerts, $folder );
        else
            $breadcrumbs->initialize( Common_Breadcrumbs::ManageAlerts, $type );

        $alertId = (int)$this->request->getQueryString( 'id' );
        $alertManager = new System_Api_AlertManager();
        $this->alert = $alertManager->getAlert( $alertId, System_Api_AlertManager::AllowEdit );

        if ( $this->alert[ 'view_name' ] === null )
            $this->alert[ 'view_name' ] = $this->tr( 'All Issues' );

        $this->form = new System_Web_Form( 'alerts', $this );

        if ( $this->form->loadForm() ) {
            if ( $this->form->isSubmittedWith( 'cancel' ) )
                $this->response->redirect( $breadcrumbs->getParentUrl() );

            if ( $this->form->isSubmittedWith( 'ok' ) ) {
                $alertManager->deleteAlert( $this->alert );
                $this->response->redirect( $breadcrumbs->getParentUrl() );
            }
        }
    }
}

System_Bootstrap::run( 'Common_Application', 'Client_Alerts_Delete' );
