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

WebIssues.initSelection = function( commands ) {
    $( '.grid td a' ).click( function() {
        var row = $( this ).parents( 'tr' );
        $( '.grid tr' ).removeClass( 'selected' );
        row.addClass( 'selected' );
        for ( i in commands ) {
            var command = commands[ i ];
            var visible = true;
            for ( j in command.conditions ) {
                if ( !row.hasClass( command.conditions[ j ] ) )
                    visible = false;
            }
            if ( visible ) {
                $( '#cmd-' + i ).show();
                var links = $( '#cmd-' + i + ' a' );
                var url = links.attr( 'href' );
                if ( command.row != undefined )
                    url = $.param.querystring( url, command.row + '=' + WebIssues.classParam( row, 'row' ) );
                if ( command.parent != undefined )
                    url = $.param.querystring( url, command.parent + '=' + WebIssues.classParam( row, 'parent' ) );
                links.attr( 'href', url );
            } else {
                $( '#cmd-' + i ).hide();
            }
        }
        return false;
    } );
}
