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

        $lines = explode( "\n", $text );

        $result = array();
        foreach ( $lines as $line ) {
            $nl = true;

            // extract balanced square brackets and backticks
            $tokens = preg_split( "/(\\[[^][]*\\]|`[^`]*`)/", $line, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY );

            foreach ( $tokens as $index => $token ) {
                $nl = true;

                $tag = null;

                // extract the contents of the square bracket
                if ( $token[ 0 ] == '[' && preg_match( "/^\\[(\\S*)(.*)\\]$/", $token, $matches ) ) {
                    $tag = $matches[ 1 ];
                    $extra = trim( $matches[ 2 ] );
                }

                if ( $state[ 'mode' ] == 'code' ) {
                    // ignore all block tags in a code block, but count nested [code] and [/code] tags
                    if ( $tag == 'code' ) {
                        $state[ 'nest' ]++;
                    } else if ( $tag == '/code' ) {
                        if ( --$state[ 'nest' ] == 0 ) {
                            $result[] = '</pre>';
                            $state = array_pop( $stack );
                            $nl = false;
                            continue;
                        }
                    }

                    // ignore any other formatting in a code block
                    $result[] = htmlspecialchars( $token );
                    continue;
                }

                // handle initial asterisks in a list block
                if ( $state[ 'mode' ] == 'list' && ( $index == 0 || $state[ 'nest' ] == 0 ) && preg_match( "/^\s*(\\*{1,6})\s(.*)/", $token, $matches ) ) {
                    $nest = strlen( $matches[ 1 ] );
                    $token = $matches[ 2 ];
                    
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

                    // check if anything remains to be processed
                    if ( $token == '' )
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
                    // fall through if not matching opening tag found; it will be handled by the generic tag case
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
                    $nl = false;
                    continue;
                }

                if ( $tag == 'quote' ) {
                    $stack[] = $state;
                    $state = array( 'mode' => 'quote' );
                    $result[] = '<div class="quote">';
                    if ( $extra != '' ) {
                        $title = htmlspecialchars( $extra );
                        if ( substr( $title, -1, 1 ) != ':' )
                            $title .= ':';
                        $result[] = "<div class=\"quote-title\">$title</div>";
                    }
                    $nl = false;
                    continue;
                }

                if ( $tag !== null ) {
                    $url = null;

                    if ( substr( $tag, 0, 1 ) == '#' ) {
                        $id = substr( $tag, 1 );
                        if ( (int)$id == $id )
                            $url = WI_BASE_URL . '/client/index.php?item=' . $id;
                    } else if ( substr_count( $tag, ':' ) > 0 ) {
                        // ensure a valid protocol to prevent JavaScript injection
                        if ( preg_match( "%^(?:mailto:|https?://|ftp://)%", $tag ) )
                            $url = $tag;
                    } else if ( substr_count( $tag, '@' ) > 0 ) {
                        if ( preg_match( "/^[^\\s@]*@[^\\s@]*$/", $tag ) )
                            $url = 'mailto:' . $tag;
                    } else if ( substr( $tag, 0, 4 ) == 'ftp.' ) {
                        $url = 'ftp://' . $tag;
                    } else if ( substr_count( $tag, '.' ) > 0 ) {
                        $url = 'http://' . $tag;
                    }

                    if ( $url != null ) {
                        $url = htmlspecialchars( $url );
                        $text = $extra != '' ? htmlspecialchars( $extra ) : htmlspecialchars( $tag );
                        $result[] = "<a href=\"$url\">$text</a>";
                        continue;
                    }

                    // display tag without further processing if URL is not valid
                    $result[] = htmlspecialchars( $token );
                    continue;
                }

                if ( $token[ 0 ] == '`' ) {
                    // display monotype text without further processing
                    $result[] = '<code>' . htmlspecialchars( substr( $token, 1, -1 ) ) . '</code>';
                    continue;
                }

                $tags = array();

                // extract sequences of asterisk and underscode characters
                $subtokens = preg_split( "/(\\*+|_+)/", $token, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY );

                foreach ( $subtokens as $subtoken ) {
                    $tag = null;

                    if ( $subtoken == '**' )
                        $tag = 'strong';
                    else if ( $subtoken == '__' )
                        $tag = 'em';

                    if ( $tag != null ) {
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
                    } else {
                        // handle implicit links in regular text
                        $result[] = System_Web_LinkLocator::convertToHtml( $subtoken );
                    }
                }

                // pop the remaining inline tags from the stack
                while ( !empty( $tags ) ) {
                    $tag = array_pop( $tags );
                    $result[] = "</$tag>";
                }
            }

            if ( $nl )
                $result[] = "\n";
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
