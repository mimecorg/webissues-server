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

class Common_Views_Helper extends System_Web_Base
{
    private $isPublic;
    private $attributes;
    private $order;
    private $type;
    private $typeManager;
    private $viewManager;
    private $columns = null;
    private $fixedColumns;
    private $selectedColumns;
    private $definitions;
    private $valueDefinitions = null;
    private $sortColumn = System_Api_Column::ID;
    private $sortOrder = 0;
    private $conditionOperators;
    private $conditionsOperands;
    private $conditions;
    private $publicViews = array();
    private $initialView = null;

    public function __construct()
    {
        parent::__construct();

        $this->typeManager = new System_Api_TypeManager();
        $this->viewManager = new System_Api_ViewManager();

        if ( $this->request->isRelativePathUnder( '/client' ) )
            $this->isPublic = false;
        else if ( $this->request->isRelativePathUnder( '/admin' ) )
            $this->isPublic = true;
        else
            throw new System_Core_Exception( 'Invalid URL' );

        $folderId = (int)$this->request->getQueryString( 'folder' );
        if ( !$this->isPublic && $folderId != 0 ) {
            $projectManager = new System_Api_ProjectManager();
            $folder = $projectManager->getFolder( $folderId );
            $this->type = $this->typeManager->getIssueTypeForFolder( $folder );
        } else {
            $typeId = (int)$this->request->getQueryString( 'type' );
            $this->type = $this->typeManager->getIssueType( $typeId );
        }
    }

    public function getBreadcrumbs( $page )
    {
        $breadcrumbs = new Common_Breadcrumbs( $page );

        if ( $this->isPublic ) {
            if ( $this->request->getScriptBaseName() == 'index' )
                $breadcrumbs->initialize( Common_Breadcrumbs::IssueTypes );
            else
                $breadcrumbs->initialize( Common_Breadcrumbs::ViewSettings );
        } else {
            $folderId = (int)$this->request->getQueryString( 'folder' );
            if ( $folderId != 0 ) {
                $projectManager = new System_Api_ProjectManager();
                $folder = $projectManager->getFolder( $folderId );

                if ( $this->request->getScriptBaseName() == 'index' )
                    $breadcrumbs->initialize( Common_Breadcrumbs::Folder, $folder );
                else
                    $breadcrumbs->initialize( Common_Breadcrumbs::ManageViews, $folder );
            } else {
                if ( $this->request->getScriptBaseName() == 'index' )
                    $breadcrumbs->initialize( Common_Breadcrumbs::Folder, $this->type );
                else
                    $breadcrumbs->initialize( Common_Breadcrumbs::ManageViews, $this->type );
            }
        }

        return $breadcrumbs;
    }

    public function initializeViewParsing()
    {
        $this->initializeAttributesAndOrder();

        $this->loadColumns();

        $this->fixedColumns = array();
        $this->fixedColumns[ System_Api_Column::ID ] = $this->columns[ System_Api_Column::ID ];
        $this->fixedColumns[ System_Api_Column::Name ] = $this->columns[ System_Api_Column::Name ];

        $this->conditionOperators = array(
            'BEG' => $this->tr( 'begins with' ),
            'CON' => $this->tr( 'contains' ),
            'END' => $this->tr( 'ends with' ),
            'IN' => $this->tr( 'in' ),
            'EQ' => '=',
            'GT' => '>',
            'GTE' => '>=',
            'LT' => '<',
            'LTE' => '<=',
            'NEQ' => '<>' );
    }

    public function initializeAttributesAndOrder()
    {
        $rows = $this->typeManager->getAttributeTypesForIssueType( $this->type );
        $this->attributes = $this->viewManager->sortByAttributeOrder( $this->type, $rows );

        $this->order = array();
        foreach ( $this->attributes as $attribute )
            $this->order[ $attribute[ 'attr_id' ] ] = $attribute[ 'attr_name' ];
    }

    public function getIsPublic()
    {
       return $this->isPublic;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getOldView()
    {
        $viewId = (int)$this->request->getQueryString( 'id' );
        return $this->viewManager->getViewForIssueType( $this->type, $viewId, System_Api_ViewManager::AllowEdit );
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function getDefinitions()
    {
        return $this->definitions;
    }

    public function getValueDefinitions()
    {
        return $this->valueDefinitions;
    }

    public function getFixedColumns()
    {
        return $this->fixedColumns;
    }

    public function getFilterConditions()
    {
        return $this->conditions;
    }

    public function getSortColumn()
    {
        return $this->sortColumn;
    }

    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    public function getOrder()
    {
        return $this->order;
    }

    public function getSelectedColumns()
    {
        return $this->selectedColumns;
    }

    public function getOrderAsString()
    {
        return join( ', ', $this->order );
    }

    public function loadOrder()
    {
        $ordered = array();

        $order = $this->viewManager->getViewSetting( $this->type, 'attribute_order' );

        if ( $order != null ) {
            $validator = new System_Api_Validator();
            $attributeIds = $validator->convertToIntArray( $order );
    
            foreach ( $attributeIds as $attributeId ) {
                if ( isset( $this->order[ $attributeId ] ) )
                    $ordered[ $attributeId ] = $this->order[ $attributeId ];
            }
        }

        foreach ( $this->order as $id => $name ) {
            if ( !isset( $ordered[ $id ] ) )
                $ordered[ $id ] = $name;
        }

        $this->order = $ordered; 
    }

    public function getDefaultView()
    {
        $definition = $this->viewManager->getViewSetting( $this->type, 'default_view' );
        if ( $definition != null ) {
            $this->loadView( $definition );
        } else {
            $this->selectedColumns[ System_Api_Column::ModifiedBy ] = $this->columns[ System_Api_Column::ModifiedBy ];
            $this->selectedColumns[ System_Api_Column::ModifiedDate ] = $this->columns[ System_Api_Column::ModifiedDate ];
        }
        $result[ 'columns' ] = $this->getViewColumnsAsString();
        $result[ 'sort' ] = $this->getSortAsString();
        return $result;
    }

    public function loadInitialView()
    {
        $this->publicViews = $this->viewManager->getPublicViewsForIssueType( $this->type );
        $this->initialView = $this->viewManager->getViewSetting( $this->type, 'initial_view' );
    }

    public function getInitialViewName()
    {
        if ( $this->initialView != '' ) {
            foreach ( $this->publicViews as $id => $name ) {
                if ( $id == $this->initialView )
                    return $name;
            }
        }
        return $this->tr( 'All Issues' );
    }

    public function getInitialView()
    {
        if ( $this->initialView != '' ) {
            foreach ( $this->publicViews as $id => $name ) {
                if ( $id == $this->initialView )
                    return $this->initialView;
            }
        }
        return '';
    }

    public function getPublicViews()
    {
        return $this->publicViews;
    }

    public function loadView( $definition )
    {
        $this->selectedColumns = array();
        $this->conditions = array();

        $info = System_Api_DefinitionInfo::fromString( $definition );

        $validator = new System_Api_Validator();
        $viewColumns = $validator->convertToIntArray( $info->getMetadata( 'columns' ) );

        foreach ( $viewColumns as $column ) {
            if ( isset( $this->columns[ $column ] ) && !isset( $this->fixedColumns[ $column ] ) )
                $this->selectedColumns[ $column ] = $this->columns[ $column ];
        }

        $sortColumn = $info->getMetadata( 'sort-column' );
        if ( isset( $this->selectedColumns[ $sortColumn ] ) ) {
            $this->sortColumn = $sortColumn;
            $this->sortOrder = $info->getMetadata( 'sort-desc', 0 );
        }

        $filters = $info->getMetadata( 'filters' );
        if ( $filters != null ) {
            $formatter = new System_Api_Formatter();
            $expressionHelper = new System_Web_ExpressionHelper();

            foreach ( $filters as $filter ) {
                $filterInfo = System_Api_DefinitionInfo::fromString( $filter );

                $column = $filterInfo->getMetadata( 'column' );
                $value = $filterInfo->getMetadata( 'value' );

                if ( isset( $this->valueDefinitions[ $column ] ) ) {
                    $valueInfo = System_Api_DefinitionInfo::fromString( $this->valueDefinitions[ $column ] );

                    $value = $expressionHelper->formatExpression( $valueInfo->getType(), $this->valueDefinitions[ $column ], $value );

                    $this->conditions[] = array(
                        'column' => $column,
                        'type' => $filterInfo->getType(),
                        'value' => $value );
                }
            }
        }
    }

    public function prepareGrid( $component )
    {
        $grid = new System_Web_Grid();
        $grid->setPageSize( 10 );
        $grid->setParameters( 'vpage', 'vorder', 'vsort' );
        $grid->setMergeParameters( array( 'id' => null ) );

        $grid->setColumns( $this->viewManager->getViewsColumns() );
        $grid->setDefaultSort( 'name', System_Web_Grid::Ascending );

        if ( $this->isPublic ) {
            $grid->setRowsCount( $this->viewManager->getPublicViewsCount( $this->type ) );
            $page = $this->viewManager->getPublicViewsPage( $this->type, $grid->getOrderBy(), $grid->getPageSize(), $grid->getOffset() );
        } else {
            $grid->setRowsCount( $this->viewManager->getPersonalViewsCount( $this->type ) );
            $page = $this->viewManager->getPersonalViewsPage( $this->type, $grid->getOrderBy(), $grid->getPageSize(), $grid->getOffset() );
        }

        $component->views = array();
        foreach ( $page as $row ) {
            $this->loadView( $row[ 'view_def' ] );
            $row[ 'columns' ] = $this->getViewColumnsAsString( 60 );
            $row[ 'sort' ] = $this->getSortAsString();
            $row[ 'conditions' ] = $this->getConditionsAsString( 60 );
            $component->views[ $row[ 'view_id' ] ] = $row;
        }

        $selectedId = (int)$this->request->getQueryString( 'id' );
        $grid->setSelection( $selectedId );

        return $grid;
    }

    public function prepareToolBar( $component )
    {
        $selectedId = (int)$this->request->getQueryString( 'id' );

        $toolBar = new System_Web_ToolBar();
        $toolBar->setSelection( $selectedId );

        $params = array();
        foreach ( $this->request->getQueryStrings() as $key => $value )
            $params[ $key ] = null;
        $params[ 'type' ] = $this->type[ 'type_id' ];

        if ( $this->isPublic ) {
            $toolBar->addFixedCommand( '/admin/views/add.php', '/common/images/view-new-16.png', $this->tr( 'Add Public View' ) );
            $toolBar->addItemCommand( '/admin/views/modify.php', '/common/images/edit-modify-16.png', $this->tr( 'Modify View' ) );
            $toolBar->addItemCommand( '/admin/views/clone.php', '/common/images/view-clone-16.png', $this->tr( 'Clone View' ) );
            $toolBar->addItemCommand( '/admin/views/rename.php', '/common/images/edit-rename-16.png', $this->tr( 'Rename View' ) );
            $toolBar->addItemCommand( '/admin/views/delete.php', '/common/images/edit-delete-16.png', $this->tr( 'Delete View' ) );
            $toolBar->addItemCommand( '/admin/views/unpublish.php', '/common/images/edit-access-16.png', $this->tr( 'Unpublish View' ) );
            $toolBar->addFixedCommand( '/client/views/index.php', '/common/images/configure-views-16.png', $this->tr( 'Manage Personal Views' ), $params );
        } else {
            $toolBar->addFixedCommand( '/client/views/add.php', '/common/images/view-new-16.png', $this->tr( 'Add Personal View' ) );
            $toolBar->addItemCommand( '/client/views/modify.php', '/common/images/edit-modify-16.png', $this->tr( 'Modify View' ) );
            $toolBar->addItemCommand( '/client/views/clone.php', '/common/images/view-clone-16.png', $this->tr( 'Clone View' ) );
            $toolBar->addItemCommand( '/client/views/rename.php', '/common/images/edit-rename-16.png', $this->tr( 'Rename View' ) );
            $toolBar->addItemCommand( '/client/views/delete.php', '/common/images/edit-delete-16.png', $this->tr( 'Delete View' ) );

            if ( System_Api_Principal::getCurrent()->isAdministrator() ) {
                $toolBar->addItemCommand( '/client/views/publish.php', '/common/images/user-16.png', $this->tr( 'Publish View' ) );
                $toolBar->addFixedCommand( '/admin/views/index.php', '/common/images/configure-views-16.png', $this->tr( 'Public View Settings' ), $params );
            }
        }

        $javaScript = new System_Web_JavaScript( $component->getView() );
        $javaScript->registerSelection( $toolBar );

        return $toolBar;
    }

    private function loadColumns()
    {
        $helper = new System_Web_ColumnHelper();
        $this->columns = $helper->getColumnHeaders();

        unset( $this->columns[ System_Api_Column::Location ] );

        foreach ( $this->attributes as $attribute )
            $this->columns[ System_Api_Column::UserDefined + $attribute[ 'attr_id' ] ] = $attribute[ 'attr_name' ];

        $this->definitions = array(
            System_Api_Column::ID => 'NUMERIC',
            System_Api_Column::Name => 'TEXT',
            System_Api_Column::CreatedBy => 'USER',
            System_Api_Column::CreatedDate => 'DATETIME',
            System_Api_Column::ModifiedBy => 'USER',
            System_Api_Column::ModifiedDate => 'DATETIME'
        );

        foreach ( $this->attributes as $attribute )
            $this->definitions[ System_Api_Column::UserDefined + $attribute[ 'attr_id' ] ] = $attribute[ 'attr_def' ];

        $this->valueDefinitions = array();

        foreach ( $this->definitions as $column => $definition ) {
            $attributeInfo = System_Api_DefinitionInfo::fromString( $definition );
            $valueInfo = new System_Api_DefinitionInfo();

            switch ( $attributeInfo->getType() ) {
                case 'TEXT':
                case 'ENUM':
                case 'USER':
                    $valueInfo->setType( 'ENUM' );
                    $valueInfo->setMetadata( 'editable', 1 );
                    break;

                case 'NUMERIC':
                    $valueInfo->setType( 'NUMERIC' );
                    $valueInfo->setMetadata( 'decimal', $attributeInfo->getMetadata( 'decimal' ) );
                    $valueInfo->setMetadata( 'strip', $attributeInfo->getMetadata( 'strip' ) );
                    break;

                case 'DATETIME':
                    $valueInfo->setType( 'DATETIME' );
                    break;
            }

            $this->valueDefinitions[ $column ] = $valueInfo->toString();
        }
    }

    private function getViewColumnsAsString( $maxLength = null )
    {
        $columns = array_merge( $this->fixedColumns, $this->selectedColumns );
        return $this->formatToString( $columns, ', ', $maxLength );
    }

    private function getSortAsString()
    {
        $order = ( $this->sortOrder == 0 ) ? $this->tr( 'ascending' ) : $this->tr( 'descending' );
        return $this->columns[ $this->sortColumn ] . ' (' . $order . ')';
    }

    private function getConditionsAsString( $maxLength = null )
    {
        $result = array();

        foreach ( $this->conditions as $condition ) {
            $column = $this->columns[ $condition[ 'column' ] ];
            $operator = $this->conditionOperators[ $condition[ 'type' ] ];
            $value = $condition[ 'value' ];
            if ( $value == '' )
                $value = $this->tr( 'empty' );
            else
                $value = '"' . $value . '"';
            $result[] = $column . ' ' . $operator . ' ' . $value;
        }

        return $this->formatToString( $result, ' ' . $this->tr( 'AND' ) . ' ', $maxLength );
    }

    private function formatToString( $array, $separator, $maxLength = null )
    {
        $result = join( $separator, $array );

        if ( $maxLength !== null )
            $result = $this->truncate( $result, $maxLength );

        return $result;
    }
}
