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

class Admin_Types_DeleteType extends System_Web_Component
{
    protected function __construct()
    {
        parent::__construct();
    }

    protected function execute()
    {
        $this->view->setDecoratorClass( 'Common_MessageBlock' );
        $this->view->setSlot( 'page_title', $this->tr( 'Delete Type' ) );

        $breadcrumbs = new Common_Breadcrumbs( $this );
        $breadcrumbs->initialize( Common_Breadcrumbs::IssueTypes );

        $typeManager = new System_Api_TypeManager();
        $typeId = (int)$this->request->getQueryString( 'type' );
        $this->type = $typeManager->getIssueType( $typeId );

        $this->form = new System_Web_Form( 'types', $this );
        $this->form->addViewState( 'warning', false );

        if ( $this->form->loadForm() ) {
            if ( $this->form->isSubmittedWith( 'cancel' ) )
                $this->response->redirect( $breadcrumbs->getParentUrl() );

            if ( $this->form->isSubmittedWith( 'ok' ) ) {
                if ( $this->submit() )
                    $this->response->redirect( $breadcrumbs->getParentUrl() );
            }
        } else {
            $this->warning = $typeManager->checkIssueTypeUsed( $this->type );
        }
    }

    private function submit()
    {
        $typeManager = new System_Api_TypeManager();
        try {
            return $typeManager->deleteIssueType( $this->type, $this->warning ? System_Api_TypeManager::ForceDelete : 0 );
        } catch ( System_Api_Error $ex ) {
            if ( $ex->getMessage() == System_Api_Error::CannotDeleteType ) {
                $this->warning = true;
                return false;
            }
            throw $ex;
        }
    }
}

System_Bootstrap::run( 'Common_Application', 'Admin_Types_DeleteType' );
