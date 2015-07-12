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

( function ( WebIssues, $ ) {
    WebIssues.mobileExpandCookie = function( cookieName, options ) {
        var expandedIds = [];
        var cookieContent = $.cookie( cookieName );
        if ( cookieContent )
            expandedIds = cookieContent.split( '|' );
        $( '.parent .ellipsis' ).show();
        $( '.children' ).hide();
        for ( i in expandedIds ) {
            if ( expandedIds[ i ].length > 0 ) {
                $( '.children.parent-' + expandedIds[ i ] ).show();
                $( '.parent.parent-' + expandedIds[ i ] + ' .ellipsis' ).hide();
            }
        }
        $( '.expandable' ).click( function() {
            var parent = $( this );
            var id = WebIssues.classParam( parent, 'parent' );
            $( '.children.parent-' + id ).slideToggle( 'fast', function() {
                var result = [];
                var k = 0;
                if ( $( this ).is( ':visible' ) ) {
                    parent.children( '.ellipsis' ).hide();
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
                    parent.children( '.ellipsis' ).show();
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
        } );
    };

    $( function() {
        $( '.cross' ).hide();
        $( '.menu' ).hide();

        $( '.pane-wrapper:not(:last)' ).each( function() {
            var pane = $( this );
            var hdr = pane.children( '.pane-header' );
            hdr.children( '.hamburger' ).hide();
            hdr.children( 'h2' ).css( 'margin-right', 0 );
            hdr.find( '.ellipsis' ).show();
            pane.children( '.pane-body' ).hide();
        } );

        $( '.hamburger' ).click( function( event ) {
            $( '.cross:visible' ).each( function() {
                var btn2 = $( this );
                btn2.parent().next( '.menu' ).slideToggle( 'fast', function() {
                    btn2.hide();
                    btn2.prev( '.hamburger' ).show();
                } );
            } );
            var btn = $( this );
            btn.parent().next( '.menu' ).slideToggle( 'fast', function() {
                btn.hide();
                btn.next( '.cross' ).show();
            } );
            event.stopPropagation();
        } );

        $( '.cross' ).click( function( event ) {
            var btn = $( this );
            btn.parent().next( '.menu' ).slideToggle( 'fast', function() {
                btn.hide();
                btn.prev( '.hamburger' ).show();
            } );
            event.stopPropagation();
        } );

        $( '.pane-header' ).click( function() {
            var hdr = $( this );
            var body = hdr.siblings( '.pane-body' );
            if ( body.is( ':visible' ) ) {
                var menu = hdr.siblings( '.menu' );
                if ( menu.is( ':visible' ) )
                    menu.slideToggle( 'fast' );
            }
            body.slideToggle( 'fast', function() {
                if ( body.is( ':visible' ) ) {
                    hdr.find( '.ellipsis' ).hide();
                    hdr.children( 'h2' ).css( 'margin-right', 36 );
                    hdr.children( '.hamburger' ).show();
                } else {
                    hdr.children( '.hamburger' ).hide();
                    hdr.children( '.cross' ).hide();
                    hdr.children( 'h2' ).css( 'margin-right', 0 );
                    hdr.find( '.ellipsis' ).show();
                }
            } );
        } );
    } );
} )( WebIssues, jQuery );
