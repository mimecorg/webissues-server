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

class Admin_Types_Attribute extends System_Web_Component
{
    private $parser = null;
    private $formatter = null;
    private $attribute = null;
    private $issueType = null;
    private $definition = null;
    private $rules = null;

    protected function __construct()
    {
        parent::__construct();

        $this->parser = new System_Api_Parser();
        $this->formatter = new System_Api_Formatter();
    }

    protected function execute()
    {
        $this->view->setDecoratorClass( 'Common_FixedBlock' );
        $this->view->setSlot( 'breadcrumbs', array( $this->mergeQueryString( '/admin/types/index.php', array( 'type' => null, 'attribute' => null ) ) => $this->tr( 'Issue Types' ) ) );

        $breadcrumbs = new System_Web_Breadcrumbs( $this );
        $breadcrumbs->initialize( System_Web_Breadcrumbs::IssueTypes );

        $helper = new Admin_Types_Helper();

        $typeManager = new System_Api_TypeManager();
        switch ( $this->request->getScriptBaseName() ) {
            case 'modifyattribute':
                $attributeId = (int)$this->request->getQueryString( 'attribute' );
                $this->attribute = $typeManager->getAttributeType( $attributeId );
                $this->oldAttributeName = $this->attribute[ 'attr_name' ];

                $info = System_Api_DefinitionInfo::fromString( $this->attribute[ 'attr_def' ] );
                $initialType = $info->getType();

                $this->typeOptions = $helper->getCompatibleTypes( $initialType );
                $this->canChangeType = count( $this->typeOptions ) > 1;

                $initialPage = 'details';
                $this->view->setSlot( 'page_title', $this->tr( 'Modify Attribute' ) );
                break;

            case 'addattribute':
                $typeId = (int)$this->request->getQueryString( 'type' );
                $this->issueType = $typeManager->getIssueType( $typeId );
                $this->typeName = $this->issueType[ 'type_name' ];
                $this->oldAttributeName = null;

                $initialType = null;
                $this->typeOptions = $helper->getAllTypes();
                $this->canChangeType = true;

                $initialPage = 'type';
                $this->view->setSlot( 'page_title', $this->tr( 'Add Attribute' ) );
                break;

            default:
                throw new System_Core_Exception( 'Invalid URL' );
        }

        $this->form = new System_Web_Form( 'types', $this );
        $this->form->addViewState( 'page', $initialPage );
        $this->form->addViewState( 'type', $initialType );
        $this->form->addField( 'attributeType' );
        $this->form->addPersistentField( 'attributeName', $this->oldAttributeName );
        $this->form->addPersistentField( 'multiLine', 0 );
        $this->form->addPersistentField( 'minimumLength' );
        $this->form->addPersistentField( 'maximumLength' );
        $this->form->addPersistentField( 'editable', 0 );
        $this->form->addPersistentField( 'items', '' );
        $this->form->addPersistentField( 'decimalPlaces', 0 );
        $this->form->addPersistentField( 'minimumValue' );
        $this->form->addPersistentField( 'maximumValue' );
        $this->form->addPersistentField( 'stripZeros' );
        $this->form->addPersistentField( 'time', 0 );
        $this->form->addPersistentField( 'members', 0 );
        $this->form->addPersistentField( 'required' );
        $this->form->addPersistentField( 'defaultValue' );
        $this->form->addViewState( 'changeMode' );

        if ( $this->form->loadForm() ) {
            switch ( $this->page ) {
                case 'type':
                    if ( $this->form->isSubmittedWith( 'cancel' ) ) {
                        if ( $this->changeMode ) {
                            $this->page = 'details';
                            break;
                        } else {
                            $this->response->redirect( $breadcrumbs->getParentUrl() );
                        }
                    }

                    $this->initializeRules();
                    $this->form->validate();

                    if ( $this->form->isSubmittedWith( 'next' ) && !$this->form->hasErrors() ) {
                        $this->type = $this->attributeType;
                        $this->page = 'details';
                    }
                    break;

                case 'details':
                    if ( $this->form->isSubmittedWith( 'cancel' ) )
                        $this->response->redirect( $breadcrumbs->getParentUrl() );

                    if ( $this->form->isSubmittedWith( 'changeType' ) && $this->canChangeType ) {
                        $this->changeMode = true;
                        $this->attributeType = $this->type;
                        $this->page = 'type';
                        break;
                    }

                    $this->initializeRules();
                    $this->validateValues();

                    if ( $this->form->isSubmittedWith( 'ok' ) && !$this->form->hasErrors() ) {
                        if ( $this->submitValues() ) {
                            if ( $this->attribute == null ) {
                                $grid = new System_Web_Grid();
                                $grid->addExpandCookieId( 'wi_types', $typeId );
                            }
                            $this->response->redirect( $breadcrumbs->getParentUrl() );
                        }
                    }
                    break;
            }
        } else {
            if ( $this->attribute != null )
                $this->loadValues( $this->attribute[ 'attr_def' ] );
        }

        $this->initializeRules();

        $this->registerJavaScript();
    }

    private function initializeRules()
    {
        if ( $this->rules == $this->page )
            return;

        $this->rules = $this->page;

        $this->form->clearRules();

        switch ( $this->page ) {
            case 'type':
                $this->form->addItemsRule( 'attributeType', $this->typeOptions );
                break;

            case 'details':
                if ( $this->attribute == null )
                    $this->form->addTextRule( 'attributeName', System_Const::NameMaxLength );
                switch ( $this->type ) {
                    case 'TEXT':
                        $this->multiLineOptions = array(
                            0 => $this->tr( 'Single line of text' ),
                            1 => $this->tr( 'Multiple lines of text' ) );
                        $this->form->addItemsRule( 'multiLine', $this->multiLineOptions );
                        $this->form->addTextRule( 'minimumLength', System_Const::ValueMaxLength, System_Api_Parser::AllowEmpty );
                        $this->form->addTextRule( 'maximumLength', System_Const::ValueMaxLength, System_Api_Parser::AllowEmpty );
                        break;

                    case 'ENUM':
                        $this->editableOptions = array(
                            0 => $this->tr( 'Allow only values from the list' ),
                            1 => $this->tr( 'Allow entering custom values' ) );
                        $this->form->addItemsRule( 'editable', $this->editableOptions );
                        $this->form->addTextRule( 'items', null, System_Api_Parser::MultiLine );
                        $this->form->addTextRule( 'minimumLength', System_Const::ValueMaxLength, System_Api_Parser::AllowEmpty );
                        $this->form->addTextRule( 'maximumLength', System_Const::ValueMaxLength, System_Api_Parser::AllowEmpty );
                        break;

                    case 'NUMERIC':
                        $this->form->addTextRule( 'decimalPlaces', System_Const::ValueMaxLength );
                        $this->form->addTextRule( 'minimumValue', System_Const::ValueMaxLength, System_Api_Parser::AllowEmpty );
                        $this->form->addTextRule( 'maximumValue', System_Const::ValueMaxLength, System_Api_Parser::AllowEmpty );
                        break;

                    case 'DATETIME':
                        $this->timeOptions = array(
                            0 => $this->tr( 'Date only' ),
                            1 => $this->tr( 'Date and time without time zone conversion' ),
                            2 => $this->tr( 'Date and time using local time zone' ) );
                        $this->form->addItemsRule( 'time', $this->timeOptions );
                        break;

                    case 'USER':
                        $this->membersOptions = array(
                            0 => $this->tr( 'All users' ),
                            1 => $this->tr( 'Only project members' ) );
                        $this->form->addItemsRule( 'members', $this->membersOptions );
                        break;
                }
                break;
        }
    }

    private function registerJavaScript()
    {
        switch ( $this->type ) {
            case 'DATETIME':
                $javaScript = new System_Web_JavaScript( $this->view );
                $flags = System_Web_JavaScript::WithToday;
                if ( $this->time != 0 )
                    $flags |= System_Web_JavaScript::WithTime;
                $javaScript->registerDatePicker( $this->form->getFieldSelector( 'defaultValue' ), $flags );
                $javaScript->registerDynamicDatePicker( $this->form->getFieldSelector( 'defaultValue' ),
                    $this->form->getRadioSelector( 'time', 0 ), $this->form->getRadioSelector( 'time', 1 ) . ', ' . $this->form->getRadioSelector( 'time', 2 ) );
                break;

            case 'USER':
                $javaScript = new System_Web_JavaScript( $this->view );
                $expressionHelper = new System_Web_ExpressionHelper();
                $javaScript->registerAutocomplete( $this->form->getFieldSelector( 'defaultValue' ), $expressionHelper->getUserItems() );
                break;
        }
    }

    private function loadValues( $definition )
    {
        $expressionHelper = new System_Web_ExpressionHelper();

        $info = System_Api_DefinitionInfo::fromString( $definition );

        switch ( $this->type ) {
            case 'TEXT':
                $this->multiLine = $info->getMetadata( 'multi-line', 0 );
                $this->minimumLength = $this->formatInteger( $info->getMetadata( 'min-length' ) );
                $this->maximumLength = $this->formatInteger( $info->getMetadata( 'max-length' ) );
                break;

            case 'ENUM':
                $this->editable = $info->getMetadata( 'editable', 0 );
                $this->items = join( "\n", $info->getMetadata( 'items' ) );
                $this->minimumLength = $this->formatInteger( $info->getMetadata( 'min-length' ) );
                $this->maximumLength = $this->formatInteger( $info->getMetadata( 'max-length' ) );
                break;

            case 'NUMERIC':
                $this->decimalPlaces = $this->formatInteger( $info->getMetadata( 'decimal', 0 ) );
                $this->stripZeros = $info->getMetadata( 'strip', 0 );
                $this->minimumValue = $this->formatDecimalNumber( $info->getMetadata( 'min-value' ) );
                $this->maximumValue = $this->formatDecimalNumber( $info->getMetadata( 'max-value' ) );
                break;

            case 'DATETIME':
                $this->time = $info->getMetadata( 'time', 0 ) ? ( $info->getMetadata( 'local', 0 ) ? 2 : 1 ) : 0;
                break;

            case 'USER':
                $this->members = $info->getMetadata( 'members', 0 );
                break;
        }

        $this->required = $info->getMetadata( 'required', 0 );

        $this->defaultValue = $expressionHelper->formatExpression( $this->type, $definition, $info->getMetadata( 'default' ) );
    }

    private function formatInteger( $value )
    {
        if ( $value !== null )
            return $this->formatter->convertDecimalNumber( $value, 0 );
        return null;
    }

    private function formatDecimalNumber( $value )
    {
        if ( $value !== null )
            return $this->formatter->convertDecimalNumber( $value, $this->decimalPlaces, $this->stripZeros ? System_Api_Formatter::StripZeros : 0 );
        return null;
    }

    private function validateValues()
    {
        $this->form->validate();

        $info = new System_Api_DefinitionInfo();
        $info->setType( $this->type );

        switch ( $this->type ) {
            case 'TEXT':
                if ( $this->multiLine )
                    $info->setMetadata( 'multi-line', 1 );
                $info->setMetadata( 'min-length', $this->validateInteger( 'minimumLength', 1, System_Const::ValueMaxLength, System_Api_Parser::AllowEmpty ) );
                $info->setMetadata( 'max-length', $this->validateInteger( 'maximumLength', 1, System_Const::ValueMaxLength, System_Api_Parser::AllowEmpty ) );
                break;

            case 'ENUM':
                if ( $this->editable )
                    $info->setMetadata( 'editable', 1 );
                $info->setMetadata( 'items', $this->validateItems( 'items' ) );
                if ( $this->editable ) {
                    $info->setMetadata( 'min-length', $this->validateInteger( 'minimumLength', 1, System_Const::ValueMaxLength, System_Api_Parser::AllowEmpty ) );
                    $info->setMetadata( 'max-length', $this->validateInteger( 'maximumLength', 1, System_Const::ValueMaxLength, System_Api_Parser::AllowEmpty ) );
                } else {
                    $this->validateEmpty( 'minimumLength' );
                    $this->validateEmpty( 'maximumLength' );
                }
                break;

            case 'NUMERIC':
                $info->setMetadata( 'decimal', $this->validateInteger( 'decimalPlaces', 0, 6 ) );
                if ( $this->stripZeros )
                    $info->setMetadata( 'strip', 1 );
                $info->setMetadata( 'min-value', $this->validateDecimalNumber( 'minimumValue', System_Api_Parser::AllowEmpty ) );
                $info->setMetadata( 'max-value', $this->validateDecimalNumber( 'maximumValue', System_Api_Parser::AllowEmpty ) );
                break;

            case 'DATETIME':
                if ( $this->time > 0 ) {
                    $info->setMetadata( 'time', 1 );
                    if ( $this->time == 2 )
                        $info->setMetadata( 'local', 1 );
                }
                break;

            case 'USER':
                if ( $this->members )
                    $info->setMetadata( 'members', 1 );
                break;
        }

        if ( !$this->form->hasErrors() ) {
            $this->definition = $info->toString();

            try {
                $this->parser->checkAttributeDefinition( $this->definition );
            } catch ( System_Api_Error $ex ) {
                $this->form->getErrorHelper()->handleError( 'definitionError', $ex );
            }
        }

        if ( $this->required )
            $info->setMetadata( 'required', 1 );

        $info->setMetadata( 'default', $this->validateValue( 'defaultValue' ) );

        if ( !$this->form->hasErrors() )
            $this->definition = $info->toString();
    }

    private function validateEmpty( $propertyName )
    {
        if ( $this->$propertyName !== '' )
            $this->form->setError( $propertyName, $this->tr( 'Invalid value' ) );
    }

    private function validateInteger( $propertyName, $min, $max, $flags = 0 )
    {
        try {
            if ( $this->$propertyName !== '' ) {
                $value = (int)$this->parser->convertDecimalNumber( $this->$propertyName, 0 );
                $this->$propertyName = $this->formatter->convertDecimalNumber( $value, 0 );
                $this->parser->checkIntegerValue( $value, $min, $max );
                return $value;
            }
        } catch ( System_Api_Error $ex ) {
            $this->form->getErrorHelper()->handleError( $propertyName, $ex );
        }
        return null;
    }

    private function validateDecimalNumber( $propertyName, $flags = 0 )
    {
        try {
            if ( $this->$propertyName !== '' && !$this->form->hasErrors( 'decimalPlaces' ) ) {
                $value = $this->parser->convertDecimalNumber( $this->$propertyName, $this->decimalPlaces );
                $this->$propertyName = $this->formatter->convertDecimalNumber( $value, $this->decimalPlaces,
                    $this->stripZeros ? System_Api_Formatter::StripZeros : 0 );
                return $value;
            }
        } catch ( System_Api_Error $ex ) {
            $this->form->getErrorHelper()->handleError( $propertyName, $ex );
        }
        return null;
    }

    private function validateValue( $propertyName )
    {
        try {
            $this->$propertyName = $this->parser->normalizeString( $this->$propertyName, System_Const::ValueMaxLength, System_Api_Parser::AllowEmpty );
            if ( $this->$propertyName !== '' && !$this->form->hasErrors() ) {
                $expressionHelper = new System_Web_ExpressionHelper();
                $value = $expressionHelper->parseExpression( $this->type, $this->definition, $this->$propertyName );
                $this->$propertyName = $expressionHelper->formatExpression( $this->type, $this->definition, $value );
                return $value;
            }
        } catch ( System_Api_Error $ex ) {
            $this->form->getErrorHelper()->handleError( $propertyName, $ex );
        }
        return null;
    }

    private function validateItems( $propertyName )
    {
        try {
            $items = explode( "\n", $this->$propertyName );
            $value = array();
            foreach ( $items as $item ) {
                $item = $this->parser->normalizeString( $item, System_Const::ValueMaxLength, System_Api_Parser::AllowEmpty );
                if ( $item !== '' )
                    $value[] = $item;
            }
            $this->$propertyName = join( "\n", $value );
            return $value;
        } catch ( System_Api_Error $ex ) {
            $this->form->getErrorHelper()->handleError( $propertyName, $ex );
        }
        return null;
    }

    private function submitValues()
    {
        $typeManager = new System_Api_TypeManager();

        if ( $this->attribute == null ) {
            try {
                $typeManager->addAttributeType( $this->issueType, $this->attributeName, $this->definition );
            } catch ( System_Api_Error $ex ) {
                $this->form->getErrorHelper()->handleError( 'attributeName', $ex );
                return false;
            }
        } else {
            $typeManager->modifyAttributeType( $this->attribute, $this->definition );
        }

        return true;
    }
}
