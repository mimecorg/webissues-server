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

class Admin_Views_Initial extends System_Web_Component
{
    protected function __construct()
    {
        parent::__construct();
    }

    protected function execute()
    {
        $this->view->setDecoratorClass( 'Common_FixedBlock' );
        $this->view->setSlot( 'page_title', $this->tr( 'Initial View' ) );

        $helper = new Common_Views_Helper();
        $breadcrumbs = $helper->getBreadcrumbs( $this );

        $this->type = $helper->getType();

        $helper->loadInitialView();

        $this->form = new System_Web_Form( 'view', $this );
        $this->form->addField( 'initialView' );

        $this->views = array();
        $this->views[ '' ] = $this->tr( 'All Issues' );
        foreach ( $helper->getPublicViews() as $id => $name )
            $this->views[ $id ] = $name;

        if ( $this->form->loadForm() ) {
            if ( $this->form->isSubmittedWith( 'cancel' ) )
                $this->response->redirect( $breadcrumbs->getParentUrl() );

            if ( $this->form->isSubmittedWith( 'ok' ) ) {
                $this->submit();
                $this->response->redirect( $breadcrumbs->getParentUrl() );
            }
        } else {
            $this->initialView = $helper->getInitialView();
        }
    }

    private function submit()
    {
        $viewManager = new System_Api_ViewManager();
        $viewManager->setViewSetting( $this->type, 'initial_view', $this->initialView );
    }
}

System_Bootstrap::run( 'Common_Application', 'Admin_Views_Initial' );
