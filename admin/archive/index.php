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

class Admin_Archive_Index extends System_Web_Component
{
    protected function __construct()
    {
        parent::__construct();
    }

    protected function execute()
    {
        $this->view->setDecoratorClass( 'Common_FixedBlock' );
        $this->view->setSlot( 'page_title', $this->tr( 'Projects Archive' ) );

        $preferencesManager = new System_Api_PreferencesManager();
        $pageSize = $preferencesManager->getPreferenceOrSetting( 'project_page_size' );

        $this->grid = new System_Web_Grid();
        $this->grid->setPageSize( $pageSize );
        $this->grid->setMergeParameters( array( 'id' => null ) );

        $projectManager = new System_Api_ProjectManager();
        $this->grid->setColumns( $projectManager->getProjectsColumns() );
        $this->grid->setDefaultSort( 'name', System_Web_Grid::Ascending );
        $this->grid->setRowsCount( $projectManager->getArchivedProjectsCount() );

        $page = $projectManager->getArchivedProjectsPage( $this->grid->getOrderBy(), $this->grid->getPageSize(), $this->grid->getOffset() );

        $this->projects = array();
        foreach ( $page as $row ) {
            $row[ 'classes' ] = array();
            if ( $row[ 'descr_id' ] != null )
                $row[ 'classes' ][] = 'description';
            $this->projects[ $row[ 'project_id' ] ] = $row;
        }

        $selectedId = (int)$this->request->getQueryString( 'id' );

        $this->grid->setSelection( $selectedId );

        $this->toolBar = new System_Web_ToolBar();
        $this->toolBar->setSelection( $selectedId );

        $this->toolBar->addItemCommand( '/admin/archive/restore.php', '/common/images/unarchive-16.png', $this->tr( 'Restore Project' ) );
        $this->toolBar->addItemCommand( '/admin/archive/rename.php', '/common/images/edit-rename-16.png', $this->tr( 'Rename Project' ) );
        $this->toolBar->addItemCommand( '/admin/archive/delete.php', '/common/images/edit-delete-16.png', $this->tr( 'Delete Project' ) );
        $this->toolBar->addItemCommand( '/admin/archive/description.php', '/common/images/view-description-16.png', $this->tr( 'View Description' ), array( 'description' ) );

        $javaScript = new System_Web_JavaScript( $this->view );
        $javaScript->registerSelection( $this->toolBar );
    }
}

System_Bootstrap::run( 'Common_Application', 'Admin_Archive_Index' );
