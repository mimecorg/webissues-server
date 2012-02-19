<?php
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

if ( !defined( 'WI_VERSION' ) ) die( -1 );

/**
* Helper methods for converting URLs into clickable links.
*
* The following items are recognized as links:
*  - email addresses
*  - URLs starting with mailto:, http://, https:// and ftp://
*  - URLs without protocol starting with www. and ftp.
*  - links to issues, comments or attachments starting with #
*
* Also the special HTML characters are converted to entities.
*/
class System_Web_LinkLocator
{
    /**
    * Convert text with links to HTML.
    * @param $text The plain text to convert.
    * @param $maxLength Optional maximum length of the text.
    * @return The HTML version of the text.
    */
    public static function convertToHtml( $text, $maxLength = null )
    {
        $mail = "\\b\\w[^\\s@]*@[^\\s@]*\\w";
        $url = "\\b(?:mailto:|https?://|ftp://|www\\.|ftp\\.)\\S*[\\w/]";
        $pattern = "%($mail|$url|#\\d+\\b)%";

        $matches = preg_split( $pattern, $text, -1, PREG_SPLIT_DELIM_CAPTURE );

        $result = array();
        for ( $i = 0; $i < count( $matches ); $i++ ) {
            $match = htmlspecialchars( $matches[ $i ] );
            if ( $i % 2 == 0 ) {
                if ( $maxLength !== null ) {
                    $length = mb_strlen( $match );
                    if ( $length > $maxLength - 3 ) {
                        if ( $maxLength > 3 )
                            $result[] = mb_substr( $match, 0, $maxLength - 3 );
                        $result[] = '...';
                        break;
                    }
                    $maxLength -= $length;
                }
                $result[] = $match;
            } else {
                if ( $match[ 0 ] == '#' )
                    $url = WI_BASE_URL . '/client/index.php?item=' . substr( $match, 1 );
                else if ( substr( $match, 0, 4 ) == 'www.' )
                    $url = 'http://' . $match;
                else if ( substr( $match, 0, 4 ) == 'ftp.' )
                    $url = 'ftp://' . $match;
                else if ( substr_count( $match, '@' ) == 1 && preg_match( "/^[^\\s@]*@[^\\s@]*$/", $match ) )
                    $url = 'mailto:' . $match;
                else
                    $url = $match;
                if ( $maxLength !== null ) {
                    $length = mb_strlen( $match );
                    if ( $length > $maxLength - 3 ) {
                        if ( $maxLength > 3 )
                            $result[] = "<a href=\"$url\">" . mb_substr( $match, 0, $maxLength - 3 ) . '...</a>';
                        else
                            $result[] = '...';
                        break;
                    }
                    $maxLength -= $length;
                }
                $result[] = "<a href=\"$url\">$match</a>";
            }
        }

        return implode( '', $result );
    }

    /**
    * Convert text with links to HTML which can be passed to a view.
    * @param $text The plain text to convert.
    * @return The HTML version of the text wrapped in System_Web_RawValue
    * to prevent escaping it twice.
    */
    public static function convertToRawHtml( $text )
    {
        return new System_Web_RawValue( self::convertToHtml( $text ) );
    }

    /**
    * Convert text with links to HTML and truncate it if it is too long.
    * A tooltip containing the original text is created if text is truncated.
    * @param $text The plain text to convert.
    * @param $maxLength The maximum length of the text.
    * @return The HTML version of the text wrapped in System_Web_RawValue.
    */
    public static function convertAndTruncate( $text, $maxLength )
    {
        if ( mb_strlen( $text ) > $maxLength ) {
            $toolTip = htmlspecialchars( $text );
            $truncated = self::convertToHtml( $text, $maxLength );
            return new System_Web_RawValue( "<span title=\"$toolTip\">$truncated</span>" );
        }

        return self::convertToRawHtml( $text );
    }
}
