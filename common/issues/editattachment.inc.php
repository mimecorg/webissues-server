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

class Common_Issues_EditAttachment extends System_Web_Component
{
    protected function __construct()
    {
        parent::__construct();
    }

    protected function execute()
    {
        $issueManager = new System_Api_IssueManager();
        $fileId = (int)$this->request->getQueryString( 'id' );
        $file = $issueManager->getFile( $fileId, System_Api_IssueManager::RequireAdministratorOrOwner );
        $issue = $issueManager->getIssue( $file[ 'issue_id' ] );

        $this->oldFileName = $file[ 'file_name' ];

        $this->view->setDecoratorClass( 'Common_FixedBlock' );
        $this->view->setSlot( 'page_title', $this->tr( 'Edit Attachment' ) );

        $breadcrumbs = new Common_Breadcrumbs( $this );
        $breadcrumbs->initialize( Common_Breadcrumbs::Issue, $issue );

        $this->form = new System_Web_Form( 'issues', $this );
        $this->form->addField( 'fileName', $file[ 'file_name' ] );
        $this->form->addField( 'description', $file[ 'file_descr' ] );

        $this->form->addTextRule( 'fileName', System_Const::FileNameMaxLength );
        $this->form->addTextRule( 'description', System_Const::DescriptionMaxLength, System_Api_Validator::AllowEmpty );

        if ( $this->form->loadForm() ) {
            if ( $this->form->isSubmittedWith( 'cancel' ) )
                $this->response->redirect( $breadcrumbs->getParentUrl() );

            $this->form->validate();

            if ( !System_Core_FileSystem::isValidFileName( $this->fileName ) )
                $this->form->setError( 'fileName', $this->tr( 'Invalid file name' ) );

            if ( $this->form->isSubmittedWith( 'ok' ) && !$this->form->hasErrors() ) {
                $issueManager->editFile( $file, $this->fileName, $this->description );
                $this->response->redirect( $breadcrumbs->getParentUrl() );
            }
        }
    }
}
