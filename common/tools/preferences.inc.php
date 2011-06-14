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

if ( !defined( 'WI_VERSION' ) ) die( -1 );

class Common_Tools_Preferences extends System_Web_Component
{
    protected function __construct()
    {
        parent::__construct();
    }

    protected function execute()
    {
        $this->view->setDecoratorClass( 'Common_FixedBlock' );
        $this->view->setSlot( 'page_title', $this->tr( 'User Preferences' ) );

        $helper = new Common_Tools_Helper();
        $breadcrumbs = $helper->getBreadcrumbs( $this );

        $this->form = new System_Web_Form( 'preferences', $this );

        Common_Tools_Locale::registerFields( $fields );

        foreach ( $fields as $field )
            $this->form->addField( $field );

        $serverManager = new System_Api_ServerManager();
        $this->emailEngine = $serverManager->getSetting( 'email_engine' );

        if ( $this->emailEngine )
            $this->addNotificationFields( $fields );

        $this->user = $helper->getUser();

        if ( $this->form->loadForm() ) {
            if ( $this->form->isSubmittedWith( 'cancel' ) )
                $this->response->redirect( $breadcrumbs->getParentUrl() );

            if ( $this->emailEngine )
                $this->formatDaysHours();

            $values = $this->validatePreferences( $fields );

            if ( $this->form->isSubmittedWith( 'ok' ) && !$this->form->hasErrors() ) {
                $this->submitPreferences( $this->user, $values );
                $this->response->redirect( $breadcrumbs->getParentUrl() );
            }
        } else {
            $this->loadPreferences( $this->user, $fields );
        }

        $javaScript = new System_Web_JavaScript( $this->view );
        $javaScript->registerCheckOnOff( '#day-select', '#day-choices :checkbox', true );
        $javaScript->registerCheckOnOff( '#day-unselect', '#day-choices :checkbox', false );
        $javaScript->registerCheckOnOff( '#hour-select', '#hour-choices :checkbox', true );
        $javaScript->registerCheckOnOff( '#hour-unselect', '#hour-choices :checkbox', false );
    }

    private function loadPreferences( $user, $fields )
    {
        $preferencesManager = new System_Api_PreferencesManager( $user );

        $parser = new System_Api_Parser();

        foreach ( $fields as $key => $field ) {
            $preferenceValue = $preferencesManager->getPreference( $key );
            switch ( $key ) {
                case 'summary_days':
                    $summary_array = $parser->convertToIntArray( $preferenceValue );
                    foreach ( $summary_array as $day )
                        $this->{'day' . $day} = '1';
                    break;
                case 'summary_hours':
                    $summary_array = $parser->convertToIntArray( $preferenceValue );
                    foreach ( $summary_array as $hour )
                        $this->{'hour' . $hour} = '1';
                    break;
                default:
                    $this->$field = $preferenceValue;
            }
        }
    }

    private function validatePreferences( $fields )
    {
        $this->form->validate();

        $parser = new System_Api_Parser();
        $values = array();

        foreach ( $fields as $key => $field ) {
            if ( $this->form->hasErrors( $field ) )
                continue;
            $value = $this->$field;
            try {
                $parser->checkPreference( $key, $value );
            } catch ( System_Api_Error $ex ) {
                $this->form->getErrorHelper()->handleError( $field, $ex );
            }
            $values[ $key ] = $value;
        }

        return $values;
    }

    private function submitPreferences( $user, $values )
    {
        $preferencesManager = new System_Api_PreferencesManager( $user );

        foreach ( $values as $key => $value )
            $preferencesManager->setPreference( $key, $value );
    }

    private function addNotificationFields( &$fields )
    {
        $fields[ 'email' ] = 'email';
        $fields[ 'notify_no_read' ] = 'notifyNoRead';
        $this->form->addField( 'email' );
        $this->form->addField( 'notifyNoRead' );

        $fields[ 'summary_days' ] = 'summaryDays';
        $fields[ 'summary_hours' ] = 'summaryHours';

        $helper = new System_Web_LocaleHelper();
        $this->days = $helper->getDaysOfWeek();
        foreach ( $this->days as $numericDay => $textDay ) {
            $fieldName = 'day' . $numericDay;
            $this->form->addField( $fieldName, false );
        }

        $formatter = new System_Api_Formatter();
        $this->hours = array();
        for ( $i = 0; $i < 24; $i++ ) {
            $hour = sprintf( "%02d:00", $i );
            $this->hours[] = $formatter->convertTime( $hour );
            $this->form->addField( 'hour' . $i, false );
        }
    }

    private function formatDaysHours()
    {
        $summary_array = array();
        foreach ( $this->days as $key => $value ) {
            if ( $this->{'day' . $key} == '1' )
                $summary_array[] = $key;
        }
        $this->summaryDays = implode( ',', $summary_array );

        $summary_array = array();
        for ( $i = 0; $i < 24; $i++ ) {
            if ( $this->{'hour' . $i} == '1' )
                $summary_array[] = $i;
        }
        $this->summaryHours = implode( ',', $summary_array );
    }
}