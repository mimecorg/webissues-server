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

class Admin_Events_Event extends System_Web_Component
{
    protected function __construct()
    {
        parent::__construct();
    }

    protected function execute()
    {
        $this->view->setDecoratorClass( 'Common_FixedBlock' );
        $this->view->setSlot( 'page_title', $this->tr( 'Event Details' ) );

        $breadcrumbs = new Common_Breadcrumbs( $this );
        $breadcrumbs->initialize( Common_Breadcrumbs::EventLog );

        $this->form = new System_Web_Form( 'event', $this );
        if ( $this->form->loadForm() )
            $this->response->redirect( $breadcrumbs->getParentUrl() );

        $eventLog = new System_Api_EventLog();
        $event = $eventLog->getEvent( $this->request->getQueryString( 'id' ) );

        $formatter = new System_Api_Formatter();
        $helper = new Admin_Events_Helper();

        $event[ 'date' ] = $formatter->formatDateTime( $event[ 'event_time' ], System_Api_Formatter::ToLocalTimeZone );
        $event[ 'icon' ] = $helper->getSeverityIcon( $event[ 'event_severity' ] );
        $event[ 'severity' ] = $helper->getSeverityCaption( $event[ 'event_severity' ] );
        $event[ 'event_type' ] = $helper->getEventType( $event[ 'event_type' ] );

        $this->event = $event;
    }
}

System_Bootstrap::run( 'Common_Application', 'Admin_Events_Event' );
