<?php
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

if ( !defined( 'WI_VERSION' ) ) die( -1 );

class Common_Tools_Locale extends System_Web_Component
{
    public function __construct( $form )
    {
        parent::__construct();

        $this->form = $form;
    }

    protected function execute()
    {
        $settingsMode = $this->request->isRelativePathUnder( '/admin/settings' );

        $locale = new System_Api_Locale();

        $date = new DateTime();

        if ( !$settingsMode ) {
            $serverManager = new System_Api_ServerManager();
            $defaultLanguage = $serverManager->getSetting( 'language' );
            $defaultZone = $serverManager->getSetting( 'time_zone' );
            if ( $defaultZone != null )
                $date->setTimezone( new DateTimeZone( $defaultZone ) );
        }

        $defaultZone = $date->getTimezone()->getName();

        $languages = $locale->getAvailableLanguages();
        if ( $settingsMode ) {
            $this->languageOptions = $languages;
        } else {
            $this->languageOptions = array();
            $this->languageOptions[ '' ] = $this->tr( 'Default (%1)', 'language', $languages[ $defaultLanguage ] );
            $this->languageOptions = array_merge( $this->languageOptions, $languages );
        }

        $this->numberOptions = array();
        $this->numberOptions[ '' ] = $this->tr( 'Default', 'format' );
        foreach ( $locale->getAvailableFormats( 'number_format' ) as $key => $format ) {
            $info = System_Api_DefinitionInfo::fromString( $format );
            $this->numberOptions[ $key ] = $this->makeSampleNumber( $info );
        }

        $this->dateOptions = array();
        $this->dateOptions[ '' ] = $this->tr( 'Default', 'format' );
        foreach ( $locale->getAvailableFormats( 'date_format' ) as $key => $format ) {
            $info = System_Api_DefinitionInfo::fromString( $format );
            $this->dateOptions[ $key ] = $this->makeSampleDate( $info );
        }

        $this->timeOptions = array();
        $this->timeOptions[ '' ] = $this->tr( 'Default', 'format' );
        foreach ( $locale->getAvailableFormats( 'time_format' ) as $key => $format ) {
            $info = System_Api_DefinitionInfo::fromString( $format );
            $this->timeOptions[ $key ] = $this->makeSampleTime( $info );
        }

        $localeHelper = new System_Web_LocaleHelper();
        $this->dayOptions = array();
        $this->dayOptions[ '' ] = $this->tr( 'Default', 'day of week' );
        $this->dayOptions = array_merge( $this->dayOptions, $localeHelper->getDaysOfWeek() );

        $zones = array();
        $offsets = array();
        foreach ( $locale->getAvailableTimeZones() as $zone ) {
            $date->setTimeZone( new DateTimeZone( $zone ) );
            $key = 'GMT' . $date->format( 'P (H:i)' );
            $zones[ $key ][ $zone ] = $this->makeZoneName( $zone );
            if ( !isset( $offsets[ $key ] ) )
                $offsets[ $key ] = $date->format( 'Z' );
        }

        array_multisort( $offsets, $zones );

        $this->zoneOptions = array();
        $this->zoneOptions[ '' ] = $this->tr( 'Default (%1)', 'time zone', $this->makeZoneName( $defaultZone ) );
        $this->zoneOptions = array_merge( $this->zoneOptions, $zones );
    }

    public static function registerFields( &$fields )
    {
        $fields[ 'language' ] = 'language';
        $fields[ 'number_format' ] = 'numberFormat';
        $fields[ 'date_format' ] = 'dateFormat';
        $fields[ 'time_format' ] = 'timeFormat';
        $fields[ 'first_day_of_week' ] = 'firstDay';
        $fields[ 'time_zone' ] = 'timeZone';
    }

    private function makeSampleNumber( $info )
    {
        return number_format( 1000, 2, $info->getMetadata( 'decimal-separator' ),  $info->getMetadata( 'group-separator' ) );
    }

    private function makeSampleDate( $info )
    {
        $part[ 'd' ] = $info->getMetadata( 'pad-day' ) ? 'dd' : 'd';
        $part[ 'm' ] = $info->getMetadata( 'pad-month' ) ? 'mm' : 'm';
        $part[ 'y' ] = 'yyyy';
        $separator = $info->getMetadata( 'date-separator' );
        $order = $info->getMetadata( 'date-order' );
        return $part[ $order[ 0 ] ] . $separator . $part[ $order[ 1 ] ] . $separator . $part[ $order[ 2 ] ];
    }

    private function makeSampleTime( $info )
    {
        $mode = $info->getMetadata( 'time-mode' );
        $hour = ( $mode == 12 ) ? 'h' : 'H';
        if ( $info->getMetadata( 'pad-hour' ) )
            $hour .= $hour;
        $time = $hour . $info->getMetadata( 'time-separator' ) . 'mm';
        if ( $mode == 12 )
            $time .= ' tt';
        return $time;
    }

    private function makeZoneName( $zone )
    {
        return str_replace( array( '_', '/', 'St ' ), array( ' ', ' / ', 'St. ' ), $zone );
    }
}
