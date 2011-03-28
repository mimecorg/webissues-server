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

class Admin_Setup_Installer extends System_Web_Base
{
    private $connection = null;
    private $generator = null;

    public function __construct( $connection )
    {
        parent::__construct();

        $this->connection = $connection;
        $this->generator = $connection->getSchemaGenerator();
    }

    public function installSchema()
    {
        $schema = array(
            'alerts' => array(
                'alert_id'          => 'SERIAL',
                'user_id'           => 'INTEGER ref-table="users" ref-column="user_id"',
                'folder_id'         => 'INTEGER ref-table="folders" ref-column="folder_id" on-delete="cascade"',
                'view_id'           => 'INTEGER null=1 ref-table="views" ref-column="view_id" on-delete="cascade"',
                'alert_email'       => 'INTEGER size="tiny"',
                'stamp_id'          => 'INTEGER null=1',
                'pk'                => 'PRIMARY columns={"alert_id"}',
                'alert_idx'         => 'INDEX columns={"user_id","folder_id","view_id"} unique=1',
                'folder_idx'        => 'INDEX columns={"folder_id"}',
                'view_idx'          => 'INDEX columns={"view_id"}'
            ),
            'attr_types' => array(
                'attr_id'           => 'SERIAL',
                'type_id'           => 'INTEGER ref-table="issue_types" ref-column="type_id" on-delete="cascade"',
                'attr_name'         => 'VARCHAR length=40',
                'attr_def'          => 'TEXT size="long"',
                'pk'                => 'PRIMARY columns={"attr_id"}',
                'type_idx'          => 'INDEX columns={"type_id"}',
                'name_idx'          => 'INDEX columns={"attr_name"}'
            ),
            'attr_values' => array(
                'issue_id'          => 'INTEGER ref-table="issues" ref-column="issue_id" on-delete="cascade"',
                'attr_id'           => 'INTEGER ref-table="attr_types" ref-column="attr_id" on-delete="cascade"',
                'attr_value'        => 'VARCHAR length=255',
                'pk'                => 'PRIMARY columns={"issue_id","attr_id"}',
                'attr_idx'          => 'INDEX columns={"attr_id"}'
            ),
            'change_stubs' => array(
                'stub_id'           => 'INTEGER ref-table="stamps" ref-column="stamp_id"',
                'change_id'         => 'INTEGER',
                'issue_id'          => 'INTEGER ref-table="issues" ref-column="issue_id" on-delete="cascade"',
                'pk'                => 'PRIMARY columns={"stub_id"}',
                'issue_idx'         => 'INDEX columns={"issue_id"}'
            ),
            'changes' => array(
                'change_id'         => 'INTEGER ref-table="stamps" ref-column="stamp_id"',
                'issue_id'          => 'INTEGER ref-table="issues" ref-column="issue_id" on-delete="cascade"',
                'change_type'       => 'INTEGER size="tiny"',
                'stamp_id'          => 'INTEGER',
                'attr_id'           => 'INTEGER null=1 ref-table="attr_types" ref-column="attr_id" on-delete="set-null"',
                'value_old'         => 'VARCHAR length=255 null=1',
                'value_new'         => 'VARCHAR length=255 null=1',
                'from_folder_id'    => 'INTEGER null=1 ref-table="folders" ref-column="folder_id" on-delete="set-null" trigger=1',
                'to_folder_id'      => 'INTEGER null=1 ref-table="folders" ref-column="folder_id" on-delete="set-null" trigger=1',
                'pk'                => 'PRIMARY columns={"change_id"}',
                'issue_idx'         => 'INDEX columns={"issue_id","change_type"}',
                'stamp_idx'         => 'INDEX columns={"stamp_id"}',
                'attr_idx'          => 'INDEX columns={"attr_id"}',
                'from_folder_idx'   => 'INDEX columns={"from_folder_id"}',
                'to_folder_idx'     => 'INDEX columns={"to_folder_id"}'
            ),
            'comments' => array(
                'comment_id'        => 'INTEGER ref-table="changes" ref-column="change_id" on-delete="cascade"',
                'comment_text'      => 'TEXT size="long"',
                'pk'                => 'PRIMARY columns={"comment_id"}'
            ),
            'files' => array(
                'file_id'           => 'INTEGER ref-table="changes" ref-column="change_id" on-delete="cascade"',
                'file_name'         => 'VARCHAR length=80',
                'file_size'         => 'INTEGER',
                'file_data'         => 'BLOB size="long" null=1',
                'file_descr'        => 'VARCHAR length=255',
                'file_storage'      => 'INTEGER size="tiny"',
                'pk'                => 'PRIMARY columns={"file_id"}'
            ),
            'folders' => array(
                'folder_id'         => 'SERIAL',
                'project_id'        => 'INTEGER ref-table="projects" ref-column="project_id" on-delete="cascade"',
                'type_id'           => 'INTEGER ref-table="issue_types" ref-column="type_id" on-delete="cascade" trigger=1',
                'folder_name'       => 'VARCHAR length=40',
                'stamp_id'          => 'INTEGER null=1',
                'pk'                => 'PRIMARY columns={"folder_id"}',
                'project_idx'       => 'INDEX columns={"project_id"}',
                'type_idx'          => 'INDEX columns={"type_id"}',
                'name_idx'          => 'INDEX columns={"folder_name"}'
            ),
            'issue_states' => array(
                'state_id'          => 'SERIAL',
                'user_id'           => 'INTEGER ref-table="users" ref-column="user_id"',
                'issue_id'          => 'INTEGER ref-table="issues" ref-column="issue_id" on-delete="cascade"',
                'read_id'           => 'INTEGER null=1',
                'pk'                => 'PRIMARY columns={"state_id"}',
                'state_idx'         => 'INDEX columns={"user_id","issue_id"} unique=1',
                'issue_idx'         => 'INDEX columns={"issue_id"}'
            ),
            'issue_stubs' => array(
                'stub_id'           => 'INTEGER ref-table="stamps" ref-column="stamp_id"',
                'prev_id'           => 'INTEGER',
                'issue_id'          => 'INTEGER',
                'folder_id'         => 'INTEGER ref-table="folders" ref-column="folder_id" on-delete="cascade"',
                'pk'                => 'PRIMARY columns={"stub_id"}',
                'folder_idx'        => 'INDEX columns={"folder_id"}'
            ),
            'issue_types' => array(
                'type_id'           => 'SERIAL',
                'type_name'         => 'VARCHAR length=40',
                'pk'                => 'PRIMARY columns={"type_id"}',
                'name_idx'          => 'INDEX columns={"type_name"}'
            ),
            'issues' => array(
                'issue_id'          => 'INTEGER ref-table="stamps" ref-column="stamp_id"',
                'folder_id'         => 'INTEGER ref-table="folders" ref-column="folder_id" on-delete="cascade"',
                'issue_name'        => 'VARCHAR length=255',
                'stamp_id'          => 'INTEGER ref-table="stamps" ref-column="stamp_id"',
                'stub_id'           => 'INTEGER null=1',
                'pk'                => 'PRIMARY columns={"issue_id"}',
                'folder_idx'        => 'INDEX columns={"folder_id"}',
                'stamp_idx'         => 'INDEX columns={"stamp_id"}'
            ),
            'log_events' => array(
                'event_id'          => 'SERIAL',
                'event_type'        => 'VARCHAR length=16 ascii=1',
                'event_severity'    => 'INTEGER size="tiny"',
                'event_message'     => 'TEXT size="long"',
                'event_time'        => 'INTEGER',
                'user_id'           => 'INTEGER null=1 ref-table="users" ref-column="user_id"',
                'host_name'         => 'VARCHAR length=40 ascii=1',
                'pk'                => 'PRIMARY columns={"event_id"}',
                'type_idx'          => 'INDEX columns={"event_type"}',
                'user_idx'          => 'INDEX columns={"user_id"}'
            ),
            'preferences' => array(
                'user_id'           => 'INTEGER ref-table="users" ref-column="user_id"',
                'pref_key'          => 'VARCHAR length=40',
                'pref_value'        => 'TEXT size="long"',
                'pk'                => 'PRIMARY columns={"user_id","pref_key"}'
            ),
            'projects' => array(
                'project_id'        => 'SERIAL',
                'project_name'      => 'VARCHAR length=40',
                'pk'                => 'PRIMARY columns={"project_id"}',
                'name_idx'          => 'INDEX columns={"project_name"}'
            ),
            'rights' => array(
                'project_id'        => 'INTEGER ref-table="projects" ref-column="project_id" on-delete="cascade"',
                'user_id'           => 'INTEGER ref-table="users" ref-column="user_id"',
                'project_access'    => 'INTEGER size="tiny"',
                'pk'                => 'PRIMARY columns={"project_id","user_id"}',
                'user_idx'          => 'INDEX columns={"user_id"}'
            ),
            'server' => array(
                'server_name'       => 'VARCHAR length=40',
                'server_uuid'       => 'CHAR length=36 ascii=1',
                'db_version'        => 'VARCHAR length=20 ascii=1'
            ),
            'sessions' => array(
                'session_id'        => 'CHAR length=32 ascii=1',
                'user_id'           => 'INTEGER ref-table="users" ref-column="user_id"',
                'session_data'      => 'TEXT size="long"',
                'last_access'       => 'INTEGER',
                'pk'                => 'PRIMARY columns={"session_id"}',
                'user_idx'          => 'INDEX columns={"user_id"}',
                'access_idx'        => 'INDEX columns={"last_access"}'
            ),
            'settings' => array(
                'set_key'           => 'VARCHAR length=40',
                'set_value'         => 'TEXT size="long"',
                'pk'                => 'PRIMARY columns={"set_key"}'
            ),
            'stamps' => array(
                'stamp_id'          => 'SERIAL',
                'user_id'           => 'INTEGER ref-table="users" ref-column="user_id"',
                'stamp_time'        => 'INTEGER',
                'pk'                => 'PRIMARY columns={"stamp_id"}',
                'user_idx'          => 'INDEX columns={"user_id"}'
            ),
            'users' => array(
                'user_id'           => 'SERIAL',
                'user_login'        => 'VARCHAR length=40',
                'user_name'         => 'VARCHAR length=40',
                'user_passwd'       => 'VARCHAR length=255 ascii=1',
                'user_access'       => 'INTEGER size="tiny"',
                'passwd_temp'       => 'INTEGER size="tiny"',
                'pk'                => 'PRIMARY columns={"user_id"}',
                'login_idx'         => 'INDEX columns={"user_login"} unique=1',
                'name_idx'          => 'INDEX columns={"user_name"}'
            ),
            'view_settings' => array(
                'type_id'           => 'INTEGER ref-table="issue_types" ref-column="type_id" on-delete="cascade"',
                'set_key'           => 'VARCHAR length=40',
                'set_value'         => 'TEXT size="long"',
                'pk'                => 'PRIMARY columns={"type_id","set_key"}'
            ),
            'views' => array(
                'view_id'           => 'SERIAL',
                'type_id'           => 'INTEGER ref-table="issue_types" ref-column="type_id" on-delete="cascade"',
                'user_id'           => 'INTEGER null=1 ref-table="users" ref-column="user_id"',
                'view_name'         => 'VARCHAR length=40',
                'view_def'          => 'TEXT size="long"',
                'pk'                => 'PRIMARY columns={"view_id"}',
                'view_idx'          => 'INDEX columns={"type_id","user_id"}',
                'user_idx'          => 'INDEX columns={"user_id"}',
                'name_idx'          => 'INDEX columns={"view_name"}'
            )
        );

        foreach ( $schema as $tableName => $fields )
            $this->generator->createTable( $tableName, $fields );

        $this->generator->updateReferences();
    }

    public function installData( $serverName, $adminPassword )
    {
        $serverManager = new System_Api_ServerManager();
        $uuid = $serverManager->generateUuid();

        $query = 'INSERT INTO {server} ( server_name, server_uuid, db_version ) VALUES ( %s, %s, %s )';
        $this->connection->execute( $query, $serverName, $uuid, WI_DATABASE_VERSION );

        $passwordHash = new System_Core_PasswordHash();
        $hash = $passwordHash->hashPassword( $adminPassword );

        $query = 'INSERT INTO {users} ( user_login, user_name, user_passwd, user_access, passwd_temp ) VALUES ( %s, %s, %s, %d, 0 )';
        $this->connection->execute( $query, 'admin', $this->tr( 'Administrator' ), $hash, System_Const::AdministratorAccess );

        $language = $this->translator->getLanguage( System_Core_Translator::SystemLanguage );

        $settings = array(
            'language'              => $language,
            'comment_max_length'    => 10000,
            'file_max_size'         => 1048576,
            'file_db_max_size'      => 4096,
            'session_max_lifetime'  => 7200,
            'log_max_lifetime'      => 604800,
            'gc_divisor'            => 100
        );

        $query = 'INSERT INTO {settings} ( set_key, set_value ) VALUES ( %s, %s )';
        foreach ( $settings as $key => $value )
            $this->connection->execute( $query, $key, $value );
    }

    public function importData( $prefix )
    {
        $this->generator->setIdentityInsert( 'users', true );

        $query = 'SELECT user_id, user_login, user_name, user_passwd, user_access FROM ' . $prefix . 'users WHERE user_id > 1';
        $users = $this->connection->queryTable( $query );

        $passwordHash = new System_Core_PasswordHash();

        foreach ( $users as $user ) {
            $oldHash = $user[ 'user_passwd' ];
            $newHash = $passwordHash->updatePasswordHash( $oldHash );

            $query = 'INSERT INTO {users} ( user_id, user_login, user_name, user_passwd, user_access, passwd_temp ) VALUES ( %d, %s, %s, %s, %d, 0 )';
            $this->connection->execute( $query, $user[ 'user_id' ], $user[ 'user_login' ], $user[ 'user_name' ], $newHash, $user[ 'user_access' ] );
        }

        $this->generator->setIdentityInsert( 'users', false );

        $tables = array(
            'issue_types' => array( 'type_id', 'type_name' ),
            'projects' => array( 'project_id', 'project_name' ),
            'stamps' => array( 'stamp_id', 'user_id', 'stamp_time' ),
            'attr_types' => array( 'attr_id', 'type_id', 'attr_name', 'attr_def' )
        );

        foreach ( $tables as $tableName => $columns ) {
            $this->generator->setIdentityInsert( $tableName, true );

            $query = 'INSERT INTO {' . $tableName . '} ( ' . join( ', ', $columns ) . ' ) SELECT ' . join( ', ', $columns ) . ' FROM ' . $prefix . $tableName;
            $this->connection->execute( $query );

            $this->generator->setIdentityInsert( $tableName, false );
        }

        $this->generator->setIdentityInsert( 'folders', true );

        $query = 'INSERT INTO {folders} ( folder_id, project_id, type_id, folder_name, stamp_id ) SELECT folder_id, project_id, type_id, folder_name, s.stamp_id'
            . ' FROM ' . $prefix . 'folders AS f'
            . ' LEFT OUTER JOIN ' . $prefix . 'stamps AS s ON s.stamp_id = f.stamp_id';
        $this->connection->execute( $query );

        $this->generator->setIdentityInsert( 'folders', false );

        $tables = array(
            'rights' => array( 'project_id', 'user_id', 'project_access' ),
            'issues' => array( 'issue_id', 'folder_id', 'issue_name', 'stamp_id' ),
            'attr_values' => array( 'issue_id', 'attr_id', 'attr_value' )
        );

        foreach ( $tables as $tableName => $columns ) {
            $query = 'INSERT INTO {' . $tableName . '} ( ' . join( ', ', $columns ) . ' ) SELECT ' . join( ', ', $columns ) . ' FROM ' . $prefix . $tableName;
            $this->connection->execute( $query );
        }

        $query = 'INSERT INTO {preferences} ( user_id, pref_key, pref_value ) SELECT user_id, pref_key, pref_value FROM ' . $prefix . 'preferences WHERE pref_key = %s';
        $this->connection->execute( $query, 'email' );

        $query = 'INSERT INTO {changes} ( change_id, issue_id, change_type, stamp_id, value_old, value_new )'
            . ' SELECT change_id, issue_id, %d AS change_type, change_id AS stamp_id, value_old, value_new FROM ' . $prefix . 'changes WHERE attr_id = 0';
        $this->connection->execute( $query, System_Const::IssueRenamed );

        $query = 'INSERT INTO {changes} ( change_id, issue_id, change_type, stamp_id, attr_id, value_old, value_new )'
            . ' SELECT change_id, issue_id, %d AS change_type, change_id AS stamp_id, attr_id, value_old, value_new FROM ' . $prefix . 'changes WHERE attr_id > 0';
        $this->connection->execute( $query, System_Const::ValueChanged );

        $query = 'INSERT INTO {changes} ( change_id, issue_id, change_type, stamp_id, value_new )'
            . ' SELECT i.issue_id AS change_id, i.issue_id, %d AS change_type, i.issue_id AS stamp_id, COALESCE( t.value_old, i.issue_name ) AS value_new'
            . ' FROM ' . $prefix . 'issues AS i'
            . ' LEFT OUTER JOIN ('
                . ' SELECT ch1.issue_id, ch1.value_old'
                . ' FROM ' . $prefix . 'changes AS ch1'
                . ' WHERE ch1.change_id = ( SELECT MIN( ch2.change_id ) FROM ' . $prefix . 'changes AS ch2 WHERE ch2.attr_id = 0 AND ch2.issue_id = ch1.issue_id )'
            . ' ) AS t ON t.issue_id = i.issue_id';
        $this->connection->execute( $query, System_Const::IssueCreated );

        $query = 'INSERT INTO {changes} ( change_id, issue_id, change_type, stamp_id )'
            . ' SELECT comment_id AS change_id, issue_id, %d AS change_type, comment_id AS stamp_id FROM ' . $prefix . 'comments';
        $this->connection->execute( $query, System_Const::CommentAdded );

        $query = 'INSERT INTO {changes} ( change_id, issue_id, change_type, stamp_id )'
            . ' SELECT file_id AS change_id, issue_id, %d AS change_type, file_id AS stamp_id FROM ' . $prefix . 'files';
        $this->connection->execute( $query, System_Const::FileAdded );

        $tables = array(
            'comments' => array( 'comment_id', 'comment_text' ),
            'files' => array( 'file_id', 'file_name', 'file_size', 'file_data', 'file_descr', 'file_storage' )
        );

        foreach ( $tables as $tableName => $columns ) {
            $query = 'INSERT INTO {' . $tableName . '} ( ' . join( ', ', $columns ) . ' ) SELECT ' . join( ', ', $columns ) . ' FROM ' . $prefix . $tableName;
            $this->connection->execute( $query );
        }
    }
}
