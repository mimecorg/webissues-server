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

class Client_IssueDetails extends System_Web_Component
{
    protected function __construct()
    {
        parent::__construct();
    }

    protected function execute()
    {
        $issueManager = new System_Api_IssueManager();
        $formatter = new System_Api_Formatter();

        $issueId = (int)$this->request->getQueryString( 'issue' );
        $issue = $issueManager->getIssue( $issueId );

        $this->issue = $issue;
        $this->issueId = $this->issue[ 'issue_id' ];

        $this->view->setSlot( 'page_title', $issue[ 'issue_name' ] );

        $breadcrumbs = new System_Web_Breadcrumbs( $this );
        $breadcrumbs->initialize( System_Web_Breadcrumbs::Folder, $issue );

        $this->issue[ 'issue_id' ] = '#' . $this->issue[ 'issue_id' ];
        $this->issue[ 'created_date' ] = $formatter->formatDateTime( $this->issue[ 'created_date' ], System_Api_Formatter::ToLocalTimeZone );
        $this->issue[ 'modified_date' ] = $formatter->formatDateTime( $this->issue[ 'modified_date' ], System_Api_Formatter::ToLocalTimeZone );

        $isRead = (int)$this->request->getQueryString( 'unread' ) == 0;

        $stateManager = new System_Api_StateManager();
        $stateId = $stateManager->setIssueRead( $issue, $isRead );

        $this->attributeValues = $issueManager->getAllAttributeValuesForIssue( $issue );

        foreach ( $this->attributeValues as &$value ) {
            $formatted = $formatter->convertAttributeValue( $value[ 'attr_def' ], $value[ 'attr_value' ], System_Api_Formatter::MultiLine );
            $value[ 'attr_value' ] = System_Api_LinkLocator::convertToRawHtml( $formatted );
        }

        $typeManager = new System_Api_TypeManager();
        $type = $typeManager->getIssueTypeForIssue( $issue );

        $viewManager = new System_Api_ViewManager();
        $this->attributeValues = $viewManager->sortByAttributeOrder( $type, $this->attributeValues );

        $historyProvider = new System_Api_HistoryProvider();
        $historyProvider->setIssueId( $issueId );

        $this->filterBar = new System_Web_FilterBar();
        $this->filterBar->setParameter( 'hflt' );
        $this->filterBar->setMergeParameters( array( 'hpg' => null ) );

        $this->filters = array(
            System_Const::CommentAdded => $this->tr( 'Only Comments' ),
            System_Const::FileAdded => $this->tr( 'Only Attachments' ) );

        $this->historyFilter = $this->request->getQueryString( 'hflt' );
        if ( ( $this->historyFilter !== null ) && !isset( $this->filters[ $this->historyFilter ] ) )
            throw new System_Core_Exception( 'Invalid filter' );

        $this->pager = new System_Web_Grid();
        $this->pager->setPageSize( 20 );
        $this->pager->setParameters( 'hpg' );

        $connection = System_Core_Application::getInstance()->getConnection();

        $query = $historyProvider->generateCountQuery( $this->historyFilter );
        $rowCount = $connection->queryScalarArgs( $query, $historyProvider->getQueryArguments() );
        if ( $this->historyFilter === null )
            $rowCount++;
        $this->pager->setRowsCount( $rowCount );

        if ( $rowCount > 0 ) {
            $query = $historyProvider->generateSelectQuery( $this->historyFilter );
            $page = $connection->queryPageArgs( $query, $historyProvider->getOrderBy(), $this->pager->getPageSize(),
                $this->pager->getOffset(), $historyProvider->getQueryArguments() );

            $this->history = $historyProvider->processPage( $page );

            $localeHelper = new System_Web_LocaleHelper();

            $principal = System_Api_Principal::getCurrent();

            foreach ( $this->history as $id => &$item ) {
                $item[ 'change_id' ] = '#' . $item[ 'change_id' ];
                $item[ 'created_date' ] = $formatter->formatDateTime( $item[ 'created_date' ], System_Api_Formatter::ToLocalTimeZone );
                $item[ 'modified_date' ] = $formatter->formatDateTime( $item[ 'modified_date' ], System_Api_Formatter::ToLocalTimeZone );
                if ( isset( $item[ 'comment_text' ] ) )
                    $item[ 'comment_text' ] = System_Api_LinkLocator::convertToRawHtml( $item[ 'comment_text' ] );
                if ( isset( $item[ 'file_descr' ] ) )
                    $item[ 'file_descr' ] = System_Api_LinkLocator::convertToRawHtml( $item[ 'file_descr' ] );
                if ( isset( $item[ 'file_size' ] ) )
                    $item[ 'file_size' ] = $localeHelper->formatFileSize( $item[ 'file_size' ] );
                if ( isset( $item[ 'changes' ] ) ) {
                    foreach ( $item[ 'changes' ] as &$change ) {
                        $newValue = $change[ 'value_new' ];
                        $oldValue = $change[ 'value_old' ];
                        if ( $change[ 'attr_def' ] != null ) {
                            $newValue = $formatter->convertAttributeValue( $change[ 'attr_def' ], $newValue );
                            $oldValue = $formatter->convertAttributeValue( $change[ 'attr_def' ], $oldValue );
                        }
                        $change[ 'value_new' ] = System_Api_LinkLocator::convertToRawHtml( $newValue );
                        $change[ 'value_old' ] = System_Api_LinkLocator::convertToRawHtml( $oldValue );
                    }
                }
                $item[ 'can_edit' ] = $issue[ 'project_access' ] == System_Const::AdministratorAccess || $item[ 'created_user' ] == $principal->getUserId();
            }
        }

        $this->toolBar = new System_Web_ToolBar();

        $this->toolBar->addFixedCommand( '/client/issues/editissue.php', '/common/images/edit-modify-16.png', $this->tr( 'Edit Attributes' ) );
        $this->toolBar->addFixedCommand( '/client/issues/addcomment.php', '/common/images/comment-16.png', $this->tr( 'Add Comment' ) );
        $this->toolBar->addFixedCommand( '/client/issues/addattachment.php', '/common/images/file-attach-16.png', $this->tr( 'Add Attachment' ) );
        if ( $issue[ 'project_access' ] == System_Const::AdministratorAccess ) {
            $this->toolBar->addFixedCommand( '/client/issues/moveissue.php', '/common/images/issue-move-16.png', $this->tr( 'Move Issue' ) );
            $this->toolBar->addFixedCommand( '/client/issues/deleteissue.php', '/common/images/edit-delete-16.png', $this->tr( 'Delete Issue' ) );
        }
        if ( $isRead )
            $this->toolBar->addFixedCommand( '/client/index.php', '/common/images/issue-unread-16.png', $this->tr( 'Mark As Unread' ), array( 'unread' => 1 ) );
        else
            $this->toolBar->addFixedCommand( '/client/index.php', '/common/images/issue-16.png', $this->tr( 'Mark As Read' ), array( 'unread' => null ) );
    }
}