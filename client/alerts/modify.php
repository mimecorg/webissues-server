<?php
/**************************************************************************
* This file is part of the WebIssues Server program
* Copyright (C) 2006 Michał Męciński
* Copyright (C) 2007-2012 WebIssues Team
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

class Client_Alerts_Modify extends System_Web_Component
{
    private $alertManager;


    protected function __construct()
    {
        parent::__construct();
    }

    protected function execute()
    {
        $helper = new Client_Alerts_Helper();
        if ( !$helper->hasEmailEngine() )
            throw new System_Core_Exception( 'Email engine is disabled' );

        $projectManager = new System_Api_ProjectManager();
        $folderId = (int)$this->request->getQueryString( 'folder' );
        $this->folder = $projectManager->getFolder( $folderId );

        $this->view->setDecoratorClass( 'Common_FixedBlock' );
        $this->view->setSlot( 'page_title', $this->tr( 'Modify Alert' ) );

        $breadcrumbs = new Common_Breadcrumbs( $this );
        $breadcrumbs->initialize( Common_Breadcrumbs::ManageAlerts, $this->folder );

        $alertId = (int)$this->request->getQueryString( 'id' );
        $this->alertManager = new System_Api_AlertManager();
        $this->alert = $this->alertManager->getAlert( $alertId );

        if ( $this->alert[ 'view_name' ] === null )
            $this->alert[ 'view_name' ] = $this->tr( 'All Issues' );

        $this->form = new System_Web_Form( 'alerts', $this );

        $this->form->addField( 'alertEmail', $this->alert[ 'alert_email' ] );

        $this->emailTypes = $helper->getEmailTypes();
        $this->form->addItemsRule( 'alertEmail', $this->emailTypes );

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
        try {
            $this->alertManager->modifyAlert( $this->alert, $this->alertEmail );
        } catch ( System_Api_Error $ex ) {
            $this->form->getErrorHelper()->handleError( 'alertEmail', $ex );
        }
    }
}

System_Bootstrap::run( 'Common_Application', 'Client_Alerts_Modify' );
