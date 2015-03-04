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

        $serverManager = new System_Api_ServerManager();

        $anonymousAccess = $serverManager->getSetting( 'anonymous_access' );
        if ( $anonymousAccess == 1 )
            $this->anonymous = $this->tr( 'enabled', 'anonymous access' );
        else
            $this->anonymous = $this->tr( 'disabled', 'anonymous access' );

        $selfRegister = $serverManager->getSetting( 'self_register' );
        if ( $selfRegister == 1 )
            $this->register = $this->tr( 'enabled', 'user registration' );
        else
            $this->register = $this->tr( 'disabled', 'user registration' );

        $emailEngine = $serverManager->getSetting( 'email_engine' );
        if ( $emailEngine == 'standard' ) 
            $this->email = $this->tr( 'standard PHP mailer' );
        else if ( $emailEngine == 'smtp' )
            $this->email = $this->tr( 'SMTP server' );
        else
            $this->email = $this->tr( 'disabled', 'sending emails' );

        if ( $emailEngine != null )
            $this->emailFrom = $serverManager->getSetting( 'email_from' );
        if ( $emailEngine == 'smtp' )
            $this->emailServer = $serverManager->getSetting( 'smtp_server' );

        $inboxEngine = $serverManager->getSetting( 'inbox_engine' );
        if ( $inboxEngine == 'pop3' ) {
            $this->inbox = $this->tr( 'POP3 server' );
        } else if ( $inboxEngine == 'imap' ) {
            $this->inbox = $this->tr( 'IMAP server' );
        } else {
            $this->inbox = $this->tr( 'disabled', 'email inbox' );
        }

        if ( $inboxEngine != null ) {
            $this->inboxEmail = $serverManager->getSetting( 'inbox_email' );
            $this->inboxServer = $serverManager->getSetting( 'inbox_server' );
        }

        $cronLast = $serverManager->getSetting( 'cron_current' );
        if ( $cronLast == null )
            $cronLast = $serverManager->getSetting( 'cron_last' );

        $current = time();

        if ( $cronLast != null )
            $this->cron = $this->formatTime( $current - $cronLast );
        else
            $this->cron = $this->tr( 'never' );

        if ( $selfRegister == 1 && $emailEngine == null )
            $this->form->setError( 'register', $this->tr( 'User self-registration requires sending emails to be configured.' ) );

        if ( ( $emailEngine != null || $inboxEngine != null ) && ( $cronLast == null || $current - $cronLast > 86400 ) )
            $this->form->setError( 'cron', $this->tr( 'Sending or receiving emails requires the cron job to be running.' ) );
    }

    private function formatTime( $seconds )
    {
        if ( $seconds < 120 )
            return $this->tr( '%1 seconds ago', null, $seconds );

        $minutes = floor( $seconds / 60 );
        if ( $minutes < 120 )
            return $this->tr( '%1 minutes ago', null, $minutes );

        $hours = floor( $minutes / 60 );
        if ( $hours <= 24 )
            return $this->tr( '%1 hours ago', null, $hours );

        return $this->tr( 'more than %1 hours ago', null, 24 );
    }
}

System_Bootstrap::run( 'Common_Application', 'Admin_Info_Index' );
