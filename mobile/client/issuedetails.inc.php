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

if ( !defined( 'WI_VERSION' ) ) die( -1 );

class Mobile_Client_IssueDetails extends System_Web_Component
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

        $this->issue[ 'issue_id' ] = '#' . $this->issue[ 'issue_id' ];
        $this->issue[ 'created_date' ] = $formatter->formatDateTime( $this->issue[ 'created_date' ], System_Api_Formatter::ToLocalTimeZone );
        $this->issue[ 'modified_date' ] = $formatter->formatDateTime( $this->issue[ 'modified_date' ], System_Api_Formatter::ToLocalTimeZone );

        $isRead = (int)$this->request->getQueryString( 'unread' ) == 0;

        $principal = System_Api_Principal::getCurrent();
        if ( $principal->isAuthenticated() ) {
            $stateManager = new System_Api_StateManager();
            $stateId = $stateManager->setIssueRead( $issue, $isRead ? $issue[ 'stamp_id' ] : 0 );
        }

        $serverManager = new System_Api_ServerManager();
        $hideEmpty = $serverManager->getSetting( 'hide_empty_values' );

        $this->attributeValues = $issueManager->getAllAttributeValuesForIssue( $issue, $hideEmpty == '1' ? System_Api_IssueManager::HideEmptyValues : 0 );

        foreach ( $this->attributeValues as &$value ) {
            $formatted = $formatter->convertAttributeValue( $value[ 'attr_def' ], $value[ 'attr_value' ], System_Api_Formatter::MultiLine );
            $value[ 'attr_value' ] = System_Web_LinkLocator::convertToRawHtml( $formatted );
        }

        $typeManager = new System_Api_TypeManager();
        $type = $typeManager->getIssueTypeForIssue( $issue );

        $viewManager = new System_Api_ViewManager();
        $this->attributeValues = $viewManager->sortByAttributeOrder( $type, $this->attributeValues );

        $prettyPrint = false;

        $this->canEditDescr = $issue[ 'project_access' ] == System_Const::AdministratorAccess || $issue[ 'created_user' ] == $principal->getUserId();

        if ( $issue[ 'descr_id' ] != null ) {
            $this->descr = $issueManager->getDescription( $issue );
            $this->descr[ 'is_modified' ] = ( $this->descr[ 'modified_date' ] - $issue[ 'created_date' ] ) > 180 || $this->descr[ 'modified_user' ] != $issue[ 'created_user' ];
            $this->descr[ 'modified_date' ] = $formatter->formatDateTime( $this->descr[ 'modified_date' ], System_Api_Formatter::ToLocalTimeZone );
            if ( $this->descr[ 'descr_format' ] == System_Const::TextWithMarkup )
                $this->descr[ 'descr_text' ] = System_Web_MarkupProcessor::convertToRawHtml( $this->descr[ 'descr_text' ], $prettyPrint );
            else
                $this->descr[ 'descr_text' ] = System_Web_LinkLocator::convertToRawHtml( $this->descr[ 'descr_text' ] );
        }

        $historyProvider = new System_Api_HistoryProvider();
        $historyProvider->setIssueId( $issueId );

        $this->filterBar = new System_Web_FilterBar();
        $this->filterBar->setParameter( 'hflt' );
        $this->filterBar->setMergeParameters( array( 'hpg' => null ) );

        $this->filters = array(
            System_Api_HistoryProvider::AllHistory => $this->tr( 'All History' ),
            System_Api_HistoryProvider::Comments => $this->tr( 'Only Comments' ),
            System_Api_HistoryProvider::Files => $this->tr( 'Only Attachments' ),
            System_Api_HistoryProvider::CommentsAndFiles => $this->tr( 'Comments & Attachments' ) );

        $this->historyFilter = $this->request->getQueryString( 'hflt' );
        if ( ( $this->historyFilter !== null ) && !isset( $this->filters[ $this->historyFilter ] ) )
            throw new System_Core_Exception( 'Invalid filter' );

        $preferencesManager = new System_Api_PreferencesManager();
        $this->defaultFilter = $preferencesManager->getPreferenceOrSetting( 'history_filter' );

        if ( $this->historyFilter === null )
            $this->historyFilter = $this->defaultFilter;

        $pageSize = $serverManager->getSetting( 'history_page_mobile' );

        $this->pager = new System_Web_Grid();
        $this->pager->setPageSize( $pageSize );
        $this->pager->setParameters( 'hpg' );

        $connection = System_Core_Application::getInstance()->getConnection();

        $query = $historyProvider->generateCountQuery( $this->historyFilter );
        $rowCount = $connection->queryScalarArgs( $query, $historyProvider->getQueryArguments() );
        $this->pager->setRowsCount( $rowCount );

        if ( $rowCount > 0 ) {
            $order = $preferencesManager->getPreferenceOrSetting( 'history_order' );

            $query = $historyProvider->generateSelectQuery( $this->historyFilter );
            $page = $connection->queryPageArgs( $query, $historyProvider->getOrderBy( $order ), $this->pager->getPageSize(),
                $this->pager->getOffset(), $historyProvider->getQueryArguments() );

            $this->history = $historyProvider->processPage( $page );

            $localeHelper = new System_Web_LocaleHelper();

            foreach ( $this->history as $id => &$item ) {
                $item[ 'change_id' ] = '#' . $item[ 'change_id' ];
                $item[ 'is_modified' ] = ( $item[ 'modified_date' ] - $item[ 'created_date' ] ) > 180 || $item[ 'modified_user' ] != $item[ 'created_user' ];
                $item[ 'created_date' ] = $formatter->formatDateTime( $item[ 'created_date' ], System_Api_Formatter::ToLocalTimeZone );
                $item[ 'modified_date' ] = $formatter->formatDateTime( $item[ 'modified_date' ], System_Api_Formatter::ToLocalTimeZone );
                if ( isset( $item[ 'comment_text' ] ) ) {
                    if ( $item[ 'comment_format' ] == System_Const::TextWithMarkup )
                        $item[ 'comment_text' ] = System_Web_MarkupProcessor::convertToRawHtml( $item[ 'comment_text' ], $prettyPrint );
                    else
                        $item[ 'comment_text' ] = System_Web_LinkLocator::convertToRawHtml( $item[ 'comment_text' ] );
                }
                if ( isset( $item[ 'file_descr' ] ) )
                    $item[ 'file_descr' ] = System_Web_LinkLocator::convertToRawHtml( $item[ 'file_descr' ] );
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
                        $change[ 'value_new' ] = System_Web_LinkLocator::convertToRawHtml( $newValue );
                        $change[ 'value_old' ] = System_Web_LinkLocator::convertToRawHtml( $oldValue );
                    }
                }
                $item[ 'can_edit' ] = $issue[ 'project_access' ] == System_Const::AdministratorAccess || $item[ 'created_user' ] == $principal->getUserId();
            }
        }

        $this->canReply = $principal->isAuthenticated();

        $this->toolBar = new System_Web_ToolBar();

        if ( $principal->isAuthenticated() ) {
            $this->toolBar->addFixedCommand( '/mobile/client/issues/editissue.php', '/common/images/edit-modify-16.png', $this->tr( 'Edit Attributes' ) );
            $this->toolBar->addFixedCommand( '/mobile/client/issues/addcomment.php', '/common/images/comment-16.png', $this->tr( 'Add Comment' ) );
            $this->toolBar->addFixedCommand( '/mobile/client/issues/addattachment.php', '/common/images/file-attach-16.png', $this->tr( 'Add Attachment' ) );
            if ( $issue[ 'descr_id' ] == null && $this->canEditDescr )
                $this->toolBar->addFixedCommand( '/mobile/client/issues/adddescription.php', '/common/images/description-new-16.png', $this->tr( 'Add Description' ) );
            $this->toolBar->addFixedCommand( '/mobile/client/issues/cloneissue.php', '/common/images/issue-clone-16.png', $this->tr( 'Clone Issue' ) );
            if ( $issue[ 'project_access' ] == System_Const::AdministratorAccess ) {
                $this->toolBar->addFixedCommand( '/mobile/client/issues/moveissue.php', '/common/images/issue-move-16.png', $this->tr( 'Move Issue' ) );
                $this->toolBar->addFixedCommand( '/mobile/client/issues/deleteissue.php', '/common/images/edit-delete-16.png', $this->tr( 'Delete Issue' ) );
            }
            if ( $isRead )
                $this->toolBar->addFixedCommand( '/mobile/client/index.php', '/common/images/issue-unread-16.png', $this->tr( 'Mark As Unread' ), array( 'unread' => 1 ) );
            else
                $this->toolBar->addFixedCommand( '/mobile/client/index.php', '/common/images/issue-16.png', $this->tr( 'Mark As Read' ), array( 'unread' => null ) );
            if ( $serverManager->getSetting( 'email_engine' ) != '' ) {
                if ( $issue[ 'subscription_id' ] != null )
                    $this->toolBar->addFixedCommand( '/mobile/client/issues/unsubscribe.php', '/common/images/issue-unsubscribe-16.png', $this->tr( 'Unsubscribe' ) );
                else
                    $this->toolBar->addFixedCommand( '/mobile/client/issues/subscribe.php', '/common/images/issue-subscribe-16.png', $this->tr( 'Subscribe' ) );
            }
        }

        if ( $prettyPrint ) {
            $script = new System_Web_JavaScript( $this->view );
            $script->registerPrettyPrint();
        }
    }
}
