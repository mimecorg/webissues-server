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

class Common_Issues_Comment extends System_Web_Component
{
    private $comment = null;
    private $issue = null;
    private $oldText = null;

    protected function __construct()
    {
        parent::__construct();
    }

    protected function execute()
    {
        $this->view->setDecoratorClass( 'Common_FixedBlock' );

        switch ( $this->request->getScriptBaseName() ) {
            case 'addcomment':
                $issueManager = new System_Api_IssueManager();
                $issueId = (int)$this->request->getQueryString( 'issue' );
                $this->issue = $issueManager->getIssue( $issueId );

                $reply = $this->request->getQueryString( 'reply' );

                if ( $reply == 'descr' ) {
                    $descr = $issueManager->getDescription( $this->issue );

                    $this->oldText = '[quote ' . $this->tr( 'Description' ) . "]\n" . $descr[ 'descr_text' ] . "\n[/quote]\n\n";
                    $defaultFormat = System_Const::TextWithMarkup;
                } else if ( $reply != null ) {
                    $commentId = (int)$reply;
                    $comment = $issueManager->getComment( $commentId );

                    $this->oldText = '[quote ' . $this->tr( 'Comment %1', null, '#' . $commentId ) . "]\n" . $comment[ 'comment_text' ] . "\n[/quote]\n\n";
                    $defaultFormat = System_Const::TextWithMarkup;
                } else {
                    $this->oldText = '';

                    $preferencesManager = new System_Api_PreferencesManager();
                    $defaultFormat = $preferencesManager->getPreferenceOrSetting( 'default_format' );
                }

                $this->issueName = $this->issue[ 'issue_name' ];
                $this->commentId = '';

                $this->view->setSlot( 'page_title', $this->tr( 'Add Comment' ) );
                break;

            case 'editcomment':
                $issueManager = new System_Api_IssueManager();
                $commentId = (int)$this->request->getQueryString( 'id' );
                $this->comment = $issueManager->getComment( $commentId, System_Api_IssueManager::RequireAdministratorOrOwner );
                $this->issue = $issueManager->getIssue( $this->comment[ 'issue_id' ] );

                $reply = null;

                $this->oldText = $this->comment[ 'comment_text' ];
                $defaultFormat = $this->comment[ 'comment_format' ];

                $this->issueName = '';
                $this->commentId = '#' . $this->comment[ 'comment_id' ];

                $this->view->setSlot( 'page_title', $this->tr( 'Edit Comment' ) );
                break;

            default:
                throw new System_Core_Exception( 'Invalid URL' );
        }

        $breadcrumbs = new Common_Breadcrumbs( $this );
        $breadcrumbs->initialize( Common_Breadcrumbs::Issue, $this->issue );

        $this->formatOptions = array(
            System_Const::PlainText => $this->tr( 'Plain Text' ),
            System_Const::TextWithMarkup => $this->tr( 'Text with Markup' )
        );

        $this->form = new System_Web_Form( 'issues', $this );
        $this->form->addField( 'commentText', $this->oldText );
        $this->form->addField( 'format', $defaultFormat );

        $serverManager = new System_Api_ServerManager();
        $this->form->addTextRule( 'commentText', $serverManager->getSetting( 'comment_max_length' ), System_Api_Parser::MultiLine );
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
        $javaScript->registerMarkItUp( $this->form->getFieldSelector( 'commentText' ), $this->form->getFieldSelector( 'format' ), '#commentPreview' );

        if ( $reply != null )
            $javaScript->registerGoToEnd( $this->form->getFieldSelector( 'commentText' ) );
    }

    private function submit()
    {
        $issueManager = new System_Api_IssueManager();
        if ( $this->comment == null )
            $issueManager->addComment( $this->issue, $this->commentText, $this->format );
        else
            $issueManager->editComment( $this->comment, $this->commentText, $this->format );
    }
}
