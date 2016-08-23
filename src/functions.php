<?php
    class WebSite {
        protected $font;
        protected $quote;
        protected $quoteChars;
        protected $css;
        protected $bg;
        protected $color;
        protected $color_id;


        public function __construct() {
            // $this->set_font();
            if ( isset( $_GET['quote'] ) && isset( $_GET['author'] ) ) {
                // echo 'QUOTE: ' . $_GET['quote'] . '<br>';
                // echo 'AUTHOR: ' . $_GET['author'] . '<br>';
                $this->quote = $this->random_quote( $_GET['quote'], $_GET['author']);
            } else {
                $this->quote = $this->random_quote();
            }

            if ( isset( $_GET['font'] ) ) {
                // echo 'FONT: ' . $_GET['font'] . '<br>';
                $this->set_font( $_GET['font'] );
            } else {
                $this->set_font();
            }

            if ( isset( $_GET['bg'] ) && isset( $_GET['fg'] ) ) {
                // echo 'FG: ' . $_GET['fg'] . '<br>';
                // echo 'BG: ' . $_GET['bg'] . '<br>';
                $this->set_css( $_GET['fg'], $_GET['bg'] );
            } else {
                $this->set_css();
            }
        }

        /**
         * Echoes HEX code without #.
         * @param string $type Which color to get: 'bg' or 'color'.
         */
        public function get_hex( $type ) {
            // var_dump($type);
            if ( $type === 'bg' ) {
                // var_dump( $this->bg);
                echo str_replace( '#', '', $this->bg );
            } elseif ( $type === 'color' ) {
                echo str_replace( '#', '', $this->color );
            }
        }

        /**
         * Echoes font name.
         */
        public function get_font_name() {
            echo $this->font;
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
            $font_name = urlencode( $this->font );
            $text = urlencode( $this->get_unique_chars() );
            echo 'https://fonts.googleapis.com/css?family=' . $font_name . '&text=' . $text . '&subset=latin';
        }

        /**
         * Echos CSS with all colors.
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
            $colors = 'bg=' . $this->bg . '&fg=' . $this->color;
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
         * Gets the unqiue characters in a quote so only characters that are
         * needed will be in the google font.
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
                $quote = $this->fetch_quote();
                $this->quote = $quote;
                return $this->quote;
            }
        }

        /**
         * Connects to forismatic.com and gets a new random quote.
         */
        protected function fetch_quote() {
            $arrContextOptions = array(
                "ssl" => array(
                    "verify_peer" => false,
                    "verify_peer_name" => false,
                ),
            );

            // $url = 'https://www.googleapis.com/webfonts/v1/webfonts?key=AIzaSyD02NoBU2DFMfsCIXMq_Rrt9SvO7a-6xNg';
            // $url = 'http://en.wikiquote.org/w/api.php?format=php&action=query&titles=HAPPINESS';
            $url = 'http://api.forismatic.com/api/1.0/?method=getQuote&lang=en&format=json';

            $results = file_get_contents( $url, false, stream_context_create( $arrContextOptions ) );
            $results = json_decode( $results );

            return $results;
        }

        protected function set_font( $font_name = false ) {
            // This is hacky, so probably shouldn't do it like this.
            $arrContextOptions = array(
                "ssl" => array(
                    "verify_peer" => false,
                    "verify_peer_name" => false,
                ),
            );

            // $url = 'https://www.googleapis.com/webfonts/v1/webfonts?key=AIzaSyD02NoBU2DFMfsCIXMq_Rrt9SvO7a-6xNg';
            $url = 'font-list.json';

            $fonts = file_get_contents( $url, false, stream_context_create( $arrContextOptions ) );
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


        protected function set_css( $foreground = false, $background = false ) {
            if ( $foreground && $background ) {
                // Get specific colors.
                $this->bg = $background;
                $this->color = $foreground;
            } else {
                // Get random colors.
                $a11y_stats = file_get_contents( 'http://www.randoma11y.com/stats/' );
                $a11y_stats = json_decode( $a11y_stats, true );
                $count = $a11y_stats['combos'];

                $color_index = rand( 1, $count );

                $a11y_color = file_get_contents('http://randoma11y.com/combos?page=' . $color_index . '&per_page=1' );
                $a11y_color = json_decode( $a11y_color, true );

                // var_dump( $a11y_color );

                $first = $a11y_color[0];

                $this->color_id = $first['id'];
                // var_dump($this->color_id);

                $this->bg = str_replace( '#', '', $first['color_one'] );
                $this->color = str_replace( '#', '', $first['color_two'] );
            }

            $css = '
            <style>
                body {
                    background-color: #' . $this->bg . ';
                    color: #' . $this->color . '
                }
                .quote-text {
                    font-family: "' . $this->font . '";
                }

                .quote-link {
                    color: #' . $this->bg . ';
                    background-color: #' . $this->color . ';
                }

                .quote-link:hover {
                    color: #' . $this->color . ';
                    background-color: #' . $this->bg . ';
                }

                footer.footer-main {
                    background-color: rgba(0, 0, 0, 0.18);
                    color: #' . $this->color . ';
                }

                ::selection {
                    background: #' . $this->color . ';
                    text-shadow: none;
                    color: #' . $this->bg . ';
                }

                footer.footer-main {
                    background: #' . $this->color . ';
                    color: #' . $this->bg . ';
                }
            </style>';

            $this->css = $css;
        }

    }
