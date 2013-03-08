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
* Convert text with markup to HTML formatting.
*/
class System_Web_MarkupProcessor
{
    /**
    * Convert text with markup to HTML.
    * @param $text The text to convert.
    * @param $prettyPrint Set to true if pretty printing is used (output).
    * @return The HTML version of the text.
    */
    public static function convertToHtml( $text, &$prettyPrint )
    {
        $state = array( 'mode' => '' );
        $stack = array();

        // extract new lines and starting/closing block tags
        $tokens = preg_split( '/(\n|\[\/?(?:list|code|quote)(?:[ \t][^]\n]*)?\](?:[ \t]*\n)?)/ui', $text, -1, PREG_SPLIT_DELIM_CAPTURE );

        $result = array();
        foreach ( $tokens as $i => $token ) {
            if ( $i % 2 == 0 ) {
                if ( $token == '' )
                    continue;

                // ignore any formatting in a code block
                if ( $state[ 'mode' ] == 'code' ) {
                    $result[] = htmlspecialchars( $token );
                    continue;
                }

                // handle initial asterisks in a list block
                if ( $state[ 'mode' ] == 'list' && preg_match( '/^[ \t]*(\*{1,6})[ \t](.*)/', $token, $parts ) ) {
                    $nest = strlen( $parts[ 1 ] );
                    $token = $parts[ 2 ];
                    
                    if ( $state[ 'nest' ] == 0 )
                        $state[ 'nest' ] = 1;
                    else
                        $result[] = '</li>';
                    
                    if ( $nest > $state[ 'nest' ] )
                        $result[] = str_repeat( '<ul>', $nest - $state[ 'nest' ] );
                    else if ( $state[ 'nest' ] > $nest )
                        $result[] = str_repeat( '</ul>', $state[ 'nest' ] - $nest );
                    
                    $state[ 'nest' ] = $nest;
                    $result[] = '<li>';
                }

                // create an implicit list item
                if ( $state[ 'mode' ] == 'list' && $state[ 'nest' ] == 0 ) {
                    $state[ 'nest' ] = 1;
                    $result[] = '<li>';
                }

                $tags = array();

                // similar to System_Web_LinkLocator's automatic links, but simpler because we know the exact beginning and end of the link
                $mail = '(?:mailto:)?[\w.%+-]+@[\w.-]+\.[a-z]{2,4}';
                $url = '(?:(?:https?|ftp|file):\/\/|www\.|ftp\.|\\\\\\\\)[\w+&@#\/\\\\%=~|$?!:,.()-]+';
                $id = '#\d+';
                $link = "(?:$mail|$url|$id)";

                // extract inline formatting: bold, italic, monotype and hyperlink
                $subtokens = preg_split( '/(\*\*+|__+|`[^`]+`|\[' . $link . '(?:[ \t][^]]*)?\])/ui', $token, -1, PREG_SPLIT_DELIM_CAPTURE );

                foreach ( $subtokens as $j => $subtoken ) {
                    if ( $j % 2 == 0 ) {
                        // handle implicit links in regular text
                        $result[] = System_Web_LinkLocator::convertToHtml( $subtoken );
                        continue;
                    }

                    if ( $subtoken == '**' || $subtoken == '__' ) {
                        $tag = $subtoken == '**' ? 'strong' : 'em';

                        // find a matching opening tag
                        $key = array_search( $tag, $tags );
                        if ( $key === false ) {
                            $tags[] = $tag;
                            $result[] = "<$tag>";
                        } else {
                            while ( count( $tags ) > $key ) {
                                $tag = array_pop( $tags );
                                $result[] = "</$tag>";
                            }
                        }
                        continue;
                    }

                    if ( $subtoken[ 0 ] == '`' ) {
                        // display monotype text without further processing
                        $result[] = '<code>' . htmlspecialchars( substr( $subtoken, 1, -1 ) ) . '</code>';
                        continue;
                    }

                    if ( $subtoken[ 0 ] == '[' ) {
                        $index = strcspn( $subtoken, " \t]" );
                        $url = strtolower( substr( $subtoken, 1, $index - 1 ) );
                        $title = trim( substr( $subtoken, $index, strrpos( $subtoken, ']' ) - $index ), " \t" );

                        if ( $title == '' )
                            $title = $url;

                        if ( $url[ 0 ] == '#' )
                            $url = WI_BASE_URL . '/client/index.php?item=' . substr( $url, 1 );
                        else if ( strtolower( substr( $url, 0, 4 ) ) == 'www.' )
                            $url = 'http://' . $url;
                        else if ( strtolower( substr( $url, 0, 4 ) ) == 'ftp.' )
                            $url = 'ftp://' . $url;
                        else if ( substr( $url, 0, 2 ) == '\\\\' )
                            $url = 'file:///' . $url;
                        else if ( strpos( $url, ':' ) === false )
                            $url = 'mailto:' . $url;

                        $url = htmlspecialchars( $url );
                        $result[] = "<a href=\"$url\">" . htmlspecialchars( $title ) . '</a>';
                        continue;
                    }

                    $result[] = htmlspecialchars( $subtoken );
                }

                // pop the remaining inline tags from the stack
                while ( !empty( $tags ) ) {
                    $tag = array_pop( $tags );
                    $result[] = "</$tag>";
                }
                
                continue;
            }

            if ( $token[ 0 ] == '[' ) {
                $index = strcspn( $token, " \t]" );
                $tag = strtolower( substr( $token, 1, $index - 1 ) );
                $extra = trim( substr( $token, $index, strrpos( $token, ']' ) - $index ), " \t" );

                // ignore all block tags in a code block, but count nested [code] and [/code] tags
                if ( $state[ 'mode' ] == 'code' ) {
                    if ( $tag == 'code' ) {
                        $state[ 'nest' ]++;
                    } else if ( $tag == '/code' ) {
                        if ( --$state[ 'nest' ] == 0 ) {
                            $result[] = '</pre>';
                            $state = array_pop( $stack );
                            continue;
                        }
                    }
                    $result[] = htmlspecialchars( $token );
                    continue;
                }

                if ( $tag == '/list' || $tag == '/quote' ) {
                    // find a matching opening tag
                    $pop = 0;
                    if ( $tag == '/' . $state[ 'mode' ] ) {
                        $pop = 1;
                    } else {
                        for ( $i = count( $stack ) - 1; $i > 0; $i-- ) {
                            if ( $tag == '/' . $stack[ $i ][ 'mode' ] ) {
                                $pop = count( $stack ) - $i + 1;
                                break;
                            }
                        }
                    }
                    if ( $pop > 0 ) {
                        // pop the block tags from the stack
                        for ( $i = 0; $i < $pop; $i++ ) {
                            if ( $state[ 'mode' ] == 'list' )
                                $result[] = $state[ 'nest' ] == 0 ? '</ul>' : '</li>' . str_repeat( '</ul>', $state[ 'nest' ] );
                            else if ( $state[ 'mode' ] == 'code' )
                                $result[] = '</pre>';
                            else
                                $result[] = '</div>';
                            $state = array_pop( $stack );
                        }
                        $nl = false;
                        continue;
                    }
                    // fall through if not matching opening tag found; it will be emitted as-is
                }

                if ( $tag == 'list' ) {
                    $stack[] = $state;
                    $state = array( 'mode' => 'list', 'nest' => 0 );
                    $result[] = '<ul>';
                    $nl = false;
                    continue;
                }

                // create an implicit list item (this must be done after handling nested [list] and closing tags)
                if ( $state[ 'mode' ] == 'list' && $state[ 'nest' ] == 0 ) {
                    $state[ 'nest' ] = 1;
                    $result[] = '<li>';
                }

                if ( $tag == 'code' ) {
                    $stack[] = $state;
                    $state = array( 'mode' => 'code', 'nest' => 1 );
                    $classes = '';
                    if ( $extra != '' ) {
                        // enable pretty printing if a valid language is given
                        $lang = strtolower( $extra );
                        $langs = array( 'bash', 'c', 'c++', 'c#', 'css', 'html', 'java', 'javascript', 'js', 'perl', 'php', 'python', 'ruby', 'sh', 'sql', 'vb', 'xml' );
                        if ( array_search( $lang, $langs ) !== false ) {
                            $lang = str_replace( array( '+', '#' ), array( 'p', 's' ), $lang );
                            $classes = ' prettyprint lang-' . $lang;
                            $prettyPrint = true;
                        }
                    }
                    $result[] = '<pre class="code' . $classes . '">';
                    continue;
                }

                if ( $tag == 'quote' ) {
                    $stack[] = $state;
                    $state = array( 'mode' => 'quote' );
                    $result[] = '<div class="quote">';
                    if ( $extra != '' ) {
                        $title = System_Web_LinkLocator::convertToHtml( $extra );
                        if ( substr( $title, -1, 1 ) != ':' )
                            $title .= ':';
                        $result[] = '<div class="quote-title">' . $title . '</div>';
                    }
                    $nl = false;
                    continue;
                }
            }

            $result[] = htmlspecialchars( $token );
        }

        // pop the remaining block tags from the stack
        while ( !empty( $stack ) ) {
            if ( $state[ 'mode' ] == 'list' )
                $result[] = $state[ 'nest' ] == 0 ? '</ul>' : '</li>' . str_repeat( '</ul>', $state[ 'nest' ] );
            else if ( $state[ 'mode' ] == 'code' )
                $result[] = '</pre>';
            else
                $result[] = '</div>';
            $state = array_pop( $stack );
        }

        return implode( '', $result );
    }

    /**
    * Convert text with markup to HTML which can be passed to a view.
    * @param $text The text to convert.
    * @param $prettyPrint Set to true if pretty printing is used (output).
    * @return The HTML version of the text wrapped in System_Web_RawValue
    * to prevent escaping it twice.
    */
    public static function convertToRawHtml( $text, &$prettyPrint )
    {
        return new System_Web_RawValue( self::convertToHtml( $text, $prettyPrint ) );
    }
}
