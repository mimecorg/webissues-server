/**************************************************************************
* This file is part of the WebIssues Server program
* Copyright (C) 2006 Michał Męciński
* Copyright (C) 2007-2010 WebIssues Team
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
            self.element.data( 'autocomplete' )._renderItem = function( ul, item ) {
                var anchor = $( '<a></a>' );
                if ( item.value == value )
                    anchor.append( $( '<strong></strong>' ).text( item.label ) );
                else
                    anchor.text( item.label );
                return $( '<li></li>' )
                    .data( 'item.autocomplete', item )
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
