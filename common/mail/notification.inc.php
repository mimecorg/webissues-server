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

class Common_Mail_Notification extends System_Web_Component
{
    private $alert = null;
    private $queryGenerator = null;
    private $page = null;

    protected function __construct( $alert )
    {
        parent::__construct();

        $this->alert = $alert;
    }

    public function prepare()
    {
        $folderId = $this->alert[ 'folder_id' ];
        $viewId = $this->alert[ 'view_id' ];

        $projectManager = new System_Api_ProjectManager();
        $folder = $projectManager->getFolder( $folderId );

        $this->projectName = $folder[ 'project_name' ];
        $this->folderName = $folder[ 'folder_name' ];

        $typeManager = new System_Api_TypeManager();
        $type = $typeManager->getIssueTypeForFolder( $folder );

        $this->queryGenerator = new System_Api_QueryGenerator();
        $this->queryGenerator->setFolder( $folder );

        $viewManager = new System_Api_ViewManager();
        if ( $viewId ) {
            $view = $viewManager->getView( $viewId );
            $definition = $view[ 'view_def' ];
            $this->viewName = $view[ 'view_name' ];
        } else {
            $definition = $viewManager->getViewSetting( $type, 'default_view' );
            $this->viewName = $this->tr( 'All Issues' );
        }

        if ( $definition != null )
            $this->queryGenerator->setViewDefinition( $definition );

        if ( $this->alert[ 'alert_email' ] != System_Const::SummaryReportEmail ) {
            $this->queryGenerator->setSinceStamp( $this->alert[ 'stamp_id' ] );

            $preferencesManager = new System_Api_PreferencesManager();
            if ( $preferencesManager->getPreference( 'notify_no_read' ) == '1' )
                $this->queryGenerator->setNoRead( true );
        }

        $connection = System_Core_Application::getInstance()->getConnection();

        $query = $this->queryGenerator->generateSelectQuery();
        $this->page = $connection->queryPageArgs( $query, $this->queryGenerator->getOrderBy(), 1000, 0, $this->queryGenerator->getQueryArguments() );

        if ( empty( $this->page ) )
            return false;

        return true;
    }

    protected function execute()
    {
        $this->view->setDecoratorClass( 'Common_Mail_Layout' );
        $this->view->setSlot( 'subject', $this->projectName . ' - ' . $this->folderName . ' - ' . $this->viewName );

        $this->columns = $this->queryGenerator->getColumnNames();

        $helper = new System_Web_ColumnHelper();
        $this->headers = $helper->getColumnHeaders() + $this->queryGenerator->getUserColumnHeaders();

        $formatter = new System_Api_Formatter();

        $this->issues = array();
        foreach ( $this->page as $row ) {
            $issue = array();
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
                            $attribute = $this->queryGenerator->getAttributeForColumn( $column );
                            $value = $formatter->convertAttributeValue( $attribute[ 'attr_def' ], $value );
                        }
                        break;
                }

                if ( $column == System_Api_Column::ID )
                    $issue[ $name ] = $value;
                else if ( $column == System_Api_Column::Name )
                    $issue[ $name ] = $this->truncate( $value, 60 );
                else
                    $issue[ $name ] = System_Api_LinkLocator::convertAndTruncate( $value, 60 );
            }
            $this->issues[ $row[ 'issue_id' ] ] = $issue;
        }
    }
}