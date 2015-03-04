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

class Client_Projects_AddProject extends System_Web_Component
{
    private $parentUrl = null;

    protected function __construct()
    {
        parent::__construct();
    }

    protected function execute()
    {
        if ( !System_Api_Principal::getCurrent()->isAdministrator() )
            throw new System_Api_Error( System_Api_Error::AccessDenied );

        $breadcrumbs = new Common_Breadcrumbs( $this );
        $breadcrumbs->initialize( Common_Breadcrumbs::ManageProjects );
        $this->parentUrl = $breadcrumbs->getParentUrl();

        $this->view->setDecoratorClass( 'Common_FixedBlock' );
        $this->view->setSlot( 'page_title', $this->tr( 'Add Project' ) );

        $preferencesManager = new System_Api_PreferencesManager();
        $defaultFormat = $preferencesManager->getPreferenceOrSetting( 'default_format' );

        $this->formatOptions = array(
            System_Const::PlainText => $this->tr( 'Plain Text' ),
            System_Const::TextWithMarkup => $this->tr( 'Text with Markup' )
        );

        $this->form = new System_Web_Form( 'projects', $this );
        $this->form->addField( 'projectName', '' );
        $this->form->addField( 'descriptionText', '' );
        $this->form->addField( 'format', $defaultFormat );

        $serverManager = new System_Api_ServerManager();
        $this->form->addTextRule( 'projectName', System_Const::NameMaxLength );
        $this->form->addTextRule( 'descriptionText', $serverManager->getSetting( 'comment_max_length' ), System_Api_Parser::MultiLine | System_Api_Parser::AllowEmpty );
        $this->form->addItemsRule( 'format', $this->formatOptions );

        if ( $this->form->loadForm() ) {
            if ( $this->form->isSubmittedWith( 'cancel' ) )
                $this->response->redirect( $this->parentUrl );

            $this->form->validate();

            if ( $this->form->isSubmittedWith( 'ok' ) && !$this->form->hasErrors() ) {
                $this->submit();
                if ( !$this->form->hasErrors() )
                    $this->response->redirect( $this->parentUrl );
            }
        }

        $javaScript = new System_Web_JavaScript( $this->view );
        $javaScript->registerMarkItUp( $this->form->getFieldSelector( 'descriptionText' ), $this->form->getFieldSelector( 'format' ), '#descriptionPreview' );
    }

    private function submit()
    {
        $projectManager = new System_Api_ProjectManager();
        try {
            $projectId = $projectManager->addProject( $this->projectName );
            if ( $this->descriptionText != '' ) {
                $project = $projectManager->getProject( $projectId );
                $projectManager->addProjectDescription( $project, $this->descriptionText, $this->format );
            }
        } catch ( System_Api_Error $ex ) {
            $this->form->getErrorHelper()->handleError( 'projectName', $ex );
        }
    }
}

System_Bootstrap::run( 'Common_Application', 'Client_Projects_AddProject' );
