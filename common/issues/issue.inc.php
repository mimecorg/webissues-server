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

class Common_Issues_Issue extends System_Web_Component
{
    private $issue = null;
    private $folder = null;
    private $type = null;
    private $parentUrl = null;
    private $projectId = null;
    private $allFolders = null;
    private $values = null;
    private $javaScript = null;

    protected function __construct()
    {
        parent::__construct();
    }

    protected function execute()
    {
        $this->view->setDecoratorClass( 'Common_FixedBlock' );

        $projectManager = new System_Api_ProjectManager();

        switch ( $this->request->getScriptBaseName() ) {
            case 'editissue':
                $issueManager = new System_Api_IssueManager();
                $issueId = (int)$this->request->getQueryString( 'issue' );
                $this->issue = $issueManager->getIssue( $issueId );

                $this->projectId = $this->issue[ 'project_id' ];
                $this->oldIssueName = $this->issue[ 'issue_name' ];

                $this->view->setSlot( 'page_title', $this->tr( 'Edit Attributes' ) );

                $breadcrumbs = new Common_Breadcrumbs( $this );
                $breadcrumbs->initialize( Common_Breadcrumbs::Issue, $this->issue );
                $this->parentUrl = $breadcrumbs->getParentUrl();
                break;

            case 'addissue':
                $folderId = (int)$this->request->getQueryString( 'folder' );

                if ( $folderId != 0 ) {
                    $this->folder = $projectManager->getFolder( $folderId );

                    $this->projectId = $this->folder[ 'project_id' ];
                    $this->folderName = $this->folder[ 'folder_name' ];
                } else {
                    $typeId = (int)$this->request->getQueryString( 'type' );

                    $typeManager = new System_Api_TypeManager();
                    $this->type = $typeManager->getIssueType( $typeId );
                }

                $this->oldIssueName = '';

                $this->view->setSlot( 'page_title', $this->tr( 'Add Issue' ) );

                $breadcrumbs = new Common_Breadcrumbs( $this );
                if ( $folderId != 0 )
                    $breadcrumbs->initialize( Common_Breadcrumbs::Folder, $this->folder );
                else
                    $breadcrumbs->initialize( Common_Breadcrumbs::Folder, $this->type );
                $this->parentUrl = $breadcrumbs->getParentUrl();

                $defaultFolder = null;

                $this->showDescription = true;

                $oldDescription = '';

                $preferencesManager = new System_Api_PreferencesManager();
                $defaultFormat = $preferencesManager->getPreferenceOrSetting( 'default_format' );
                break;

            case 'cloneissue':
                $issueId = (int)$this->request->getQueryString( 'issue' );

                $issueManager = new System_Api_IssueManager();
                $this->issue = $issueManager->getIssue( $issueId );

                $this->oldIssueName = $this->issue[ 'issue_name' ];
                $this->clone = true;

                $this->view->setSlot( 'page_title', $this->tr( 'Clone Issue' ) );

                $breadcrumbs = new Common_Breadcrumbs( $this );
                $breadcrumbs->initialize( Common_Breadcrumbs::Issue, $this->issue );
                $this->parentUrl = $breadcrumbs->getParentUrl();

                $defaultFolder = $this->issue[ 'folder_id' ];

                $typeManager = new System_Api_TypeManager();
                $this->type = $typeManager->getIssueTypeForIssue( $this->issue );

                $this->showDescription = true;

                if ( $this->issue[ 'descr_id' ] != null ) {
                    $descr = $issueManager->getDescription( $this->issue );

                    $oldDescription = $descr[ 'descr_text' ];
                    $defaultFormat = $descr[ 'descr_format' ];
                } else {
                    $oldDescription = '';

                    $preferencesManager = new System_Api_PreferencesManager();
                    $defaultFormat = $preferencesManager->getPreferenceOrSetting( 'default_format' );
                }
                break;

            default:
                throw new System_Core_Exception( 'Invalid URL' );
        }

        $this->form = new System_Web_Form( 'issues', $this );
        $this->form->addField( 'issueName', $this->oldIssueName );
        $this->form->addTextRule( 'issueName', System_Const::ValueMaxLength );

        if ( $this->clone || ( $this->issue == null && $this->folder == null ) ) {
            $this->form->addField( 'targetFolder', $defaultFolder );

            $projects = $projectManager->getProjects();
            $this->allFolders = $projectManager->getFoldersByIssueType( $this->type );

            $this->folders = array( '' => $this->tr( 'Please Select' ) );

            foreach ( $projects as $project ) {
                $list = array();
                foreach ( $this->allFolders as $folder ) {
                    if ( $folder[ 'project_id' ] == $project[ 'project_id' ] )
                        $list[ $folder[ 'folder_id' ] ] = $folder[ 'folder_name' ];
                }
                if ( !empty( $list ) )
                    $this->folders[ $project[ 'project_name' ] ] = $list;
            }

            if ( count( $this->folders ) == 1 )
                $this->noFolders = true;

            $this->form->addItemsRule( 'targetFolder', $this->folders );
            $this->form->addRequiredRule( 'targetFolder' );
        }

        if ( $this->showDescription ) {
            $this->formatOptions = array(
                System_Const::PlainText => $this->tr( 'Plain Text' ),
                System_Const::TextWithMarkup => $this->tr( 'Text with Markup' )
            );

            $this->form->addField( 'descriptionText', $oldDescription );
            $this->form->addField( 'format', $defaultFormat );

            $serverManager = new System_Api_ServerManager();
            $this->form->addTextRule( 'descriptionText', $serverManager->getSetting( 'comment_max_length' ), System_Api_Parser::AllowEmpty | System_Api_Parser::MultiLine );
            $this->form->addItemsRule( 'format', $this->formatOptions );
        }

        $this->javaScript = new System_Web_JavaScript( $this->view );

        $this->processValues();

        if ( $this->form->loadForm() ) {
            if ( $this->form->isSubmittedWith( 'cancel' ) || $this->form->isSubmittedWith( 'close' ) )
                $this->response->redirect( $this->parentUrl );

            $this->validateValues();

            if ( $this->form->isSubmittedWith( 'ok' ) && !$this->form->hasErrors() ) {
                $this->submitValues();
                $this->response->redirect( $this->parentUrl );
            }
        }
        $this->displayValues();

        if ( $this->showDescription )
            $this->javaScript->registerMarkItUp( $this->form->getFieldSelector( 'descriptionText' ), $this->form->getFieldSelector( 'format' ), '#descriptionPreview' );
    }

    private function processValues()
    {
        $allUsers = null;
        $projectMembers = null;

        $this->attributes = array();
        $this->values = array();
        $this->multiLine = array();

        $oldValues = array();

        $typeManager = new System_Api_TypeManager();

        if ( $this->issue != null ) {
            $issueManager = new System_Api_IssueManager();
            $type = $typeManager->getIssueTypeForIssue( $this->issue );
            $rows = $issueManager->getAllAttributeValuesForIssue( $this->issue );
        } else if ( $this->folder != null ) {
            $type = $typeManager->getIssueTypeForFolder( $this->folder );
            $rows = $typeManager->getAttributeTypesForIssueType( $type );
        } else {
            $type = $this->type;
            $rows = $typeManager->getAttributeTypesForIssueType( $this->type );
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

            if ( $this->issue != null && $this->clone )
                $oldValues[ $attributeId ] = $typeManager->convertInitialValue( $info, $info->getMetadata( 'default', '' ) );
            else
                $oldValues[ $attributeId ] = $this->values[ $attributeId ];

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
                        if ( $info->getMetadata( 'editable', 0 ) )
                            $maxLength = $info->getMetadata( 'max-length', $maxLength );
                        $this->javaScript->registerAutocomplete( $selector, $info->getMetadata( 'items' ) );
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
                        foreach ( $users as $user )
                            $allUsers[ $user[ 'user_id' ] ] = $user[ 'user_name' ];
                    }
                    if ( $info->getMetadata( 'members', 0 ) && $this->projectId != null ) {
                        if ( $projectMembers === null ) {
                            $members = $userManager->getMembers( array( 'project_id' => $this->projectId ) );
                            $allMembers = array();
                            foreach ( $members as $member )
                                $allMembers[ $member[ 'user_id' ] ] = $member;
                            $projectMembers = array_intersect_key( $allUsers, $allMembers );
                        }
                        $items = $projectMembers;
                    } else {
                        $items = $allUsers;
                    }
                    if ( empty( $items ) ) {
                        if ( $info->getMetadata( 'required', 0 ) )
                            $this->noMembers = true;
                    } else {
                        if ( $info->getMetadata( 'multi-select', 0 ) )
                            $this->javaScript->registerAutocomplete( $selector, $items, System_Web_JavaScript::MultiSelect );
                        else
                            $this->javaScript->registerAutocomplete( $selector, $items );
                    }
                    break;
            }

            $this->form->addField( 'value' . $attributeId );

            $flags = 0;
            if ( !$info->getMetadata( 'required', 0 ) )
                $flags |= System_Api_Parser::AllowEmpty;
            if ( !empty( $this->multiLine[ $attributeId ] ) )
                $flags |= System_Api_Parser::MultiLine;
            $this->form->addTextRule( 'value' . $attributeId, $maxLength, $flags );
        }

        $this->form->addViewState( 'oldValues', $oldValues );
    }

    private function validateValues()
    {
        $this->form->validate();

        if ( $this->clone || ( $this->issue == null && $this->folder == null ) ) {
            if ( $this->targetFolder != '' ) {
                foreach ( $this->allFolders as $folder ) {
                    if ( $folder[ 'folder_id' ] == $this->targetFolder ) {
                        $this->projectId = $folder[ 'project_id' ];
                        break;
                    }
                }
            } else {
                $this->form->setError( 'targetFolder', $this->tr( 'No folder selected.' ) );
            }
        }

        $parser = new System_Api_Parser();
        $parser->setProjectId( $this->projectId );

        $this->values = array();

        foreach ( $this->attributes as $attributeId => $attribute ) {
            $propertyName = 'value' . $attributeId;
            if ( $this->form->hasErrors( $propertyName ) )
                continue;
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

        if ( $this->issue == null || $this->clone ) {
            if ( $this->folder == null ) {
                $projectManager = new System_Api_ProjectManager();
                $this->folder = $projectManager->getFolder( $this->targetFolder );
            }

            $issueId = $issueManager->addIssue( $this->folder, $this->issueName, $this->oldValues );
            $this->issue = $issueManager->getIssue( $issueId );
            if ( $this->descriptionText != '' )
                $issueManager->addDescription( $this->issue, $this->descriptionText, $this->format );

            if ( $this->request->isRelativePathUnder( '/mobile' ) )
                $this->parentUrl = $this->mergeQueryString( '/mobile/client/index.php', array( 'issue' => $issueId, 'folder' => null ) );
            else
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
            $flags = !empty( $this->multiLine[ $attributeId ] ) ? System_Api_Formatter::MultiLine : 0;
            $this->$propertyName = $formatter->convertAttributeValue( $attribute[ 'attr_def' ], $value, $flags );
        }
    }
}
