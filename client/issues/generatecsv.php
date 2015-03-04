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

class Client_Issues_GenerateCsv extends Common_Application
{
    protected function __construct()
    {
        parent::__construct( null );
    }

    protected function execute()
    {
        $principal = System_Api_Principal::getCurrent();
        if ( !$principal->isAuthenticated() ) {
            $redirect = true;

            $serverManager = new System_Api_ServerManager();
            if ( $serverManager->getSetting( 'anonymous_access' ) == 1 ) {
                $this->isAnonymous = true;
                $redirect = false;
            }

            if ( $redirect )
                $this->redirectToLoginPage();
        }

        $issueManager = new System_Api_IssueManager();
        $projectManager = new System_Api_ProjectManager();
        $typeManager = new System_Api_TypeManager();

        $issueId = (int)$this->request->getQueryString( 'issue' );
        $typeId = (int)$this->request->getQueryString( 'type' );
        if ( $typeId ) {
            $type = $typeManager->getIssueType( $typeId );
            $folder = null;
        } else {
            if ( $issueId ) {
                $issue = $issueManager->getIssue( $issueId );
                $folder = $projectManager->getFolderFromIssue( $issue );
                $folderId = $folder[ 'folder_id' ];
            } else {
                $folderId = (int)$this->request->getQueryString( 'folder' );
                $folder = $projectManager->getFolder( $folderId );
            }
            $type = $typeManager->getIssueTypeForFolder( $folder );
        }

        $viewManager = new System_Api_ViewManager();
        $views = $viewManager->getViewsForIssueType( $type );

        $initialView = $viewManager->getViewSetting( $type, 'initial_view' );

        if ( $initialView != '' && empty( $views[ 1 ][ (int)$initialView ] ) )
            $initialView = '';

        $viewParam = $this->request->getQueryString( 'view' );

        if ( $viewParam == '' && $initialView != '' )
            $viewParam = (int)$initialView;
        else
            $viewParam = (int)$viewParam;

        $query = $this->request->getQueryString( 'q' );
        $queryColumn = (int)$this->request->getQueryString( 'qc' );

        $reportType = (int)$this->request->getQueryString( 'report' );

        $queryGenerator = new System_Api_QueryGenerator();
        if ( $folder )
            $queryGenerator->setFolder( $folder );
        else
            $queryGenerator->setIssueType( $type );

        $personalViewId = 0;

        if ( $viewParam != 0 ) {
            $view = $viewManager->getViewForIssueType( $type, $viewParam );
            $definition = $view[ 'view_def' ];
            if ( !$view[ 'is_public' ] )
                $personalViewId = $view[ 'view_id' ];
        } else {
            $definition = $viewManager->getViewSetting( $type, 'default_view' );
        }
        if ( $definition != null )
            $queryGenerator->setViewDefinition( $definition );

        if ( $query != '' )
            $queryGenerator->setSearchText( $queryColumn, $query );

        if ( $reportType == 1 )
            $queryGenerator->includeAvailableColumns();

        $columns = $queryGenerator->getColumnNames();

        if ( $reportType != 1 ) {
            $serverManager = new System_Api_ServerManager();
            if ( $serverManager->getSetting( 'hide_id_column' ) == 1 )
                unset( $columns[ System_Api_Column::ID ] );
        }

        $helper = new System_Web_ColumnHelper();
        $headers = $helper->getColumnHeaders() + $queryGenerator->getUserColumnHeaders();

        $grid = new System_Web_Grid();
        $grid->setColumns( $queryGenerator->getGridColumns() );
        $grid->setDefaultSort( $queryGenerator->getSortColumn(), $queryGenerator->getSortOrder() );

        $connection = System_Core_Application::getInstance()->getConnection();

        $query = $queryGenerator->generateSelectQuery();
        $page = $connection->queryPageArgs( $query, $grid->getOrderBy(), System_Const::INT_MAX, 0, $queryGenerator->getQueryArguments() );

        $lines = array();

        $cells = array();
        foreach ( $columns as $column => $name )
            $cells[] = $headers[ $column ];

        $lines[] = $this->mergeCsvCells( $cells );

        $formatter = new System_Api_Formatter();

        foreach ( $page as $row ) {
            $cells = array();

            foreach ( $columns as $column => $name ) {
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

                if ( $column == System_Api_Column::Location )
                    $value = $row[ 'project_name' ] . ' — ' . $row[ 'folder_name' ];

                $cells[] = $value;
            }

            $lines[] = $this->mergeCsvCells( $cells );
        }

        $bom = "\xEF\xBB\xBF";

        $report = $bom . join( "\r\n", $lines );

        $this->response->setContentType( 'text/csv' );
        $this->response->setCustomHeader( 'Content-Disposition', 'attachment; filename="report.csv"' );

        $this->response->setContent( $report );
    }

    private function mergeCsvCells( $cells )
    {
        $escaped = array();

        foreach ( $cells as $cell ) {
            if ( substr( $cell, 0, 1 ) == ' ' || substr( $cell, -1, 1 ) == ' ' || strpbrk( $cell, "\",\n" ) !== false || $cell == 'ID' )
                $escaped[] = '"' . str_replace( '"', '""', $cell ) . '"';
            else
                $escaped[] = $cell;
        }

        return join( ',', $escaped );
    }
}

System_Bootstrap::run( 'Client_Issues_GenerateCsv' );
