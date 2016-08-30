<?php
class WebSite {
    public $font;
    public $color_one;
    public $color_two;

    public $quote;

    public $quoteLink;

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

        $this->get_quote_link();
    }

    /**
     * Echoes HEX code without #.
     * @param string $type Which color to get: 'bg' or 'color'.
     */
    public function get_hex( $type ) {
        // var_dump($type);
        if ( $type === 'bg' ) {
            // var_dump( $this->color_one);
            echo str_replace( '#', '', $this->color_one );
        } elseif ( $type === 'color' ) {
            echo str_replace( '#', '', $this->color_two);
        }
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
    public function get_quote_link() {
        $base = 'http://' . $_SERVER['SERVER_NAME'] . '/src/';
        $colors = 'bg=' . $this->color_one . '&fg=' . $this->color_two;
        $quote = 'quote=' . urlencode( $this->quote->quoteText ) . '&author=' . urlencode( $this->quote->quoteAuthor );
        $font = 'font=' . urlencode( $this->font );

        $this->quoteLink['colors'] = $base . '?' . $font . '&' . $quote;
        $this->quoteLink['font'] = $base . '?' . $colors . '&' . $quote;
        $this->quoteLink['quote'] = $base . '?' . $font . '&' . $colors;
        $this->quoteLink['all'] = $base . '?' . $font . '&' . $colors . '&' . $quote;
    }

    /**
     * Gets the unqiue characters in a quote so only characters that are
     * needed will be in the google font.
     *
     * @return string $chars All unqiue chars in a string that can be passed to google fonts api.
     */
    public function unique_chars() {
        $chars = array_unique( str_split( $this->quote->quoteText ) );
        $chars = implode( '', $chars );
        // $this->quoteChars = $chars;
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

    protected function set_font( $font = false ) {
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

        if ( $font ) {

            foreach ( $fonts_list as $font) {
                if ( $font['family'] === urldecode( $_GET['font'] ) ) {
                    $this->font = $font['family'];
                    // var_dump($font);
                    break;
                }
            }
        } else {
            $rand = rand( 0, count( $fonts_list ) );

            $font = $fonts_list[$rand]['family'];

            $this->font = $font;
        }


        return $font;
    }


    protected function set_css( $foreground = false, $background = false ) {
        if ( $foreground && $background ) {
            // Get specific colors.
            $this->color_one = $background;
            $this->color_two= $foreground;
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
            $a11y_stats = file_get_contents( $url, false, stream_context_create( $arrContextOptions ));
            $a11y_stats = json_decode( $a11y_stats, true );
            $count = $a11y_stats['combos'];

            $color_index = rand( 1, $count );

            $url = 'http://randoma11y.com/combos?page=' . $color_index . '&per_page=1';
            $a11y_color = file_get_contents( $url, false, stream_context_create( $arrContextOptions ));
            $a11y_color = json_decode( $a11y_color, true );

            $first = $a11y_color[0];

            $this->color_id = $first['id'];

            $this->color_one = str_replace( '#', '', $first['color_one'] );
            $this->color_two = str_replace( '#', '', $first['color_two'] );
        }
    }


}
