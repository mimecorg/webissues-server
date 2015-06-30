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

class Common_Issues_GetAttachment extends Common_Application
{
    protected function __construct()
    {
        parent::__construct( null );
    }

    protected function execute()
    {
        $principal = System_Api_Principal::getCurrent();
        if ( !$principal->isAuthenticated() ) {
            $redirect = true;

            $serverManager = new System_Api_ServerManager();
            if ( $serverManager->getSetting( 'anonymous_access' ) == 1 ) {
                $this->isAnonymous = true;
                $redirect = false;
            }

            if ( $redirect )
                $this->redirectToLoginPage();
        }

        $issueManager = new System_Api_IssueManager();
        $attachmentId = (int)$this->request->getQueryString( 'id' );

        $file = $issueManager->getFile( $attachmentId );

        $serverManager = new System_Api_ServerManager();
        $server = $serverManager->getServer();

        $etag = '"' . $server[ 'server_uuid' ] . '-' . sprintf( '%08x', $attachmentId ) . '"';

        $this->response->setCustomHeader( 'Last-Modified', gmdate( 'D, d M Y H:i:s', $file[ 'stamp_time' ] ) . ' GMT' );
        $this->response->setCustomHeader( 'Etag', $etag );

        $this->response->setCustomHeader( 'Cache-Control', 'private, must-revalidate, max-age=0, post-check=0, pre-check=0' );
        $this->response->setCustomHeader( 'Expires', '' );
        $this->response->setCustomHeader( 'Pragma', '' );

        $since = $this->request->getIfModifiedSince();
        $match = $this->request->getIfNoneMatch();

        if ( ( $since == null && $match == null ) || ( $since != null && @strtotime( $since ) < $file[ 'stamp_time' ] ) || ( $match != null && $match != $etag ) ) {
            $type = $this->getContentType( $file[ 'file_name' ] );
            $this->response->setContentType( $type );
            
            $attachment = $issueManager->getAttachment( $attachmentId );
            $this->response->setAttachment( $attachment );
        } else {
            $this->response->setStatus( '304 Not Modified' );
        }
    }

    private function getContentType( $fileName )
    {
        $extension = pathinfo( $fileName, PATHINFO_EXTENSION );

        if ( $extension != null ) {
            $extension = strtolower( $extension );

            $types = System_Core_IniFile::parse( '/common/data/mimetypes.ini' );

            if ( isset( $types[ $extension ] ) )
                return $types[ $extension ];
        }

        return 'application/octet-stream';
    }
}
