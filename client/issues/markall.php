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

class Client_Issues_MarkAll extends System_Web_Component
{
    protected function __construct()
    {
        parent::__construct();
    }

    protected function execute()
    {
        $projectManager = new System_Api_ProjectManager();
        $folderId = (int)$this->request->getQueryString( 'folder' );
        $this->folder = $projectManager->getFolder( $folderId );

        $this->isRead = (int)$this->request->getQueryString( 'status' );

        $this->view->setDecoratorClass( 'Common_MessageBlock' );
        if ( $this->isRead )
            $this->view->setSlot( 'page_title', $this->tr( 'Mark All As Read' ) );
        else
            $this->view->setSlot( 'page_title', $this->tr( 'Mark All As Unread' ) );

        $breadcrumbs = new Common_Breadcrumbs( $this );
        $breadcrumbs->initialize( Common_Breadcrumbs::Folder, $this->folder );

        $this->form = new System_Web_Form( 'markall', $this );

        if ( $this->form->loadForm() ) {
            if ( $this->form->isSubmittedWith( 'cancel' ) )
                $this->response->redirect( $breadcrumbs->getParentUrl() );

            if ( $this->form->isSubmittedWith( 'ok' ) ) {
                $this->submit();
                $this->response->redirect( $breadcrumbs->getParentUrl() );
            }
        }
    }
    
    private function submit()
    {
        $stateManager = new System_Api_StateManager();
        $stateManager->setFolderRead( $this->folder, $this->isRead ? $this->folder[ 'stamp_id' ] : 0 );
    }
}

System_Bootstrap::run( 'Common_Application', 'Client_Issues_MarkAll' );
