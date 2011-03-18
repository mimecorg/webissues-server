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
* Implementation of schema generator engine for Microsoft SQL Server.
*/
class System_Db_Mssql_SchemaEngine implements System_Db_ISchemaEngine
{
    /**
    * Constructor.
    */
    public function __construct()
    {
    }

    public function getIntegerType( $size, $autoIncrement, $null, $default )
    {
        static $types = array( 'tiny' => 'tinyint', 'small' => 'smallint', 'medium' => 'int', 'normal' => 'int', 'big' => 'bigint' );
        $type = $types[ $size ];
        if ( $autoIncrement )
            $type .= ' IDENTITY';
        if ( !$null )
            $type .= ' NOT NULL';
        if ( !$autoIncrement && ( !$null || !is_null( $default ) ) )
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
        if ( !$ascii )
            $type = 'n' . $type;
        $type .= ' COLLATE Latin1_General_CS_AS';
        if ( !$null )
            $type .= ' NOT NULL';
        if ( !$null || !is_null( $default ) )
            $type .= ' default \'' . $default . '\'';
        return $type;
    }

    public function getTextType( $size, $ascii, $null )
    {
        $type = 'text';
        if ( !$ascii )
            $type = 'n' . $type;
        $type .= ' COLLATE Latin1_General_CS_AS';
        if ( !$null )
            $type .= ' NOT NULL';
        return $type;
    }

    public function getBlobType( $size, $null )
    {
        $type = 'image';
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
        return null;
    }

    public function getCreateTable( $tableName, $fields )
    {
        return 'CREATE TABLE {' . $tableName . '} (' . "\n  " . join( ",\n  ", $fields ) . "\n" . ')';
    }

    public function getCreateIndex( $tableName, $indexName, $columns, $unique )
    {
        $query = 'CREATE ';
        if ( $unique )
            $query .= 'UNIQUE ';
        $query .= 'INDEX {' . $tableName . '}_' . $indexName . ' ON {' . $tableName . '} ( ' . join( ', ', $columns ) . ' )';
        return $query;
    }

    public function getCreateForeignKey( $tableName, $column, $refTable, $refColumn, $onDelete )
    {
        return 'ALTER TABLE {' . $tableName . '} ADD CONSTRAINT {' . $tableName . '}_' . $column . '_fk FOREIGN KEY ( '
            . $column . ' ) REFERENCES {' . $refTable . '} ( ' . $refColumn . ' )';
    }

    public function getTriggerStatement( $tableName, $column, $refTable, $refColumn, $onDelete )
    {
        if ( $onDelete == 'cascade' ) {
            return 'DELETE FROM {' . $tableName . '} WHERE ' . $column . ' IN ( SELECT ' . $refColumn
                . ' FROM deleted )';
        }
        if ( $onDelete == 'set-null' ) {
            return 'UPDATE {' . $tableName . '} SET ' . $column . ' = NULL WHERE ' . $column
                . ' IN ( SELECT ' . $refColumn . ' FROM deleted )';
        }
        return null;
    }

    public function getCreateTrigger( $tableName, $primaryColumns, $statements )
    {
        if ( $primaryColumns == null || count( $primaryColumns ) != 1 )
            throw new System_Db_Exception( "Invalid foreign key reference to table '$tableName'" );

        return 'CREATE TRIGGER {' . $tableName . '}_on_delete ON {' . $tableName . '} INSTEAD OF DELETE AS' . "\n"
            . 'BEGIN' . "\n  " . join( "\n  ", $statements ) . "\n  " . 'DELETE FROM {' . $tableName . '} '
            . 'WHERE ' . $primaryColumns[ 0 ] . ' IN ( SELECT ' . $primaryColumns[ 0 ] . ' FROM deleted )' . "\n" . 'END';
    }
}
