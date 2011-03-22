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
* Implementation of schema generator engine for PostgreSQL.
*/
class System_Db_Pgsql_SchemaEngine implements System_Db_ISchemaEngine
{
    /**
    * Constructor.
    */
    public function __construct()
    {
    }

    public function getIntegerType( $size, $autoIncrement, $null, $default )
    {
        if ( $autoIncrement )
            return $size == 'big' ? 'bigserial' : 'serial';
        static $types = array( 'tiny' => 'smallint', 'small' => 'smallint', 'medium' => 'integer', 'normal' => 'integer', 'big' => 'bigint' );
        $type = $types[ $size ];
        if ( !$null )
            $type .= ' NOT NULL';
        if ( !$null || !is_null( $default ) )
            $type .= ' default ' . (int)$default;
        return $type;
    }

    public function getCharType( $length, $ascii, $null, $default )
    {
        return $this->getCharTypeInternal( 'char', $length, $ascii, $null, $default );
    }

    public function getVarCharType( $length, $ascii, $null, $default )
    {
        return $this->getCharTypeInternal( 'varchar', $length, $ascii, $null, $default );
    }

    private function getCharTypeInternal( $type, $length, $ascii, $null, $default )
    {
        $type .= '(' . $length . ')';
        if ( !$null )
            $type .= ' NOT NULL';
        if ( !$null || !is_null( $default ) )
            $type .= ' default \'' . $default . '\'';
        return $type;
    }

    public function getTextType( $size, $ascii, $null )
    {
        $type = 'text';
        if ( !$null )
            $type .= ' NOT NULL';
        return $type;
    }

    public function getBlobType( $size, $null )
    {
        $type = 'bytea';
        if ( !$null )
            $type .= ' NOT NULL';
        return $type;
    }

    public function getPrimaryKeyConstraint( $columns )
    {
        return 'PRIMARY KEY ( ' . join( ', ', $columns ) . ' )';
    }

    public function getIndexConstraint( $tableName, $indexName, $columns, $unique )
    {
        if ( $unique )
            return 'UNIQUE ( ' . join( ', ', $columns ) . ' )';
        return null;
    }

    public function getCreateTable( $tableName, $fields )
    {
        return 'CREATE TABLE {' . $tableName . '} (' . "\n  " . join( ",\n  ", $fields ) . "\n" . ')';
    }

    public function getCreateIndex( $tableName, $indexName, $columns, $unique )
    {
        if ( !$unique );
            return 'CREATE INDEX {' . $tableName . '}_' . $indexName . ' ON {' . $tableName . '} ( ' . join( ', ', $columns ) . ' )';
        return null;
    }

    public function getCreateForeignKey( $tableName, $column, $refTable, $refColumn, $onDelete )
    {
        $query = 'ALTER TABLE {' . $tableName . '} ADD CONSTRAINT {' . $tableName . '}_' . $column . '_fk FOREIGN KEY ( '
            . $column . ' ) REFERENCES {' . $refTable . '} ( ' . $refColumn . ' )';
        if ( $onDelete == 'cascade' )
            $query .= ' ON DELETE CASCADE';
        else if ( $onDelete == 'set-null' )
            $query .= ' ON DELETE SET NULL';
        return $query;
    }

    public function getTriggerStatement( $tableName, $columns, $refTable, $refColumns, $onDelete )
    {
        return null;
    }

    public function getCreateTrigger( $tableName, $primaryColumns, $statements )
    {
        return null;
    }
}
