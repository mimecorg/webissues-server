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

class Client_Issues_ExportCsv extends System_Web_Component
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

        $this->view->setDecoratorClass( 'Common_FixedBlock' );
        $this->view->setSlot( 'page_title', $this->tr( 'Export To CSV' ) );

        $breadcrumbs = new Common_Breadcrumbs( $this );
        if ( $folderId != 0 )
            $breadcrumbs->initialize( Common_Breadcrumbs::Folder, $folder );
        else
            $breadcrumbs->initialize( Common_Breadcrumbs::Folder, $type );

        $this->form = new System_Web_Form( 'issues', $this );
        $this->form->addField( 'report', 0 );

        $this->reportTypes = array(
            0 => $this->tr( 'Table with visible columns only' ),
            1 => $this->tr( 'Table with all system and user columns' ) );

        $this->form->addItemsRule( 'report', $this->reportTypes );

        if ( $this->form->loadForm() ) {
            if ( $this->form->isSubmittedWith( 'cancel' ) )
                $this->response->redirect( $breadcrumbs->getParentUrl() );

            $this->form->validate();

            if ( $this->form->isSubmittedWith( 'ok' ) && !$this->form->hasErrors() )
                $this->response->redirect( $this->mergeQueryString( '/client/issues/generatecsv.php', array( 'report' => $this->report ) ) );
        }

        $javaScript = new System_Web_JavaScript( $this->view );
        $javaScript->registerChangeText( $this->form->getSubmitSelector( 'ok' ), $this->form->getSubmitSelector( 'cancel' ), $this->tr( 'Close' ) );
    }
}

System_Bootstrap::run( 'Common_Application', 'Client_Issues_ExportCsv' );
