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

class Admin_Setup_Updater extends System_Web_Base
{
    private $connection = null;

    public function __construct( $connection )
    {
        parent::__construct();

        $this->connection = $connection;
    }

    public function updateDatabase( $version )
    {
        if ( version_compare( $version, '1.0.002' ) < 0 ) {
            $settings = array(
                'folder_page_size'      => 10,
                'history_page_size'     => 20
            );

            $query = 'INSERT INTO {settings} ( set_key, set_value ) VALUES ( %s, %s )';
            foreach ( $settings as $key => $value )
                $this->connection->execute( $query, $key, $value );
        }

        if ( version_compare( $version, '1.0.003' ) < 0 ) {
            $fields = array(
                'request_id'        => 'SERIAL',
                'user_login'        => 'VARCHAR length=40',
                'user_name'         => 'VARCHAR length=40',
                'user_email'        => 'VARCHAR length=40',
                'user_passwd'       => 'VARCHAR length=255 ascii=1',
                'request_key'       => 'CHAR length=8 ascii=1',
                'created_time'      => 'INTEGER',
                'is_active'         => 'INTEGER size="tiny"',
                'is_sent'           => 'INTEGER size="tiny"',
                'pk'                => 'PRIMARY columns={"request_id"}'
            );

            $generator = $this->connection->getSchemaGenerator();

            $generator->createTable( 'register_requests', $fields );
            $generator->updateReferences();

            $settings = array(
                'register_max_lifetime' => 86400
            );

            $query = 'INSERT INTO {settings} ( set_key, set_value ) VALUES ( %s, %s )';
            foreach ( $settings as $key => $value )
                $this->connection->execute( $query, $key, $value );
        }

        if ( version_compare( $version, '1.0.004' ) < 0 ) {
            $settings = array(
                'history_order'         => 'asc',
                'history_filter'        => 1
            );

            $query = 'INSERT INTO {settings} ( set_key, set_value ) VALUES ( %s, %s )';
            foreach ( $settings as $key => $value )
                $this->connection->execute( $query, $key, $value );
        }

        if ( version_compare( $version, '1.1.001' ) < 0 ) {
            $newTables = array(
                'issue_descriptions' => array(
                    'issue_id'          => 'INTEGER ref-table="issues" ref-column="issue_id" on-delete="cascade"',
                    'descr_text'        => 'TEXT size="long"',
                    'descr_format'      => 'INTEGER size="tiny" default=0',
                    'pk'                => 'PRIMARY columns={"issue_id"}'
                ),
                'project_descriptions' => array(
                    'project_id'        => 'INTEGER ref-table="projects" ref-column="project_id" on-delete="cascade"',
                    'descr_text'        => 'TEXT size="long"',
                    'descr_format'      => 'INTEGER size="tiny" default=0',
                    'pk'                => 'PRIMARY columns={"project_id"}'
                )
            );

            $newFields = array(
                'comments' => array(
                    'comment_format'    => 'INTEGER size="tiny" default=0'
                ),
                'issues' => array(
                    'descr_id'          => 'INTEGER null=1',
                    'descr_stub_id'     => 'INTEGER null=1'
                ),
                'projects' => array(
                    'descr_id'          => 'INTEGER null=1',
                    'descr_stub_id'     => 'INTEGER null=1'
                )
            );

            $generator = $this->connection->getSchemaGenerator();

            foreach ( $newTables as $tableName => $fields )
                $generator->createTable( $tableName, $fields );

            foreach ( $newFields as $tableName => $fields )
                $generator->addFields( $tableName, $fields );

            $generator->updateReferences();

            $settings = array(
                'default_format'        => 1
            );

            $query = 'INSERT INTO {settings} ( set_key, set_value ) VALUES ( %s, %s )';
            foreach ( $settings as $key => $value )
                $this->connection->execute( $query, $key, $value );
        }

        $query = 'DELETE FROM {sessions}';
        $this->connection->execute( $query );

        $query = 'UPDATE {server} SET db_version = %s';
        $this->connection->execute( $query, WI_DATABASE_VERSION );
    }
}
