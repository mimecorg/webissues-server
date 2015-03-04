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

class Client_Projects_Description extends System_Web_Component
{
    private $descr = null;
    private $project = null;

    protected function __construct()
    {
        parent::__construct();
    }

    protected function execute()
    {
        $this->view->setDecoratorClass( 'Common_FixedBlock' );

        $projectManager = new System_Api_ProjectManager();
        $projectId = (int)$this->request->getQueryString( 'project' );

        $this->project = $projectManager->getProject( $projectId, System_Api_ProjectManager::RequireAdministrator );
        $this->projectName = $this->project[ 'project_name' ];

        switch ( $this->request->getScriptBaseName() ) {
            case 'adddescription':
                if ( $this->project[ 'descr_id' ] != null )
                    throw new System_Api_Error( System_Api_Error::DescriptionAlreadyExists );

                $oldText = '';

                $preferencesManager = new System_Api_PreferencesManager();
                $defaultFormat = $preferencesManager->getPreferenceOrSetting( 'default_format' );

                $this->view->setSlot( 'page_title', $this->tr( 'Add Description' ) );
                break;

            case 'editdescription':
                $this->descr = $projectManager->getProjectDescription( $this->project );

                $oldText = $this->descr[ 'descr_text' ];
                $defaultFormat = $this->descr[ 'descr_format' ];

                $this->exists = true;

                $this->view->setSlot( 'page_title', $this->tr( 'Edit Description' ) );
                break;

            default:
                throw new System_Core_Exception( 'Invalid URL' );
        }

        $breadcrumbs = new Common_Breadcrumbs( $this );
        $breadcrumbs->initialize( Common_Breadcrumbs::Project, $this->project );

        $this->formatOptions = array(
            System_Const::PlainText => $this->tr( 'Plain Text' ),
            System_Const::TextWithMarkup => $this->tr( 'Text with Markup' )
        );

        $this->form = new System_Web_Form( 'projects', $this );
        $this->form->addField( 'descriptionText', $oldText );
        $this->form->addField( 'format', $defaultFormat );

        $serverManager = new System_Api_ServerManager();
        $this->form->addTextRule( 'descriptionText', $serverManager->getSetting( 'comment_max_length' ), System_Api_Parser::MultiLine );
        $this->form->addItemsRule( 'format', $this->formatOptions );

        if ( $this->form->loadForm() ) {
            if ( $this->form->isSubmittedWith( 'cancel' ) )
                $this->response->redirect( $breadcrumbs->getParentUrl() );

            $this->form->validate();

            if ( $this->form->isSubmittedWith( 'ok' ) && !$this->form->hasErrors() ) {
                $this->submit();
                $this->response->redirect( $breadcrumbs->getParentUrl() );
            }
        }

        $javaScript = new System_Web_JavaScript( $this->view );
        $javaScript->registerMarkItUp( $this->form->getFieldSelector( 'descriptionText' ), $this->form->getFieldSelector( 'format' ), '#descriptionPreview' );
    }

    private function submit()
    {
        $projectManager = new System_Api_ProjectManager();
        if ( !$this->exists )
            $projectManager->addProjectDescription( $this->project, $this->descriptionText, $this->format );
        else
            $projectManager->editProjectDescription( $this->descr, $this->descriptionText, $this->format );
    }
}
