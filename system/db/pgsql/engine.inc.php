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
* Back-end engine for PostgreSQL database.
*
* This engine requires the mysqli PHP module and MySQL version 4.1 or newer.
*/
class System_Db_Pgsql_Engine implements System_Db_IEngine
{
    private $connection = null;
    private $result = null;

    /**
    * Constructor.
    */
    public function __construct()
    {
    }

    public function open( $host, $database, $user, $password )
    {
        $port = '';

        $parts = explode( ':', $host );
        if ( isset( $parts[ 1 ] ) ) {
            $host = $parts[ 0 ];
            $port = $parts[ 1 ];
        }

        $string = "host='$host' port='$port' dbname='$database' user='$user' password='$password'";

        $this->connection = @pg_connect( $string );

        if ( $this->connection == false )
            throw new System_Db_Exception( 'Connection to database failed' );
    }

    public function close()
    {
        if ( $this->result ) {
            pg_free_result( $this->result );
            $this->result = null;
        }

        pg_close( $this->connection );
        $this->connection = null;
    }

    public function execute( $query, $params )
    {
        $this->result = $this->executeQuery( $query, $params );
    }

    public function query( $query, $params )
    {
        return new System_Db_Pgsql_Result( $this->executeQuery( $query, $params ) );
    }

    public function executeQuery( $query, $params )
    {
        if ( $this->result ) {
            pg_free_result( $this->result );
            $this->result = null;
        }

        if ( empty( $params ) )
            $result = pg_query( $this->connection, $query );
        else
            $result = pg_query_params( $this->connection, $query, $params );

        if ( !$result )
            throw new System_Db_Exception( pg_last_error( $this->connection ) );

        return $result;
    }

    public function escapeArgument( $arg, $type, &$params )
    {
        switch( $type ) {
            case 'd':
                $params[] = (int)$arg;
                return '$' . count( $params ) . '::int';
            case 'f':
                $params[] = (float)$arg;
                return '$' . count( $params ) . '::decimal(14,6)';
            case 's':
                $params[] = (string)$arg;
                return '$' . count( $params ) . '::text';
            case 'b':
                $params[] = $arg->getData();
                return '$' . count( $params ) . '::bytea';
        }
    }

    public function getPagedQuery( $query, $orderBy, $limit, $offset )
    {
        if ( $offset != 0 )
            $limit = "$limit OFFSET $offset";
        return "$query ORDER BY $orderBy LIMIT $limit";
    }

    public function createAttachment( $data, $size, $fileName )
    {
        return new System_Core_Attachment( pg_unescape_bytea( $this->connection, $data ), $size, $fileName );
    }

    public function getAffectedRows()
    {
        return pg_affected_rows( $this->result );
    }

    public function getInsertId( $table, $column )
    {
        $query = "SELECT currval('${table}_${column}_seq')";
        $result = pg_query( $this->connection, $query );
        $row = pg_fetch_row( $result );
        pg_free_result( $result );
        return $row[ 0 ];
    }

    public function checkTableExists( $table )
    {
        $query = "SELECT relname FROM pg_class WHERE relkind = 'r' AND relname = '$table'";
        $result = pg_query( $this->connection, $query );
        $count = pg_num_rows( $result );
        pg_free_result( $result );
        return $count > 0;
    }

    public function getParameter( $key )
    {
        switch ( $key ) {
            case 'version':
                $version = pg_version( $this->connection );
                return $version[ 'server' ];
            default:
                return null;
        }
    }
}
