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

( function( $ ) {
    $.datepicker._gotoToday = function( id ) {
        var target = $( id );
        var inst = this._getInst( target[ 0 ] );
        this._selectDate( id, this._get(  inst, 'currentValue' ) );
    };
    $.datepicker._updateDatepickerOrig = $.datepicker._updateDatepicker;
    $.datepicker._updateDatepicker = function( inst ) {
        this._updateDatepickerOrig( inst );
        inst.dpDiv.find( '.ui-datepicker-current' ).removeClass( 'ui-priority-secondary' ).addClass( 'ui-priority-primary' );
    };
    $.widget( 'ui.datetimepicker', {
        options: {
            withTime: false,
            zeroTime: ''
        },
        _create: function() {
            var self = this;
            var oldTime = ' ' + self.options.zeroTime;
            self.element.datepicker( {
                showOn: 'button',
                buttonText: self.options.buttonText,
                buttonImage: self.options.buttonImage,
                buttonImageOnly: true,
                showAnim: '',
                monthNamesShort: self.options.monthNamesShort,
                dayNames: self.options.dayNames,
                dayNamesMin: self.options.dayNamesMin,
                firstDay: self.options.firstDay,
                nextText: self.options.nextText,
                prevText: self.options.prevText,
                currentText: self.options.currentText,
                currentValue: self.options.currentValue,
                closeText: self.options.closeText,
                changeMonth: true,
                changeYear: true,
                dateFormat: self.options.dateFormat,
                constrainInput: self.options.constrainInput,
                showButtonPanel: true,
                showOtherMonths: true,
                selectOtherMonths: true,
                beforeShow: function( input ) {
                    if ( self.options.withTime ) {
                        if ( input.value.length != 0 && ( ( pos = input.value.indexOf( ' ' ) ) >= 0 ) )
                            oldTime = input.value.substring( pos );
                    }
                },
                onSelect: function() {
                    if ( self.options.withTime && this.value.length != 0 && this.value[ 0 ] != '[' && this.value.indexOf( ' ' ) < 0 )
                        this.value += oldTime;
                }
            } );
        }
    } );

    $.widget( 'ui.autocompletebutton', {
        options: {
            minLength: 0,
            multiSelect: false
        },
        _create: function() {
            var self = this;
            self.element.bind( 'keydown', function( event ) {
                if ( event.keyCode === $.ui.keyCode.TAB && $( this ).data( 'ui-autocomplete' ).menu.active )
                    event.preventDefault();
            } );
            self.element.autocomplete( {
                minLength: self.options.minLength,
                source: function( request, response ) {
                    var term = request.term;
                    if ( self.options.multiSelect )
                        term = term.split( /,\s*/ ).pop();
                    var matcher = new RegExp( '^' + $.ui.autocomplete.escapeRegex( term ), 'i' );
                    response( $.grep( self.options.source, function( value ) {
                        return matcher.test( value.label || value.value || value );
                    } ) );
                },
                focus: function( event, ui ) {
                    if ( !self.options.multiSelect )
                        return true;
                    if ( /^key/.test( event.originalEvent.originalEvent.type ) ) {
                        var parts = this.value.split( /,\s*/ );
                        parts.pop();
                        parts.push( ui.item.value );
                        this.value = parts.join( ', ' );
                    }
                    return false;
                },
                select: function( event, ui ) {
                    if ( !self.options.multiSelect )
                        return true;
                    var parts = this.value.split( /,\s*/ );
                    parts.pop();
                    parts.push( ui.item.value );
                    this.value = parts.join( ', ' );
                    return false;
                }
            } );
            var url = self.options.buttonImage;
            var text = self.options.buttonText;
            var button = $( '<img src="' + url + '" alt="' + text + '" title="' + text + '" class="ui-autocomplete-trigger" />' );
            self.element.after( button );
            button.click( function() {
                if ( self.element.autocomplete( 'widget' ).is( ':visible' ) ) {
                    self.element.autocomplete( 'close' );
                } else {
                    self.element.autocomplete( 'search', '' );
                    self.element.focus();
                }
                return false;
            } );
        }
    } );

    $.widget( 'ui.searchoptions', {
        options: {},
        _create: function() {
            var self = this;
            var url = self.options.buttonImage;
            var text = self.options.buttonText;
            var button = $( '<img src="' + url + '" alt="' + text + '" title="' + text + '" class="icon" />' );
            self.element.parent().before( button );
            var hiddenField = $( self.options.hiddenField );
            var value = hiddenField.val();
            var promptSpan = $( '<span class="input-prompt"></span>' );
            for ( i in self.options.source ) {
                var item = self.options.source[ i ];
                if ( item.value == value )
                    promptSpan.text( item.label );
            }
            if ( self.element.val() != '' )
                promptSpan.hide();
            self.element.before( promptSpan );
            var isEnabled = false;
            self.element.autocomplete( {
                minLength: 0,
                position: { of: button },
                source: function( request, response ) {
                    response( self.options.source );
                },
                search: function( event, ui ) {
                    return isEnabled;
                },
                close: function( event, ui ) {
                    isEnabled = false;
                },
                focus: function( event, ui ) {
                    return false;
                },
                select: function( event, ui ) {
                    value = ui.item.value;
                    hiddenField.val( value );
                    promptSpan.text( ui.item.label );
                    return false;
                }
            } );
            self.element.data( 'ui-autocomplete' )._renderItem = function( ul, item ) {
                var anchor = $( '<a></a>' );
                if ( item.value == value )
                    anchor.append( $( '<strong></strong>' ).text( item.label ) );
                else
                    anchor.text( item.label );
                return $( '<li></li>' )
                    .data( 'ui-autocomplete-item', item )
                    .append( anchor )
                    .appendTo( ul );
            };
            button.click( function() {
                isEnabled = true;
                self.element.autocomplete( 'search', '' );
                self.element.focus();
            } );
            promptSpan.click( function() {
                promptSpan.hide();
                self.element.focus();
            } );
            self.element.focus( function() {
                promptSpan.hide()
            } );
            self.element.blur( function() {
                if ( self.element.val() == '' )
                    promptSpan.attr( 'style', 'display:inline' );
            } );
        }
    } );
} )( jQuery );
