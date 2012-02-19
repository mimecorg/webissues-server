<?php
/**************************************************************************
* This file is part of the WebIssues Server program
* Copyright (C) 2006 Michał Męciński
* Copyright (C) 2007-2012 WebIssues Team
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
* This class generatates database dependent DDL queries based on a common
* field definition format. This is an abstract class; to create an instance
* of this class call System_Db_Connection::getSchemaGenerator().
*/
abstract class System_Db_SchemaGenerator
{
    protected $connection = null;

    protected $references = array();

    /**
    * Constructor.
    * @param $connection The associated database connection.
    */
    public function __construct( $connection )
    {
        $this->connection = $connection;
    }

    /**
    * Create a table with columns, constraints and indexes.
    * @param $tableName The name of the table.
    * @param $fields An associative array of field definitions.
    */
    public function createTable( $tableName, $fields )
    {
        foreach ( $fields as $fieldName => $definition ) {
            $info = System_Api_DefinitionInfo::fromString( $definition );
            $this->prepareTableField( $tableName, $fieldName, $info );
        }

        $this->executeCreateTable( $tableName );
    }

    protected abstract function prepareTableField( $tableName, $fieldName, $info );

    protected abstract function executeCreateTable( $tableName );

    /**
    * Set identity insert on or off for the given table.
    */
    public function setIdentityInsert( $tableName, $fieldName, $on )
    {
    }

    /**
    * Create foreign key references.
    * This method must be called after creating and updating all tables.
    */
    public function updateReferences()
    {
        foreach ( $this->references as $reference )
            $this->connection->execute( $reference );

        $this->references = array();
    }
}
