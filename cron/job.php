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

require_once( dirname( dirname( __FILE__ ) ) . '/system/bootstrap.inc.php' );

class Cron_Job extends System_Core_Application
{
    const TimeLimit = 10800;

    private $last = null;
    private $current = null;

    private $sent = 0;

    protected function __construct()
    {
        parent::__construct();
    }

    protected function processCommandLine( $argc, $argv )
    {
        if ( $argc == 2 )
            $this->setSiteName( $argv[ 1 ] );
        else if ( $argc > 2 )
            throw new System_Core_Exception( 'Invalid command line' );
    }

    protected function execute()
    {
        set_time_limit( self::TimeLimit );

        $serverManager = new System_Api_ServerManager();
        $eventLog = new System_Api_EventLog( $this );

        $current = $serverManager->getSetting( 'cron_current' );
        if ( $current != null ) {
            if ( time() - $current < self::TimeLimit )
                return;

            $eventLog->addEvent( System_Api_EventLog::Cron, System_Api_EventLog::Warning, $eventLog->tr( 'Previous cron job timed out' ) );
        }

        $this->last = $serverManager->getSetting( 'cron_last' );

        $this->current = time();
        $serverManager->setSetting( 'cron_current', $this->current );

        $eventLog->addEvent( System_Api_EventLog::Cron, System_Api_EventLog::Information, $eventLog->tr( 'Cron job started' ) );

        $divisor = $serverManager->getSetting( 'gc_divisor' );
        if ( $divisor == 0 )
            $this->collectGarbage();

        $email = $serverManager->getSetting( 'email_engine' );
        if ( $email != null )
            $this->sendNotificationEmails();
    }

    protected function cleanUp()
    {
        if ( $this->current != null ) {
            $serverManager = new System_Api_ServerManager();
            $serverManager->setSetting( 'cron_last', $this->current );
            $serverManager->setSetting( 'cron_current', null );
        }

        if ( $this->isLoggingEnabled() ) {
            $eventLog = new System_Api_EventLog( $this );
            if ( $this->getFatalError() != null )
                $eventLog->addEvent( System_Api_EventLog::Cron, System_Api_EventLog::Error, $eventLog->tr( 'Cron job finished with error' ) );
            else if ( $this->current == null )
                $eventLog->addEvent( System_Api_EventLog::Cron, System_Api_EventLog::Warning, $eventLog->tr( 'Previous cron job is still running' ) );
            else if ( $this->sent > 0 )
                $eventLog->addEvent( System_Api_EventLog::Cron, System_Api_EventLog::Information, $eventLog->tr( 'Cron job finished (sent %1 emails)', null, $this->sent ) );
            else
                $eventLog->addEvent( System_Api_EventLog::Cron, System_Api_EventLog::Information, $eventLog->tr( 'Cron job finished' ) );
        }
    }

    private function sendNotificationEmails()
    {
        $engine = new System_Mail_Engine();
        $engine->loadSettings();

        $userManager = new System_Api_UserManager();
        $users = $userManager->getUsersWithEmail();

        foreach ( $users as $user ) {
            $principal = new System_Api_Principal( $user );
            System_Api_Principal::setCurrent( $principal );

            $includeSummary = false;

            $preferencesManager = new System_Api_PreferencesManager();
            $locale = new System_Api_Locale();

            $this->translator->setLanguage( System_Core_Translator::UserLanguage, $locale->getSetting( 'language' ) );

            $validator = new System_Api_Validator();

            $days = $validator->convertToIntArray( $preferencesManager->getPreference( 'summary_days' ) );
            $hours = $validator->convertToIntArray( $preferencesManager->getPreference( 'summary_hours' ) );

            if ( !empty( $days ) && !empty( $hours ) ) {
                $timezone = new DateTimeZone( $locale->getSetting( 'time_zone' ) );

                $currentDate = new DateTime( '@' . $this->current );
                $currentDate->setTimezone( $timezone );

                $day = $currentDate->format( 'w' );
                $hour = $currentDate->format( 'G' );

                if ( array_search( $day, $days ) !== false && array_search( $hour, $hours ) !== false ) {
                    $lastDate = new DateTime( '@' . $this->last );
                    $lastDate->setTimezone( $timezone );

                    if ( $lastDate->format( 'YmdH' ) != $currentDate->format( 'YmdH' ) )
                        $includeSummary = true;
                }
            }

            $alertManager = new System_Api_AlertManager();

            $alerts = $alertManager->getAlertsToEmail( $includeSummary );

            if ( !empty( $alerts ) ) {
                $alertManager->updateAlertStamps( $includeSummary );

                foreach ( $alerts as $alert ) {
                    $mail = System_Web_Component::createComponent( 'Common_Mail_Notification', null, $alert );

                    if ( $mail->prepare() ) {
                        $body = $mail->run();
                        $subject = $mail->getView()->getSlot( 'subject' );

                        $engine->send( $preferencesManager->getPreference( 'email' ), $principal->getUserName(), $subject, $body );
                        $this->sent++;
                    }
                }
            }
        }
    }
}

System_Bootstrap::run( 'Cron_Job' );
