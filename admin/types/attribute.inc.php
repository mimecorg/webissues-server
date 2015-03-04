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

class Admin_Types_Attribute extends System_Web_Component
{
    private $parser = null;
    private $formatter = null;
    private $attribute = null;
    private $issueType = null;
    private $details = array();
    private $definition = null;
    private $autoComplete = array();
    private $flags = 0;

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

        $breadcrumbs = new Common_Breadcrumbs( $this );
        $breadcrumbs->initialize( Common_Breadcrumbs::IssueTypes );

        $helper = new Admin_Types_Helper();

        $typeManager = new System_Api_TypeManager();
        switch ( $this->request->getScriptBaseName() ) {
            case 'modifyattribute':
                $attributeId = (int)$this->request->getQueryString( 'attribute' );
                $this->attribute = $typeManager->getAttributeType( $attributeId );
                $this->oldAttributeName = $this->attribute[ 'attr_name' ];

                $info = System_Api_DefinitionInfo::fromString( $this->attribute[ 'attr_def' ] );
                $this->typeOptions = $helper->getCompatibleTypes( $info->getType() );

                $this->view->setSlot( 'page_title', $this->tr( 'Modify Attribute' ) );
                break;

            case 'addattribute':
                $typeId = (int)$this->request->getQueryString( 'type' );
                $this->issueType = $typeManager->getIssueType( $typeId );
                $this->typeName = $this->issueType[ 'type_name' ];
                $this->oldAttributeName = null;

                $info = null;
                $this->typeOptions = $helper->getAllTypes();

                $this->view->setSlot( 'page_title', $this->tr( 'Add Attribute' ) );
                break;

            default:
                throw new System_Core_Exception( 'Invalid URL' );
        }

        $this->form = new System_Web_Form( 'types', $this );
        $this->form->addViewState( 'page', 'basic' );
        $this->form->addPersistentField( 'attributeType' );
        $this->form->addPersistentField( 'attributeName', $this->oldAttributeName );
        $this->form->addPersistentField( 'required' );
        $this->form->addPersistentField( 'defaultValue' );
        $this->form->addField( 'multiLine', 0 );
        $this->form->addField( 'minimumLength' );
        $this->form->addField( 'maximumLength' );
        $this->form->addField( 'editable', 0 );
        $this->form->addField( 'multiSelect', 0 );
        $this->form->addField( 'items', '' );
        $this->form->addField( 'decimalPlaces', 0 );
        $this->form->addField( 'minimumValue' );
        $this->form->addField( 'maximumValue' );
        $this->form->addField( 'stripZeros' );
        $this->form->addField( 'time', 0 );
        $this->form->addField( 'members', 0 );
        $this->form->addViewState( 'metadata' );
        $this->form->addViewState( 'type' );

        if ( $this->form->loadForm() ) {
            switch ( $this->page ) {
                case 'basic':
                    if ( $this->form->isSubmittedWith( 'cancel' ) )
                        $this->response->redirect( $breadcrumbs->getParentUrl() );

                    if ( $this->attributeType != $this->type )
                        $this->switchType();

                    if ( $this->form->isSubmittedWith( 'details' ) ) {
                        $this->page = 'details';
                        $this->initializeDetailsRules();
                        $this->loadDetails();
                    } else {
                        $this->initializeBasicRules();

                        if ( $this->form->isSubmittedWith( 'ok' ) ) {
                            $this->validateValues();

                            if ( !$this->form->hasErrors() && $this->submitValues() ) {
                                if ( $this->attribute == null ) {
                                    $grid = new System_Web_Grid();
                                    $grid->addExpandCookieId( 'wi_types', $typeId );
                                }
                                $this->response->redirect( $breadcrumbs->getParentUrl() );
                            }
                        }
                    }
                    break;

                case 'details':
                    if ( $this->form->isSubmittedWith( 'cancel' ) ) {
                        $this->page = 'basic';
                        $this->initializeBasicRules();
                    } else {
                        $this->initializeDetailsRules();

                        if ( $this->form->isSubmittedWith( 'ok' ) ) {
                            $this->validateDetails();
                            
                            if ( !$this->form->hasErrors() ) {
                                $this->saveDetails();
                                $this->page = 'basic';
                                $this->initializeBasicRules();
                            }
                        }
                    }
                    break;
            }
        } else {
            if ( $this->attribute != null ) {
                $this->type = $info->getType();
                $this->metadata = $info->getAllMetadata();

                $this->required = $info->getMetadata( 'required', 0 );

                $flags = 0;
                if ( $info->getMetadata( 'multi-line', 0 ) )
                    $flags |= System_Api_Formatter::MultiLine;

                $expressionHelper = new System_Web_ExpressionHelper();
                $this->defaultValue = $expressionHelper->formatExpression( $this->type, $this->attribute[ 'attr_def' ], $info->getMetadata( 'default' ), $flags );
            } else {
                $this->type = 'TEXT';
                $this->metadata = array();
            }

            $this->attributeType = $this->type;

            $this->initializeBasicRules();
        }

        if ( $this->page == 'basic' ) {
            $this->details = array();
            $info = $this->createDefinition();

            $this->attributeDetails = $helper->getAttributeDetails( $info );

            $javaScript = new System_Web_JavaScript( $this->view );

            $javaScript->registerAutoSubmit( $this->form->getFormSelector(), $this->form->getFieldSelector( 'attributeType' ),
                $this->form->getSubmitSelector( 'go' ) );

            if ( !empty( $this->autoComplete ) )
                $javaScript->registerAutocomplete( $this->form->getFieldSelector( 'defaultValue' ), $this->autoComplete, $this->jsFlags );
            else if ( $this->type == 'DATETIME' )
                $javaScript->registerDatePicker( $this->form->getFieldSelector( 'defaultValue' ), $this->jsFlags );
        }
    }

    private function initializeBasicRules()
    {
        $this->form->clearRules();

        if ( $this->attribute == null )
            $this->form->addTextRule( 'attributeName', System_Const::NameMaxLength );

        $this->form->addItemsRule( 'attributeType', $this->typeOptions );

        $maxLength = System_Const::ValueMaxLength;

        switch ( $this->type ) {
            case 'TEXT':
                if ( $this->getMetadata( 'multi-line', 0 ) )
                    $this->multiLine = true;
                $maxLength = $this->getMetadata( 'max-length', $maxLength );
                break;

            case 'ENUM':
                $this->autoComplete = $this->getMetadata( 'items' );
                if ( $this->getMetadata( 'multi-select', 0 ) ) {
                    $this->jsFlags |= System_Web_JavaScript::MultiSelect;
                } else {
                    if ( $this->getMetadata( 'editable', 0 ) )
                        $maxLength = $this->getMetadata( 'max-length', $maxLength );
                }
                break;

            case 'DATETIME':
                $this->jsFlags = System_Web_JavaScript::WithToday;
                if ( $this->getMetadata( 'time', 0 ) )
                    $this->jsFlags |= System_Web_JavaScript::WithTime;
                break;

            case 'USER':
                $expressionHelper = new System_Web_ExpressionHelper();
                $this->autoComplete = $expressionHelper->getUserItems();
                if ( $this->getMetadata( 'multi-select', 0 ) )
                    $this->jsFlags |= System_Web_JavaScript::MultiSelect;
                break;
        }

        $flags = System_Api_Parser::AllowEmpty;
        if ( !empty( $this->multiLine ) )
            $flags |= System_Api_Parser::MultiLine;
        $this->form->addTextRule( 'defaultValue', $maxLength, $flags );
    }

    private function validateValues()
    {
        $this->form->validate();

        $info = $this->createDefinition();
        $this->definition = $info->toString();

        try {
            $this->parser->checkAttributeDefinition( $this->definition );
        } catch ( System_Api_Error $ex ) {
            $this->form->getErrorHelper()->handleError( 'definitionError', $ex );
        }

        try {
            $flags = System_Api_Parser::AllowEmpty;
            if ( !empty( $this->multiLine ) )
                $flags |= System_Api_Parser::MultiLine;

            $this->defaultValue = $this->parser->normalizeString( $this->defaultValue, System_Const::ValueMaxLength, $flags );

            if ( $this->defaultValue !== '' && !$this->form->hasErrors() ) {
                $expressionHelper = new System_Web_ExpressionHelper();
                $value = $expressionHelper->parseExpression( $this->type, $this->definition, $this->defaultValue );

                $flags = 0;
                if ( $info->getMetadata( 'multi-line', 0 ) )
                    $flags |= System_Api_Formatter::MultiLine;

                $this->defaultValue = $expressionHelper->formatExpression( $this->type, $this->definition, $value, $flags );

                $info->setMetadata( 'default', $value );
                $this->definition = $info->toString();
            }
        } catch ( System_Api_Error $ex ) {
            $this->form->getErrorHelper()->handleError( 'defaultValue', $ex );
        }
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

    private function initializeDetailsRules()
    {
        $this->form->clearRules();

        switch ( $this->type ) {
            case 'TEXT':
                $this->form->addTextRule( 'minimumLength', System_Const::ValueMaxLength, System_Api_Parser::AllowEmpty );
                $this->form->addTextRule( 'maximumLength', System_Const::ValueMaxLength, System_Api_Parser::AllowEmpty );
                break;

            case 'ENUM':
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
                break;
        }
    }

    private function loadDetails()
    {
        switch ( $this->type ) {
            case 'TEXT':
                $this->multiLine = $this->getMetadata( 'multi-line', 0 );
                $this->minimumLength = $this->formatInteger( $this->getMetadata( 'min-length' ) );
                $this->maximumLength = $this->formatInteger( $this->getMetadata( 'max-length' ) );
                break;

            case 'ENUM':
                $this->editable = $this->getMetadata( 'editable', 0 );
                $this->multiSelect = $this->getMetadata( 'multi-select', 0 );
                $this->items = join( "\n", $this->getMetadata( 'items', array() ) );
                $this->minimumLength = $this->formatInteger( $this->getMetadata( 'min-length' ) );
                $this->maximumLength = $this->formatInteger( $this->getMetadata( 'max-length' ) );
                break;

            case 'NUMERIC':
                $this->decimalPlaces = $this->formatInteger( $this->getMetadata( 'decimal', 0 ) );
                $this->stripZeros = $this->getMetadata( 'strip', 0 );
                $this->minimumValue = $this->formatDecimalNumber( $this->getMetadata( 'min-value' ) );
                $this->maximumValue = $this->formatDecimalNumber( $this->getMetadata( 'max-value' ) );
                break;

            case 'DATETIME':
                $this->time = $this->getMetadata( 'time', 0 ) ? ( $this->getMetadata( 'local', 0 ) ? 2 : 1 ) : 0;
                break;

            case 'USER':
                $this->members = $this->getMetadata( 'members', 0 );
                $this->multiSelect = $this->getMetadata( 'multi-select', 0 );
                break;
        }
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

    private function validateDetails()
    {
        $this->form->validate();

        $this->details = array();

        switch ( $this->type ) {
            case 'TEXT':
                $this->details[ 'multi-line' ] = $this->multiLine ? 1 : 0;
                $this->details[ 'min-length' ] = $this->validateInteger( 'minimumLength', 1, System_Const::ValueMaxLength, System_Api_Parser::AllowEmpty );
                $this->details[ 'max-length' ] = $this->validateInteger( 'maximumLength', 1, System_Const::ValueMaxLength, System_Api_Parser::AllowEmpty );
                break;

            case 'ENUM':
                $this->details[ 'editable' ] = $this->editable ? 1 : 0;
                $this->details[ 'multi-select' ] = $this->multiSelect ? 1 : 0;
                $this->details[ 'items' ] = $this->validateItems( 'items' );
                $this->details[ 'min-length' ] = $this->validateInteger( 'minimumLength', 1, System_Const::ValueMaxLength, System_Api_Parser::AllowEmpty );
                $this->details[ 'max-length' ] = $this->validateInteger( 'maximumLength', 1, System_Const::ValueMaxLength, System_Api_Parser::AllowEmpty );
                if ( !$this->editable || $this->multiSelect ) {
                    $this->validateEmpty( 'minimumLength' );
                    $this->validateEmpty( 'maximumLength' );
                }
                break;

            case 'NUMERIC':
                $this->details[ 'decimal' ] = $this->validateInteger( 'decimalPlaces', 0, 6 );
                $this->details[ 'strip' ] = $this->stripZeros ? 1 : 0;
                $this->details[ 'min-value' ] = $this->validateDecimalNumber( 'minimumValue', System_Api_Parser::AllowEmpty );
                $this->details[ 'max-value' ] = $this->validateDecimalNumber( 'maximumValue', System_Api_Parser::AllowEmpty );
                break;

            case 'DATETIME':
                $this->details[ 'time' ] = ( $this->time > 0 ) ? 1 : 0;
                $this->details[ 'local' ] = ( $this->time == 2 );
                break;

            case 'USER':
                $this->details[ 'members' ] = $this->members ? 1 : 0;
                $this->details[ 'multi-select' ] = $this->multiSelect ? 1 : 0;
                break;
        }

        if ( !$this->form->hasErrors() ) {
            $info = $this->createDefinition();
            $this->definition = $info->toString();

            try {
                $this->parser->checkAttributeDefinition( $this->definition );
            } catch ( System_Api_Error $ex ) {
                $this->form->getErrorHelper()->handleError( 'definitionError', $ex );
            }
        }
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

    private function saveDetails()
    {
        $this->metadata = array_merge( $this->metadata, $this->details );
        $this->details = array();

        $this->normalizeDefaultValue();
    }

    private function switchType()
    {
        $helper = new Admin_Types_Helper();
        $compatible = $helper->getCompatibleTypes( $this->type );

        $this->type = $this->attributeType;

        if ( isset( $compatible[ $this->attributeType ] ) )
            $this->normalizeDefaultValue();
        else
            $this->defaultValue = '';
    }

    private function normalizeDefaultValue()
    {
        $flags = 0;
        if ( $this->type == 'TEXT' && $this->getMetadata( 'multi-line', 0 ) )
            $flags |= System_Api_Parser::MultiLine;

        try {
            $this->defaultValue = $this->parser->normalizeString( $this->defaultValue, null, $flags );
        } catch ( System_Api_Error $ex ) {
            $this->defaultValue = '';
        }
    }

    private function createDefinition()
    {
        $info = new System_Api_DefinitionInfo();
        $info->setType( $this->type );

        switch ( $this->type ) {
            case 'TEXT':
                if ( $this->getMetadata( 'multi-line', 0 ) == 1 )
                    $info->setMetadata( 'multi-line', 1 );
                $info->setMetadata( 'min-length', $this->getMetadata( 'min-length' ) );
                $info->setMetadata( 'max-length', $this->getMetadata( 'max-length' ) );
                break;

            case 'ENUM':
                $info->setMetadata( 'items', $this->getMetadata( 'items' ) );
                if ( $this->getMetadata( 'editable', 0 ) == 1 )
                    $info->setMetadata( 'editable', 1 );
                if ( $this->getMetadata( 'multi-select', 0 ) == 1 )
                    $info->setMetadata( 'multi-select', 1 );
                $info->setMetadata( 'min-length', $this->getMetadata( 'min-length' ) );
                $info->setMetadata( 'max-length', $this->getMetadata( 'max-length' ) );
                break;

            case 'NUMERIC':
                $info->setMetadata( 'decimal', $this->getMetadata( 'decimal' ) );
                $info->setMetadata( 'min-value', $this->getMetadata( 'min-value' ) );
                $info->setMetadata( 'max-value', $this->getMetadata( 'max-value' ) );
                if ( $this->getMetadata( 'strip', 0 ) == 1 )
                    $info->setMetadata( 'strip', 1 );
                break;

            case 'DATETIME':
                if ( $this->getMetadata( 'time', 0 ) == 1 )
                    $info->setMetadata( 'time', 1 );
                if ( $this->getMetadata( 'local', 0 ) == 1 )
                    $info->setMetadata( 'local', 1 );
                break;

            case 'USER':
                if ( $this->getMetadata( 'members', 0 ) == 1 )
                    $info->setMetadata( 'members', 1 );
                if ( $this->getMetadata( 'multi-select', 0 ) == 1 )
                    $info->setMetadata( 'multi-select', 1 );
                break;
        }

        if ( $this->required )
            $info->setMetadata( 'required', 1 );

        return $info;
    }

    private function getMetadata( $key, $default = null )
    {
        if ( array_key_exists( $key, $this->details ) )
            return $this->details[ $key ];
        return isset( $this->metadata[ $key ] ) ? $this->metadata[ $key ] : $default;
    }
}
