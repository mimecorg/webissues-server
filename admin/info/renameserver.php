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

class Admin_Info_RenameServer extends System_Web_Component
{
    protected function __construct()
    {
        parent::__construct();
    }

    protected function execute()
    {
        $this->view->setDecoratorClass( 'Common_FixedBlock' );
        $this->view->setSlot( 'page_title', $this->tr( 'Rename Server' ) );

        $breadcrumbs = new Common_Breadcrumbs( $this );
        $breadcrumbs->initialize( Common_Breadcrumbs::GeneralInformation );

        $serverManager = new System_Api_ServerManager();
        $this->server = $serverManager->getServer();

        $this->form = new System_Web_Form( 'info', $this );
        $this->form->addField( 'serverName', $this->server[ 'server_name' ] );
        $this->form->addTextRule( 'serverName', System_Const::NameMaxLength );

        if ( $this->form->loadForm() ) {
            if ( $this->form->isSubmittedWith( 'cancel' ) )
                $this->response->redirect( $breadcrumbs->getParentUrl() );

            if ( $this->form->isSubmittedWith( 'ok' ) ) {
                $this->form->validate();
                if ( !$this->form->hasErrors() ) {
                    $serverManager->renameServer( $this->serverName );
                    $this->response->redirect( $breadcrumbs->getParentUrl() );
                }
            }
        }
    }
}

System_Bootstrap::run( 'Common_Application', 'Admin_Info_RenameServer' );
