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

( function( $ ) {
    $.datepicker._gotoToday = function( id ) {
        var target = $( id );
        var inst = this._getInst( target[ 0 ] );
        this._selectDate( id, '[' + this._get(  inst, 'currentText' ) + ']' );
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
                showButtonPanel: true,
                monthNamesShort: self.options.monthNamesShort,
                dayNames: self.options.dayNames,
                dayNamesMin: self.options.dayNamesMin,
                firstDay: self.options.firstDay,
                nextText: self.options.nextText,
                prevText: self.options.prevText,
                currentText: self.options.currentText,
                closeText: self.options.closeText,
                changeMonth: true,
                changeYear: true,
                dateFormat: self.options.dateFormat,
                constrainInput: !self.options.showButtonPanel && !self.options.withTime,
                showButtonPanel: self.options.showButtonPanel,
                showOtherMonths: true,
                selectOtherMonths: true,
                beforeShow: function( input ) {
                    if ( self.options.withTime ) {
                        if ( input.value.length != 0 && ( ( pos = input.value.indexOf( ' ' ) ) >= 0 ) )
                            oldTime = input.value.substring( pos );
                    }
                },
                onSelect: function() {
                    if ( self.options.withTime && this.value.length >= 1 && this.value[ 0 ] != '[' )
                        this.value += oldTime;
                }
            } );
        }
    } );
} )( jQuery );
