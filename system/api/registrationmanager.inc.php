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
* Manage user registration requests.
*/
class System_Api_RegistrationManager extends System_Api_Base
{
    /**
    * Constructor.
    */
    public function __construct()
    {
        parent::__construct();
    }

    /**
    * Generate a random activation key.
    */
    public function generateKey()
    {
        $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $len = strlen( $chars );

        $result = '';

        for ( $i = 0; $i < 8; $i ++ )
            $result .= $chars[ mt_rand( 0, $len - 1 ) ];

        return $result;
    }

    /**
    * Return the registration request with given activation key.
    * @param $key The activation key.
    * @return Array containing the request.
    */
    public function getRequestWithKey( $key )
    {
        $query = 'SELECT request_id, user_login, user_name, request_key, created_time, is_active, is_sent FROM {register_requests} WHERE request_key = %s';

        if ( !( $request = $this->connection->queryRow( $query, $key ) ) )
            throw new System_Api_Error( System_Api_Error::InvalidActivationKey );

        return $request;
    }

    /**
    * Add a request to register a new account.
    * @param $login The login of the user.
    * @param $name The name of the user.
    * @param $password The password of the user.
    * @param $email The email address of the user.
    * @param $key The activation key.
    * @return Identifier of the request.
    */
    public function addRequest( $login, $name, $password, $email, $key )
    {
        $transaction = $this->connection->beginTransaction( System_Db_Transaction::Serializable, 'users' );

        try {
            $query = 'SELECT user_id FROM {users} WHERE user_login = %s OR user_name = %s';
            if ( $this->connection->queryScalar( $query, $login, $name ) !== false )
                throw new System_Api_Error( System_Api_Error::UserAlreadyExists );

            $query = 'SELECT request_id FROM {register_requests} WHERE user_login = %s OR user_name = %s';
            if ( $this->connection->queryScalar( $query, $login, $name ) !== false )
                throw new System_Api_Error( System_Api_Error::UserAlreadyExists );

            $query = 'SELECT user_id FROM {preferences} WHERE pref_key = %s AND pref_value = %s';
            if ( $this->connection->queryScalar( $query, 'email', $email ) !== false )
                throw new System_Api_Error( System_Api_Error::EmailAlreadyExists );

            $query = 'SELECT request_id FROM {register_requests} WHERE user_email = %s';
            if ( $this->connection->queryScalar( $query, $email ) !== false )
                throw new System_Api_Error( System_Api_Error::EmailAlreadyExists );

            $passwordHash = new System_Core_PasswordHash();
            $hash = $passwordHash->hashPassword( $password );

            $query = 'INSERT INTO {register_requests} ( user_login, user_name, user_email, user_passwd, request_key, created_time, is_active, is_sent ) VALUES ( %s, %s, %s, %s, %s, %d, 0, 0 )';
            $this->connection->execute( $query, $login, $name, $email, $hash, $key, time() );

            $requestId = $this->connection->getInsertId( 'register_requests', 'request_id' );

            $transaction->commit();
        } catch ( Exception $ex ) {
            $transaction->rollback();
            throw $ex;
        }

        $eventLog = new System_Api_EventLog( $this );
        $eventLog->addEvent( System_Api_EventLog::Access, System_Api_EventLog::Information,
            $eventLog->tr( 'User "%1" registered', null, $name ) );

        return $requestId;
    }

    /**
    * Activate the registration request.
    * @param $request The request to activate.
    * @return @c true if the request was activated.
    */
    public function activateRequest( $request )
    {
        $requestId = $request[ 'request_id' ];
        $isActive = $request[ 'is_active' ];

        if ( $isActive == true )
            return false;

        $query = 'UPDATE {register_requests} SET is_active = 1 WHERE request_id = %d';
        $this->connection->execute( $query, $requestId );

        $eventLog = new System_Api_EventLog( $this );
        $eventLog->addEvent( System_Api_EventLog::Access, System_Api_EventLog::Information,
            $eventLog->tr( 'Registration request for user "%1" activated', null, $request[ 'user_name' ] ) );

        return true;
    }
}
