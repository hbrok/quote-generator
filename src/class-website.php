<?php

class WebSite {
	public $font;
	public $color_one;
	public $color_two;

	public $quote;

	public $quoteLink;

	public function __construct() {
//		$url = 'https://www.googleapis.com/webfonts/v1/webfonts?key=AIzaSyD02NoBU2DFMfsCIXMq_Rrt9SvO7a-6xNg';
//		var_dump( $this->getJson( $url ) );


		// TODO: Move $_GET stuff to function.
		// Rename function to getQuote... it's not always getting a random quote.
		if ( isset( $_GET['quote'] ) && isset( $_GET['author'] ) && isset( $_GET['id'] ) ) {
			$this->quote = $this->random_quote( $_GET['quote'], $_GET['author'], $_GET['id'] );
		} else {
			$this->quote = $this->random_quote();
		}

		// Set font from GET variable, or get a random font.
		$this->set_font();
//        var_dump( $this->font );


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
	 * Echoes HEX code without #.
	 *
	 * @param string $type Which color to get: 'bg' or 'color'.
	 */
	public function get_hex( $type ) {
		// var_dump($type);
		if ( $type === 'bg' ) {
			// var_dump( $this->color_one);
			echo str_replace( '#', '', $this->color_one );
		} elseif ( $type === 'color' ) {
			echo str_replace( '#', '', $this->color_two );
		}
	}

	/**
	 * Echoes author of quote.
	 */
	public function get_author() {
		if ( isset( $this->quote->quoteAuthor ) ) {
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
		$base   = 'http://' . $_SERVER['SERVER_NAME'] . '/src/';
		$colors = 'bg=' . $this->color_one . '&fg=' . $this->color_two;
		$quote  = 'quote=' . urlencode( $this->quote->quoteText ) . '&author=' . urlencode( $this->quote->quoteAuthor );
		$font   = 'font=' . urlencode( $this->font );

		// Get the 10 char id, so we can link to the source URL, if the quote text/author is set.
		$id = 'id=' . substr( $this->quote->quoteLink, count( $this->quote->quoteLink ) - 12, 10 );

		$this->quoteLink['colors'] = $base . '?' . $font . '&' . $quote;
		$this->quoteLink['font']   = $base . '?' . $colors . '&' . $quote;
		$this->quoteLink['quote']  = $base . '?' . $font . '&' . $colors . '&' . $id;
		$this->quoteLink['all']    = $base . '?' . $font . '&' . $colors . '&' . $quote . '&' . $id;
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
	protected function random_quote( $quoteText = false, $quoteAuthor = false, $quoteLink = false ) {
		if ( $quoteText && $quoteAuthor && $quoteLink ) {
//        if ( $quoteText && $quoteAuthor && $quoteLink ) {
			$quote              = new stdClass();
			$quote->quoteText   = urldecode( $quoteText );
			$quote->quoteAuthor = urldecode( $quoteAuthor );
			$quote->quoteLink   = urldecode( $quoteLink );
			$this->quote        = $quote;

			return $this->quote;
		} else {
			// TODO: Sometimes a NULL result is returned, figure out how to stop this.
			$quote       = $this->fetch_quote();
			$this->quote = $quote;

			return $this->quote;
		}
	}

	/**
	 * Connects to forismatic.com and gets a new random quote.
	 */
	public function fetch_quote() {
		$url = 'http://api.forismatic.com/api/1.0/?method=getQuote&lang=en&format=json';

		$results = $this->getJson( $url, false );

		return $results;
	}

	protected function set_font() {
		 $url = 'https://www.googleapis.com/webfonts/v1/webfonts?key=AIzaSyD02NoBU2DFMfsCIXMq_Rrt9SvO7a-6xNg';
		$fonts = $this->getJson( $url );

		$fonts_list = $fonts['items'];

		if ( isset( $_GET['font'] ) ) {
			foreach ( $fonts_list as $font ) {
				if ( $font['family'] === urldecode( $_GET['font'] ) ) {
					$this->font = $font['family'];
				}
			}
		} else {
			$rand       = rand( 0, count( $fonts_list ) );
			$this->font = $fonts_list[ $rand ]['family'];
		}

		return $this->font;
	}


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
