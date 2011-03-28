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
* Manage users and project members.
*/
class System_Api_UserManager extends System_Api_Base
{
    /**
    * Constructor.
    */
    public function __construct()
    {
        parent::__construct();
    }

    /**
    * Get list of users.
    * @return An array of associative arrays representing users.
    */
    public function getUsers()
    {
        $query = 'SELECT user_id, user_login, user_name, user_access FROM {users}';

        return $this->connection->queryTable( $query );
    }

    /**
    * Get the user with given identifier.
    * @param $userId Identifier of the user.
    * @return Array containing the user.
    */
    public function getUser( $userId )
    {
        $query = 'SELECT user_id, user_login, user_name, user_access FROM {users} WHERE user_id = %d';

        if ( !( $user = $this->connection->queryRow( $query, $userId ) ) )
            throw new System_Api_Error( System_Api_Error::UnknownUser );

        return $user;
    }

    /**
    * Get the rights of all project members.
    * @return An array of associative arrays representing member rights.
    */
    public function getRights()
    {
        $principal = System_Api_Principal::getCurrent();

        $query = 'SELECT r.project_id, r.user_id, r.project_access FROM {rights} AS r';
        if ( !$principal->isAdministrator() )
            $query .= ' JOIN {rights} AS r2 ON r2.project_id = r.project_id AND r2.user_id = %d';

        return $this->connection->queryTable( $query, $principal->getUserId() );
    }

    /**
    * Get list of the members of given project.
    * @param $project The project to retrieve members.
    * @return An array of associative arrays representing members.
    */
    public function getMembers( $project )
    {
        $projectId = $project[ 'project_id' ];

        $query = 'SELECT project_id, user_id, project_access FROM {rights} WHERE project_id = %d';

        return $this->connection->queryTable( $query, $projectId );
    }

    /**
    * Chech the access to a project for the given user.
    * @param $user The user whose access is modified.
    * @param $project The project to which the access is related.
    * @return An associative array representing member.
    */
    public function getMember( $user, $project )
    {
        $userId = $user[ 'user_id' ];
        $projectId = $project[ 'project_id' ];

        $query = 'SELECT project_id, user_id, project_access FROM {rights} WHERE user_id = %d AND project_id = %d';

        if ( !( $member = $this->connection->queryRow( $query, $userId, $projectId ) ) )
            throw new System_Api_Error( System_Api_Error::UnknownUser );

        return $member;
    }

    /**
    * Get the total number of users.
    */
    public function getUsersCount()
    {
        $query = 'SELECT COUNT(*) FROM {users}';
 
        return $this->connection->queryScalar( $query );
    }

    /**
    * Get a paged list of users.
    * @param $orderBy The sorting order specifier.
    * @param $limit Maximum number of rows to return.
    * @param $offset Zero-based index of first row to return.
    * @return An array of associative arrays representing types.
    */
    public function getUsersPage( $orderBy, $limit, $offset )
    {
        $query = 'SELECT user_id, user_login, user_name, user_access FROM {users}';

        return $this->connection->queryPage( $query, $orderBy, $limit, $offset );
    }

    /**
    * Return sortable column definitions for the System_Web_Grid.
    */
    public function getUsersColumns()
    {
        return array(
            'name' => 'user_name',
            'login' => 'user_login',
            'access' => 'user_access'
            );
    }

    /**
    * Return the number of members of given project.
    * @param $project The project to count members.
    * @return The number of members.
    */
    public function getMembersCount( $project )
    {
        $projectId = $project[ 'project_id' ];

        $query = 'SELECT COUNT(*) FROM {rights} WHERE project_id = %d';
 
        return $this->connection->queryScalar( $query, $projectId );
    }

    /**
    * Get paged list of the members of given project.
    * @param $project The project to retrieve members.
    * @param $orderBy The sorting order specifier.
    * @param $limit Maximum number of rows to return.
    * @param $offset Zero-based index of first row to return.
    * @return An array of associative arrays representing members.
    */
    public function getMembersPage( $project, $orderBy, $limit, $offset )
    {
        $projectId = $project[ 'project_id' ];

        $query = 'SELECT r.project_id, r.user_id, r.project_access, u.user_name FROM {rights} AS r'
            . ' JOIN {users} AS u ON u.user_id = r.user_id AND r.project_id = %d';

        return $this->connection->queryPage( $query, $orderBy, $limit, $offset, $projectId );
    }

    /**
    * Return sortable column definitions for the System_Web_Grid.
    */
    public function getMembersColumns()
    {
        return array(
            'name' => 'u.user_name',
            'access' => 'r.project_access'
        );
    }

    /**
    * Check if the value is a valid user name. This is a helper method for
    * System_Api_Validator.
    * @param $value The value to validate.
    * @param $projectId Identifier of the project the user must be a member or
    * or @c null to accept all users.
    */
    public function checkUserName( $value, $projectId )
    {
        $query = 'SELECT u.user_id FROM {users} AS u';
        if ( $projectId )
            $query .= ' JOIN {rights} AS r ON r.user_id = u.user_id AND r.project_id = %2d';
        $query .= ' WHERE u.user_name = %1s';

        if ( $this->connection->queryScalar( $query, $value, $projectId ) === false )
            throw new System_Api_Error( System_Api_Error::InvalidValue );
    }

    /**
    * Return only users which have valid email and are not disabled.
    */
    public function getUsersWithEmail()
    {
        $query = 'SELECT u.user_id, u.user_name, u.user_access'
            . ' FROM {users} AS u'
            . ' JOIN {preferences} AS p ON p.user_id = u.user_id AND p.pref_key = %s'
            . ' WHERE u.user_access > %d';

        return $this->connection->queryTable( $query, 'email', System_Const::NoAccess );
    }

    /**
    * Create a new user. An error is thrown if a user with given login or name
    * already exists. The user has System_Const::NormalAccess by default.
    * @param $login The login of the user.
    * @param $name The name of the user.
    * @param $password The password of the user.
    * @param $isTemp If @c true the password is temporary and user must
    * change it at next logon.
    * @return Identifier of the user.
    */
    public function addUser( $login, $name, $password, $isTemp )
    {
        $query = 'SELECT user_id FROM {users} WHERE user_login = %s OR user_name = %s';
        if ( $this->connection->queryScalar( $query, $login, $name ) !== false )
            throw new System_Api_Error( System_Api_Error::UserAlreadyExists );

        $passwordHash = new System_Core_PasswordHash();
        $hash = $passwordHash->hashPassword( $password );

        $query = 'INSERT INTO {users} ( user_login, user_name, user_passwd, user_access, passwd_temp ) VALUES ( %s, %s, %s, %d, %d )';
        $this->connection->execute( $query, $login, $name, $hash, System_Const::NormalAccess, $isTemp );

        return $this->connection->getInsertId( 'users', 'user_id' );
    }

    /**
    * Set the password of a user.
    * @param $user The user whoose password is changed.
    * @param $newPassword The new password.
    * @param $isTemp If @c true the password is temporary and user must
    * change it at next logon.
    * @return @c true if the password was changed.
    */
    public function setPassword( $user, $newPassword, $isTemp )
    {
        $principal = System_Api_Principal::getCurrent();

        $userId = $user[ 'user_id' ];

        if ( $userId == $principal->getUserId() )
            throw new System_Api_Error( System_Api_Error::AccessDenied );

        $passwordHash = new System_Core_PasswordHash();
        $newHash = $passwordHash->hashPassword( $newPassword );

        $query = 'UPDATE {users} SET user_passwd = %s, passwd_temp = %d WHERE user_id = %d';
        $this->connection->execute( $query, $newHash, $isTemp, $userId );

        return true;
    }

    /**
    * Change the password of the current user.
    * @param $password The current password.
    * @param $newPassword The current password.
    * @return @c true if the password was changed.
    */
    public function changePassword( $password, $newPassword )
    {
        $userId = System_Api_Principal::getCurrent()->getUserId();

        $query = 'SELECT user_passwd FROM {users} WHERE user_id = %d';
        $hash = $this->connection->queryScalar( $query, $userId );

        $passwordHash = new System_Core_PasswordHash();

        if ( !$passwordHash->checkPassword( $password, $hash ) )
            throw new System_Api_Error( System_Api_Error::IncorrectLogin );

        if ( $newPassword == $password )
            throw new System_Api_Error( System_Api_Error::CannotReusePassword );

        $newHash = $passwordHash->hashPassword( $newPassword );

        $query = 'UPDATE {users} SET user_passwd = %s, passwd_temp = 0 WHERE user_id = %d';
        $this->connection->execute( $query, $newHash, $userId );

        return true;
    }

    /**
    * Rename a user. An error is thrown if another user with given name
    * already exists.
    * @param $user The user to rename.
    * @param $newName The new name of the user.
    * @return @c true if the name was modified.
    */
    public function renameUser( $user, $newName )
    {
        $userId = $user[ 'user_id' ];
        $oldName = $user[ 'user_name' ];

        if ( $newName == $oldName )
            return false;

        $query = 'SELECT user_id FROM {users} WHERE user_name = %s';
        if ( $this->connection->queryScalar( $query, $newName ) !== false )
            throw new System_Api_Error( System_Api_Error::UserAlreadyExists );

        $query = 'UPDATE {users} SET user_name = %s WHERE user_id = %d';
        $this->connection->execute( $query, $newName, $userId );

        return true;
    }

    /**
    * Modify the access to the server for the given user.
    * The access level of the built-in 'admin' user cannot be changed.
    * @param $user The user whoose access is modified.
    * @param $newAccess The new access level of the user.
    * @return @c true if the access level was modified.
    */
    public function grantUser( $user, $newAccess )
    {
        $principal = System_Api_Principal::getCurrent();

        $userId = $user[ 'user_id' ];
        $oldAccess = $user[ 'user_access' ];

        if ( $userId == $principal->getUserId() )
            throw new System_Api_Error( System_Api_Error::AccessDenied );

        if ( $newAccess == $oldAccess )
            return false;

        $query = 'UPDATE {users} SET user_access = %d WHERE user_id = %d';
        $this->connection->execute( $query, $newAccess, $userId );

        return true;
    }

    /**
    * Modify the access to a project for the given user.
    * @param $user The user whose access is modified.
    * @param $project The project to which the access is related.
    * @param $newAccess The new access level of the user.
    * @return @c true if the access level was modified.
    */
    public function grantMember( $user, $project, $newAccess )
    {
        $principal = System_Api_Principal::getCurrent();

        $projectId = $project[ 'project_id' ];
        $userId = $user[ 'user_id' ];

        if ( $userId == $principal->getUserId() && $principal->getUserAccess() != System_Const::AdministratorAccess )
            throw new System_Api_Error( System_Api_Error::AccessDenied );

        $query = 'SELECT project_access FROM {rights} WHERE project_id = %d AND user_id = %d';
        $oldAccess = $this->connection->queryScalar( $query, $projectId, $userId );
        if ( $oldAccess === false )
            $oldAccess = System_Const::NoAccess;

        if ( $newAccess == $oldAccess )
            return false;

        if ( $oldAccess == System_Const::NoAccess )
            $query = 'INSERT INTO {rights} ( project_id, user_id, project_access ) VALUES ( %1d, %2d, %3d )';
        else if ( $newAccess == System_Const::NoAccess )
            $query = 'DELETE FROM {rights} WHERE project_id = %1d AND user_id = %2d';
        else
            $query = 'UPDATE {rights} SET project_access = %3d WHERE project_id = %1d AND user_id = %2d';
        $this->connection->execute( $query, $projectId, $userId, $newAccess );

        return true;
    }
}