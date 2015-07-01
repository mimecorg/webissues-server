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
        if ( !$this->request->isRelativePathUnder( '/mobile' ) )
            Common_Tools_PageSize::registerFields( $fields );
        Common_Tools_ViewSettings::registerFields( $fields );
        Common_Tools_Editing::registerFields( $fields );

        $serverManager = new System_Api_ServerManager();
        $this->emailEngine = $serverManager->getSetting( 'email_engine' );

        if ( $this->emailEngine )
            $this->addNotificationFields( $fields );

        foreach ( $fields as $field )
            $this->form->addField( $field );

        $this->user = $helper->getUser();
        $this->isOwn = $this->user == null || $this->user[ 'user_id' ] == System_Api_Principal::getCurrent()->getUserId();

        if ( $this->form->loadForm() ) {
            if ( $this->form->isSubmittedWith( 'cancel' ) )
                $this->response->redirect( $breadcrumbs->getParentUrl() );

            $values = $this->validatePreferences( $fields );

            if ( $this->form->isSubmittedWith( 'ok' ) && !$this->form->hasErrors() ) {
                if ( $this->submitPreferences( $this->user, $values ) )
                    $this->response->redirect( $breadcrumbs->getParentUrl() );
            }
        } else {
            $this->loadPreferences( $this->user, $fields );
        }
    }

    private function loadPreferences( $user, $fields )
    {
        $preferencesManager = new System_Api_PreferencesManager( $user );

        foreach ( $fields as $key => $field ) {
            $preferenceValue = $preferencesManager->getPreference( $key );
            $this->$field = $preferenceValue;
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
        try {
            foreach ( $values as $key => $value )
                $preferencesManager->setPreference( $key, $value );
            return true;
        } catch ( System_Api_Error $ex ) {
            $this->form->getErrorHelper()->handleError( 'email', $ex );
            return false;
        }
    }

    private function addNotificationFields( &$fields )
    {
        $fields[ 'email' ] = 'email';
        $fields[ 'notify_details' ] = 'notifyDetails';
        $fields[ 'notify_no_read' ] = 'notifyNoRead';
        $this->form->addField( 'email' );
        $this->form->addField( 'notifyDetails' );
        $this->form->addField( 'notifyNoRead' );

        $this->form->addTextRule( 'email', System_Const::ValueMaxLength, System_Api_Parser::AllowEmpty );
    }
}
