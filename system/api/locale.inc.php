<?php
/**************************************************************************
* This file is part of the WebIssues Server program
* Copyright (C) 2006 Michał Męciński
* Copyright (C) 2007-2011 WebIssues Team
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

/**
* Information about current locale.
*/
class System_Api_Locale
{
    private static $cache = array();

    private $userId = null;

    /**
    * Constructor.
    */
    public function __construct()
    {
        $this->userId = System_Api_Principal::getCurrent()->getUserId();
    }

    /**
    * Return all locale settings as an assotiative array.
    */
    public function getSettings()
    {
        if ( !isset( self::$cache[ $this->userId ] ) ) {
            $preferencesManager = new System_Api_PreferencesManager();
            $serverManager = new System_Api_ServerManager();

            $language = $preferencesManager->getPreference( 'language' );
            if ( $language == null )
                $language = $serverManager->getSetting( 'language' );

            $locale = System_Core_IniFile::parseExtended( '/common/data/locale.ini', '/data/locale.ini' );

            $settings = $locale[ 'global' ];
            if ( isset( $locale[ $language ] ) )
                $settings = array_merge( $settings, $locale[ $language ] );

            $settings[ 'time_zone' ] = date_default_timezone_get();

            foreach ( $settings as $key => &$value ) {
                $preference = $preferencesManager->getPreference( $key );
                if ( $preference != null ) {
                    $value = $preference;
                } else {
                    $setting = $serverManager->getSetting( $key );
                    if ( $setting != null )
                        $value = $setting;
                }
            }

            $settings[ 'language' ] = $language;

            self::$cache[ $this->userId ] = $settings;
        }

        return self::$cache[ $this->userId ];
    }

    /**
    * Return an array of associative arrays representing locale settings.
    */
    public function getSettingsAsTable()
    {
        $result = array();

        foreach ( array( 'language', 'first_day_of_week', 'time_zone' ) as $key )
            $result[] = array( 'set_key' => $key, 'set_value' => $this->getSetting( $key ) );
        foreach ( array( 'number_format', 'date_format', 'time_format' ) as $key )
            $result[] = array( 'set_key' => $key, 'set_value' => $this->getSettingFormat( $key ) );

        return $result;
    }

    /**
    * Return value of the locale setting.
    * @param $key Name of the setting to return.
    * @return The value of the setting.
    */
    public function getSetting( $key )
    {
        $settings = $this->getSettings();

        return isset( $settings[ $key ] ) ? $settings[ $key ] : null;
    }

    /**
    * Return the current format for the given setting.
    * @param $key Name of the setting to return.
    * @return The format of the setting.
    */
    public function getSettingFormat( $key )
    {
        $settings = $this->getSettings();
        $formats = $this->getAvailableFormats( $key );

        return isset( $settings[ $key ] ) ? $formats[ $settings[ $key ] ] : null;
    }

    /**
    * Return the list of available languages.
    */
    public function getAvailableLanguages()
    {
        $locale = System_Core_IniFile::parseExtended( '/common/data/locale.ini', '/data/locale.ini' );

        $languages = $locale[ 'languages' ];
        ksort( $languages );

        return $languages;
    }

    /**
    * Return an array of associative arrays representing available languages.
    */
    public function getLanguagesAsTable()
    {
        $languages = $this->getAvailableLanguages();

        $result = array();
        foreach ( $languages as $key => $name )
            $result[] = array( 'lang_key' => $key, 'lang_name' => $name );

        return $result;
    }

    /**
    * Return the list of available formats for the given setting.
    */
    public function getAvailableFormats( $key )
    {
        $formats = System_Core_IniFile::parseRaw( '/common/data/formats.ini' );

        return $formats[ $key ];
    }

    /**
    * Return an array of associative arrays representing available formats.
    */
    public function getFormatsAsTable()
    {
        $result = array();

        foreach ( array( 'number_format', 'date_format', 'time_format' ) as $type ) {
            $formats = $this->getAvailableFormats( $type );

            foreach ( $formats as $key => $definition )
                $result[] = array( 'form_type' => $type, 'form_key' => $key, 'form_def' => $definition );
        }

        return $result;
    }

    /**
    * Return the list of available time zones.
    */
    public function getAvailableTimeZones()
    {
        $timeZones = System_Core_IniFile::parse( '/common/data/timezones.ini', true );

        $zones = array();
        foreach ( DateTimeZone::listIdentifiers() as $zone ) {
            if ( preg_match( '/^(Africa|America|Asia|Atlantic|Australia|Europe|Indian|Pacific)\//', $zone ) ) {
                if ( !isset( $timeZones[ 'aliases' ][ $zone ] ) )
                    $zones[] = $zone;
            }
        }

        return $zones;
    }

    /**
    * Return an array of associative arrays representing available time zones.
    */
    public function getTimeZonesAsTable()
    {
        $zones = $this->getAvailableTimeZones();

        $date = new DateTime();

        $result = array();
        foreach ( $zones as $zone ) {
            $date->setTimeZone( new DateTimeZone( $zone ) );
            $offset = $date->format( 'Z' );
            $result[] = array( 'zone_name' => $zone, 'zone_offset' => $offset );
        }

        return $result;
    }
}