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

class Common_Views_View extends System_Web_Component
{
    private $parentUrl = null;
    private $columns = null;
    private $attributes = null;
    private $definitions = null;
    private $valueDefinitions = null;
    private $javaScript = null;
    private $allUsers = null;
    private $filters = null;
    private $helper;

    protected function __construct()
    {
        parent::__construct();
    }

    protected function execute()
    {
        $this->view->setDecoratorClass( 'Common_FixedBlock' );

        $this->helper = new Common_Views_Helper();

        $this->isPublic = $this->helper->getIsPublic();
        $this->oldView = null;
        $this->isDefault = false;
        $this->clone = false;

        $baseName = $this->request->getScriptBaseName();

        if ( !$this->isPublic ) {
            if ( $baseName == 'modify' ) {
                $this->oldView = $this->helper->getOldView();
                $this->view->setSlot( 'page_title', $this->tr( 'Modify Personal View' ) );
            } else if ( $baseName == 'clone' ) {
                $this->oldView = $this->helper->getOldView();
                $this->view->setSlot( 'page_title', $this->tr( 'Clone View' ) );
                $this->clone = true;
            } else {
                $this->view->setSlot( 'page_title', $this->tr( 'Add Personal View' ) );
            }

            $breadcrumbs = $this->helper->getBreadcrumbs( $this );
            $this->parentUrl = $breadcrumbs->getAncestorUrl( $this->request->getQueryString( 'direct' ) ? 1 : 0 );
        } else {
            if ( $baseName == 'default' ) {
                $this->isDefault = true;
                $this->view->setSlot( 'page_title', $this->tr( 'Default View' ) );
            } else if ( $baseName == 'modify' ) {
                $this->oldView = $this->helper->getOldView();
                $this->view->setSlot( 'page_title', $this->tr( 'Modify Public View' ) );
            } else if ( $baseName == 'clone' ) {
                $this->oldView = $this->helper->getOldView();
                $this->view->setSlot( 'page_title', $this->tr( 'Clone View' ) );
                $this->clone = true;
            } else {
                $this->view->setSlot( 'page_title', $this->tr( 'Add Public View' ) );
            }

            $breadcrumbs = $this->helper->getBreadcrumbs( $this );
            $this->parentUrl = $breadcrumbs->getParentUrl();
        }

        $this->form = new System_Web_Form( 'view', $this );
        $this->form->addViewState( 'conditions', array() );

        if ( $this->form->loadForm() ) {
            if ( $this->form->isSubmittedWith( 'cancel' ) )
                $this->response->redirect( $this->parentUrl );
        }

        $this->type = $this->helper->getType();

        $this->helper->initializeViewParsing();
        $this->attributes = $this->helper->getAttributes();
        $this->columns = $this->helper->getColumns();
        $this->definitions = $this->helper->getDefinitions();
        $this->valueDefinitions = $this->helper->getValueDefinitions();

        if ( $this->oldView == null && !$this->isDefault || $this->clone ) {
            $this->form->addField( 'viewName', $this->clone ? $this->oldView[ 'view_name' ] : '' );
            $this->form->addTextRule( 'viewName', System_Const::NameMaxLength );
        }

        $this->form->addViewState( 'previousColumns' );

        foreach ( $this->columns as $column => $name ) {
            $this->form->addField( "checkColumn$column" );
            $this->form->addField( "orderColumn$column" );
        }

        $this->form->addField( 'sortColumn', System_Api_Column::ID );
        $this->form->addField( 'sortOrder', 0 );

        if ( !$this->isDefault ) {
            foreach ( $this->conditions as $index => $column ) {
                $this->form->addField( "checkCondition$index" );
                $this->form->addField( "operatorCondition$index" );
                $this->form->addField( "valueCondition$index" );
            }

            foreach ( $this->columns as $column => $name ) {
                $this->form->addField( "checkAvailable$column" );
                $this->form->addField( "operatorAvailable$column" );
                $this->form->addField( "valueAvailable$column" );
            }
        }

        $this->fixedColumns = $this->helper->getFixedColumns();

        $this->selectedColumns = array();

        if ( $this->form->loadForm() ) {
            $this->updateColumns();

            $this->populateSortOptions();

            $this->form->addItemsRule( 'sortColumn', $this->columnOptions );
            $this->form->addItemsRule( 'sortOrder', $this->orderOptions );

            if ( !$this->isDefault )
                $this->updateConditions();

            $this->validate();

            if ( $this->form->isSubmittedWith( 'ok' ) && !$this->form->hasErrors() ) {
                $this->submit();
                if ( !$this->form->hasErrors() )
                    $this->response->redirect( $this->parentUrl );
            }
        } else {
            if ( $this->oldView != null )
                $definition = $this->oldView[ 'view_def' ];
            else {
                $viewManager = new System_Api_ViewManager();
                $definition = $viewManager->getViewSetting( $this->type, 'default_view' );
            }

            if ( $definition != null ) {
                $this->helper->loadView( $definition );
                $this->processDefinition();
            } else {
                $this->selectedColumns[ System_Api_Column::ModifiedBy ] = $this->columns[ System_Api_Column::ModifiedBy ];
                $this->selectedColumns[ System_Api_Column::ModifiedDate ] = $this->columns[ System_Api_Column::ModifiedDate ];
            }

            $this->populateSortOptions();
        }

        $this->populateColumns();

        if ( !$this->isDefault )
            $this->populateConditions();
    }

    private function processDefinition()
    {
        $this->sortColumn = $this->helper->getSortColumn();
        $this->sortOrder = $this->helper->getSortOrder();

        $this->selectedColumns = $this->helper->getSelectedColumns();

        $conditionInfos = $this->helper->getFilterConditions();

        foreach ( $conditionInfos as $index => $conditionInfo ) {
            $this->conditions[ $index ] = $conditionInfo[ 'column' ];

            $this->form->addField( "checkCondition$index", true );
            $this->form->addField( "operatorCondition$index", $conditionInfo[ 'type' ] );
            $this->form->addField( "valueCondition$index", $conditionInfo[ 'value' ] );
        }
    }

    private function updateColumns()
    {
        $weigh = array();
        $order = array();
        $map = array();
        foreach ( $this->columns as $column => $name ) {
            $checkField = "checkColumn$column";
            if ( $this->$checkField && !isset( $this->fixedColumns[ $column ] ) ) {
                $orderField = "orderColumn$column";
                $weigh[] = $this->$orderField;
                $order[] = array_search( $column, $this->previousColumns );
                $map[] = $column;
            }
        }

        array_multisort( $weigh, SORT_ASC, $order, SORT_DESC, $map );

        foreach ( $map as $column )
            $this->selectedColumns[ $column ] = $this->columns[ $column ];
    }

    private function updateConditions()
    {
        $nextIndex = 1;
        foreach ( $this->conditions as $index => $column ) {
            if ( $nextIndex <= $index )
                $nextIndex = $index + 1;
            $checkField = "checkCondition$index";
            if ( !$this->$checkField )
                unset( $this->conditions[ $index ] );
        }

        foreach ( $this->columns as $column => $name ) {
            $checkField = "checkAvailable$column";
            if ( $this->$checkField ) {
                $index = $nextIndex++;
                $this->conditions[ $index ] = $column;

                $this->form->addField( "checkCondition$index", true );

                $operatorField = "operatorAvailable$column";
                $this->form->addField( "operatorCondition$index", $this->$operatorField );

                $valueField = "valueAvailable$column";
                $this->form->addField( "valueCondition$index", $this->$valueField );
            }
        }
    }

    private function populateColumns()
    {
        $this->availableColumns = array();
        foreach ( $this->columns as $column => $name ) {
            if ( !isset( $this->fixedColumns[ $column ] ) && !isset( $this->selectedColumns[ $column ] ) )
                $this->availableColumns[ $column ] = $name;
        }

        $index = 1;

        foreach ( $this->fixedColumns as $column => $name ) {
            $checkField = "checkColumn$column";
            $this->$checkField = true;
            $orderField = "orderColumn$column";
            $this->$orderField = $index++;
        }

        foreach ( $this->selectedColumns as $column => $name ) {
            $checkField = "checkColumn$column";
            $this->$checkField = true;
            $orderField = "orderColumn$column";
            $this->$orderField = $index++;
        }

        foreach ( $this->availableColumns as $column => $name ) {
            $checkField = "checkColumn$column";
            $this->$checkField = false;
            $orderField = "orderColumn$column";
            $this->$orderField = $index++;
        }

        $this->previousColumns = array_merge( array_keys( $this->selectedColumns ), array_keys( $this->availableColumns ) );

        $this->columnOrder = array();
        for ( $i = 1; $i < $index; $i++ )
            $this->columnOrder[ $i ] = $i;
    }

    private function populateSortOptions()
    {
        $this->columnOptions = array();
        foreach ( $this->fixedColumns as $column => $name )
            $this->columnOptions[ $column ] = $name;
        foreach ( $this->selectedColumns as $column => $name )
            $this->columnOptions[ $column ] = $name;

        $this->orderOptions = array( $this->tr( 'Ascending' ), $this->tr( 'Descending' ) );
    }

    private function populateConditions()
    {
        $this->javaScript = new System_Web_JavaScript( $this->view );

        $this->activeConditions = array();
        foreach ( $this->conditions as $index => $column ) {
            $info = System_Api_DefinitionInfo::fromString( $this->definitions[ $column ] );

            $this->activeConditions[ $index ][ 'name' ] = $this->columns[ $column ];
            $this->activeConditions[ $index ][ 'operators' ] = $this->getOperatorsForType( $info->getType() );

            $this->registerJavaScript( $this->form->getFieldSelector( "valueCondition$index" ), $info );
        }

        $this->availableConditions = array();
        foreach ( $this->columns as $column => $name ) {
            $info = System_Api_DefinitionInfo::fromString( $this->definitions[ $column ] );

            $this->availableConditions[ $column ][ 'name' ] = $name;
            $this->availableConditions[ $column ][ 'operators' ] = $this->getOperatorsForType( $info->getType() );

            $checkField = "checkAvailable$column";
            $this->$checkField = false;
            $orderField = "operatorAvailable$column";
            $this->$orderField = 'EQ';
            $valueField = "valueAvailable$column";
            $this->$valueField = '';

            $this->registerJavaScript( $this->form->getFieldSelector( "valueAvailable$column" ), $info );
        }
    }

    private function getOperatorsForType( $type )
    {
        $operators = array(
            'EQ' => $this->tr( 'is equal to' ),
            'NEQ' => $this->tr( 'is not equal to' )
        );

        switch ( $type ) {
            case 'TEXT':
            case 'ENUM':
            case 'USER':
                $operators[ 'BEG' ] = $this->tr( 'begins with' );
                $operators[ 'CON' ] = $this->tr( 'contains' );
                $operators[ 'END' ] = $this->tr( 'ends with' );
                $operators[ 'IN' ] = $this->tr( 'in list' );
                break;

            case 'NUMERIC':
            case 'DATETIME':
                $operators[ 'LT' ] = $this->tr( 'is less than' );
                $operators[ 'LTE' ] = $this->tr( 'is less than or equal to' );
                $operators[ 'GT' ] = $this->tr( 'is greater than' );
                $operators[ 'GTE' ] = $this->tr( 'is greater than or equal to' );
                break;
        }

        return $operators;
    }

    private function registerJavaScript( $selector, $info )
    {
        switch ( $info->getType() ) {
            case 'DATETIME':
                $this->javaScript->registerDatePicker( $selector, System_Web_JavaScript::WithToday );
                break;

            case 'ENUM':
                $this->javaScript->registerAutocomplete( $selector, $info->getMetadata( 'items' ), System_Web_JavaScript::MultiSelect );
                break;

            case 'USER':
                if ( $this->allUsers === null ) {
                    $expressionHelper = new System_Web_ExpressionHelper();
                    $this->allUsers = $expressionHelper->getUserItems();
                }
                $this->javaScript->registerAutocomplete( $selector, $this->allUsers, System_Web_JavaScript::MultiSelect );
                break;
        }
    }

    private function validate()
    {
        if ( $this->form->isSubmittedWith( 'ok' ) )
            $this->form->validate();

        if ( $this->form->isSubmittedWith( 'ok' ) || $this->form->isSubmittedWith( 'updateFilter' ) ) {
            $parser = new System_Api_Parser();
            $formatter = new System_Api_Formatter();
            $expressionHelper = new System_Web_ExpressionHelper();

            $info = new System_Api_DefinitionInfo();

            $this->filters = array();
            foreach ( $this->conditions as $index => $column ) {
                $operatorField = "operatorCondition$index";
                $info->setType( $this->$operatorField );

                $info->setMetadata( 'column', $column );
                $valueField = "valueCondition$index";
                $value = $this->$valueField;

                try {
                    $value = $parser->normalizeString( $value, System_Const::ValueMaxLength, System_Api_Parser::AllowEmpty );

                    $valueInfo = System_Api_DefinitionInfo::fromString( $this->valueDefinitions[ $column ] );
                    if ( $info->getType() == 'IN' )
                        $valueInfo->setMetadata( 'multi-select', 1 );

                    $value = $expressionHelper->parseExpression( $valueInfo->getType(), $this->valueDefinitions[ $column ], $value );
                    $this->$valueField = $expressionHelper->formatExpression( $valueInfo->getType(), $this->valueDefinitions[ $column ], $value );

                    $info->setMetadata( 'value', $value );

                    $filter = $info->toString();
                    $parser->checkFilterDefinition( $this->attributes, $filter );
                } catch ( System_Api_Error $ex ) {
                    $this->form->getErrorHelper()->handleError( $valueField, $ex );
                    continue;
                }

                $this->filters[] = $filter;
            }
        }
    }

    private function submit()
    {
        $info = new System_Api_DefinitionInfo();
        $info->setType( 'VIEW' );

        $columns = array_merge( array_keys( $this->fixedColumns ), array_keys( $this->selectedColumns ) );
        $info->setMetadata( 'columns', implode( ',', $columns ) );

        $info->setMetadata( 'sort-column', (int)$this->sortColumn );
        if ( $this->sortOrder == 1 )
            $info->setMetadata( 'sort-desc', 1 );

        if ( !empty( $this->filters ) )
            $info->setMetadata( 'filters', $this->filters );

        $viewManager = new System_Api_ViewManager();

        try {
            if ( $this->isDefault ) {
                $viewManager->setViewSetting( $this->type, 'default_view', $info->toString() );
            } else if ( $this->oldView != null && !$this->clone ) {
                $viewManager->modifyView( $this->oldView, $info->toString() );
            } else if ( $this->isPublic ) {
                $viewManager->addPublicView( $this->type, $this->viewName, $info->toString() );
            } else {
                $viewId = $viewManager->addPersonalView( $this->type, $this->viewName, $info->toString() );
                if ( $this->request->getQueryString( 'direct' ) )
                    $this->parentUrl = $this->filterQueryString( '/client/index.php', array( 'ps', 'po', 'ppg', 'folder', 'type' ), array( 'view' => $viewId ) );
            }
        } catch ( System_Api_Error $ex ) {
            $this->form->getErrorHelper()->handleError( 'viewName', $ex );
        }
    }
}
