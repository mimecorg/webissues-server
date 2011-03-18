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
    $.widget( "ui.autocompletebutton", {
        options: {
            minLength: 0
        },
        _create: function() {
            var self = this;
            self.element.autocomplete( {
                minLength: self.options.minLength,
                source: self.options.source
            } );
            var url = self.options.buttonImage;
            var text = self.options.buttonText;
            button = $( '<img src="' + url + '" alt="' + text + '" title="' + text + '" class="ui-autocomplete-trigger" />' );
            self.element.after( button );
            button.click( function() {
                if ( self.element.autocomplete( "widget" ).is( ":visible" ) ) {
                    self.element.autocomplete( "close" );
                } else {
                    self.element.autocomplete( "search", "" );
                    self.element.focus();
                }
                return false;
            } );
        }
    } );
} )( jQuery );
