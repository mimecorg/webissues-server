<?php
/**************************************************************************
* This file is part of the WebIssues Server program
* Copyright (C) 2006 Michał Męciński
* Copyright (C) 2007-2017 WebIssues Team
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

class Admin_Settings_Index extends System_Web_Component
{
    protected function __construct()
    {
        parent::__construct();
    }

    protected function execute()
    {
        $this->view->setDecoratorClass( 'Common_FixedBlock' );
        $this->view->setSlot( 'page_title', $this->tr( 'Server Settings' ) );

        Common_Tools_Locale::registerFields( $fields );
        Common_Tools_PageSize::registerFields( $fields );
        Common_Tools_ViewSettings::registerFields( $fields );
        Common_Tools_Editing::registerFields( $fields );
        $fields[ 'project_page_mobile' ] = 'projectPageMobile';
        $fields[ 'folder_page_mobile' ] = 'folderPageMobile';
        $fields[ 'history_page_mobile' ] = 'historyPageMobile';

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

        $this->projectOptions = array();
        foreach ( array( 5, 10, 15, 20, 25, 30 ) as $i )
            $this->projectOptions[ $i ] = $i;

        $this->folderOptions = array();
        foreach ( array( 5, 10, 15, 20, 25, 30 ) as $i )
            $this->folderOptions[ $i ] = $i;

        $this->historyOptions = array();
        foreach ( array( 10, 20, 30, 40, 50 ) as $i )
            $this->historyOptions[ $i ] = $i;
    }
}

System_Bootstrap::run( 'Common_Application', 'Admin_Settings_Index' );
