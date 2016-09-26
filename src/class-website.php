<?php

class WebSite {
	public $font;
	public $color_one;
	public $color_two;

	public $quote;

	public $quoteLink;

	public function __construct() {
		// TODO: Move $_GET stuff to function.
		// Rename function to getQuote... it's not always getting a random quote.
		if ( isset( $_GET['quote'] ) && isset( $_GET['author'] ) && isset( $_GET['id'] ) ) {
			$this->quote = $this->random_quote( $_GET['quote'], $_GET['author'], $_GET['id'] );
		} else {
			$this->quote = $this->random_quote();
		}

		// Set font from GET variable, or get a random font.
//		$this->set_font();
		if ( isset( $_GET['font'] ) ) {
			$this->set_font( $_GET['font'] );
		} else {
			$this->set_font();
		}

		// TODO: Move $_GET stuff to the function.
		// Rename function??
		if ( isset( $_GET['bg'] ) && isset( $_GET['fg'] ) ) {
			$this->set_css( $_GET['fg'], $_GET['bg'] );
		} else {
			$this->set_css();
		}

		$this->get_quote_link();
	}

	/**
	 * Get HEX codes without #.
	 *
	 * @param string $type Which color to get: 'bg' or 'color'.
	 */
	public function get_hex( $type ) {
		if ( $type === 'bg' ) {
			echo str_replace( '#', '', $this->color_one );
		} elseif ( $type === 'color' ) {
			echo str_replace( '#', '', $this->color_two );
		}
	}

	/**
	 * Get quote author.
	 */
	public function get_author() {
		if ( isset( $this->quote->quoteAuthor ) ) {
			echo $this->quote->quoteAuthor;
		} else {
			echo 'Anonymous';
		}
	}

	/**
	 * Get a link back to the same quote with colors/font/quote set.
	 */
	public function get_quote_link() {
		$base   = 'http://' . $_SERVER['SERVER_NAME'] . '/src/';
		$colors = 'bg=' . $this->color_one . '&fg=' . $this->color_two;
		$quote  = 'quote=' . urlencode( $this->quote->quoteText ) . '&author=' . urlencode( $this->quote->quoteAuthor );
		$font   = 'font=' . urlencode( $this->font );

		// Get the 10 char id, so we can link to the Forismatic URL.
		$id = 'id=' . substr( $this->quote->quoteLink, count( $this->quote->quoteLink ) - 12, 10 );

		$this->quoteLink['colors'] = $base . '?' . $font . '&' . $quote;
		$this->quoteLink['font']   = $base . '?' . $colors . '&' . $quote;
		$this->quoteLink['quote']  = $base . '?' . $font . '&' . $colors . '&' . $id;
		$this->quoteLink['all']    = $base . '?' . $font . '&' . $colors . '&' . $quote . '&' . $id;
	}

	/**
	 * Gets the unique characters in a quote so we can load a subset of the font.
	 *
	 * @return string $chars All unique chars in a string that can be passed to google fonts api.
	 */
	public function unique_chars() {
		$chars = array_unique( str_split( $this->quote->quoteText ) );
		$chars = implode( '', $chars );
		return $chars;
	}

	/**
	 * Gets random quote from forismatic.com API, or a specific quote if text,
	 * author, and link are set.
	 *
	 * @param bool|string $quoteText Quote text.
	 * @param bool|string $quoteAuthor Quote author.
	 * @param bool|string $quoteLink Quote link.
	 *
	 * @return array|mixed|stdClass
	 */
	protected function random_quote( $quoteText = false, $quoteAuthor = false, $quoteLink = false ) {
		if ( $quoteText && $quoteAuthor && $quoteLink ) {
			$quote              = new stdClass();
			$quote->quoteText   = urldecode( $quoteText );
			$quote->quoteAuthor = urldecode( $quoteAuthor );
			$quote->quoteLink   = urldecode( $quoteLink );
			$this->quote        = $quote;
			return $this->quote;
		} else {
			// FIXME: Sometimes a NULL result is returned, figure out how to stop this.
			$quote       = $this->fetch_quote();
			$this->quote = $quote;
			return $this->quote;
		}
	}

	/**
	 * Connect to forismatic.com and get a new random quote.
	 */
	public function fetch_quote() {
		$url     = 'http://api.forismatic.com/api/1.0/?method=getQuote&lang=en&format=json';
		$results = $this->getJson( $url, false );

		return $results;
	}

	/**
	 * Get a list of all available Google fonts.
	 * @param bool $new_font
	 *
	 * @return bool
	 */
	protected function set_font( $new_font = false ) {
		$url = 'https://www.googleapis.com/webfonts/v1/webfonts?key=AIzaSyD02NoBU2DFMfsCIXMq_Rrt9SvO7a-6xNg';
		$fonts = $this->getJson( $url );

		$fonts_list = $fonts['items'];

		// Check if $new_font is an existing font, and set it as the global font if so.
		if ( $new_font ) {
			foreach ( $fonts_list as $font ) {
				if ( $font['family'] === $new_font ) {
					$this->font = $font['family'];

					return $this->font;
				}
			}
		}

		// Get random font.
		$rand       = rand( 0, count( $fonts_list ) );
		$this->font = $fonts_list[ $rand ]['family'];

		return $this->font;
	}

	/**
	 *
	 * @param bool|string $foreground Color one.
	 * @param bool|string $background Color two.
	 */
	protected function set_css( $foreground = false, $background = false ) {
		if ( $foreground && $background ) {
			// Get specific colors.
			$this->color_one = $background;
			$this->color_two = $foreground;
		} else {
			// Get random colors.
			$url        = 'http://www.randoma11y.com/stats/';
			$a11y_stats = $this->getJson( $url );
			$count      = $a11y_stats['combos'];
			$color_index = rand( 1, $count );

			$url        = 'http://randoma11y.com/combos?page=' . $color_index . '&per_page=1';
			$a11y_color = $this->getJson( $url, false );

			$first = $a11y_color[0];

			$this->color_id = $first['id'];

			$this->color_one = str_replace( '#', '', $first['color_one'] );
			$this->color_two = str_replace( '#', '', $first['color_two'] );
		}
	}


	/**
	 * Check if a cached version of this data already exists, and get data
	 * from that is so.
	 *
	 * @link http://stackoverflow.com/questions/11407514/caching-json-output-in-php
	 *
	 * @param string $url URL to get data from.
	 * @param bool $cache If data should be cached.
	 *
	 * @return mixed|array Array of JSON data.
	 */
	public function getJson( $url, $cache = true ) {
		date_default_timezone_set('America/Chicago');
		// cache files are created like cache/abcdef123456...
		$cacheFile = getcwd() . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . md5( $url );

		// Check if file exists.
		if ( $cache && file_exists( $cacheFile ) ) {
//			echo 'File exists.';
			$fh        = fopen( $cacheFile, 'r' );

			// if data was cached recently, return cached data
			if ( filemtime( $cacheFile ) > strtotime( '-60 minutes' ) ) {
				return json_decode( fread( $fh, filesize($cacheFile) ), true );
			}

			// else delete cache file
			fclose( $fh );
			unlink( $cacheFile );
		}

//		echo 'Get new data.';


		// If no cached file, or cached file was too old, get data as normal.
		$options = array(
			'ssl' => array(
				'verify_peer'      => false,
				'verify_peer_name' => false,
			),
		);

		$json = file_get_contents( $url, false, stream_context_create( $options ) );

		if ( $cache ) {
			// Save new data.
			$fh = fopen( $cacheFile, 'w' );
			fwrite( $fh, $json );
			fclose( $fh );
		}


		return json_decode( $json, true );
	}
}
