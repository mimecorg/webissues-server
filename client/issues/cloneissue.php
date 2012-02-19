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

class Client_Issues_CloneIssue extends System_Web_Component
{
    protected function __construct()
    {
        parent::__construct();
    }

    protected function execute()
    {
        $issueManager = new System_Api_IssueManager();
        $issueId = (int)$this->request->getQueryString( 'issue' );
        $this->issue = $issueManager->getIssue( $issueId );

        $this->view->setDecoratorClass( 'Common_FixedBlock' );
        $this->view->setSlot( 'page_title', $this->tr( 'Clone Issue' ) );

        $breadcrumbs = new Common_Breadcrumbs( $this );
        $breadcrumbs->initialize( Common_Breadcrumbs::Issue, $this->issue );

        $this->form = new System_Web_Form( 'issues', $this );
        $this->form->addField( 'folder', $this->issue[ 'folder_id' ] );

        $projectManager = new System_Api_ProjectManager();
        $projects = $projectManager->getProjects();
        $folders = $projectManager->getFolders();

        $this->folders = array();

        foreach ( $projects as $project ) {
            $list = array();
            foreach ( $folders as $folder ) {
                if ( $folder[ 'project_id' ] == $project[ 'project_id' ] && $folder[ 'type_id' ] == $this->issue[ 'type_id' ] )
                    $list[ $folder[ 'folder_id' ] ] = $folder[ 'folder_name' ];
            }
            if ( !empty( $list ) )
                $this->folders[ $project[ 'project_name' ] ] = $list;
        }

        $this->form->addItemsRule( 'folder', $this->folders );

        if ( $this->form->loadForm() ) {
            if ( $this->form->isSubmittedWith( 'cancel' ) )
                $this->response->redirect( $breadcrumbs->getParentUrl() );

            $this->form->validate();

            if ( $this->form->isSubmittedWith( 'ok' ) && !$this->form->hasErrors() )
                $this->response->redirect( $this->mergeQueryString( '/client/issues/addissue.php', array( 'folder' => $this->folder, 'clone' => $issueId, 'issue' => null ) ) );
        }
    }
}

System_Bootstrap::run( 'Common_Application', 'Client_Issues_CloneIssue' );
