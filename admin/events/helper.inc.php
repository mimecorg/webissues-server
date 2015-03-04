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

if ( !defined( 'WI_VERSION' ) ) die( -1 );

class Admin_Events_Helper extends System_Web_Base
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getSeverityIcon( $severity )
    {
        static $icons = array( 'info', 'warning', 'error' );
        return $this->url( '/common/images/status-' . $icons[ $severity ] . '-16.png' );
    }

    public function getSeverityCaption( $severity )
    {
        static $captions = null;
        if ( !isset( $captions ) )
            $captions = array( $this->tr( 'Information' ), $this->tr( 'Warning' ), $this->tr( 'Error' ) );
        return $captions[ $severity ];
    }

    public function getEventType( $eventType = null )
    {
        static $eventTypes = null;
        if ( !isset( $eventTypes ) )
            $eventTypes = array(
                System_Api_EventLog::Errors => $this->tr( 'Errors' ),
                System_Api_EventLog::Access => $this->tr( 'Access' ),
                System_Api_EventLog::Audit => $this->tr( 'Audit' ),
                System_Api_EventLog::Cron => $this->tr( 'Cron' ),
            );
        if ( $eventType == null )
            return $eventTypes;
        elseif ( isset( $eventTypes[ $eventType ] ) )
            return $eventTypes[ $eventType ];
        else
            return null;
    }
}
