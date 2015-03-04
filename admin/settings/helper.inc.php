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

class Admin_Settings_Helper extends System_Web_Base
{
    private $page = null;

    public function __construct( $page )
    {
        parent::__construct();
        $this->page = $page;
    }

    public function loadSettings( $fields )
    {
        $serverManager = new System_Api_ServerManager();

        foreach ( $fields as $key => $field )
            $this->page->$field = $serverManager->getSetting( $key );
    }

    public function validateSettings( $fields )
    {
        $validator = new System_Api_Validator();
        $values = array();

        foreach ( $fields as $key => $field ) {
            $value = $this->page->$field;
            try {
                $validator->checkSetting( $key, $value );
            } catch ( System_Api_Error $ex ) {
                $this->page->form->getErrorHelper()->handleError( $field, $ex );
            }
            $values[ $key ] = $value;
        }

        return $values;
    }

    public function submitSettings( $values )
    {
        $serverManager = new System_Api_ServerManager();

        foreach ( $values as $key => $value )
            $serverManager->setSetting( $key, $value );
    }
}
