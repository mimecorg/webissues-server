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
* Interface for engines for System_Db_SchemaGenerator.
*/
interface System_Db_ISchemaEngine
{
    /**
    * Generate an integer column type definition.
    * @param $size Size of the integer: 'tiny', 'small', 'medium', 'normal' or 'big'.
    * @param $autoIncrement @c true if the column is an auto-incremented primary key.
    * @param $null @c true if the column accepts @c NULL values.
    * @param $default The default value of the column.
    * @return Type definition appended to column name.
    */
    public function getIntegerType( $size, $autoIncrement, $null, $default );

    /**
    * Generate a character column type definition.
    * @param $length Number of characters.
    * @param $ascii @c true if the column accepts ASCII characters only,
    *        @c false if it's UNICODE.
    * @param $null @c true if the column accepts @c NULL values.
    * @param $default The default value of the column.
    * @return Type definition appended to column name.
    */
    public function getCharType( $length, $ascii, $null, $default );

    /**
    * Generate a variable character column type definition.
    * @param $length Maximum number of characters.
    * @param $ascii @c true if the column accepts ASCII characters only,
    *        @c false if it's UNICODE.
    * @param $null @c true if the column accepts @c NULL values.
    * @param $default The default value of the column.
    * @return Type definition appended to column name.
    */
    public function getVarCharType( $length, $ascii, $null, $default );

    /**
    * Generate a text column type definition.
    * @param $size Size of the text: 'tiny', 'normal', 'medium' or 'long'.
    * @param $ascii @c true if the column accepts ASCII characters only,
    *        @c false if it's UNICODE.
    * @param $null @c true if the column accepts @c NULL values.
    * @return Type definition appended to column name.
    */
    public function getTextType( $size, $ascii, $null );

    /**
    * Generate a binary column type definition.
    * @param $size Size of the binary data: 'tiny', 'normal', 'medium' or 'long'.
    * @param $null @c true if the column accepts @c NULL values.
    * @return Type definition appended to column name.
    */
    public function getBlobType( $size, $null );

    /**
    * Generate a primary key declaration.
    * @param $columns Array of column names forming the primary key.
    * @return Constraint added to the CREATE TABLE statement.
    */
    public function getPrimaryKeyConstraint( $columns );

    /**
    * Generate an index declaration.
    * @param $tableName Name of the table (without prefix - use curly brackets
    *        to append the prefix when executing the query).
    * @param $indexName Name of the index (prepend table name to make sure the
    *        index name is unique).
    * @param $columns Array of column names forming the index.
    * @param $unique @c true if the index has unique values.
    * @return Constraint added to the CREATE TABLE statement or @c null.
    */
    public function getIndexConstraint( $tableName, $indexName, $columns, $unique );

    /**
    * Generate a table declaration.
    * @param $tableName Name of the table (without prefix - use curly brackets
    *        to append the prefix when executing the query).
    * @param $fields Array of column and constraint declarations.
    * @return Statement creating the table.
    */
    public function getCreateTable( $tableName, $fields );

    /**
    * Generate an index declaration.
    * @param $tableName Name of the table (without prefix - use curly brackets
    *        to append the prefix when executing the query).
    * @param $indexName Name of the index (prepend table name to make sure the
    *        index name is unique).
    * @param $columns Array of column names forming the index.
    * @param $unique @c true if the index has unique values.
    * @return Statement creating the index or @c null if index was already
    * declared by getKeyConstraint.
    */
    public function getCreateIndex( $tableName, $indexName, $columns, $unique );

    /**
    * Generate a foreign key constraint declaration.
    * @param $tableName Name of the table.
    * @param $column Name of the column.
    * @param $refTable Name of the referenced table.
    * @param $refColumn Name of the referenced column.
    * @param $onDelete Behavior when parent rows are deleted: 'cascade', 'set-null'
    *        or 'restrict' (default).
    * @return Statement creating the constraint.
    */
    public function getCreateForeignKey( $tableName, $column, $refTable, $refColumn, $onDelete );

    /**
    * Generate statement for enforcing a foreign key constraint as part of
    * the trigger.
    * @param $tableName Name of the table.
    * @param $column Name of the column.
    * @param $refTable Name of the referenced table.
    * @param $refColumn Name of the referenced column.
    * @param $onDelete Behavior when parent rows are deleted: 'cascade', 'set-null'
    *        or 'restrict' (default).
    * @return Statement enforcing the constraint of @c null if triger is not
    * necessary.
    */
    public function getTriggerStatement( $tableName, $column, $refTable, $refColumns, $onDelete );

    /**
    * Generate a trigger declaration. The trigger can be used to implement
    * cascade deletes.
    * @param $tableName Name of the table.
    * @param $primaryColumns Array of column names forming the primary key.
    * @param $statements Array of statements which are part of the trigger.
    * @return Statement creating the trigger.
    */
    public function getCreateTrigger( $tableName, $primaryColumns, $statements );
}
