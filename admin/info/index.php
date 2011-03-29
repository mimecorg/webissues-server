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

class Admin_Info_Index extends System_Web_Component
{
    protected function __construct()
    {
        parent::__construct();
    }

    protected function execute()
    {
        $this->view->setDecoratorClass( 'Common_FixedBlock' );
        $this->view->setSlot( 'page_title', $this->tr( 'General Information' ) );

        $this->form = new System_Web_Form( $this, 'info' );

        $connection = System_Core_Application::getInstance()->getConnection();

        $this->dbServer = $connection->getParameter( 'server' );
        $this->dbVersion = $connection->getParameter( 'version' );

        $site = System_Core_Application::getInstance()->getSite();

        $this->dbHost = $site->getConfig( 'db_host' );
        $this->dbDatabase = $site->getConfig( 'db_database' );
        $this->dbPrefix = $site->getConfig( 'db_prefix' );

        $serverManager = new System_Api_ServerManager();

        $current = $serverManager->getSetting( 'cron_current' );
        if ( $current != null )
            $this->cronCurrent = true;

        $last = $serverManager->getSetting( 'cron_last' );
        if ( $last != null ) {
            $formatter = new System_Api_Formatter();
            $this->cronLast = $formatter->formatDateTime( $last, System_Api_Formatter::ToLocalTimeZone );
        }

        if ( $current == null && ( $last == null || time() - $last > 86400 ) )
            $this->form->setError( 'cron', $this->tr( 'The cron job was not started within the last 24 hours.' ) );
    }
}

System_Bootstrap::run( 'Common_Application', 'Admin_Info_Index' );
