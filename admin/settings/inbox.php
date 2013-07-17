<?php
/**************************************************************************
* This file is part of the WebIssues Server program
* Copyright (C) 2006 Michał Męciński
* Copyright (C) 2007-2012 WebIssues Team
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

class Admin_Settings_Inbox extends System_Web_Component
{
    protected function __construct()
    {
        parent::__construct();
    }

    protected function execute()
    {
        $this->view->setSlot( 'page_title', $this->tr( 'Inbox Settings' ) );

        if ( !function_exists( 'imap_open' ) ) {
            $this->view->setDecoratorClass( 'Common_MessageBlock' );

            $this->form = new System_Web_Form( 'settings', $this );

            if ( $this->form->loadForm() && $this->form->isSubmittedWith( 'ok' ) )
                $this->response->redirect( '/admin/index.php' );

            $this->noImap = true;
            return;
        }

        $this->view->setDecoratorClass( 'Common_FixedBlock' );

        $fields[ 'inbox_engine' ] = 'inboxEngine';
        $fields[ 'inbox_email' ] = 'inboxEmail';
        $fields[ 'inbox_server' ] = 'inboxServer';
        $fields[ 'inbox_port' ] = 'inboxPort';
        $fields[ 'inbox_encryption' ] = 'inboxEncryption';
        $fields[ 'inbox_user' ] = 'inboxUser';
        $fields[ 'inbox_password' ] = 'inboxPassword';
        $fields[ 'inbox_mailbox' ] = 'inboxMailbox';
        $fields[ 'inbox_no_validate' ] = 'inboxNoValidate';
        $fields[ 'inbox_leave_messages' ] = 'inboxLeaveMessages';
        $fields[ 'inbox_allow_external' ] = 'inboxAllowExternal';
        $fields[ 'inbox_robot' ] = 'inboxRobot';
        $fields[ 'inbox_map_folder' ] = 'inboxMapFolder';
        $fields[ 'inbox_default_folder' ] = 'inboxDefaultFolder';
        $fields[ 'inbox_respond' ] = 'inboxRespond';
        $fields[ 'inbox_subscribe' ] = 'inboxSubscribe';

        $this->form = new System_Web_Form( 'settings', $this );
        foreach ( $fields as $field )
            $this->form->addField( $field );

        $settingHelper = new Admin_Settings_Helper( $this );

        if ( $this->form->loadForm() ) {
            if ( $this->form->isSubmittedWith( 'cancel' ) )
                $this->response->redirect( '/admin/index.php' );

            $values = $settingHelper->validateSettings( $fields );

            if ( $this->inboxEngine != '' )
                $this->validateConsistency();

            if ( $this->form->isSubmittedWith( 'ok' ) && !$this->form->hasErrors() ) {
                $settingHelper->submitSettings( $values );
                $this->response->redirect( '/admin/index.php' );
            }

            if ( $this->form->isSubmittedWith( 'test' ) && !$this->form->hasErrors() )
                $this->testMailboxConnection( $values );
        } else {
            $settingHelper->loadSettings( $fields );
        }

        $this->engineOptions = array(
            '' => $this->tr( 'Disabled' ),
            'imap' => $this->tr( 'IMAP server' ),
            'pop3' => $this->tr( 'POP3 server' ) );

        $this->encryptionOptions = array(
            '' => $this->tr( 'None', 'encryption' ),
            'ssl' => 'SSL',
            'tls' => 'TLS' );

        $userManager = new System_Api_UserManager();
        $users = $userManager->getUsers();

        $this->users = array( '' => $this->tr( 'None', 'user' ) );

        foreach ( $users as $user )
            $this->users[ $user[ 'user_id' ] ] = $user[ 'user_name' ];

        $projectManager = new System_Api_ProjectManager();
        $projects = $projectManager->getProjects();
        $folders = $projectManager->getFolders();

        $this->folders = array( '' => $this->tr( 'None', 'folder' ) );

        foreach ( $projects as $project ) {
            $list = array();
            foreach ( $folders as $folder ) {
                if ( $folder[ 'project_id' ] == $project[ 'project_id' ] )
                    $list[ $folder[ 'folder_id' ] ] = $folder[ 'folder_name' ];
            }
            if ( !empty( $list ) )
                $this->folders[ $project[ 'project_name' ] ] = $list;
        }
    }

    private function validateConsistency()
    {
        $errorHelper = $this->form->getErrorHelper();

        if ( $this->inboxEmail == '' )
            $errorHelper->handleError( 'inboxEmail', System_Api_Error::EmptyValue );

        if ( $this->inboxServer == '' )
            $errorHelper->handleError( 'inboxServer', System_Api_Error::EmptyValue );

        if ( $this->inboxPort == '' )
            $errorHelper->handleError( 'inboxPort', System_Api_Error::EmptyValue );

        if ( $this->inboxEngine == 'pop3' && $this->inboxLeaveMessages == 1 )
            $this->form->setError( 'inboxLeaveMessages', $this->tr( 'Cannot leave messages on the server in POP3 mode.' ) );

        if ( $this->inboxAllowExternal == 1 && $this->inboxRobot == '' )
            $errorHelper->handleError( 'inboxRobot', System_Api_Error::EmptyValue );

        if ( $this->inboxMapFolder != 1 && $this->inboxDefaultFolder == '' )
            $errorHelper->handleError( 'inboxDefaultFolder', System_Api_Error::EmptyValue );
    }

    private function testMailboxConnection( $values )
    {
        if ( $this->inboxEngine == '' ) {
            $this->form->setError( 'testConnection', $this->tr( 'Please select the method of receiving emails to test connection.' ) );
            return;
        }

        try {
            $inboxEngine = new System_Mail_InboxEngine();
            $inboxEngine->setSettings( $values );
            $inboxEngine->getMessagesCount();
        } catch ( Exception $ex ) {
            $this->form->setError( 'testConnection', $ex->getMessage() );
            return;
        }

        $this->testSuccessful = true;
    }
}

System_Bootstrap::run( 'Common_Application', 'Admin_Settings_Inbox' );
