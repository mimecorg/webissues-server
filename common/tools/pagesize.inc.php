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

class Common_Tools_PageSize extends System_Web_Component
{
    public function __construct( $form )
    {
        parent::__construct();

        $this->form = $form;
    }

    protected function execute()
    {
        $settingsMode = $this->request->isRelativePathUnder( '/admin/settings' );

        if ( !$settingsMode ) {
            $serverManager = new System_Api_ServerManager();
            $defaultProject = $serverManager->getSetting( 'project_page_size' );
            $defaultFolder = $serverManager->getSetting( 'folder_page_size' );
            $defaultHistory = $serverManager->getSetting( 'history_page_size' );
        }

        $this->projectOptions = array();
        if ( !$settingsMode )
            $this->projectOptions[ '' ] = $this->tr( 'Default (%1)', null, $defaultProject );
        foreach ( array( 5, 10, 15, 20, 25, 30 ) as $i )
            $this->projectOptions[ $i ] = $i;

            $this->folderOptions = array();
        if ( !$settingsMode )
            $this->folderOptions[ '' ] = $this->tr( 'Default (%1)', null, $defaultFolder );
        foreach ( array( 5, 10, 15, 20, 25, 30 ) as $i )
            $this->folderOptions[ $i ] = $i;

        $this->historyOptions = array();
        if ( !$settingsMode )
            $this->historyOptions[ '' ] = $this->tr( 'Default (%1)', null, $defaultHistory );
        foreach ( array( 10, 20, 30, 40, 50 ) as $i )
            $this->historyOptions[ $i ] = $i;
    }

    public static function registerFields( &$fields )
    {
        $fields[ 'project_page_size' ] = 'projectPageSize';
        $fields[ 'folder_page_size' ] = 'folderPageSize';
        $fields[ 'history_page_size' ] = 'historyPageSize';
    }
}
