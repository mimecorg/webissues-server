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

class Server_Application extends System_Core_Application
{
    protected $handler;

    protected function __construct( $handlerClass )
    {
        parent::__construct();

        $this->handler = new $handlerClass();
    }

    public function getHandler()
    {
        return $this->handler;
    }

    protected function execute()
    {
        $this->handler->parseCommand();
        $actionName = $this->handler->getActionName();
        $arguments = $this->handler->getArguments();

        $actions = new Server_Actions();
        call_user_func_array( array( $actions, $actionName ), $arguments );

        $reply = $actions->getReply();
        if ( is_array( $reply ) )
            $this->handler->setReply( $reply );
        else if ( is_object( $reply ) && is_a( $reply, 'System_Core_Attachment' ) )
            $this->handler->setOutputAttachment( $reply );
        else
            throw new System_Core_Exception( 'Invalid action reply' );
    }

    protected function displayErrorPage()
    {
        $error = Server_Error::ServerError;

        $exception = $this->getFatalError();
        if ( is_a( $exception, 'System_Api_Error' ) || is_a( $exception, 'Server_Error' ) ) {
            $error = $exception->getMessage();
        } else if ( is_a( $exception, 'System_Core_SetupException' ) ) {
            switch ( $exception->getCode() ) {
                case System_Core_SetupException::SiteConfigNotFound:
                    $error = Server_Error::ServerNotConfigured;
                    break;
                case System_Core_SetupException::DatabaseNotCompatible:
                case System_Core_SetupException::DatabaseNotUpdated:
                    $error = Server_Error::BadDatabaseVersion;
                    break;
            }
        }

        list( $code, $message ) = explode( ' ', $error, 2 );

        $this->handler->setErrorReply( (int)$code, $message );
        $this->response->send();
    }
}
