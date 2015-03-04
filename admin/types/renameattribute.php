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

class Admin_Types_RenameAttribute extends System_Web_Component
{
    protected function __construct()
    {
        parent::__construct();
    }

    protected function execute()
    {
        $this->view->setDecoratorClass( 'Common_FixedBlock' );
        $this->view->setSlot( 'page_title', $this->tr( 'Rename Attribute' ) );

        $breadcrumbs = new Common_Breadcrumbs( $this );
        $breadcrumbs->initialize( Common_Breadcrumbs::IssueTypes );

        $typeManager = new System_Api_TypeManager();
        $attributeId = (int)$this->request->getQueryString( 'attribute' );
        $this->attribute = $typeManager->getAttributeType( $attributeId );

        $this->form = new System_Web_Form( 'types', $this );
        $this->form->addField( 'attributeName', $this->attribute[ 'attr_name' ] );

        $this->form->addTextRule( 'attributeName', System_Const::NameMaxLength );

        if ( $this->form->loadForm() ) {
            if ( $this->form->isSubmittedWith( 'cancel' ) )
                $this->response->redirect( $breadcrumbs->getParentUrl() );

            $this->form->validate();

            if ( $this->form->isSubmittedWith( 'ok' ) && !$this->form->hasErrors() ) {
                $this->submit();
                if ( !$this->form->hasErrors() )
                    $this->response->redirect( $breadcrumbs->getParentUrl() );
            }
        }
    }

    private function submit()
    {
        $typeManager = new System_Api_TypeManager();
        try {
            $typeManager->renameAttributeType( $this->attribute, $this->attributeName );
        } catch ( System_Api_Error $ex ) {
            $this->form->getErrorHelper()->handleError( 'attributeName', $ex );
        }
    }
}

System_Bootstrap::run( 'Common_Application', 'Admin_Types_RenameAttribute' );
