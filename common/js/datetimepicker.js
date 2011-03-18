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
    $.widget( "ui.datetimepicker", {
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
                changeMonth: true,
                changeYear: true,
                dateFormat: self.options.dateFormat,
                constrainInput: self.options.constrainInput || self.options.withTime,
                beforeShow: function( input ) {
                    if ( self.options.withTime ) {
                        if ( input.value.length != 0 && ( ( pos = input.value.indexOf( ' ' ) ) >= 0 ) )
                            oldTime = input.value.substring( pos );
                    }
                },
                onSelect: function() {
                    if ( self.options.withTime )
                        this.value += oldTime;
                }
            } );
        }
    } );
} )( jQuery );
