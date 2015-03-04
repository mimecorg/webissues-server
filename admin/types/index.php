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

class Admin_Types_Index extends System_Web_Component
{
    protected function __construct()
    {
        parent::__construct();
    }

    protected function execute()
    {
        $this->view->setDecoratorClass( 'Common_SinglePane' );
        $this->view->setSlot( 'page_title', $this->tr( 'Issue Types' ) );

        $this->grid = new System_Web_Grid();
        $this->grid->setPageSize( 10 );
        $this->grid->setMergeParameters( array( 'type' => null, 'attribute' => null ) );

        $typeManager = new System_Api_TypeManager();
        $this->grid->setColumns( $typeManager->getIssueTypesColumns() );
        $this->grid->setDefaultSort( 'name', System_Web_Grid::Ascending );
        $this->grid->setRowsCount( $typeManager->getIssueTypesCount() );

        $types = $typeManager->getIssueTypesPage( $this->grid->getOrderBy(), $this->grid->getPageSize(), $this->grid->getOffset() );
        $attributes = $typeManager->getAttributeTypesForIssueTypes( $types );

        $helper = new Admin_Types_Helper();
        $expressionHelper = new System_Web_ExpressionHelper();

        $this->types = array();
        foreach ( $types as $type ) {
            $type[ 'attributes' ] = array();
            $this->types[ $type[ 'type_id' ] ] = $type;
        }

        foreach ( $attributes as $attribute ) {
            $info = System_Api_DefinitionInfo::fromString( $attribute[ 'attr_def' ] );
            $attribute[ 'type' ] = $helper->getTypeName( $info->getType() );
            $attribute[ 'default_value' ] = $expressionHelper->formatExpression( $info->getType(), $attribute[ 'attr_def' ], $info->getMetadata( 'default', '' ) );
            $attribute[ 'required' ] = $info->getMetadata( 'required', 0 ) ? $this->tr( 'Yes' ) : $this->tr( 'No' );
            $attribute[ 'details' ] = $helper->getAttributeDetails( $info );
            $this->types[ $attribute[ 'type_id' ] ][ 'attributes' ][ $attribute[ 'attr_id' ] ] = $attribute;
        }

        $emptyTypes = array();
        foreach ( $types as $type ) {
            if ( empty( $type[ 'attributes' ] ) )
                $emptyTypes[] = $type[ 'type_id' ];
        }

        $attributeId = (int)$this->request->getQueryString( 'attribute' );
        if ( $attributeId != 0 ) {
            $attribute = $typeManager->getAttributeType( $attributeId );
            $typeId = $attribute[ 'type_id' ];
        } else {
            $typeId = (int)$this->request->getQueryString( 'type' );
        }

        $this->grid->setSelection( $attributeId, $typeId );

        $this->toolBar = new System_Web_ToolBar();
        $this->toolBar->setParameters( 'attribute', 'type' );
        $this->toolBar->setSelection( $attributeId, $typeId );

        $this->toolBar->addFixedCommand( '/admin/types/addtype.php', '/common/images/type-new-16.png', $this->tr( 'Add Type' ) );
        $this->toolBar->addItemCommand( '/admin/types/addattribute.php', '/common/images/attribute-new-16.png', $this->tr( 'Add Attribute' ) );
        $this->toolBar->addParentCommand( '/admin/types/renametype.php', '/common/images/edit-rename-16.png', $this->tr( 'Rename Type' ) );
        $this->toolBar->addParentCommand( '/admin/types/deletetype.php', '/common/images/edit-delete-16.png', $this->tr( 'Delete Type' ) );
        $this->toolBar->addChildCommand( '/admin/types/modifyattribute.php', '/common/images/edit-modify-16.png', $this->tr( 'Modify Attribute' ) );
        $this->toolBar->addChildCommand( '/admin/types/renameattribute.php', '/common/images/edit-rename-16.png', $this->tr( 'Rename Attribute' ) );
        $this->toolBar->addChildCommand( '/admin/types/deleteattribute.php', '/common/images/edit-delete-16.png', $this->tr( 'Delete Attribute' ) );
        $this->toolBar->addItemCommand( '/admin/views/index.php', '/common/images/configure-views-16.png', $this->tr( 'View Settings' ) );

        $javaScript = new System_Web_JavaScript( $this->view );
        $javaScript->registerExpandCookie( 'wi_types' );
        $javaScript->registerSelection( $this->toolBar );

        $this->grid->removeExpandCookieIds( 'wiAdminTypes', $emptyTypes );
    }
}

System_Bootstrap::run( 'Common_Application', 'Admin_Types_Index' );
