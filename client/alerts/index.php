<?php
/**************************************************************************
* This file is part of the WebIssues Server program
* Copyright (C) 2006 Michał Męciński
* Copyright (C) 2007-2015 WebIssues Team
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

require_once( '../../system/bootstrap.inc.php' );

class Client_Alerts_Index extends System_Web_Component
{
    private $folder = null;
    private $type = null;

    protected function __construct()
    {
        parent::__construct();
    }

    protected function execute()
    {
        $projectManager = new System_Api_ProjectManager();
        $typeManager = new System_Api_TypeManager();

        $folderId = (int)$this->request->getQueryString( 'folder' );
        if ( $folderId != 0 ) {
            $this->folder = $projectManager->getFolder( $folderId );
            $this->type = $typeManager->getIssueTypeForFolder( $this->folder );
            $this->folderName = $this->folder[ 'folder_name' ];
        } else {
            $typeId = (int)$this->request->getQueryString( 'type' );
            $this->type = $typeManager->getIssueType( $typeId );
            $this->typeName = $this->type[ 'type_name' ];
        }

        $this->view->setDecoratorClass( 'Common_FixedBlock' );
        $this->view->setSlot( 'page_title', $this->tr( 'Manage Alerts' ) );

        $breadcrumbs = new Common_Breadcrumbs( $this );
        if ( $this->folder != null )
            $breadcrumbs->initialize( Common_Breadcrumbs::Folder, $this->folder );
        else
            $breadcrumbs->initialize( Common_Breadcrumbs::Folder, $this->type );

        $this->form = new System_Web_Form( 'views', $this );
        if ( $this->form->loadForm() )
            $this->response->redirect( $breadcrumbs->getParentUrl() );

        $alertManager = new System_Api_AlertManager();

        $this->grid = new System_Web_Grid();
        $this->grid->setPageSize( 10 );
        $this->grid->setParameters( 'apage', 'aorder', 'asort' );
        $this->grid->setMergeParameters( array( 'alert' => null ) );

        $this->grid->setColumns( $alertManager->getAlertsColumns() );
        $this->grid->setDefaultSort( 'name', System_Web_Grid::Ascending );
        if ( $this->folder != null )
            $this->grid->setRowsCount( $alertManager->getAlertsCount( $this->folder ) );
        else
            $this->grid->setRowsCount( $alertManager->getGlobalAlertsCount( $this->type ) );

        $viewManager = new System_Api_ViewManager();

        $helper = new Client_Alerts_Helper();
        $this->emailEngine = $helper->hasEmailEngine();

        $emailTypes = $helper->getEmailTypes();

        if ( $this->folder != null )
            $page = $alertManager->getAlertsPage( $this->folder, $this->grid->getOrderBy(), $this->grid->getPageSize(), $this->grid->getOffset() );
        else
            $page = $alertManager->getGlobalAlertsPage( $this->type, $this->grid->getOrderBy(), $this->grid->getPageSize(), $this->grid->getOffset() );

        $isAdmin = System_Api_Principal::getCurrent()->isAdministrator();
        if ( !$isAdmin && $this->folder != null )
            $isAdmin = $this->folder[ 'project_access' ] == System_Const::AdministratorAccess;

        $this->alerts = array();
        foreach ( $page as $row ) {
            if ( $row[ 'view_name' ] === null ) {
                $row[ 'view_name' ] = $this->tr( 'All Issues' );
                $row[ 'view_def' ] = $viewManager->getViewSetting( $this->type, 'default_view' );
            }
            $row[ 'alert_email' ] = $emailTypes[ $row[ 'alert_email' ] ];
            $row[ 'classes' ] = array();
            if ( $row[ 'is_public' ] == false || $isAdmin )
                $row[ 'classes' ][] = 'editable';
            $this->getAlertStatus( $row );
            $this->alerts[ $row[ 'alert_id' ] ] = $row;
        }

        $selectedId = (int)$this->request->getQueryString( 'id' );

        $this->grid->setSelection( $selectedId );

        $classes = array();
        if ( $isAdmin ) {
            $classes[] = 'editable';
        } else if ( $selectedId ) {
            $alert = $alertManager->getAlert( $selectedId );
            if ( $alert[ 'is_public' ] == false )
                $classes[] = 'editable';
        }

        $this->toolBar = new System_Web_ToolBar();
        $this->toolBar->setSelection( $selectedId, null, $classes );

        $this->toolBar->addFixedCommand( '/client/alerts/add.php', '/common/images/alert-new-16.png', $this->tr( 'Add Alert' ) );
        if ( $isAdmin )
            $this->toolBar->addFixedCommand( '/client/alerts/add.php', '/common/images/alert-public-new-16.png', $this->tr( 'Add Public Alert' ), array( 'public' => 1 ) );
        if ( $this->emailEngine )
            $this->toolBar->addItemCommand( '/client/alerts/modify.php', '/common/images/edit-modify-16.png', $this->tr( 'Modify Alert' ), array( 'editable' ) );
        $this->toolBar->addItemCommand( '/client/alerts/delete.php', '/common/images/edit-delete-16.png', $this->tr( 'Delete Alert' ), array( 'editable' ) );

        $javaScript = new System_Web_JavaScript( $this->view );
        $javaScript->registerSelection( $this->toolBar );
    }

    private function getAlertStatus( &$row )
    {
        $queryGenerator = new System_Api_QueryGenerator();
        if ( $this->folder != null )
            $queryGenerator->setFolder( $this->folder );
        else
            $queryGenerator->setIssueType( $this->type );

        if ( $row[ 'view_def' ] != null )
            $queryGenerator->setViewDefinition( $row[ 'view_def' ] );

        $query = $queryGenerator->generateAlertQuery();

        $connection = System_Core_Application::getInstance()->getConnection();
        $issues = $connection->queryTableArgs( $query, $queryGenerator->getQueryArguments() );

        $unread = 0;
        $modified = 0;
        $read = 0;

        foreach ( $issues as $issue ) {
            $count = $issue[ 's_count' ];
            $sign = $issue[ 's_sign' ];

            if ( $sign === null )
                $unread += $count;
            else if ( $sign == 1 )
                $modified += $count;
            else
                $read += $count;
        } 

        $row[ 'alert_unread' ] = $unread;
        $row[ 'alert_modified' ] = $modified;
        $row[ 'alert_total' ] = $unread + $modified + $read;
    }
}

System_Bootstrap::run( 'Common_Application', 'Client_Alerts_Index' );
