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

class Admin_Views_Order extends System_Web_Component
{
    protected function __construct()
    {
        parent::__construct();
    }

    protected function execute()
    {
        $this->view->setDecoratorClass( 'Common_FixedBlock' );
        $this->view->setSlot( 'page_title', $this->tr( 'Order of Attributes' ) );

        $helper = new Common_Views_Helper();
        $breadcrumbs = $helper->getBreadcrumbs( $this );

        $this->type = $helper->getType();

        $helper->initializeAttributesAndOrder();
        $this->attributes = $helper->getAttributes();

        if ( empty( $this->attributes ) )
            throw new System_Core_Exception( 'No attributes available' );

        $this->order = $helper->getOrder();

        $this->form = new System_Web_Form( 'order', $this );
        $this->form->addViewState( 'previousOrder' );

        foreach ( $this->attributes as $attribute )
            $this->form->addField( 'order' . $attribute[ 'attr_id' ] );

        if ( $this->form->loadForm() ) {
            if ( $this->form->isSubmittedWith( 'cancel' ) )
                $this->response->redirect( $breadcrumbs->getParentUrl() );

            $this->updateOrder();

            if ( $this->form->isSubmittedWith( 'ok' ) ) {
                $this->submit();
                $this->response->redirect( $breadcrumbs->getParentUrl() );
            }
        } else {
            $helper->loadOrder();
            $this->order = $helper->getOrder();
        }
        
        $this->populateOrder();
    }

    private function updateOrder()
    {
        $weigh = array();
        $order = array();
        $map = array();
        foreach ( $this->order as $id => $name ) {
            $orderField = "order$id";
            $weigh[] = $this->$orderField;
            $order[] = array_search( $id, $this->previousOrder );
            $map[] = $id;
        }

        array_multisort( $weigh, SORT_ASC, $order, SORT_DESC, $map );

        $ordered = array();
        foreach ( $map as $id )
            $ordered[ $id ] = $this->order[ $id ];

        $this->order = $ordered;
    }

    private function populateOrder()
    {
        $index = 1;

        foreach ( $this->order as $id => $name ) {
            $orderField = "order$id";
            $this->$orderField = $index++;
        }

        $this->previousOrder = array_keys( $this->order );

        $this->orderOptions = array();
        for ( $i = 1; $i < $index; $i++ )
            $this->orderOptions[ $i ] = $i;
    }

    private function submit()
    {
        $order = implode( ',', array_keys( $this->order ) );

        $viewManager = new System_Api_ViewManager();
        $viewManager->setViewSetting( $this->type, 'attribute_order', $order );
    }
}

System_Bootstrap::run( 'Common_Application', 'Admin_Views_Order' );
