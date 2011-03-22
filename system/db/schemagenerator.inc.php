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
* Universal database schema generator.
*
* This class is a front-end for generating database definition statements
* for various database engines.
*
* Database schema is loaded from the /common/data/setup/schema.ini configuration
* file which is common to all database engines and converted into a series of
* SQL statements creating the tables with necessary indexes, generators and foreign
* key constraints using an appropriate back-end engine.
*
* The back-end engine must implement a System_Db_ISchemaEngine interface.
* The following engines are currently supported:
*  - mysqli
*  - mssql
*/
class System_Db_SchemaGenerator
{
    private $engine = null;

    /**
    * Constructor.
    */
    public function __construct()
    {
    }

    /**
    * Load a back-end engine.
    * @param $engine Name of the engine to load.
    */
    public function loadEngine( $engine )
    {
        switch ( $engine ) {
            case 'mysqli':
                $this->engine = new System_Db_Mysqli_SchemaEngine();
                break;
            case 'pgsql':
                $this->engine = new System_Db_Pgsql_SchemaEngine();
                break;
            case 'mssql':
                $this->engine = new System_Db_Mssql_SchemaEngine();
                break;
            default:
                throw new System_Db_Exception( "Unknown database engine '$engine'" );
        }
    }

    /**
    * Return the list of statements for generating the full database schema.
    * @return An array of SQL statements.
    */
    public function getDatabaseSchema()
    {
        $schema = System_Core_IniFile::parseRaw( '/common/data/setup/schema.ini');

        $tableInfo = array();
        foreach ( $schema as $tableName => $fields ) {
            foreach ( $fields as $fieldName => $definition ) {
                $info = System_Api_DefinitionInfo::fromString( $definition );
                $tableInfo[ $tableName ][ $fieldName ] = $info;
            }
        }

        $tableFields = array();

        $indexes = array();
        $foreignKeys = array();

        $primaryColumns = array();
        $triggerStatements = array();

        foreach ( $tableInfo as $tableName => $fields ) {
            foreach ( $fields as $fieldName => $info ) {
                switch ( $info->getType() ) {
                    case 'PRIMARY':
                        $columns = $info->getMetadata( 'columns' );
                        $primaryColumns[ $tableName ] = $columns;

                        $field = $this->engine->getPrimaryKeyConstraint( $columns );
                        $tableFields[ $tableName ][] = $field;
                        break;

                    case 'INDEX':
                        $columns = $info->getMetadata( 'columns' );
                        $unique = (bool)$info->getMetadata( 'unique', false );

                        $field = $this->engine->getIndexConstraint( $tableName, $fieldName, $columns, $unique );
                        if ( $field != null )
                            $tableFields[ $tableName ][] = $field;

                        $index = $this->engine->getCreateIndex( $tableName, $fieldName, $columns, $unique );
                        if ( $index != null )
                            $indexes[] = $index;
                        break;

                    default:
                        $tableFields[ $tableName ][] = $fieldName . ' ' . $this->getFieldType( $info );

                        $refTable = $info->getMetadata( 'ref-table' );

                        if ( $refTable != null ) {
                            $refColumn = $info->getMetadata( 'ref-column' );
                            $onDelete = $info->getMetadata( 'on-delete', 'restrict' );

                            $foreignKey = $this->engine->getCreateForeignKey( $tableName, $fieldName, $refTable, $refColumn, $onDelete );
                            if ( $foreignKey != null )
                                $foreignKeys[] = $foreignKey;

                            $statement = $this->engine->getTriggerStatement( $tableName, $fieldName, $refTable, $refColumn, $onDelete );
                            if ( $statement != null )
                                $triggerStatements[ $refTable ][] = $statement;
                        }
                        break;
                }
            }
        }

        $queries = array();

        foreach ( $tableFields as $tableName => $fields )
            $queries[] = $this->engine->getCreateTable( $tableName, $fields );

        foreach ( $indexes as $index )
            $queries[] = $index;

        foreach ( $foreignKeys as $foreignKey )
            $queries[] = $foreignKey;

        foreach ( $triggerStatements as $tableName => $statements )
            $queries[] = $this->engine->getCreateTrigger( $tableName, $primaryColumns[ $tableName ], $statements );

        return $queries;
    }

    private function getFieldType( $info )
    {
        switch ( $info->getType() ) {
            case 'INTEGER':
                return $this->engine->getIntegerType( $info->getMetadata( 'size', 'normal' ),
                    (bool)$info->getMetadata( 'auto-increment', false ), (bool)$info->getMetadata( 'null', false ),
                    $info->getMetadata( 'default' ) );

            case 'CHAR':
                return $this->engine->getCharType( $info->getMetadata( 'length', 255 ),
                    (bool)$info->getMetadata( 'ascii', false ), (bool)$info->getMetadata( 'null', false ),
                    $info->getMetadata( 'default' ) );                            

            case 'VARCHAR':
                return $this->engine->getVarCharType( $info->getMetadata( 'length', 255 ),
                    (bool)$info->getMetadata( 'ascii', false ), (bool)$info->getMetadata( 'null', false ),
                    $info->getMetadata( 'default' ) );                            

            case 'TEXT':
                return $this->engine->getTextType( $info->getMetadata( 'size', 'normal' ),
                    (bool)$info->getMetadata( 'ascii', false ), (bool)$info->getMetadata( 'null', false ) );
                break;

            case 'BLOB':
                return $this->engine->getBlobType( $info->getMetadata( 'size', 'normal' ),
                    (bool)$info->getMetadata( 'null', false ) );
                break;

            default:
                throw new System_Db_Exception( "Unknown field type '$fieldType'" );
        }
    }
}
