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

class Common_Tools_About extends System_Web_Component
{
    protected function __construct()
    {
        parent::__construct();
    }

    protected function execute()
    {
        $this->view->setDecoratorClass( 'Common_FixedBlock' );
        $this->view->setSlot( 'page_title', $this->tr( 'About WebIssues' ) );

        $helper = new Common_Tools_Helper();
        $breadcrumbs = $helper->getBreadcrumbs( $this );

        $this->form = new System_Web_Form( 'tools', $this );
        $this->form->addViewState( 'checkState' );
        $this->form->addViewState( 'updateVersion' );
        $this->form->addViewState( 'notesUrl' );
        $this->form->addViewState( 'downloadUrl' );

        $manualUrl = System_Core_Application::getInstance()->getManualUrl();
        $donateUrl = 'http://webissues.mimec.org/donations';

        $javaScript = new System_Web_JavaScript( $this->view );
        $javaScript->registerExternalLink( $this->form->getSubmitSelector( 'manual' ), $manualUrl );
        $javaScript->registerExternalLink( $this->form->getSubmitSelector( 'donate' ), $donateUrl );

        $this->checkLastVersion = System_Api_Principal::getCurrent()->isAdministrator() && @class_exists( 'DOMDocument' ) && ini_get( 'allow_url_fopen' );

        if ( $this->form->loadForm() ) {
            if ( $this->form->isSubmittedWith( 'ok' ) )
                $this->response->redirect( $breadcrumbs->getParentUrl() );

            if ( $this->form->isSubmittedWith( 'manual' ) )
                $this->response->redirect( $manualUrl );

            if ( $this->form->isSubmittedWith( 'donate' ) )
                $this->response->redirect( $donateUrl );

            if ( $this->form->isSubmittedWith( 'notes' ) && $this->notesUrl != '' )
                $this->response->redirect( $this->notesUrl );

            if ( $this->form->isSubmittedWith( 'download' ) && $this->downloadUrl != '' )
                $this->response->redirect( $this->downloadUrl );

            if ( $this->form->isSubmittedWith( 'lastVersion' ) && $this->checkLastVersion ) {
                $url = $this->appendQueryString( 'http://update.mimec.org/service.php', array( 'app' => 'webissues', 'ver' => WI_VERSION ) );
                $url = str_replace( '&amp;', '&', $url );
                $document = new DOMDocument();
                if ( @$document->load( $url ) ) {
                    $this->checkState = 'current';
                    foreach ( $document->documentElement->childNodes as $node ) {
                        if ( $node->nodeType == XML_ELEMENT_NODE && $node->tagName == 'version' ) {
                            $this->checkState = 'available';
                            $this->updateVersion = $node->getAttribute( 'id' );
                            foreach ( $node->childNodes as $subNode ) {
                                if ( $subNode->nodeType == XML_ELEMENT_NODE && $subNode->tagName == 'notesUrl' )
                                    $this->notesUrl = $subNode->textContent;
                                else if ( $subNode->nodeType == XML_ELEMENT_NODE && $subNode->tagName == 'downloadUrl' )
                                    $this->downloadUrl = $subNode->textContent;
                            }
                            break;
                        }
                    }
                } else {
                    $this->checkState = 'error';
                }
            }
        }

        if ( $this->notesUrl != '' )
            $javaScript->registerExternalLink( $this->form->getSubmitSelector( 'notes' ), $this->notesUrl );
        if ( $this->downloadUrl != '' )
            $javaScript->registerExternalLink( $this->form->getSubmitSelector( 'download' ), $this->downloadUrl );
    }
}
