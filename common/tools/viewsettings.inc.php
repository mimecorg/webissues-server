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

class Common_Tools_ViewSettings extends System_Web_Component
{
    public function __construct( $form )
    {
        parent::__construct();

        $this->form = $form;
    }

    protected function execute()
    {
        $this->settingsMode = $this->request->isRelativePathUnder( '/admin/settings' );

        if ( !$this->settingsMode ) {
            $serverManager = new System_Api_ServerManager();
            $defaultOrder = $serverManager->getSetting( 'history_order' );
            $defaultFilter = $serverManager->getSetting( 'history_filter' );
        }

        $order = array(
            'asc' => $this->tr( 'Oldest First' ),
            'desc' => $this->tr( 'Newest First' )
        );

        $filter = array(
            1 => $this->tr( 'All History' ),
            4 => $this->tr( 'Comments & Attachments' )
        );

        $this->orderOptions = array();
        if ( !$this->settingsMode )
            $this->orderOptions[ '' ] = $this->tr( 'Default (%1)', 'order', $order[ $defaultOrder ] );
        foreach ( $order as $key => $value )
            $this->orderOptions[ $key ] = $value;

        $this->filterOptions = array();
        if ( !$this->settingsMode )
            $this->filterOptions[ '' ] = $this->tr( 'Default (%1)', 'filter', $filter[ $defaultFilter ] );
        foreach ( $filter as $key => $value )
            $this->filterOptions[ $key ] = $value;
    }

    public static function registerFields( &$fields )
    {
        $request = System_Core_Application::getInstance()->getRequest();
        $settingsMode = $request->isRelativePathUnder( '/admin/settings' );

        if ( $settingsMode ) {
            $fields[ 'hide_id_column' ] = 'hideIdColumn';
            $fields[ 'hide_empty_values' ] = 'hideEmptyValues';
        }

        $fields[ 'history_order' ] = 'historyOrder';
        $fields[ 'history_filter' ] = 'historyFilter';
    }
}
