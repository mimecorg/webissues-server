<?php
/**************************************************************************
* This file is part of the WebIssues Server program
* Copyright (C) 2006 Michał Męciński
* Copyright (C) 2007-2013 WebIssues Team
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
* Manage alerts.
*
* Like all API classes, this class does not check permissions to perform
* an operation and does not validate the input values. An error is thrown
* only if the requested object does not exist or is inaccessible.
*/
class System_Api_AlertManager extends System_Api_Base
{
    /**
    * Constructor.
    */
    public function __construct()
    {
        parent::__construct();
    }

    /**
    * Return all alerts for the current user.
    * @return An array of associative arrays representing alerts.
    */
    public function getAlerts()
    {
        $principal = System_Api_Principal::getCurrent();

        $query = 'SELECT alert_id, folder_id, view_id, alert_email'
            . ' FROM {alerts}'
            . ' WHERE user_id = %d';

        return $this->connection->queryTable( $query, $principal->getUserId() );
    }

    /**
    * Get the total number of alerts for given folder.
    * @param $folder Folder for which alerts are retrieved.
    */
    public function getAlertsCount( $folder )
    {
        $principal = System_Api_Principal::getCurrent();

        $folderId = $folder[ 'folder_id' ];

        $query = 'SELECT COUNT(*)'
            . ' FROM {alerts}'
            . ' WHERE user_id = %d AND folder_id = %d';

        return $this->connection->queryScalar( $query, $principal->getUserId(), $folderId );
    }

    /**
    * Get the paged list of alerts for given folder.
    * @param $folder Folder for which alerts are retrieved.
    * @param $orderBy The sorting order specifier.
    * @param $limit Maximum number of rows to return.
    * @param $offset Zero-based index of first row to return.
    * @return An array of associative arrays representing alerts.
    */
    public function getAlertsPage( $folder, $orderBy, $limit, $offset )
    {
        $principal = System_Api_Principal::getCurrent();

        $folderId = $folder[ 'folder_id' ];

        $query = 'SELECT a.alert_id, v.view_id, v.view_name, v.view_def, a.alert_email'
            . ' FROM {alerts} AS a'
            . ' LEFT OUTER JOIN {views} AS v ON v.view_id = a.view_id'
            . ' WHERE a.user_id = %d AND a.folder_id = %d';

        return $this->connection->queryPage( $query, $orderBy, $limit, $offset, $principal->getUserId(), $folderId );
    }

    /**
    * Check if given folder has the "All Issues" view.
    * @param $folder Folder for which alert is retrieved.
    * @return @c true if the folder has the "All Issues" view.
    */
    public function hasAllIssuesAlert( $folder )
    {
        $principal = System_Api_Principal::getCurrent();

        $folderId = $folder[ 'folder_id' ];

        $query = 'SELECT alert_id'
            . ' FROM {alerts}'
            . ' WHERE user_id = %d AND folder_id = %d AND view_id IS NULL';

        return $this->connection->queryScalar( $query, $principal->getUserId(), $folderId ) !== false;
    }

    /**
    * Get views for which there is no alert for a given folder.
    * @param $type Folder for which views are retrieved.
    * @return An array of associative arrays representing views.
    */
    public function getViewsWithoutAlerts( $folder )
    {
        $principal = System_Api_Principal::getCurrent();

        $folderId = $folder[ 'folder_id' ];
        $typeId = $folder[ 'type_id' ];

        $query = 'SELECT v.view_id, v.view_name, ( CASE WHEN v.user_id IS NULL THEN 1 ELSE 0 END ) AS is_public'
            . ' FROM {views} AS v'
            . ' LEFT OUTER JOIN {alerts} AS a ON a.view_id = v.view_id AND a.user_id = %1d AND a.folder_id = %2d'
            . ' WHERE v.type_id = %3d AND ( v.user_id = %1d OR v.user_id IS NULL ) AND a.alert_id IS NULL'
            . ' ORDER BY v.view_name COLLATE LOCALE';

        $views = $this->connection->queryTable( $query, $principal->getUserId(), $folderId, $typeId );

        $result = array();
        foreach ( $views as $view )
            $result[ $view[ 'is_public' ] ][ $view[ 'view_id' ] ] = $view[ 'view_name' ];

        return $result;
    }

    /**
    * Return sortable column definitions for the System_Web_Grid.
    */
    public function getAlertsColumns()
    {
        return array(
            'name' => 'view_name COLLATE LOCALE'
        );
    }

    /**
    * Get the alert with given identifier.
    * @param $alertId Identifier of the alert.
    * @return Array representing the alert.
    */
    public function getAlert( $alertId )
    {
        $principal = System_Api_Principal::getCurrent();

        $query = 'SELECT a.alert_id, v.view_id, v.view_name, a.alert_email'
            . ' FROM {alerts} AS a'
            . ' LEFT OUTER JOIN {views} AS v ON v.view_id = a.view_id'
            . ' WHERE a.alert_id = %d AND a.user_id = %d';

        if ( !( $alert = $this->connection->queryRow( $query, $alertId, $principal->getUserId() ) ) )
            throw new System_Api_Error( System_Api_Error::UnknownAlert );

        return $alert;
    }

    /**
    * Create a new alert. An error is thrown if such alert already exists.
    * @param $folder Folder for which the alert is created.
    * @param $view Optional view associated with the alert.
    * @param $alertEmail Type of emails associated with the alert.
    * @return The identifier of the new alert.
    */
    public function addAlert( $folder, $view, $alertEmail )
    {
        $principal = System_Api_Principal::getCurrent();

        $folderId = $folder[ 'folder_id' ];
        $stampId = $folder[ 'stamp_id' ];

        $transaction = $this->connection->beginTransaction( System_Db_Transaction::Serializable, 'alerts' );

        try {
            if ( $view != null ) {
                $viewId = $view[ 'view_id' ];

                $query = 'SELECT alert_id FROM {alerts} WHERE user_id = %d AND folder_id = %d AND view_id = %d';
                if ( $this->connection->queryScalar( $query, $principal->getUserId(), $folderId, $viewId ) !== false )
                    throw new System_Api_Error( System_Api_Error::AlertAlreadyExists );

                if ( $stampId != null ) {
                    $query = 'INSERT INTO {alerts} ( user_id, folder_id, view_id, alert_email, stamp_id ) VALUES ( %d, %d, %d, %d, %d )';
                    $this->connection->execute( $query, $principal->getUserId(), $folderId, $viewId, $alertEmail, $stampId );
                } else {
                    $query = 'INSERT INTO {alerts} ( user_id, folder_id, view_id, alert_email, stamp_id ) VALUES ( %d, %d, %d, %d, NULL )';
                    $this->connection->execute( $query, $principal->getUserId(), $folderId, $viewId, $alertEmail );
                }
            } else {
                $query = 'SELECT alert_id FROM {alerts} WHERE user_id = %d AND folder_id = %d AND view_id IS NULL';
                if ( $this->connection->queryScalar( $query, $principal->getUserId(), $folderId ) !== false )
                    throw new System_Api_Error( System_Api_Error::AlertAlreadyExists );

                if ( $stampId != null ) {
                    $query = 'INSERT INTO {alerts} ( user_id, folder_id, view_id, alert_email, stamp_id ) VALUES ( %d, %d, NULL, %d, %d )';
                    $this->connection->execute( $query, $principal->getUserId(), $folderId, $alertEmail, $stampId );
                } else {
                    $query = 'INSERT INTO {alerts} ( user_id, folder_id, view_id, alert_email, stamp_id ) VALUES ( %d, %d, NULL, %d, NULL )';
                    $this->connection->execute( $query, $principal->getUserId(), $folderId, $alertEmail );
                }
            }

            $alertId = $this->connection->getInsertId( 'alerts', 'alert_id' );

            $transaction->commit();
        } catch ( Exception $ex ) {
            $transaction->rollback();
            throw $ex;
        }

        return $alertId;
    }

    /**
    * Modify settings of an alert.
    * @param $alert The alert to modify.
    * @param $alertEmail Type of emails associated with the alert.
    * @return @c true if the alert was modified.
    */
    public function modifyAlert( $alert, $alertEmail )
    {
        $alertId = $alert[ 'alert_id' ];
        $oldEmail = $alert[ 'alert_email' ];

        if ( $alertEmail == $oldEmail )
            return false;

        $query = 'UPDATE {alerts} SET alert_email = %d WHERE alert_id = %d';
        $this->connection->execute( $query, $alertEmail, $alertId );

        return true;
    }

    /**
    * Delete an alert.
    * @param $alert The alert to delete.
    * @return @c true if the alert was deleted.
    */
    public function deleteAlert( $alert )
    {
        $alertId = $alert[ 'alert_id' ];

        $query = 'DELETE FROM {alerts} WHERE alert_id = %d';
        $this->connection->execute( $query, $alertId );

        return true;
    }

    /**
    * Return alerts for which emails should be sent.
    * @param $includeSummary If @c true, the summary notifications and reports are
    * included in addition to immediate notifications.
    * @return An array of associative arrays representing alerts.
    */
    public function getAlertsToEmail( $includeSummary )
    {
        $principal = System_Api_Principal::getCurrent();

        if ( $includeSummary ) {
            $query = 'SELECT a.alert_id, a.folder_id, a.view_id, a.alert_email, a.stamp_id'
                . ' FROM {alerts} AS a'
                . ' JOIN {folders} AS f ON f.folder_id = a.folder_id';
            if ( !$principal->isAdministrator() )
                $query .= ' JOIN {rights} AS r ON r.project_id = f.project_id AND r.user_id = %1d';
            $query .= ' WHERE a.user_id = %1d AND ( a.alert_email > %2d AND f.stamp_id > COALESCE( a.stamp_id, 0 ) OR a.alert_email = %3d )';

            return $this->connection->queryTable( $query, $principal->getUserId(), System_Const::NoEmail, System_Const::SummaryReportEmail );
        } else {
            $query = 'SELECT a.alert_id, a.folder_id, a.view_id, a.alert_email, a.stamp_id'
                . ' FROM {alerts} AS a'
                . ' JOIN {folders} AS f ON f.folder_id = a.folder_id';
            if ( !$principal->isAdministrator() )
                $query .= ' JOIN {rights} AS r ON r.project_id = f.project_id AND r.user_id = %1d';
            $query .= ' WHERE a.user_id = %1d AND a.alert_email = %2d AND f.stamp_id > COALESCE( a.stamp_id, 0 )';

            return $this->connection->queryTable( $query, $principal->getUserId(), System_Const::ImmediateNotificationEmail );
        }
    }

    /**
    * Update the stamp of last sent email for given alert.
    * @param $alert The alert to update.
    */
    public function updateAlertStamp( $alert )
    {
        $alertId = $alert[ 'alert_id' ];
        $folderId = $alerts[ 'folder_id' ];

        $query = 'UPDATE {alerts}'
            . ' SET stamp_id = ( SELECT f.stamp_id FROM {folders} AS f WHERE f.folder_id = %d )'
            . ' WHERE alert_id = %d';

        $this->connection->execute( $query, $folderId, $alertId );
    }
}
