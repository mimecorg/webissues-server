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

class Admin_Events_Index extends System_Web_Component
{
    protected function __construct()
    {
        parent::__construct();
    }

    protected function execute()
    {
        $this->view->setDecoratorClass( 'Common_FixedBlock' );
        $this->view->setSlot( 'page_title', $this->tr( 'Event Log' ) );

        $this->filterBar = new System_Web_FilterBar();
        $this->filterBar->setParameter( 'type' );
        $this->filterBar->setMergeParameters( array( 'page' => null ) );

        $helper = new Admin_Events_Helper();

        $this->eventTypes = $helper->getEventType();
        $type = $this->request->getQueryString( 'type' );

        if ( ( $type !== null ) && !isset( $this->eventTypes[ $type ] ) )
            throw new System_Core_Exception( 'Invalid event type' );

        $this->grid = new System_Web_Grid();
        $this->grid->setPageSize( 20 );

        $eventLog = new System_Api_EventLog();
        $this->grid->setColumns( $eventLog->getEventsColumns() );
        $this->grid->setDefaultSort( 'date', System_Web_Grid::Descending );
        $this->grid->setRowsCount( $eventLog->getEventsCount( $type ) );

        $page = $eventLog->getEvents( $type, $this->grid->getOrderBy(), $this->grid->getPageSize(), $this->grid->getOffset() );

        $formatter = new System_Api_Formatter();

        $this->events = array();
        foreach ( $page as $row ) {
            $event = array();
            if ( isset( $this->eventTypes[ $row[ 'event_type' ] ] ) )
                $event[ 'type' ] = $this->eventTypes[ $row[ 'event_type' ] ];
            else
                $event[ 'type' ] = $row[ 'event_type' ];
            $event[ 'date' ] = $formatter->formatDateTime( $row[ 'event_time' ], System_Api_Formatter::ToLocalTimeZone );
            $event[ 'message' ] = $this->truncate( $row[ 'event_message' ], 80 );
            $event[ 'icon' ] = $helper->getSeverityIcon( $row[ 'event_severity' ] );
            $event[ 'severity' ] = $helper->getSeverityCaption( $row[ 'event_severity' ] );
            $this->events[ $row[ 'event_id' ] ] = $event;
        }
    }
}

System_Bootstrap::run( 'Common_Application', 'Admin_Events_Index' );
