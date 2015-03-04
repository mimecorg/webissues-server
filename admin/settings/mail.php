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

class Admin_Settings_Mail extends System_Web_Component
{
    protected function __construct()
    {
        parent::__construct();
    }

    protected function execute()
    {
        $this->view->setDecoratorClass( 'Common_FixedBlock' );
        $this->view->setSlot( 'page_title', $this->tr( 'Email Settings' ) );

        $fields[ 'email_engine' ] = 'emailEngine';
        $fields[ 'email_from' ] = 'emailFrom';
        $fields[ 'smtp_server' ] = 'smtpServer';
        $fields[ 'smtp_port' ] = 'smtpPort';
        $fields[ 'smtp_encryption' ] = 'smtpEncryption';
        $fields[ 'smtp_user' ] = 'smtpUser';
        $fields[ 'smtp_password' ] = 'smtpPassword';
        $fields[ 'base_url' ] = 'baseUrl';

        $this->form = new System_Web_Form( 'settings', $this );
        foreach ( $fields as $field )
            $this->form->addField( $field );

        $settingHelper = new Admin_Settings_Helper( $this );

        if ( $this->form->loadForm() ) {
            if ( $this->form->isSubmittedWith( 'cancel' ) )
                $this->response->redirect( '/admin/index.php' );

            if ( $this->form->isSubmittedWith( 'detect' ) )
                $this->baseUrl = WI_BASE_URL . '/';

            $values = $settingHelper->validateSettings( $fields );

            if ( $this->emailEngine != '' )
                $this->validateConsistency();

            if ( $this->form->isSubmittedWith( 'ok' ) && !$this->form->hasErrors() ) {
                $settingHelper->submitSettings( $values );
                $this->response->redirect( '/admin/index.php' );
            }

            if ( $this->form->isSubmittedWith( 'test' ) && !$this->form->hasErrors() )
                $this->testSmtpConnection( $values );
        } else {
            $settingHelper->loadSettings( $fields );
        }

        $this->engineOptions = array(
            '' => $this->tr( 'Disabled' ),
            'standard' => $this->tr( 'Standard PHP mailer' ),
            'smtp' => $this->tr( 'Custom SMTP server' ) );

        $this->encryptionOptions = array(
            '' => $this->tr( 'None' ),
            'ssl' => 'SSL',
            'tls' => 'TLS' );
    }

    private function validateConsistency()
    {
        $errorHelper = $this->form->getErrorHelper();

        if ( $this->emailFrom == '' )
            $errorHelper->handleError( 'emailFrom', System_Api_Error::EmptyValue );

        if ( $this->emailEngine == 'smtp' ) {
            if ( $this->smtpServer == '' )
                $errorHelper->handleError( 'smtpServer', System_Api_Error::EmptyValue );

            if ( $this->smtpPort == '' )
                $errorHelper->handleError( 'smtpPort', System_Api_Error::EmptyValue );
        }
    }

    private function testSmtpConnection( $values )
    {
        if ( $this->emailEngine != 'smtp' ) {
            $this->form->setError( 'testConnection', $this->tr( 'Please select the SMTP method of sending emails to test connection.' ) );
            return;
        }

        $mail = System_Web_Component::createComponent( 'Common_Mail_TestConnection', null, $values );
        $body = $mail->run();
        $subject = $mail->getView()->getSlot( 'subject' );

        try {
            $engine = new System_Mail_Engine();
            $engine->setSettings( $values );
            $engine->send( $values[ 'email_from' ], '', $subject, $body );
        } catch ( phpmailerException $ex ) {
            $this->form->setError( 'testConnection', $ex->getMessage() );
            return;
        }

        $this->testSuccessful = true;
    }
}

System_Bootstrap::run( 'Common_Application', 'Admin_Settings_Mail' );
