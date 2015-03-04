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

class Admin_Register_Index extends System_Web_Component
{
    protected function __construct()
    {
        parent::__construct();
    }

    protected function execute()
    {
        $this->view->setDecoratorClass( 'Common_FixedBlock' );
        $this->view->setSlot( 'page_title', $this->tr( 'Registration Requests' ) );

        $this->grid = new System_Web_Grid();
        $this->grid->setPageSize( 20 );
        $this->grid->setMergeParameters( array( 'id' => null ) );

        $registrationManager = new System_Api_RegistrationManager();
        $this->grid->setColumns( $registrationManager->getRequestsColumns() );
        $this->grid->setDefaultSort( 'name', System_Web_Grid::Ascending );
        $this->grid->setRowsCount( $registrationManager->getRequestsCount() );

        $page = $registrationManager->getRequestsPage( $this->grid->getOrderBy(), $this->grid->getPageSize(), $this->grid->getOffset() );

        $formatter = new System_Api_Formatter();

        $this->requests = array();
        foreach ( $page as $row ) {
            $row[ 'date' ] = $formatter->formatDateTime( $row[ 'created_time' ], System_Api_Formatter::ToLocalTimeZone );
            $this->requests[ $row[ 'request_id' ] ] = $row;
        }

        $selectedId = (int)$this->request->getQueryString( 'id' );

        $this->grid->setSelection( $selectedId );

        $this->toolBar = new System_Web_ToolBar();
        $this->toolBar->setSelection( $selectedId );

        $this->toolBar->addItemCommand( '/admin/register/approve.php', '/common/images/approve-16.png', $this->tr( 'Approve Request' ) );
        $this->toolBar->addItemCommand( '/admin/register/reject.php', '/common/images/reject-16.png', $this->tr( 'Reject Request' ) );

        $javaScript = new System_Web_JavaScript( $this->view );
        $javaScript->registerSelection( $this->toolBar );
    }
}

System_Bootstrap::run( 'Common_Application', 'Admin_Register_Index' );
