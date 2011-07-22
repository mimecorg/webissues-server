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

class Client_Issues_Issue extends System_Web_Component
{
    private $issue = null;
    private $folder = null;
    private $parentUrl = null;
    private $projectId = null;
    private $values = null;
    private $javaScript = null;

    protected function __construct()
    {
        parent::__construct();
    }

    protected function execute()
    {
        $this->view->setDecoratorClass( 'Common_FixedBlock' );

        switch ( $this->request->getScriptBaseName() ) {
            case 'editissue':
                $issueManager = new System_Api_IssueManager();
                $issueId = (int)$this->request->getQueryString( 'issue' );
                $this->issue = $issueManager->getIssue( $issueId );

                $this->projectId = $this->issue[ 'project_id' ];
                $this->oldIssueName = $this->issue[ 'issue_name' ];

                $this->view->setSlot( 'page_title', $this->tr( 'Edit Attributes' ) );

                $breadcrumbs = new System_Web_Breadcrumbs( $this );
                $breadcrumbs->initialize( System_Web_Breadcrumbs::Issue, $this->issue );
                $this->parentUrl = $breadcrumbs->getParentUrl();
                break;

            case 'addissue':
                $projectManager = new System_Api_ProjectManager();
                $folderId = (int)$this->request->getQueryString( 'folder' );
                $this->folder = $projectManager->getFolder( $folderId );

                $this->projectId = $this->folder[ 'project_id' ];
                $this->folderName = $this->folder[ 'folder_name' ];
                $this->oldIssueName = '';

                $this->view->setSlot( 'page_title', $this->tr( 'Add Issue' ) );

                $breadcrumbs = new System_Web_Breadcrumbs( $this );
                $breadcrumbs->initialize( System_Web_Breadcrumbs::Folder, $this->folder );
                $this->parentUrl = $breadcrumbs->getParentUrl();
                break;

            default:
                throw new System_Core_Exception( 'Invalid URL' );
        }

        $this->form = new System_Web_Form( 'issues', $this );
        $this->form->addField( 'issueName', $this->oldIssueName );
        $this->form->addTextRule( 'issueName', System_Const::ValueMaxLength );

        $this->javaScript = new System_Web_JavaScript( $this->view );

        $this->processValues();

        if ( $this->form->loadForm() ) {
            if ( $this->form->isSubmittedWith( 'cancel' ) )
                $this->response->redirect( $this->parentUrl );

            $this->validateValues();

            if ( $this->form->isSubmittedWith( 'ok' ) && !$this->form->hasErrors() ) {
                $this->submitValues();
                $this->response->redirect( $this->parentUrl );
            }
        }
        $this->displayValues();
    }

    private function processValues()
    {
        $allUsers = null;
        $projectMembers = null;

        $this->attributes = array();
        $this->values = array();
        $this->items = array();
        $this->multiLine = array();

        $typeManager = new System_Api_TypeManager();

        if ( $this->issue != null ) {
            $issueManager = new System_Api_IssueManager();
            $type = $typeManager->getIssueTypeForIssue( $this->issue );
            $rows = $issueManager->getAllAttributeValuesForIssue( $this->issue );
        } else {
            $type = $typeManager->getIssueTypeForFolder( $this->folder );
            $rows = $typeManager->getAttributeTypesForIssueType( $type );
        }

        $viewManager = new System_Api_ViewManager();
        $rows = $viewManager->sortByAttributeOrder( $type, $rows );

        foreach ( $rows as $row ) {
            $attributeId = $row[ 'attr_id' ];
            $this->attributes[ $attributeId ] = $row;

            $info = System_Api_DefinitionInfo::fromString( $row[ 'attr_def' ] );

            if ( $this->issue != null )
                $this->values[ $attributeId ] = $row[ 'attr_value' ];
            else
                $this->values[ $attributeId ] = $typeManager->convertInitialValue( $info, $info->getMetadata( 'default', '' ) );

            $items = null;
            $maxLength = System_Const::ValueMaxLength;

            $selector = $this->form->getFieldSelector( 'value' . $attributeId );

            switch ( $info->getType() ) {
                case 'TEXT':
                    if ( $info->getMetadata( 'multi-line', 0 ) )
                        $this->multiLine[ $attributeId ] = true;
                    $maxLength = $info->getMetadata( 'max-length', $maxLength );
                    break;

                case 'ENUM':
                    if ( $info->getMetadata( 'multi-select', 0 ) ) {
                        $this->javaScript->registerAutocomplete( $selector, $info->getMetadata( 'items' ), System_Web_JavaScript::MultiSelect );
                    } else {
                        if ( $info->getMetadata( 'editable', 0 ) ) {
                            $this->javaScript->registerAutocomplete( $selector, $info->getMetadata( 'items' ) );
                            $maxLength = $info->getMetadata( 'max-length', $maxLength );
                        } else {
                            $items = $info->getMetadata( 'items' );
                        }
                    }
                    break;

                case 'DATETIME':
                    if ( $info->getMetadata( 'time', 0 ) )
                        $this->javaScript->registerDatePicker( $selector, System_Web_JavaScript::WithTime );
                    else
                        $this->javaScript->registerDatePicker( $selector );
                    break;

                case 'USER':
                    if ( $allUsers === null ) {
                        $userManager = new System_Api_UserManager();
                        $users = $userManager->getUsers();
                        $allUsers = array();
                        foreach ( $users as $user ) {
                            if ( $user[ 'user_access' ] != System_Const::NoAccess )
                                $allUsers[ $user[ 'user_id' ] ] = $user[ 'user_name' ];
                        }
                    }
                    if ( $info->getMetadata( 'members', 0 ) ) {
                        if ( $projectMembers === null ) {
                            $userManager = new System_Api_UserManager();
                            $members = $userManager->getMembers( array( 'project_id' => $this->projectId ) );
                            $projectMembers = array();
                            foreach ( $members as $member ) {
                                if ( isset( $allUsers[ $member[ 'user_id' ] ] ) )
                                    $projectMembers[ $member[ 'user_id' ] ] = $allUsers[ $member[ 'user_id' ] ] ;
                            }
                        }
                        $items = $projectMembers;
                    } else {
                        $items = $allUsers;
                    }
                    if ( $info->getMetadata( 'multi-select', 0 ) ) {
                        $this->javaScript->registerAutocomplete( $selector, $items, System_Web_JavaScript::MultiSelect );
                        $items = null;
                    }
                    break;
            }

            if ( $items !== null ) {
                if ( !$info->getMetadata( 'required', 0 ) )
                    $items = array_merge( array( '' => '' ), $items );
                $this->items[ $attributeId ] = $items;
            }

            $this->form->addField( 'value' . $attributeId );

            if ( $items !== null ) {
                $this->form->addItemsRule( 'value' . $attributeId, $items );
            } else {
                $flags = 0;
                if ( !$info->getMetadata( 'required', 0 ) )
                    $flags |= System_Api_Parser::AllowEmpty;
                if ( !empty( $this->multiLine[ $attributeId ] ) )
                    $flags |= System_Api_Parser::MultiLine;
                $this->form->addTextRule( 'value' . $attributeId, $maxLength, $flags );
            }
        }

        $this->form->addViewState( 'oldValues', $this->values );
    }

    private function validateValues()
    {
        $this->form->validate();

        $parser = new System_Api_Parser();
        $parser->setProjectId( $this->projectId );

        $this->values = array();

        foreach ( $this->attributes as $attributeId => $attribute ) {
            $propertyName = 'value' . $attributeId;
            if ( $this->form->hasErrors( $propertyName ) )
                continue;
            if ( isset( $this->items[ $attributeId ] ) )
                $value = $this->items[ $attributeId ][ $this->$propertyName ];
            else
                $value = $this->$propertyName;
            try {
                $this->values[ $attributeId ] = $parser->convertAttributeValue( $attribute[ 'attr_def' ], $value );
            } catch ( System_Api_Error $ex ) {
                $this->form->getErrorHelper()->handleError( $propertyName, $ex );
            }
        }
    }

    private function submitValues()
    {
        $issueManager = new System_Api_IssueManager();

        if ( $this->issue == null ) {
            $issueId = $issueManager->addIssue( $this->folder, $this->issueName, $this->oldValues );
            $this->issue = $issueManager->getIssue( $issueId );
            $this->parentUrl = $this->mergeQueryString( '/client/index.php', array( 'issue' => $issueId, 'folder' => null ) );
        } else {
            if ( $this->issueName !== $this->oldIssueName )
                $issueManager->renameIssue( $this->issue, $this->issueName );
        }

        foreach ( $this->attributes as $attributeId => $attribute ) {
            if ( !System_Api_ValueHelper::areAttributeValuesEqual( $attribute[ 'attr_def' ], $this->values[ $attributeId ], $this->oldValues[ $attributeId ] ) )
                $issueManager->setValue( $this->issue, $attribute, $this->values[ $attributeId ] );
        }
    }

    private function displayValues()
    {
        $formatter = new System_Api_Formatter();

        foreach ( $this->values as $attributeId => $value ) {
            $attribute = $this->attributes[ $attributeId ];
            $propertyName = 'value' . $attributeId;
            if ( isset( $this->items[ $attributeId ] ) ) {
                $this->$propertyName = array_search( $value, $this->items[ $attributeId ] );
            } else {
                $flags = !empty( $this->multiLine[ $attributeId ] ) ? System_Api_Formatter::MultiLine : 0;
                $this->$propertyName = $formatter->convertAttributeValue( $attribute[ 'attr_def' ], $value, $flags );
            }
        }
    }
}
