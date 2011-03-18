<?php
/**************************************************************************
* This file is part of the WebIssues Server program
* Copyright (C) 2006 Michał Męciński
* Copyright (C) 2007-2011 WebIssues Team
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

class Admin_Types_Helper extends System_Web_Base
{
    private static $types = null;

    public function __construct()
    {
        parent::__construct();

        $this->initialize();
    }

    public function getTypeName( $type )
    {
        return self::$types[ $type ];
    }

    public function getAllTypes()
    {
        return self::$types;
    }

    public function getCompatibleTypes( $type )
    {
        $compatibleTypes = array( 'TEXT', 'ENUM', 'USER' );

        $result = array();

        if ( in_array( $type, $compatibleTypes ) ) {
            foreach ( $compatibleTypes as $key )
                $result[ $key ] = self::$types[ $key ];
        } else {
            $result[ $type ] = self::$types[ $type ];
        }

        return $result;
    }

    private function initialize()
    {
        if ( !isset( self::$types ) ) {
            self::$types = array(
                'TEXT' => $this->tr( 'Text' ),
                'ENUM' => $this->tr( 'Dropdown list' ),
                'NUMERIC' => $this->tr( 'Numeric' ),
                'DATETIME' => $this->tr( 'Date & time' ),
                'USER' => $this->tr( 'User' )
            );
        }
    }
}
