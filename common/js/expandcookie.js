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

WebIssues.expandCookie = function( cookieName, options ) {
    var expandedIds = [];
    var cookieContent = $.cookie( cookieName );
    if ( cookieContent )
        expandedIds = cookieContent.split( '|' );
    $( '.expand' ).show();
    $( '.blank' ).show();
    $( '.collapse' ).hide();
    $( '.child' ).hide();
    for ( i in expandedIds ) {
        if ( expandedIds[ i ].length > 0 ) {
            $( '.child.parent-' + expandedIds[ i ] ).show();
            $( '.parent.parent-' + expandedIds[ i ] + ' .collapse' ).show();
            $( '.parent.parent-' + expandedIds[ i ] + ' .expand' ).hide();
        }
    }
    $( '.expand, .collapse' ).click( function() {
        var id = WebIssues.classParam( $( this ).parents( 'tr' ), 'parent' );
        $( '.child.parent-' + id ).toggle();
        $( this ).hide();
        var result = [];
        var k = 0;
        if ( $( this ).hasClass( 'expand' ) ) {
            $( '.parent.parent-' + id + ' .collapse' ).show();
            var found = false;
            for ( i in expandedIds ) {
                if ( expandedIds[ i ].length > 0 ) {
                    if ( expandedIds[ i ] == id )
                        found = true;
                    result[ k ] = expandedIds[ i ];
                    k++;
                }
            }
            if ( !found )
               result[ k ] = id;
        } else {
            $( '.parent.parent-' + id + ' .expand' ).show();
            for ( i in expandedIds ) {
                if ( ( expandedIds[ i ].length > 0 ) && ( expandedIds[ i ] != id ) ) {
                    result[ k ] = expandedIds[ i ];
                    k++;
                }
            }
        }
        expandedIds = result;
        $.cookie( cookieName, expandedIds.join( '|' ), options );
    } );
};
