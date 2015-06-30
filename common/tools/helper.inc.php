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

class Common_Tools_Helper extends System_Web_Base
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getBreadcrumbs( $page )
    {
        $breadcrumbs = new Common_Breadcrumbs( $page );
        if ( $this->request->isRelativePathUnder( '/admin' ) )
            $breadcrumbs->initialize( Common_Breadcrumbs::UserAccounts );
        else if ( $this->request->isRelativePathUnder( '/mobile' ) )
            $breadcrumbs->initialize( Common_Breadcrumbs::Mobile );
        else
            $breadcrumbs->initialize( Common_Breadcrumbs::Tools );
        return $breadcrumbs;
    }

    public function getUser()
    {
        if ( $this->request->isRelativePathUnder( '/admin' ) ) {
            $userId = $this->request->getQueryString( 'id' );
            $userManager = new System_Api_UserManager();
            return $userManager->getUser( $userId );
        }
        return null;
    }
}
