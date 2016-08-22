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
            $this->quote = $this->random_quote();
            $this->set_font();
            $this->set_css();
        }


        public function get_quote() {
            echo $this->quote->quoteText;
        }

        public function get_author() {
            if( isset( $this->quote->quoteAuthor ) ) {
                echo $this->quote->quoteAuthor;
            } else {
                echo 'Anonymous';
            }
        }

        public function get_link( $type = false ) {
            echo $this->quote->quoteLink;
        }

        public function get_google_font() {
            $font_name = urlencode( $this->font );
            $text = urlencode( $this->get_unique_chars() );
            echo 'https://fonts.googleapis.com/css?family=' . $font_name . '&text=' . $text . '&subset=latin';
        }

        public function get_css() {
            echo $this->css;
        }


        public function get_quote_link( $type = false ) {
            switch ( $type ) {
                case 'colors':
                    echo 'http://' . $_SERVER['SERVER_NAME'] . '/src/?&font=' . urlencode( $this->font );
                    break;

                case 'font':
                    echo 'http://' . $_SERVER['SERVER_NAME'] . '/src/?bg=' . $this->bg . '&color=' . $this->color;
                    break;

                case 'quote':
                    echo 'http://' . $_SERVER['SERVER_NAME'] . '/src/?font=' . urlencode( $this->font ) . '&bg=' . $this->bg . '&color=' . $this->color;
                    break;

                default:
                    echo 'http://' . $_SERVER['SERVER_NAME'] . '/src/?font=' . urlencode( $this->font ) . '&bg=' . $this->bg . '&color=' . $this->color;
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
        protected function random_quote() {
            $quote = '';
            if ( isset( $_GET['key'] ) ) {
                $quote = $this->fetch_quote( urlencode( $_GET['key'] ) );
            } else {
                // TODO: Sometimes a NULL result is returned, figure out how to stop this.
                $quote = $this->fetch_quote();
            }

            $this->quote = $quote;
            return $this->quote;
        }

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

            if ( isset( $_GET['font'] ) ) {

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

        protected function set_css() {
            $a11y_stats = file_get_contents( 'http://www.randoma11y.com/stats/' );
            $a11y_stats = json_decode( $a11y_stats, true );
            $count = $a11y_stats['combos'];

            $a11y_color = file_get_contents('http://randoma11y.com/combos?page=' . rand( 1, $count ) . '&per_page=1' );
            $a11y_color = json_decode( $a11y_color, true );


            $first = $a11y_color[0];

            $this->bg = $first['color_one'];
            $this->color = $first['color_two'];

            $css = '
            <style>
                body {
                    background-color: ' . $this->bg . ';
                    color: ' . $this->color . '
                }
                .quote-text {
                    font-family: "' . $this->font . '";
                }

                .quote-link {
                    color: ' . $this->bg . ';
                    background-color: ' . $this->color . ';
                }

                .quote-link:hover {
                    color: ' . $this->color . ';
                    background-color: ' . $this->bg . ';
                }

                footer.footer-main {
                    background-color: rgba(0, 0, 0, 0.18);
                    color: ' . $this->color . ';
                }

                ::selection {
                    background: ' . $this->color . ';
                    text-shadow: none;
                    color: ' . $this->bg . ';
                }
            </style>';

            $this->css = $css;
        }
        
    }
