<?php
/**************************************************************************
* This file is part of the WebIssues Server program
* Copyright (C) 2006 Michał Męciński
* Copyright (C) 2007-2013 WebIssues Team
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

class Client_Alerts_Add extends System_Web_Component
{
    private $folder = null;
    private $type = null;

    protected function __construct()
    {
        parent::__construct();
    }

    protected function execute()
    {
        $projectManager = new System_Api_ProjectManager();
        $typeManager = new System_Api_TypeManager();

        $folderId = (int)$this->request->getQueryString( 'folder' );
        if ( $folderId != 0 ) {
            $this->folder = $projectManager->getFolder( $folderId );
            $this->type = $typeManager->getIssueTypeForFolder( $this->folder );
            $this->folderName = $this->folder[ 'folder_name' ];
        } else {
            $typeId = (int)$this->request->getQueryString( 'type' );
            $this->type = $typeManager->getIssueType( $typeId );
            $this->typeName = $this->type[ 'type_name' ];
        }

        $breadcrumbs = new Common_Breadcrumbs( $this );
        if ( $this->folder != null )
            $breadcrumbs->initialize( Common_Breadcrumbs::ManageAlerts, $this->folder );
        else
            $breadcrumbs->initialize( Common_Breadcrumbs::ManageAlerts, $this->type );

        $alertManager = new System_Api_AlertManager();

        $this->viewOptions = array();

        if ( $this->folder != null ) {
            if ( !$alertManager->hasAllIssuesAlert( $this->folder ) )
                $this->viewOptions[ 0 ] = $this->tr( 'All Issues' );

            $views = $alertManager->getViewsWithoutAlerts( $this->folder );
        } else {
            if ( !$alertManager->hasAllIssuesGlobalAlert( $this->type ) )
                $this->viewOptions[ 0 ] = $this->tr( 'All Issues' );

            $views = $alertManager->getViewsWithoutGlobalAlerts( $this->type );
        }

        if ( !empty( $views[ 0 ] ) )
            $this->viewOptions[ $this->tr( 'Personal Views' ) ] = $views[ 0 ];

        if ( !empty( $views[ 1 ] ) )
            $this->viewOptions[ $this->tr( 'Public Views' ) ] = $views[ 1 ];

        $this->form = new System_Web_Form( 'alerts', $this );

        $this->form->addField( 'viewId', '' );
        $this->form->addItemsRule( 'viewId', $this->viewOptions );

        $helper = new Client_Alerts_Helper();
        $this->emailEngine = $helper->hasEmailEngine();

        if ( $this->emailEngine ) {
            $this->form->addField( 'alertEmail', System_Const::NoEmail );

            $this->emailTypes = $helper->getEmailTypes();
            $this->form->addItemsRule( 'alertEmail', $this->emailTypes );
        }

        if ( empty( $this->viewOptions ) )
            $this->view->setDecoratorClass( 'Common_MessageBlock' );
        else
            $this->view->setDecoratorClass( 'Common_FixedBlock' );
        $this->view->setSlot( 'page_title', $this->tr( 'Add Alert' ) );

        if ( $this->form->loadForm() ) {
            if ( $this->form->isSubmittedWith( 'cancel' ) || $this->form->isSubmittedWith( 'close' ) )
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
        $view = null;

        if ( $this->viewId != 0 ) {
            $viewManager = new System_Api_ViewManager();
            $view = $viewManager->getView( $this->viewId );
        }

        if ( $this->emailEngine == false )
            $this->alertEmail = System_Const::NoEmail;

        $alertManager = new System_Api_AlertManager();

        try {
            if ( $this->folder != null )
                $alertManager->addAlert( $this->folder, $view, $this->alertEmail );
            else
                $alertManager->addGlobalAlert( $this->type, $view, $this->alertEmail );
        } catch ( System_Api_Error $ex ) {
            $this->form->getErrorHelper()->handleError( 'viewId', $ex );
        }
    }
}

System_Bootstrap::run( 'Common_Application', 'Client_Alerts_Add' );
