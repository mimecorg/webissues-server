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

class Common_Tools_GoToItem extends System_Web_Component
{
    protected function __construct()
    {
        parent::__construct();
    }

    protected function execute()
    {
        $this->view->setDecoratorClass( 'Common_FixedBlock' );
        $this->view->setSlot( 'page_title', $this->tr( 'Go To Item' ) );

        $helper = new Common_Tools_Helper();
        $breadcrumbs = $helper->getBreadcrumbs( $this );

        $this->form = new System_Web_Form( 'tools', $this );
        $this->form->addField( 'itemId', '' );

        if ( $this->form->loadForm() ) {
            if ( $this->form->isSubmittedWith( 'cancel' ) )
                $this->response->redirect( $breadcrumbs->getParentUrl() );

            $this->form->validate();

            if ( $this->form->isSubmittedWith( 'ok' ) && !$this->form->hasErrors() )
                $this->submit();
        }
    }

    private function submit()
    {
        $parser = new System_Api_Parser();
        $helper = new Common_Tools_ItemHelper();
        try {
            $value = $parser->normalizeString( $this->itemId );
            if ( $value[ 0 ] == '#' )
                $value = substr( $value, 1 );
            $parser->checkDecimalNumber( $value, 0, 1, System_Const::INT_MAX );
            $helper->findItem( (int)$value );
        } catch ( System_Api_Error $ex ) {
            $this->form->getErrorHelper()->handleError( 'itemId', $ex );
        }
    }
}
