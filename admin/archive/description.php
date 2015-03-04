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

class Admin_Archive_Description extends System_Web_Component
{
    protected function __construct()
    {
        parent::__construct();
    }

    protected function execute()
    {
        $projectManager = new System_Api_ProjectManager();
        $projectId = (int)$this->request->getQueryString( 'id' );
        $project = $projectManager->getArchivedProject( $projectId );

        $formatter = new System_Api_Formatter();
        $prettyPrint = false;

        $this->descr = $projectManager->getProjectDescription( $project );
        $this->descr[ 'modified_date' ] = $formatter->formatDateTime( $this->descr[ 'modified_date' ], System_Api_Formatter::ToLocalTimeZone );
        if ( $this->descr[ 'descr_format' ] == System_Const::TextWithMarkup )
            $this->descr[ 'descr_text' ] = System_Web_MarkupProcessor::convertToRawHtml( $this->descr[ 'descr_text' ], $prettyPrint );
        else
            $this->descr[ 'descr_text' ] = System_Web_LinkLocator::convertToRawHtml( $this->descr[ 'descr_text' ] );

        $this->view->setDecoratorClass( 'Common_FixedBlock' );
        $this->view->setSlot( 'page_title', $project[ 'project_name' ] );

        $breadcrumbs = new Common_Breadcrumbs( $this );
        $breadcrumbs->initialize( Common_Breadcrumbs::ProjectsArchive );

        $this->form = new System_Web_Form( 'projects', $this );

        if ( $this->form->loadForm() ) {
            if ( $this->form->isSubmittedWith( 'ok' ) )
                $this->response->redirect( $breadcrumbs->getParentUrl() );
        }

        if ( $prettyPrint ) {
            $script = new System_Web_JavaScript( $this->view );
            $script->registerPrettyPrint();
        }
    }
}

System_Bootstrap::run( 'Common_Application', 'Admin_Archive_Description' );
