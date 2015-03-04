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

class Common_Tools_Editing extends System_Web_Component
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
            $defaultFormat = $serverManager->getSetting( 'default_format' );
        }

        $formats = array(
            System_Const::PlainText => $this->tr( 'Plain Text' ),
            System_Const::TextWithMarkup => $this->tr( 'Text with Markup' )
        );

        $this->formatOptions = array();
        if ( !$settingsMode )
            $this->formatOptions[ '' ] = $this->tr( 'Default (%1)', 'format', $formats[ $defaultFormat ] );
        foreach ( $formats as $key => $value )
            $this->formatOptions[ $key ] = $value;
    }

    public static function registerFields( &$fields )
    {
        $fields[ 'default_format' ] = 'defaultFormat';
    }
}
