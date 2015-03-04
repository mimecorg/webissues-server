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

class Client_Alerts_Helper extends System_Web_Base
{
    public function __construct()
    {
        parent::__construct();
    }

    public function hasEmailEngine()
    {
        $serverManager = new System_Api_ServerManager();
        return $serverManager->getSetting( 'email_engine' ) != '';
    }

    public function getEmailTypes()
    {
        return array(
            System_Const::NoEmail => $this->tr( 'None' ),
            System_Const::ImmediateNotificationEmail => $this->tr( 'Immediate notifications' ),
            System_Const::SummaryNotificationEmail => $this->tr( 'Summary of notifications' ),
            System_Const::SummaryReportEmail => $this->tr( 'Summary reports' ) );
    }
}
