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

class Client_IssuesList extends System_Web_Component
{
    protected function __construct()
    {
        parent::__construct();
    }

    protected function execute()
    {
        $issueManager = new System_Api_IssueManager();
        $projectManager = new System_Api_ProjectManager();

        $issueId = (int)$this->request->getQueryString( 'issue' );
        if ( $issueId ) {
            $issue = $issueManager->getIssue( $issueId );
            $folder = $projectManager->getFolderFromIssue( $issue );
            $folderId = $folder[ 'folder_id' ];
        } else {
            $folderId = (int)$this->request->getQueryString( 'folder' );
            $folder = $projectManager->getFolder( $folderId );
        }

        $this->folderName = $folder[ 'folder_name' ];

        if ( !$issueId ) {
            $breadcrumbs = new System_Web_Breadcrumbs( $this );
            $breadcrumbs->initialize( System_Web_Breadcrumbs::Project, $folder );
            $this->view->setSlot( 'page_title', $folder[ 'folder_name' ] );
        }

        $viewParam = (int)$this->request->getQueryString( 'view' );

        $this->viewForm = new System_Web_Form( 'switchView', $this );
        $this->viewForm->addField( 'viewSelect', $viewParam );

        $typeManager = new System_Api_TypeManager();
        $type = $typeManager->getIssueTypeForFolder( $folder );

        $viewManager = new System_Api_ViewManager();
        $views = $viewManager->getViewsForIssueType( $type );

        $this->viewOptions[ '' ] = $this->tr( 'All Issues' );
        if ( !empty( $views[ 0 ] ) )
            $this->viewOptions[ $this->tr( 'Personal Views' ) ] = $views[ 0 ];
        if ( !empty( $views[ 1 ] ) )
            $this->viewOptions[ $this->tr( 'Public Views' ) ] = $views[ 1 ];

        $this->viewForm->addItemsRule( 'viewSelect', $this->viewOptions );

        if ( $this->viewForm->loadForm() ) {
            $this->viewForm->validate();

            if ( !$this->viewForm->hasErrors() ) {
                if ( $this->viewSelect != '' )
                    $url = $this->appendQueryString( '/client/index.php', array( 'folder' => $folderId, 'view' => $this->viewSelect ) );
                else
                    $url = $this->appendQueryString( '/client/index.php', array( 'folder' => $folderId ) );
                $this->response->redirect( $url );
            }
        }

        $javaScript = new System_Web_JavaScript( $this->view );
        $javaScript->registerAutoSubmit( $this->viewForm->getFormSelector(), $this->viewForm->getFieldSelector( 'viewSelect' ),
            $this->viewForm->getSubmitSelector( 'go' ) );

        $queryGenerator = new System_Api_QueryGenerator();
        $queryGenerator->setFolder( $folder );

        $personalViewId = 0;

        if ( $viewParam != 0 ) {
            $view = $viewManager->getViewForIssueType( $type, $this->viewSelect );
            $definition = $view[ 'view_def' ];
            if ( !$view[ 'is_public' ] )
                $personalViewId = $view[ 'view_id' ];
        } else {
            $definition = $viewManager->getViewSetting( $type, 'default_view' );
        }
        if ( $definition != null )
            $queryGenerator->setViewDefinition( $definition );

        $this->columns = $queryGenerator->getColumnNames();

        $helper = new System_Web_ColumnHelper();
        $this->headers = $helper->getColumnHeaders() + $queryGenerator->getUserColumnHeaders();

        $this->grid = new System_Web_Grid();
        $this->grid->setPageSize( 10 );
        $this->grid->setSelection( $issueId );

        $this->grid->setColumns( $queryGenerator->getGridColumns() );
        $this->grid->setDefaultSort( $queryGenerator->getSortColumn(), $queryGenerator->getSortOrder() );

        $connection = System_Core_Application::getInstance()->getConnection();

        $query = $queryGenerator->generateCountQuery();
        $this->grid->setRowsCount( $connection->queryScalarArgs( $query, $queryGenerator->getQueryArguments() ) );

        $query = $queryGenerator->generateSelectQuery();
        $page = $connection->queryPageArgs( $query, $this->grid->getOrderBy(), $this->grid->getPageSize(),
            $this->grid->getOffset(), $queryGenerator->getQueryArguments() );

        $formatter = new System_Api_Formatter();

        $this->issues = array();
        foreach ( $page as $row ) {
            $issue = array();
            $issue[ 'stamp_id' ] = $row[ 'stamp_id' ];
            $issue[ 'read_id' ] = $row[ 'read_id' ];
            foreach ( $this->columns as $column => $name ) {
                $value = $row[ $name ];

                switch ( $column ) {
                    case System_Api_Column::ID:
                        $value = '#' . $value;
                        break;
                    case System_Api_Column::ModifiedDate:
                    case System_Api_Column::CreatedDate:
                        $value = $formatter->formatDateTime( $value, System_Api_Formatter::ToLocalTimeZone );
                        break;
                    default:
                        if ( $column > System_Api_Column::UserDefined ) {
                            $attribute = $queryGenerator->getAttributeForColumn( $column );
                            $value = $formatter->convertAttributeValue( $attribute[ 'attr_def' ], $value );
                        }
                        break;
                }

                if ( $column == System_Api_Column::Name )
                    $issue[ 'tip_name' ] = $value;

                if ( $column == System_Api_Column::ID )
                    $issue[ $name ] = $value;
                else if ( $column == System_Api_Column::Name )
                    $issue[ $name ] = $this->truncate( $value, 60 );
                else
                    $issue[ $name ] = System_Api_LinkLocator::convertAndTruncate( $value, 60 );
            }
            $this->issues[ $row[ 'issue_id' ] ] = $issue;
        }

        $this->toolBar = new System_Web_ToolBar();
        $this->toolBar->setFilterParameters( array( 'sort', 'order', 'page', 'view' ) );

        $this->toolBar->addFixedCommand( '/client/issues/addissue.php', '/common/images/issue-new-16.png', $this->tr( 'Add Issue' ), array( 'folder' => $folderId ) );
        $this->toolBar->addFixedCommand( '/client/issues/markall.php', '/common/images/folder-read-16.png', $this->tr( 'Mark All As Read' ), array( 'folder' => $folderId, 'status' => 1 ) );
        $this->toolBar->addFixedCommand( '/client/issues/markall.php', '/common/images/folder-unread-16.png', $this->tr( 'Mark All As Unread' ), array( 'folder' => $folderId, 'status' => 0 ) );
        $this->toolBar->addFixedCommand( '/client/views/index.php', '/common/images/configure-views-16.png', $this->tr( 'Manage Views' ), array( 'folder' => $folderId ) );
        $this->toolBar->addFixedCommand( '/client/alerts/index.php', '/common/images/configure-alerts-16.png', $this->tr( 'Manage Alerts' ), array( 'folder' => $folderId ) );

        $this->viewToolBar = new System_Web_ToolBar();
        $this->viewToolBar->setFilterParameters( array( 'sort', 'order', 'page', 'view' ) );

        $this->viewToolBar->addFixedCommand( '/client/views/add.php', '/common/images/view-new-16.png', $this->tr( 'Add View' ), array( 'folder' => $folderId, 'direct' => 1 ) );
        if ( $personalViewId != 0 )
            $this->viewToolBar->addFixedCommand( '/client/views/modify.php', '/common/images/edit-modify-16.png', $this->tr( 'Modify View' ), array( 'folder' => $folderId, 'id' => $personalViewId, 'direct' => 1 ) );
    }
}
