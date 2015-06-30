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

class Common_Issues_Preview extends System_Core_Application
{
    protected function __construct()
    {
        parent::__construct();
    }

    protected function execute()
    {
        $principal = System_Api_Principal::getCurrent();
        if ( !$principal->isAuthenticated() )
            throw new System_Api_Error( System_Api_Error::LoginRequired );

        $serverManager = new System_Api_ServerManager();
        $parser = new System_Api_Parser();

        $data = $parser->normalizeString( $this->request->getFormField( 'data' ), $serverManager->getSetting( 'comment_max_length' ), System_Api_Parser::MultiLine | System_Api_Parser::AllowEmpty );

        $prettyPrint = false;
        $content = System_Web_MarkupProcessor::convertToRawHtml( $data, $prettyPrint );

        $this->response->setContentType( 'text/html; charset=UTF-8' );
        $this->response->setContent( $content );
    }

    protected function displayErrorPage()
    {
        $exception = System_Core_Application::getInstance()->getFatalError();
        if ( is_a( $exception, 'System_Api_Error' ) ) {
            $helper = new System_Web_ErrorHelper();
            $content = '<div class="error">' . $this->tr( 'Error: %1.', null, $helper->getErrorMessage( $exception->getMessage() ) ) . '</div>';
        } else {
            $content = '<div class="error">' . $this->tr( 'An unexpected error occured while processing the request.' ) . '</div>';
        }

        $this->response->setStatus( '200 OK' );
        $this->response->setContentType( 'text/html; charset=UTF-8' );
        $this->response->setContent( $content );

        $this->response->send();
    }

    private function tr( $source, $comment = null )
    {
        $args = func_get_args();
        return $this->translator->translate( System_Core_Translator::UserLanguage, get_class( $this ), $args );
    }
}
