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

class Admin_Views_Unpublish extends System_Web_Component
{
    protected function __construct()
    {
        parent::__construct();
    }

    protected function execute()
    {
        $this->view->setDecoratorClass( 'Common_MessageBlock' );
        $this->view->setSlot( 'page_title', $this->tr( 'Unpublish View' ) );

        $helper = new Common_Views_Helper();
        $breadcrumbs = $helper->getBreadcrumbs( $this );

        $this->oldView = $helper->getOldView();

        $this->form = new System_Web_Form( 'view', $this );
        $this->form->addViewState( 'canUnpublish', false );

        if ( $this->form->loadForm() ) {
            if ( $this->form->isSubmittedWith( 'cancel' ) )
                $this->response->redirect( $breadcrumbs->getParentUrl() );

            if ( $this->form->isSubmittedWith( 'ok' ) ) {
                if ( $this->canUnpublish ) {
                    $this->submit();
                    if ( $this->canUnpublish )
                        $this->response->redirect( $breadcrumbs->getParentUrl() );
                } else {
                    $this->response->redirect( $breadcrumbs->getParentUrl() );
                }
            }
        } else {
            $this->canUnpublish = $this->oldView[ 'is_public' ];
        }
    }

    private function submit()
    {
        $viewManager = new System_Api_ViewManager();
        try {
            $this->canUnpublish = $viewManager->unpublishView( $this->oldView );
        } catch ( System_Api_Error $ex ) {
            $this->canUnpublish = false;
        }
    }
}

System_Bootstrap::run( 'Common_Application', 'Admin_Views_Unpublish' );
