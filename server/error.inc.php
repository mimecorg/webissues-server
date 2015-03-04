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

class Server_Error extends System_Core_Exception
{
    const SyntaxError = '400 Syntax Error';
    const UnknownCommand = '401 Unknown Command';
    const InvalidArguments = '402 Invalid Arguments';
    const UploadError = '403 Upload Error';

    const ServerError = '500 Server Error';
    const ServerNotConfigured = '501 Server Not Configured';
    const BadDatabaseVersion = '502 Bad Database Version';

    public function __construct( $message, $wrappedException = null )
    {
        parent::__construct( $message, $wrappedException );
    }
}
