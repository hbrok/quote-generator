<?php
    class WebSite {
        protected $font;
        protected $quote;
        protected $quoteChars;
        protected $css;
        protected $color_one;
        protected $color_two;

        public function __construct() {
            // Check for quote & author.
            if ( isset( $_GET['quote'] ) && isset( $_GET['author'] ) ) {
                $this->quote = $this->random_quote( $_GET['quote'], $_GET['author']);
            } else {
                $this->quote = $this->random_quote();
            }

            // Check for font.
            if ( isset( $_GET['font'] ) ) {
                $this->set_font( $_GET['font'] );
            } else {
                $this->set_font();
            }

            // Check for colors.
            if ( isset( $_GET['c1'] ) && isset( $_GET['c2'] ) ) {
                $this->set_css( $_GET['c1'], $_GET['c2'] );
            } else {
                $this->set_css();
            }
        }

        /**
         * Get hex code wihtout hash (#).
         * @param string $type Color.
         */
        public function get_hex( $type ) {
            switch ( $type ) {
                case 'color_one':
                    echo str_replace( '#', '', $this->color_one );
                    break;

                case 'color_two':
                    echo str_replace( '#', '', $this->color_two );
                    break;
            }
        }

        /**
         * Echoes font name.
         * @param bool $urlencode Whether echo a URL encoded font name.
         */
        public function get_font_name( $urlencode = false ) {
            if ( $urlencode ) {
                echo $this->font;
            } else {
                echo urlencode( $this->font );
            }
        }

        /**
         * Echoes quote text.
         */
        public function get_quote() {
            echo $this->quote->quoteText;
        }

        /**
         * Echoes author of quote.
         */
        public function get_author() {
            if( isset( $this->quote->quoteAuthor ) ) {
                echo $this->quote->quoteAuthor;
            } else {
                echo 'Anonymous';
            }
        }

        /**
         * Echoes link to quote on Forismatic.
         */
        public function get_link( $type = false ) {
            echo $this->quote->quoteLink;
        }

        /**
         * Echoes URL to Google font.
         */
        public function get_google_font() {
            echo 'https://fonts.googleapis.com/css?family=' .urlencode( $this->font ) . '&text=' . urlencode( $this->get_unique_chars() ) . '&subset=latin';
        }

        /**
         * Echos CSS with dynamic colors.
         */
        public function get_css() {
            echo $this->css;
        }

        /**
         * Returns a link with all parameters so you can come back to the same
         * quote. Can be passed a string which returns a link minus the part
         * that has been specified.
         *
         * E.g: 'colors' will return URL that will generate new colors, but
         * keep everything else the same.
         *
         * @param bool|string $type Part to switch out: 'colors', 'font', or 'quote'.
         */
        public function get_quote_link( $type = false ) {
            $base = 'http://' . $_SERVER['SERVER_NAME'] . '/src/';
            $colors = 'c1=' . $this->color_one . '&c2=' . $this->color_two;
            $quote = 'quote=' . urlencode( $this->quote->quoteText ) . '&author=' . urlencode( $this->quote->quoteAuthor );
            $font = 'font=' . urlencode( $this->font );

            switch ( $type ) {
                // New colors.
                case 'colors':
                    echo $base . '?' . $font . '&' . $quote;
                    break;

                // New font.
                case 'font':
                    echo $base . '?' . $colors . '&' . $quote;
                    break;

                // New quote.
                case 'quote':
                    echo $base . '?' . $font . '&' . $colors;
                    break;

                // All params.
                default:
                    echo $base . '?' . $font . '&' . $colors . '&' . $quote;
                    break;
            }

        }

        /**
         * Gets the unqiue characters in teh quoteText so we can request a
         * subset of a font from Google.
         *
         * @return string $chars All unqiue chars in a string that can be passed to google fonts api.
         */
        protected function get_unique_chars() {
            $chars = array_unique( str_split( $this->quote->quoteText ) );
            $chars = implode( '', $chars );
            return $chars;
        }

        /**
         * Gets random quote from forismatic.com API.
         * @param bool|string $quoteText Quote text.
         * @param bool|string $quoteAuthor Quote author.
         */
        protected function random_quote( $quoteText = false, $quoteAuthor = false ) {
            if ( $quoteText && $quoteAuthor ) {
                $quote = new stdClass();
                $quote->quoteText = urldecode( $quoteText );
                $quote->quoteAuthor = urldecode( $quoteAuthor );
                $this->quote = $quote;
                return $this->quote;
            } else {
                // TODO: Sometimes a NULL result is returned, figure out how to stop this.
                $context_options = array(
                    'ssl' => array(
                        'verify_peer'      => false,
                        'verify_peer_name' => false,
                    ),
                );

                // Get random quote from Forismatic API.
                $url = 'http://api.forismatic.com/api/1.0/?method=getQuote&lang=en&format=json';
                $results = file_get_contents( $url, false, stream_context_create( $context_options ) );
                $results = json_decode( $results );

                // var_dump( $results->quoteAuthor );
                // var_dump( isset( $results->quoteAuthor ) );
                // echo '<br>';
                // var_dump( $results->quoteText );
                // var_dump( isset( $results->quoteText ) );

                if ( isset( $results->quoteAuthor ) ) {
                    // Reuturn results if successful.
                    $this->quote = $results;
                    return $this->quote;
                } else {
                    // If author or quote is empty, try again.
                    return $this->random_quote();
                }
            }
        }

        protected function set_font( $font_name = false ) {
            // This is hacky, so probably shouldn't do it like this.
            $context_options = array(
                'ssl' => array(
                    'verify_peer'      => false,
                    'verify_peer_name' => false,
                ),
            );

            // $url = 'https://www.googleapis.com/webfonts/v1/webfonts?key=AIzaSyD02NoBU2DFMfsCIXMq_Rrt9SvO7a-6xNg';
            $url = 'font-list.json';

            $fonts = file_get_contents( $url, false, stream_context_create( $context_options ) );
            $fonts = json_decode( $fonts, true );
            $fonts_list = $fonts['items'];

            if ( $font_name ) {
                foreach ( $fonts_list as $font) {
                    if ( $font['family'] === urldecode( $_GET['font'] ) ) {
                        $this->font = $font_name;
                        break;
                    }
                }
            } else {
                $rand = rand( 0, count( $fonts_list ) );

                $font_name = $fonts_list[$rand]['family'];

                $this->font = $font_name;
            }


            return $font_name;
        }


        protected function set_css( $color_one = false, $color_two = false ) {
            if ( $color_one && $color_two ) {
                // Get specific colors.
                $this->color_one = $color_one;
                $this->color_two = $color_two;
            } else {
                // This is hacky, so probably shouldn't do it like this.
                $arrContextOptions = array(
                    "ssl" => array(
                        "verify_peer" => false,
                        "verify_peer_name" => false,
                    ),
                );

                // Get random colors.
                $url = 'http://www.randoma11y.com/stats/';
                $a11y_stats = file_get_contents( $url, false, stream_context_create( $arrContextOptions ) );
                $a11y_stats = json_decode( $a11y_stats, true );
                $count = $a11y_stats['combos'];

                $color_index = rand( 1, $count );


                $url = 'http://randoma11y.com/combos?page=' . $color_index . '&per_page=1';
                $a11y_color = file_get_contents( $url, false, stream_context_create( $arrContextOptions ) );
                $a11y_color = json_decode( $a11y_color, true );

                // var_dump( $a11y_color );

                $first = $a11y_color[0];

                $this->color_id = $first['id'];
                // var_dump($this->color_id);

                $this->color_one = str_replace( '#', '', $first['color_one'] );
                $this->color_two = str_replace( '#', '', $first['color_two'] );
            }

            $css = '
            <style>
                .quote-text {
                    font-family: "' . $this->font . '";
                }

                a:hover,
                .quote-link,
                ::selection,
                .button,
                .button.hollow:hover {
                    color: #' . $this->color_one . ';
                }

                body,
                .quote-link:hover {
                    background-color: #' . $this->color_one . ';
                }

                body,
                a,
                .quote-link:hover,
                .button:hover,
                .button.hollow,
                .footer {
                    color: #' . $this->color_two. ';
                }

                body,
                .actions,
                .footer,
                .button:hover,
                .button.hollow {
                    border-color: #' . $this->color_two. ';
                }

                a:hover,
                .quote-link,
                ::selection,
                .button,
                .button.hollow:hover {
                    background-color: #' . $this->color_two. ';
                }
            </style>';

            $this->css = $css;
        }

    }
