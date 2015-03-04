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

class Client_Projects_AddFolder extends System_Web_Component
{
    private $parentUrl = null;

    protected function __construct()
    {
        parent::__construct();
    }

    protected function execute()
    {
        $projectManager = new System_Api_ProjectManager();
        $projectId = (int)$this->request->getQueryString( 'project' );
        $this->project = $projectManager->getProject( $projectId, System_Api_ProjectManager::RequireAdministrator );

        $breadcrumbs = new Common_Breadcrumbs( $this );
        $breadcrumbs->initialize( Common_Breadcrumbs::ManageProjects );
        $this->parentUrl = $breadcrumbs->getParentUrl();

        $this->form = new System_Web_Form( 'projects', $this );
        $this->form->addField( 'folderName', '' );
        $this->form->addField( 'issueType' );

        $typeManager = new System_Api_TypeManager();
        $types = $typeManager->getIssueTypes();

        $this->issueTypes = array();
        foreach ( $types as $type )
            $this->issueTypes[ $type[ 'type_id' ] ] = $type[ 'type_name' ];

        if ( empty( $this->issueTypes ) )
            $this->view->setDecoratorClass( 'Common_MessageBlock' );
        else
            $this->view->setDecoratorClass( 'Common_FixedBlock' );
        $this->view->setSlot( 'page_title', $this->tr( 'Add Folder' ) );

        $this->form->addTextRule( 'folderName', System_Const::NameMaxLength );
        $this->form->addItemsRule( 'issueType', $this->issueTypes );

        if ( $this->form->loadForm() ) {
            if ( $this->form->isSubmittedWith( 'cancel' ) || $this->form->isSubmittedWith( 'close' ) )
                $this->response->redirect( $this->parentUrl );

            $this->form->validate();

            if ( $this->form->isSubmittedWith( 'ok' ) && !$this->form->hasErrors() ) {
                $this->submit();
                if ( !$this->form->hasErrors() ) {
                    $grid = new System_Web_Grid();
                    $grid->addExpandCookieId( 'wi_projects', $projectId );

                    $this->response->redirect( $this->parentUrl );
                }
            }
        }
    }
    
    private function submit()
    {
        $typeManager = new System_Api_TypeManager();
        $projectManager = new System_Api_ProjectManager();
        try {
            $type = $typeManager->getIssueType( $this->issueType );
            $folderId = $projectManager->addFolder( $this->project, $type, $this->folderName );
        } catch ( System_Api_Error $ex ) {
            $this->form->getErrorHelper()->handleError( 'folderName', $ex );
        }
    }
}

System_Bootstrap::run( 'Common_Application', 'Client_Projects_AddFolder' );
