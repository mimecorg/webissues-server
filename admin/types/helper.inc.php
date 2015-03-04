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

    public function getAttributeDetails( $info )
    {
        $formatter = new System_Api_Formatter();

        $details = array();

        switch ( $info->getType() ) {
            case 'TEXT':
                if ( $info->getMetadata( 'multi-line', 0 ) )
                    $details[] = $this->tr( 'Multiple lines' );
                $minLength = $info->getMetadata( 'min-length' );
                if ( $minLength !== null )
                    $details[] = $this->tr( 'Min. length: %1', null, $minLength );
                $maxLength = $info->getMetadata( 'max-length' );
                if ( $maxLength !== null )
                    $details[] = $this->tr( 'Max. length: %1', null, $maxLength );
                break;

            case 'ENUM':
                if ( $info->getMetadata( 'editable', 0 ) )
                    $details[] = $this->tr( 'Editable' );
                if ( $info->getMetadata( 'multi-select', 0 ) )
                    $details[] = $this->tr( 'Multiple selection' );
                $items = $info->getMetadata( 'items' );
                if ( $items !== null )
                    $details[] = $this->tr( 'Items: %1', null, join( ', ', $items ) );
                $minLength = $info->getMetadata( 'min-length' );
                if ( $minLength !== null )
                    $details[] = $this->tr( 'Min. length: %1', null, $minLength );
                $maxLength = $info->getMetadata( 'max-length' );
                if ( $maxLength !== null )
                    $details[] = $this->tr( 'Max. length: %1', null, $maxLength );
                break;

            case 'NUMERIC':
                $decimal = $info->getMetadata( 'decimal', 0 );
                $strip = $info->getMetadata( 'strip', 0 );
                if ( $decimal != 0 )
                    $details[] = $this->tr( 'Decimal places: %1', null, $decimal );
                $minimum = $info->getMetadata( 'min-value' );
                if ( $minimum !== null )
                    $details[] = $this->tr( 'Min. value: %1', null, $formatter->convertDecimalNumber( $minimum, $decimal, $strip ? System_Api_Formatter::StripZeros : 0 ) );
                $maximum = $info->getMetadata( 'max-value' );
                if ( $maximum !== null )
                    $details[] = $this->tr( 'Max. value: %1', null, $formatter->convertDecimalNumber( $maximum, $decimal, $strip ? System_Api_Formatter::StripZeros : 0 ) );
                if ( $strip )
                    $details[] = $this->tr( 'Strip zeros' );
                break;

            case 'DATETIME':
                if ( $info->getMetadata( 'time', 0 ) )
                    $details[] = $this->tr( 'With time' );
                if ( $info->getMetadata( 'local', 0 ) )
                    $details[] = $this->tr( 'Local time zone' );
                break;

            case 'USER':
                if ( $info->getMetadata( 'members', 0 ) )
                    $details[] = $this->tr( 'Members only' );
                if ( $info->getMetadata( 'multi-select', 0 ) )
                    $details[] = $this->tr( 'Multiple selection' );
                break;
        }

        return $this->truncate( join( '; ', $details ), 80 );
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
