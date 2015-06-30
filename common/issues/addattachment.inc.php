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

class Common_Issues_AddAttachment extends System_Web_Component
{
    private $attachment = null;
    private $fileName = null;

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
        $this->view->setSlot( 'page_title', $this->tr( 'Add Attachment' ) );

        $breadcrumbs = new Common_Breadcrumbs( $this );
        $breadcrumbs->initialize( Common_Breadcrumbs::Issue, $this->issue );

        $this->form = new System_Web_Form( 'issues', $this );
        $this->form->addField( 'file' );
        $this->form->addField( 'description', '' );

        $this->form->addTextRule( 'description', System_Const::DescriptionMaxLength, System_Api_Validator::AllowEmpty );
        $this->form->addRequiredRule( 'file' );

        if ( $this->form->loadForm() ) {
            if ( $this->form->isSubmittedWith( 'cancel' ) )
                $this->response->redirect( $breadcrumbs->getParentUrl() );

            $this->validate();

            if ( $this->form->isSubmittedWith( 'ok' ) && !$this->form->hasErrors() ) {
                $this->submit();
                if ( !$this->form->hasErrors() )
                    $this->response->redirect( $breadcrumbs->getParentUrl() );
            }
        }
    }

    private function validate( )
    {
        $this->form->validate();

        $parser = new System_Api_Parser();

        $this->attachment = $this->request->getUploadedFile( 'file' );
        if ( $this->attachment === null ) {
            $this->form->setError( 'file', $this->tr( 'No file uploaded' ) );
        } else if ( $this->attachment === false ) {
            $this->form->setError( 'file', $this->tr( 'An error occurred while uploading the file' ) );
        } else {
            $serverManager = new System_Api_ServerManager();
            if ( $this->attachment->getSize() > $serverManager->getSetting( 'file_max_size' ) ) {
                $this->form->setError( 'file', $this->tr( 'File too large' ) );
            } else {
                $this->fileName = $this->attachment->getFileName();
                try {
                    if ( !System_Core_FileSystem::isValidFileName( $this->fileName ) )
                        throw new System_Api_Error( System_Api_Error::InvalidValue );
                    $parser->checkString( $this->fileName, System_Const::FileNameMaxLength );
                } catch ( System_Api_Error $ex ) {
                    $this->form->setError( 'file', $this->tr( 'Invalid file name' ) );
                }
            }
        }
    }

    private function submit()
    {
        $issueManager = new System_Api_IssueManager();
        $issueManager->addFile( $this->issue, $this->attachment, $this->fileName, $this->description );
    }
}
