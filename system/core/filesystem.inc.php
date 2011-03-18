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

/**
* Helper functions for various file system operations.
*/
class System_Core_FileSystem
{
    /**
    * Check if the file name is valid. A file name is invalid when it is empty,
    * begins with a dot or contains special characters like backslash, colon, etc.
    * @param $name The name to check.
    * @return @c true if the name is valid.
    */
    public static function isValidFileName( $name )
    {
        return ( $name != '' && $name[ 0 ] != '.' && strpbrk( $name, '\\/:*?"<>|' ) === false );
    }

    /**
    * Check if the specified directory exists.
    * @param $path The absolute path of the directory (without trailing slash).
    * @param $create @c true if the directory should be created if it doesn't exist.
    * @return @c true if the directory exists or was successfully created.
    */
    public static function isDirectory( $path, $create = false )
    {
        if ( is_dir( $path ) )
            return true;
        if ( $create && @mkdir( $path, 0755, true ) )
            return true;
        return false;
    }

    /**
    * Check if the specified directory is writable.
    * A temporary file is created and deleted to make sure the check works reliably
    * on all platforms and configurations.
    * @param $path The absolute path of the directory (without trailing slash).
    * @return @c true if the directory exists and is writable.
    */
    public static function isDirectoryWritable( $path )
    {
        $tempFile = $path . '/' . md5( uniqid( mt_rand(), true ) ) . '.tmp';

        if ( !( $fp = @fopen( $tempFile, 'a' ) ) )
            return false;

        fclose( $fp );
        @unlink( $tempFile );

        return true;
    }
}
