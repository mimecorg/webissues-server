/**************************************************************************
* This file is part of the WebIssues Server program
* Copyright (C) 2006 Michał Męciński
* Copyright (C) 2007-2013 WebIssues Team
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

var WebIssues = WebIssues || {};

WebIssues.classParam = function( node, name ) {
    var classes = node.attr( 'class' ).split( ' ' );
    for ( i in classes ) {
        if ( classes[ i ].substring( 0, name.length + 1 ) == name + '-' )
            return classes[ i ].substring( name.length + 1 );
    }
    return '';
}

WebIssues.autofocus = function() {
    if ( $( 'form' ).length > 0 ) {
        var wrongInput = [];
        var fieldError = $( '.error' );
        if ( fieldError.length > 0 ) {
            wrongInput = fieldError.prev( ':input' );
            if ( wrongInput.length == 0 )
                wrongInput = fieldError.prevAll( '.form-field:first' ).children( ':input' );
        }

        var toHighlight = [];
        if  ( wrongInput.length > 0 )
            toHighlight = wrongInput;
        else
            toHighlight = $( ':input:enabled' );

        toHighlight.each( function() {
            if ( $( this ).is( ':text,:password,:radio:checked,:checkbox,select,textarea' ) ) {
                this.focus();
                return false;
            }
        } );
    }
}

WebIssues.autoalign = function() {
    var info = $( '.info-align' );
    if ( info.length > 0 ) {
        var maxW = 0;
        info.each( function() {
            var w = $( this ).find( 'td' ).first().width();
            if ( maxW < w )
                maxW = w;
        } );
        info.each( function() {
            $( this ).find( 'td' ).first().css( 'width', maxW );
        } );
    }
}


$( function() {
    WebIssues.autofocus();
    WebIssues.autoalign();
} );
