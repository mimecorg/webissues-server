<?php
/**************************************************************************
* This file is part of the WebIssues Server program
* Copyright (C) 2006 Michał Męciński
* Copyright (C) 2007-2012 WebIssues Team
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

/**
* Class for interfacing with JavaScript controls.
*/
class System_Web_JavaScript extends System_Web_Base
{
    private $scriptFiles = array(
        'autocompletebutton'  => 'autocompletebutton.js',
        'ba-bbq' => 'jquery.ba-bbq.js',
        'bgiframe' =>  'jquery.bgiframe.js',
        'blockui' => 'jquery.blockui.js',
        'cookie' => 'jquery.cookie.js',
        'datetimepicker' => 'datetimepicker.js',
        'expandcookie' => 'expandcookie.js',
        'searchoptions' => 'searchoptions.js',
        'selection' => 'selection.js',
        'ui.autocomplete' => 'ui/jquery.ui.autocomplete.js',
        'ui.core' => 'ui/jquery.ui.core.js',
        'ui.datepicker' => 'ui/jquery.ui.datepicker.js',
        'ui.position' => 'ui/jquery.ui.position.js',
        'ui.widget' => 'ui/jquery.ui.widget.js'
        );

    private $cssFiles = array(
        'ui.autocomplete' => 'ui/jquery.ui.autocomplete.css',
        'ui.core' => 'ui/jquery.ui.core.css',
        'ui.datepicker' => 'ui/jquery.ui.datepicker.css',
        'ui.theme' => 'ui/jquery.ui.theme.css'
        );

    private $view = null;

    /**
    * @name Flags
    */
    /*@{*/
    /**
    * Include time part in the date picker.
    */
    const WithTime = 1;
    /**
    * Include 'Today' button in the date picker.
    */
    const WithToday = 2;
    /**
    * Allow selecting multiple items in the autocomplete control.
    */
    const MultiSelect = 4;
    /*@}*/

    /**
    * Constructor.
    * @param $view The view to attach the JavaScript to.
    */
    public function __construct( $view )
    {
        parent::__construct();

        $this->view = $view;
    }

    /**
    * Register date picker controls based on click events.
    * @param $selector The jQuery selector of the datePicker control.
    * @param $noTimeSelector The jQuery selector on which a click 
    * triggers a datePicker without time.
    * @param $withTimeSelector The jQuery selector on which a click 
    * triggers a datePicker with time.
    */
    public function registerDynamicDatePicker( $selector, $noTimeSelector, $withTimeSelector )
    {
        $this->registerCode( "
            $( '$noTimeSelector' ).click( function() {
                $( '$selector' ).datetimepicker( 'option', 'withTime', false );
            } );
            $( '$withTimeSelector' ).click( function() {
                $( '$selector' ).datetimepicker( 'option', 'withTime', true );
            } );" );
    }

    /**
    * Registers a date picker control.
    * @param $selector The jQuery selector of the control.
    * @param $flags If WithTime is set then time is included.
    */
    public function registerDatePicker( $selector, $flags = 0 )
    {
        $this->registerScripts( array( 'ui.core', 'ui.widget', 'ui.datepicker', 'datetimepicker' ) );
        $this->registerCss( array( 'ui.datepicker' ) );

        $locale = new System_Api_Locale();
        $localeHelper = new System_Web_LocaleHelper();
        $formatter = new System_Api_Formatter();

        $this->registerCode( "
            $( '$selector' ).datetimepicker( {
                buttonText: " . $this->escape( $this->tr( 'Choose' ) ) . ",
                buttonImage: " . $this->escape( $this->url( '/common/images/arrow-down-16.png' ) ) . ",
                monthNamesShort: " . $this->escapeArray( $localeHelper->getShortMonths() ) . ",
                dayNames: " . $this->escapeArray( $localeHelper->getDaysOfWeek() ) . ",
                dayNamesMin: " . $this->escapeArray( $localeHelper->getShortDaysOfWeek() ) . ",
                firstDay: " . $locale->getSetting( 'first_day_of_week' ) . ",
                nextText: " . $this->escape( $this->tr( 'Next' ) ) . ",
                prevText: " . $this->escape( $this->tr( 'Previous' ) ) . ",
                currentText: " . $this->escape( $this->tr( 'Today' ) ) . ",
                closeText: " . $this->escape( $this->tr( 'Close' ) ) . ",
                dateFormat: '" . $this->getDateFormat( $locale->getSettingFormat( 'date_format' ) ) . "',
                showButtonPanel: " . ( ( $flags & self::WithToday ) ? 'true' : 'false' ) . ",
                withTime: " . ( ( $flags & self::WithTime ) ? 'true' : 'false' ) . ",
                zeroTime: '" . $formatter->convertTime( '00:00' ) . "' } );" );
    }

    /**
    * Registers an autocomplete control.
    * @param $selector The jQuery selector of the control.
    * @param $items Items for autocomplete.
    * @param $flags If MultiSelect is set then multiple items can be entered.
    */
    public function registerAutocomplete( $selector, $items, $flags = 0 )
    {
        $this->registerScripts( array( 'bgiframe', 'ui.core', 'ui.widget', 'ui.position', 'ui.autocomplete', 'autocompletebutton' ) );
        $this->registerCss( array( 'ui.autocomplete' ) );

        $this->registerCode( "
            $( '$selector' ).autocompletebutton( {
                buttonText: " . $this->escape( $this->tr( 'Choose' ) ) . ",
                buttonImage: " . $this->escape( $this->url( '/common/images/arrow-down-16.png' ) ) . ",
                multiSelect: " . ( ( $flags & self::MultiSelect ) ? 'true' : 'false' ) . ",
                source: " . $this->escapeArray( $items ) . " } );" );
    }

    /**
    * Registers a search options control.
    * @param $selector The jQuery selector of the control.
    * @param $hiddenSelector The jQuery selector of the hidden field.
    * @param $items Associative array of option items.
    */
    public function registerSearchOptions( $selector, $hiddenSelector, $items )
    {
        $this->registerScripts( array( 'bgiframe', 'ui.core', 'ui.widget', 'ui.position', 'ui.autocomplete', 'searchoptions' ) );
        $this->registerCss( array( 'ui.autocomplete' ) );

        $this->registerCode( "
            $( '$selector' ).searchoptions( {
                buttonText: " . $this->escape( $this->tr( 'Search Options' ) ) . ",
                buttonImage: " . $this->escape( $this->url( '/common/images/find-options-16.png' ) ) . ",
                hiddenField: " . $this->escape( $hiddenSelector ). ",
                source: " . $this->escapeOptions( $items ) . " } );" );
    }

    /**
    * Registers an expand/collapse effect.
    * @param $cookieName Name of the cookie that stores item ids that
    * are expanded.
    */
    public function registerExpandCookie( $cookieName )
    {
        $this->registerScripts( array( 'cookie', 'expandcookie' ) );

        $session = System_Core_Application::getInstance()->getSession();
        $path = $session->getCookiePath();
        $secure = $session->isCookieSecure() ? 'true' : 'false';

        $this->registerCode( "
            WebIssues.expandCookie( '$cookieName', { path: '$path', expires: 90, secure: $secure, raw: true } );" );
    }

    /**
    * Register automatic form submission when field is changed.
    * @param $formId The selector of the form.
    * @param $selectKey The selector of the field which triggers the submit.
    * @param $submitKey The selector to the submit button to hide.
    */
    public function registerAutoSubmit( $formSelector, $fieldSelector, $submitSelector )
    {
        $this->registerCode( "
            $( '$submitSelector' ).hide();
            $( '$fieldSelector' ).change( function() {
                $( '$formSelector' ).submit();
            } );" );
    }

    /**
    * Register checking form fields on/off.
    * @param $triggerSelector The jQuery selector that, when clicked, triggers the field change.
    * @param $targetSelector The jQuery selector of the fields to check on/off.
    * @param $on @c true to check on, @c false to check off.
    */
    public function registerCheckOnOff( $triggerSelector, $targetSelector, $on )
    {
        $checked = $on ? 'true' : 'false';
        $this->registerCode( "
            $( '$triggerSelector' ).show();
            $( '$triggerSelector' ).click( function() {
                $( '$targetSelector' ).each( function() { this.checked = $checked; } );
                return false;
            } );" );
    }

    /**
    * Register dynamic selection in grid connected with a toolbar.
    * @param $toolBar The System_Web_ToolBar object.
    */
    public function registerSelection( $toolBar )
    {
        $this->registerScripts( array( 'ba-bbq', 'selection' ) );

        $this->registerCode( "
            WebIssues.initSelection( [" );
        foreach ( $toolBar->getCommands() as $command ) {
            $conditions = $command[ 'conditions' ];

            if ( empty( $conditions ) )
                continue;

            $info = array( 'conditions: ' . $this->escapeArray( $conditions ) );

            if ( $command[ 'row' ] )
                $info[] = 'row: ' . $this->escape( $toolBar->getRowParam() );
            if ( $command[ 'parent' ] )
                $info[] = 'parent: ' . $this->escape( $toolBar->getParentParam() );

            $this->registerCode( "
                { " . join( ', ', $info ) . " }," );
        }
        $this->registerCode( '
            ] ); ' );
    }

    /**
    * Register blocking the UI when a button is clicked.
    * @param $triggerSelector The jQuery selector of the submit button.
    * @param $messageSelector The jQuery selector of the message box to display.
    */
    public function registerBlockUI( $triggerSelector, $messageSelector )
    {
        $this->registerScripts( array( 'blockui' ) );

        $this->registerCode( "
            $( '$triggerSelector' ).click( function() {
                $.blockUI( {
                    message: $( '$messageSelector' )
                } );
            } );" );
    }

    /**
    * Register opening a link in new window when a button is clicked.
    * @param $triggerSelector The jQuery selector of the submit button.
    * @param $url The absolute or relative URL to open.
    */
    public function registerExternalLink( $triggerSelector, $url )
    {
        $this->registerCode( "
            $( '$triggerSelector' ).click( function() {
                window.open( " . $this->escape( $this->url( $url ) ) . " );
                return false;
            } );" );
    }

    private function registerScripts( $scripts )
    {
        foreach ( $scripts as $file )
            $this->view->mergeSlotItem( 'script_files', '/common/js/' . $this->scriptFiles[ $file ] );
    }

    private function registerCss( $css )
    {
        foreach ( $css as $file )
           $this->view->mergeSlotItem( 'css_files', '/common/theme/' . $this->cssFiles[ $file ] );
    }

    private function registerCode( $code )
    {
        $this->view->appendSlotItem( 'inline_code', $code );
    }

    private function escape( $value )
    {
        return "'" . addcslashes( $value, "\\\"'" ) . "'";
    }

    private function escapeArray( $values )
    {
        $escaped = array();
        foreach ( $values as $value )
            $escaped[] = $this->escape( $value );
        return '[ ' . implode( ', ', $escaped ) . ' ]';
    }

    private function escapeOptions( $options )
    {
        $escaped = array();
        foreach ( $options as $value => $label )
            $escaped[] = '{ value: ' . $value . ', label: ' . $this->escape( $label ) . ' }';
        return '[ ' . implode( ', ', $escaped ) . ' ]';
    }

    private function getDateFormat( $format )
    {
        $info = System_Api_DefinitionInfo::fromString( $format );

        $order = $info->getMetadata( 'date-order' );
        $separator = $info->getMetadata( 'date-separator' );

        $parts[ 'y' ] = 'yy';
        $parts[ 'm' ] = $info->getMetadata( 'pad-month' ) ? 'mm' : 'm';
        $parts[ 'd' ] = $info->getMetadata( 'pad-day' ) ? 'dd' : 'd';

        return $parts[ $order[ 0 ] ] . $separator . $parts[ $order[ 1 ] ] . $separator . $parts[ $order[ 2 ] ];
    }
}
