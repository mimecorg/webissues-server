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

class Client_Alerts_Alert extends System_Web_Component
{
    private $folder = null;
    private $type = null;

    protected function __construct()
    {
        parent::__construct();
    }

    protected function execute()
    {
        $this->isPublic = false;
        $this->oldAlert = null;

        $baseName = $this->request->getScriptBaseName();

        if ( $baseName == 'add' )
            $this->isPublic = (int)$this->request->getQueryString( 'public' ) == 1;

        $projectManager = new System_Api_ProjectManager();
        $typeManager = new System_Api_TypeManager();

        $folderId = (int)$this->request->getQueryString( 'folder' );
        if ( $folderId != 0 ) {
            $this->folder = $projectManager->getFolder( $folderId, $this->isPublic ? System_Api_ProjectManager::RequireAdministrator : 0 );
            $this->type = $typeManager->getIssueTypeForFolder( $this->folder );
            $this->folderName = $this->folder[ 'folder_name' ];
        } else {
            if ( $this->isPublic && !System_Api_Principal::getCurrent()->isAdministrator() )
                throw new System_Api_Error( System_Api_Error::AccessDenied );
            $typeId = (int)$this->request->getQueryString( 'type' );
            $this->type = $typeManager->getIssueType( $typeId );
            $this->typeName = $this->type[ 'type_name' ];
        }

        $breadcrumbs = new Common_Breadcrumbs( $this );
        if ( $this->folder != null )
            $breadcrumbs->initialize( Common_Breadcrumbs::ManageAlerts, $this->folder );
        else
            $breadcrumbs->initialize( Common_Breadcrumbs::ManageAlerts, $this->type );

        $helper = new Client_Alerts_Helper();
        $this->emailEngine = $helper->hasEmailEngine();

        $alertManager = new System_Api_AlertManager();

        if ( $baseName == 'modify' ) {
            if ( !$this->emailEngine )
                throw new System_Core_Exception( 'Email engine is disabled' );

            $alertId = (int)$this->request->getQueryString( 'id' );
            $this->oldAlert = $alertManager->getAlert( $alertId, System_Api_AlertManager::AllowEdit );

            if ( $this->oldAlert[ 'view_name' ] === null )
                $this->oldAlert[ 'view_name' ] = $this->tr( 'All Issues' );

            $this->isPublic = $this->oldAlert[ 'is_public' ];
            $oldEmail = $this->oldAlert[ 'alert_email' ];
        }

        if ( $baseName == 'add' ) {
            $this->viewOptions = array();

            if ( $this->folder != null ) {
                if ( !$alertManager->hasAllIssuesAlert( $this->folder, $this->isPublic ? System_Api_AlertManager::IsPublic : 0 ) )
                    $this->viewOptions[ 0 ] = $this->tr( 'All Issues' );

                $views = $alertManager->getViewsWithoutAlerts( $this->folder, $this->isPublic ? System_Api_AlertManager::IsPublic : 0 );
            } else {
                if ( !$alertManager->hasAllIssuesGlobalAlert( $this->type, $this->isPublic ? System_Api_AlertManager::IsPublic : 0 ) )
                    $this->viewOptions[ 0 ] = $this->tr( 'All Issues' );

                $views = $alertManager->getViewsWithoutGlobalAlerts( $this->type, $this->isPublic ? System_Api_AlertManager::IsPublic : 0 );
            }

            if ( !empty( $views[ 0 ] ) )
                $this->viewOptions[ $this->tr( 'Personal Views' ) ] = $views[ 0 ];

            if ( !empty( $views[ 1 ] ) )
                $this->viewOptions[ $this->tr( 'Public Views' ) ] = $views[ 1 ];

            if ( empty( $this->viewOptions ) )
                $this->noViews = true;

            $oldEmail = System_Const::NoEmail;
        }

        if ( $this->emailEngine && !$this->isPublic ) {
            $preferences = new System_Api_PreferencesManager();
            if ( $preferences->getPreference( 'email' ) == null )
                $this->noEmailAddress = true;
        }

        $this->form = new System_Web_Form( 'alerts', $this );

        if ( $baseName == 'add' ) {
            $this->form->addField( 'viewId', '' );
            $this->form->addItemsRule( 'viewId', $this->viewOptions );
        }

        if ( $this->emailEngine ) {
            $this->form->addField( 'alertEmail', $oldEmail );

            $this->emailTypes = $helper->getEmailTypes();
            $this->form->addItemsRule( 'alertEmail', $this->emailTypes );

            $fields[ 'summary_days' ] = 'summaryDays';
            $fields[ 'summary_hours' ] = 'summaryHours';

            $localeHelper = new System_Web_LocaleHelper();
            $this->days = $localeHelper->getDaysOfWeek();
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

            $locale = new System_Api_Locale();
            $this->firstDay = $locale->getSetting( 'first_day_of_week' );
        }

        if ( $baseName == 'add' ) {
            if ( empty( $this->viewOptions ) )
                $this->view->setDecoratorClass( 'Common_MessageBlock' );
            else
                $this->view->setDecoratorClass( 'Common_FixedBlock' );
            if ( $this->isPublic )
                $this->view->setSlot( 'page_title', $this->tr( 'Add Public Alert' ) );
            else
                $this->view->setSlot( 'page_title', $this->tr( 'Add Personal Alert' ) );
        } else {
            $this->view->setDecoratorClass( 'Common_FixedBlock' );
            $this->view->setSlot( 'page_title', $this->tr( 'Modify Alert' ) );
        }

        if ( $this->form->loadForm() ) {
            if ( $this->form->isSubmittedWith( 'cancel' ) || $this->form->isSubmittedWith( 'close' ) )
                $this->response->redirect( $breadcrumbs->getParentUrl() );

            $this->form->validate();

            if ( $this->form->isSubmittedWith( 'ok' ) && !$this->form->hasErrors() ) {
                $this->submit();
                if ( !$this->form->hasErrors() )
                    $this->response->redirect( $breadcrumbs->getParentUrl() );
            }
        } else if ( $baseName == 'modify' ) {
            $parser = new System_Api_Parser();

            $days = $parser->convertToIntArray( $this->oldAlert[ 'summary_days' ] );
            foreach ( $days as $day ) {
                $field = 'day' . $day;
                $this->$field = '1';
            }

            $hours = $parser->convertToIntArray( $this->oldAlert[ 'summary_hours' ] );
            foreach ( $hours as $hour ) {
                $field = 'hour' . $hour;
                $this->$field = '1';
            }
        }

        if ( $this->emailEngine ) {
            $javaScript = new System_Web_JavaScript( $this->view );
            $javaScript->registerCheckOnOff( '#day-select', '#day-choices :checkbox', true );
            $javaScript->registerCheckOnOff( '#day-unselect', '#day-choices :checkbox', false );
            $javaScript->registerCheckOnOff( '#hour-select', '#hour-choices :checkbox', true );
            $javaScript->registerCheckOnOff( '#hour-unselect', '#hour-choices :checkbox', false );
        }
    }

    private function submit()
    {
        $view = null;
        $alertEmail = System_Const::NoEmail;
        $summaryDays = null;
        $summaryHours = null;

        if ( $this->oldAlert == null && $this->viewId != 0 ) {
            $viewManager = new System_Api_ViewManager();
            $view = $viewManager->getView( $this->viewId );
        }

        if ( $this->emailEngine ) {
            $alertEmail = $this->alertEmail;

            if ( $alertEmail == System_Const::SummaryNotificationEmail || $alertEmail == System_Const::SummaryReportEmail ) {
                $days = array();
                for ( $day = 0; $day < 7; $day++ ) {
                    $field = 'day' . $day;
                    if ( $this->$field == '1' )
                        $days[] = $day;
                }

                if ( empty( $days ) )
                    $this->form->setError( 'days', $this->tr( 'No days selected' ) );

                $summaryDays = implode( ',', $days );

                $hours = array();
                for ( $hour = 0; $hour < 24; $hour++ ) {
                    $field = 'hour' . $hour;
                    if ( $this->$field == '1' )
                        $hours[] = $hour;
                }

                if ( empty( $hours ) )
                    $this->form->setError( 'hours', $this->tr( 'No hours selected' ) );

                $summaryHours = implode( ',', $hours );
            }
        }

        if ( $this->form->hasErrors() )
            return;

        $alertManager = new System_Api_AlertManager();

        try {
            if ( $this->oldAlert != null )
                $alertManager->modifyAlert( $this->oldAlert, $alertEmail, $summaryDays, $summaryHours );
            else if ( $this->folder != null )
                $alertManager->addAlert( $this->folder, $view, $alertEmail, $summaryDays, $summaryHours, $this->isPublic ? System_Api_AlertManager::IsPublic : 0 );
            else
                $alertManager->addGlobalAlert( $this->type, $view, $alertEmail, $summaryDays, $summaryHours, $this->isPublic ? System_Api_AlertManager::IsPublic : 0 );
        } catch ( System_Api_Error $ex ) {
            $this->form->getErrorHelper()->handleError( 'viewId', $ex );
        }
    }
}
