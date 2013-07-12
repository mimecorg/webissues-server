<?php
/**************************************************************************
* This file is part of the WebIssues Server program
* Copyright (C) 2006 Michał Męciński
* Copyright (C) 2007-2013 WebIssues Team
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
    private $last = null;
    private $current = null;

    private $mailEngine = null;
    private $inboxEngine = null;

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
        set_time_limit( 0 );

        $serverManager = new System_Api_ServerManager();
        $eventLog = new System_Api_EventLog( $this );

        $current = $serverManager->getSetting( 'cron_current' );
        if ( $current != null ) {
            if ( time() - $current < 10800 )
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

        $inbox = $serverManager->getSetting( 'inbox_engine' );
        if ( $inbox != null )
            $this->processInboxEmails();
    }

    protected function cleanUp()
    {
        if ( $this->mailEngine != null )
            $this->mailEngine->close();

        if ( $this->inboxEngine != null )
            $this->inboxEngine->close();

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
            else
                $eventLog->addEvent( System_Api_EventLog::Cron, System_Api_EventLog::Information, $eventLog->tr( 'Cron job finished' ) );
        }
    }

    private function sendNotificationEmails()
    {
        $this->mailEngine = new System_Mail_Engine();
        $this->mailEngine->loadSettings();

        System_Web_Base::setLinkMode( System_Web_Base::MailLinks );

        $sent = 0;

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
                    if ( $this->last ) {
                        $lastDate = new DateTime( '@' . $this->last );
                        $lastDate->setTimezone( $timezone );

                        if ( $lastDate->format( 'YmdH' ) != $currentDate->format( 'YmdH' ) )
                            $includeSummary = true;
                    } else {
                        $includeSummary = true;
                    }
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

                        $this->mailEngine->send( $preferencesManager->getPreference( 'email' ), $principal->getUserName(), $subject, $body );
                        $sent++;
                    }
                }
            }
        }

        System_Api_Principal::setCurrent( null );

        $this->translator->setLanguage( System_Core_Translator::UserLanguage, null );

        $serverManager = new System_Api_ServerManager();

        $selfRegister = $serverManager->getSetting( 'self_register' );
        $notifyEmail = $serverManager->getSetting( 'register_notify_email' );

        if ( $selfRegister == 1 && $notifyEmail != null ) {
            $registrationManager = new System_Api_RegistrationManager();

            $page = $registrationManager->getRequestsToEmail();

            if ( !empty( $page ) ) {
                $registrationManager->setRequestsMailed();

                $mail = System_Web_Component::createComponent( 'Common_Mail_RegisterNotification', null, $page );

                $body = $mail->run();
                $subject = $mail->getView()->getSlot( 'subject' );

                $this->mailEngine->send( $notifyEmail, null, $subject, $body );
                $sent++;
            }
        }

        if ( $sent > 0 ) {
            $eventLog = new System_Api_EventLog( $this );
            $eventLog->addEvent( System_Api_EventLog::Cron, System_Api_EventLog::Information, $eventLog->tr( 'Sent %1 notification emails', null, $sent ) );
        }
    }

    public function processInboxEmails()
    {
        $this->inboxEngine = new System_Mail_InboxEngine();
        $this->inboxEngine->loadSettings();

        $received = 0;

        $messages = $this->inboxEngine->getMessages();

        if ( empty( $messages ) )
            return;

        $serverManager = new System_Api_ServerManager();
        $userManager = new System_Api_UserManager();
        $projectManager = new System_Api_ProjectManager();
        $issueManager = new System_Api_IssueManager();
        $typeManager = new System_Api_TypeManager();
        $parser = new System_Api_Parser();
        $eventLog = new System_Api_EventLog( $this );

        $inboxEmail = $serverManager->getSetting( 'inbox_email' );

        if ( $this->mailEngine != null )
            $this->mailEngine->setReplyTo( $inboxEmail );

        $allowExternal = $serverManager->getSettings( 'inbox_allow_external' ) == 1;

        if ( $allowExternal ) {
            $robotUserId = $serverManager->getSetting( 'inbox_robot' );
            $robotUser = $userManager->getUser( $robotUserId );
            $robotPrincipal = new System_Api_Principal( $robotUser );
        }

        $mapFolder = $serverManager->getSetting( 'inbox_map_folder' ) == 1;

        if ( $mapFolder ) {
            $parts = explode( '@', $inboxEmail );
            $mapPattern = '/^' . preg_quote( $parts[ 0 ] ) . '[+-](\w+)-(\w+)@' . preg_quote( $parts[ 1 ] ) . '$/ui';

            $allFolders = $projectManager->getFoldersMap();
        }

        $defaultFolderId = $serverManager->getSetting( 'inbox_default_folder' );

        $leaveMessages = $serverManager->getSetting( 'inbox_leave_messages' ) == 1;
        $respond = $serverManager->getSetting( 'inbox_respond' ) == 1;

        foreach ( $messages as $msgno ) {
            $processed = false;

            $headers = $this->inboxEngine->getHeaders( $msgno );

            $fromEmail = $headers[ 'from' ][ 'email' ];

            try {
                $user = $userManager->getUserByEmail( $fromEmail );
            } catch ( System_Api_Error $e ) {
                $user = null;
            }

            if ( $user != null ) {
                $principal = new System_Api_Principal( $user );
            } else if ( $allowExternal ) {
                $principal = $robotPrincipal;
            } else {
                $eventLog->addEvent( System_Api_EventLog::Cron, System_Api_EventLog::Warning, $eventLog->tr( 'Ignored inbox email from unknown address "%1"', null, $fromEmail ) );
                $principal = null;
            }

            if ( $principal != null ) {
                System_Api_Principal::setCurrent( $principal );

                $folder = null;
                $issue = null;

                if ( preg_match( '/\[#(\d+)\]/', $headers[ 'subject' ], $matches ) ) {
                    $issueId = $matches[ 1 ];
                    try {
                        $issue = $issueManager->getIssue( $issueId );
                    } catch ( System_Api_Error $e ) {
                        $eventLog->addEvent( System_Api_EventLog::Cron, System_Api_EventLog::Warning, $eventLog->tr( 'Ignored inbox email from "%1" because issue %2 is inaccessible', null, $fromEmail, '#' . $issueId ) );
                    }
                } else {
                    $folderId = null;

                    if ( $mapFolder ) {
                        $toEmail = $this->matchRecipient( $mapPattern, $headers, $matches );

                        if ( $toEmail != null ) {
                            $matching = array();

                            foreach ( $allFolders as $row ) {
                                if ( $this->matchPart( $matches[ 1 ], $row[ 'project_name' ] ) && $this->matchPart( $matches[ 2 ], $row[ 'folder_name' ] ) )
                                    $matching[] = $row[ 'folder_id' ];
                            }

                            if ( count( $matching ) == 1 ) {
                                $folderId = $matching[ 0 ];
                            } else if ( count( $matching ) > 1 ) {
                                $eventLog->addEvent( System_Api_EventLog::Cron, System_Api_EventLog::Warning, $eventLog->tr( 'Ambiguous folder for inbox email address "%1"', null, $toEmail ) );
                            } else {
                                $eventLog->addEvent( System_Api_EventLog::Cron, System_Api_EventLog::Warning, $eventLog->tr( 'No matching folder for inbox email address "%1"', null, $toEmail ) );
                            }
                        }
                    }

                    if ( $folderId != null ) {
                        try {
                            $folder = $projectManager->getFolder( $folderId );
                        } catch ( System_Api_Error $e ) {
                            $eventLog->addEvent( System_Api_EventLog::Cron, System_Api_EventLog::Warning, $eventLog->tr( 'Ignored inbox email from "%1" to "%2" because folder is inaccessible', null, $fromEmail, $toEmail ) );
                        }
                    } else if ( $defaultFolderId != null ) {
                        try {
                            $folder = $projectManager->getFolder( $defaultFolderId );
                        } catch ( System_Api_Error $e ) {
                            $eventLog->addEvent( System_Api_EventLog::Cron, System_Api_EventLog::Warning, $eventLog->tr( 'Ignored inbox email from "%1" because default folder is inaccessible', null, $fromEmail ) );
                        }
                    } else {
                        $eventLog->addEvent( System_Api_EventLog::Cron, System_Api_EventLog::Warning, $eventLog->tr( 'Ignored inbox email from "%1" because folder cannot be mapped', null, $fromEmail ) );
                    }
                }

                if ( $issue != null || $folder != null ) {
                    $issueId = null;

                    $parts = $this->inboxEngine->getStructure( $msgno );
                    
                    try {
                        $text = $this->formatHeaders( $headers );

                        foreach ( $parts as $part ) {
                            if ( $part[ 'type' ] == 'plain' ) {
                                $text .= $this->inboxEngine->convertToUtf8( $part );
                                break;
                            }
                        }

                        $text = $parser->normalizeString( $text, null, System_Api_Parser::MultiLine );
                        $text = preg_replace( '/\n(?:[ \t]*\n)+/', "\n\n", $text );

                        $maxLength = $serverManager->getSetting( 'comment_max_length' );
                        if ( mb_strlen( $text ) > $maxLength )
                            $text = mb_substr( $text, 0, $maxLength - 3 ) . '...';

                        if ( $issue == null ) {
                            $name = $headers[ 'subject' ];
                            $name = $parser->normalizeString( $name, null, System_Api_Parser::AllowEmpty );
                            if ( mb_strlen( $name ) > System_Const::ValueMaxLength )
                                $name = mb_substr( $name, 0, System_Const::ValueMaxLength - 3 ) . '...';
                            if ( $name == '' )
                                $name = $this->tr( 'No subject' );

                            $values = $typeManager->getDefaultAttributeValuesForFolder( $folder );

                            $issueId = $issueManager->addIssue( $folder, $name, $values );
                            $issue = $issueManager->getIssue( $issueId );

                            $issueManager->addDescription( $issue, $text, System_Const::PlainText );

                            $emailId = '#' . $issueId;
                        } else {
                            $commentId = $issueManager->addComment( $issue, $text, System_Const::PlainText );
                            $emailId = '#' . $commentId;
                        }

                        $received++;

                        foreach ( $parts as $part ) {
                            if ( $part[ 'type' ] == 'html' || $part[ 'type' ] == 'attachment' ) {
                                $size = strlen( $part[ 'body' ] );

                                if ( $size > $serverManager->getSetting( 'file_max_size' ) ) {
                                    $eventLog->addEvent( System_Api_EventLog::Cron, System_Api_EventLog::Warning, $eventLog->tr( 'Attachment for message %1 from "%2" exceeded maximum size', null, $emailId, $fromEmail ) );
                                    continue;
                                }

                                if ( $part[ 'type' ] == 'html' ) {
                                    $name = 'message.html';
                                    $description = $this->tr( 'HTML message for email %1', null, $emailId );
                                } else {
                                    $name = $part[ 'name' ];
                                    $parser->checkString( $name, System_Const::FileNameMaxLength );
                                    $description = $this->tr( 'Attachment for email %1', null, $emailId );
                                }

                                $attachment = new System_Core_Attachment( $part[ 'body' ], $size, $name );
                                $issueManager->addFile( $issue, $attachment, $name, $description );
                            }
                        }
                    } catch ( System_Api_Error $e ) {
                        $eventLog->addErrorEvent( $e );
                    }

                    if ( $respond && $this->mailEngine != null && $issueId != null ) {
                        if ( $user != null ) {
                            $locale = new System_Api_Locale();
                            $this->translator->setLanguage( System_Core_Translator::UserLanguage, $locale->getSetting( 'language' ) );

                            System_Web_Base::setLinkMode( System_Web_Base::MailLinks );
                        } else {
                            System_Web_Base::setLinkMode( System_Web_Base::NoInternalLinks );
                        }

                        $mail = System_Web_Component::createComponent( 'Common_Mail_IssueCreated', null, $issue );

                        $body = $mail->run();
                        $subject = $mail->getView()->getSlot( 'subject' );

                        $this->mailEngine->send( $fromEmail, $user != null ? $user[ 'user_name' ] : null, $subject, $body );

                        if ( $user != null )
                            $this->translator->setLanguage( System_Core_Translator::UserLanguage, null );
                    }

                    $processed = true;
                }
            }

            System_Api_Principal::setCurrent( null );

            if ( !$leaveMessages )
                $this->inboxEngine->markAsDeleted( $msgno );
            else if ( !$processed )
                $this->inboxEngine->markAsProcessed( $msgno );
        }

        if ( $received > 0 )
            $eventLog->addEvent( System_Api_EventLog::Cron, System_Api_EventLog::Information, $eventLog->tr( 'Processed %1 inbox emails', null, $received ) );
    }

    private function matchRecipient( $mapPattern, $headers, &$matches )
    {
        foreach ( $headers[ 'to' ] as $recipient ) {
            if ( preg_match( $mapPattern, $recipient[ 'email' ], $matches ) )
                return $recipient[ 'email' ];
        }

        foreach ( $headers[ 'cc' ] as $recipient ) {
            if ( preg_match( $mapPattern, $recipient[ 'email' ], $matches ) )
                return $recipient[ 'email' ];
        }

        return null;
    }

    private function matchPart( $part, $name )
    {
        $name = preg_replace( '/\W+/ui', '', $name );

        if ( $name != '' && mb_strripos( $name, $part ) !== false )
            return true;

        return false;
    }

    private function formatHeaders( $headers )
    {
        $text = $this->tr( 'From:' ) . ' ' . $this->formatAddress( $headers[ 'from' ] ) . "\n";

        if ( !empty( $headers[ 'to' ] ) ) {
            $to = array();
            foreach ( $headers[ 'to' ] as $addr )
                $to[] = $this->formatAddress( $addr );
            $text .= $this->tr( 'To:' ) . ' ' . implode( '; ', $to ) . "\n";
        }

        if ( !empty( $headers[ 'cc' ] ) ) {
            $cc = array();
            foreach ( $headers[ 'cc' ] as $addr )
                $cc[] = $this->formatAddress( $addr );
            $text .= $this->tr( 'CC:' ) . ' ' . implode( '; ', $cc ) . "\n";
        }

        $text .= $this->tr( 'Subject:' ) . ' ' . $headers[ 'subject' ] . "\n\n";

        return $text;
    }

    private function formatAddress( $addr )
    {
        $text = '';
        if ( isset( $addr[ 'name' ] ) )
            $text = $addr[ 'name' ] . ' ';
        $text .= '<' . $addr[ 'email' ] . '>';
        return $text;
    }

    private function tr( $source, $comment = null )
    {
        $args = func_get_args();
        return $this->translator->translate( System_Core_Translator::SystemLanguage, get_class( $this ), $args );
    }
}

System_Bootstrap::run( 'Cron_Job' );
