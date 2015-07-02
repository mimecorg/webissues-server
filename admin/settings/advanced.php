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

class Admin_Settings_Advanced extends System_Web_Component
{
    protected function __construct()
    {
        parent::__construct();
    }

    protected function execute()
    {
        $this->view->setDecoratorClass( 'Common_FixedBlock' );
        $this->view->setSlot( 'page_title', $this->tr( 'Advanced Settings' ) );

        $fields[ 'comment_max_length' ] = 'commentMaxLength';
        $fields[ 'file_max_size' ] = 'fileMaxSize';
        $fields[ 'file_db_max_size' ] = 'fileDbMaxSize';
        $fields[ 'session_max_lifetime' ] = 'sessionMaxLifetime';
        $fields[ 'log_max_lifetime' ] = 'logMaxLifetime';
        $fields[ 'register_max_lifetime' ] = 'registerMaxLifetime';
        $fields[ 'gc_divisor' ] = 'gcDivisor';

        $this->form = new System_Web_Form( 'settings', $this );
        foreach ( $fields as $field )
            $this->form->addField( $field );

        $settingHelper = new Admin_Settings_Helper( $this );

        if ( $this->form->loadForm() ) {
            if ( $this->form->isSubmittedWith( 'cancel' ) )
                $this->response->redirect( '/admin/index.php' );

            $values = $settingHelper->validateSettings( $fields );

            if ( $this->form->isSubmittedWith( 'ok' ) && !$this->form->hasErrors() ) {
                $settingHelper->submitSettings( $values );
                $this->response->redirect( '/admin/index.php' );
            }
        } else {
            $settingHelper->loadSettings( $fields );
        }

        $this->commentOptions = array();
        foreach ( array( 1000, 2000, 5000, 10000, 20000, 50000, 100000 ) as $i )
            $this->commentOptions[ $i ] = number_format( $i, 0, '.', ',' );

        $this->fileOptions = array();
        for ( $i = 16; $i <= 512; $i *= 2 )
            $this->fileOptions[ 1024 * $i ] = $this->tr( '%1 kB', null, $i );
        for ( $i = 1; $i <= 256; $i *= 2 )
            $this->fileOptions[ 1024 * 1024 * $i ] = $this->tr( '%1 MB', null, $i );

        $this->fileDbOptions = array();
        $this->fileDbOptions[ 0 ] = $this->tr( 'Never' );
        for ( $i = 1; $i <= 256; $i *= 2 )
            $this->fileDbOptions[ 1024 * $i ] = $this->tr( '%1 kB', null, $i );
        $this->fileDbOptions[ System_Const::INT_MAX ] = $this->tr( 'Always' );

        $this->sessionOptions = array();
        for ( $i = 10; $i <= 50; $i += 10 )
            $this->sessionOptions[ 60 * $i ] = $this->tr( '%1 minutes', null, $i );
        $this->sessionOptions[ 3600 ] = $this->tr( '1 hour' );
        foreach ( array( 2, 3, 4, 6, 8, 10, 12, 18, 24 ) as $i )
            $this->sessionOptions[ 3600 * $i ] = $this->tr( '%1 hours', null, $i );

        $this->logOptions = array();
        $this->logOptions[ 86400 ] = $this->tr( '1 day' );
        foreach ( array( 2, 3, 4, 5, 6, 7, 10, 14, 21, 30, 50, 70, 90, 120 ) as $i )
            $this->logOptions[ 86400 * $i ] = $this->tr( '%1 days', null, $i );

        $this->registerOptions = array();
        foreach ( array( 2, 4, 6, 12, 18, 24, 36, 48 ) as $i )
            $this->registerOptions[ 3600 * $i ] = $this->tr( '%1 hours', null, $i );

        $this->gcOptions = array();
        $this->gcOptions[ 0 ] = $this->tr( 'Use cron job' );
        foreach ( array( 10, 100, 1000, 10000 ) as $i )
            $this->gcOptions[ $i ] = '1 / ' . number_format( $i, 0, '.', ',' );
    }
}

System_Bootstrap::run( 'Common_Application', 'Admin_Settings_Advanced' );
