<?php

use Colors\RandomColor;
use ColorContrast\ColorContrast;

class WebSite {
	public $font;
	public $background_color;
	public $foreground_color;
	public $quote;
	public $quoteLink;
	public $shareQuoteLink;

	public function __construct() {
		/*
		 * Check quote params.
		 */
		if ( isset( $_GET['quote'] ) && isset( $_GET['author'] ) && isset( $_GET['id'] ) ) {
			$this->quote = $this->get_new_quote( $_GET['quote'], $_GET['author'], $_GET['id'] );
		} else {
			$this->quote = $this->get_new_quote();
		}

		/*
		 * Check if font is set, or if we must get a new one.
		 */
		if ( isset( $_GET['font'] ) ) {
			$this->get_new_font( $_GET['font'] );
		} else {
			$this->get_new_font();
		}

		/*
		 * Check if colors are set.
		 */
		if ( isset( $_GET['bg'] ) && isset( $_GET['fg'] ) ) {
			$this->get_new_colors( $_GET['bg'], $_GET['fg'] );
		} else {
			$this->get_new_colors();
		}

		$this->get_quote_link();
	}

	/**
	 * Get a link back to the same quote with colors/font/quote set.
	 */
	public function get_quote_link() {
		$base   = 'http://' . $_SERVER['HTTP_HOST'] . '/';
		$colors = 'bg=' . $this->background_color . '&fg=' . $this->foreground_color;
		$quote  = 'quote=' . urlencode( $this->quote->quoteText ) . '&author=' . urlencode( $this->quote->quoteAuthor );
		$font   = 'font=' . urlencode( $this->font );

		// Get the 10 char id, so we can link to the Forismatic URL.
		$id = 'id=' . substr( $this->quote->quoteLink, -11, 10 );

		$this->quoteLink['colors'] = $base . '?' . $font . '&' . $quote;
		$this->quoteLink['font']   = $base . '?' . $colors . '&' . $quote;
		$this->quoteLink['quote']  = $base . '?' . $font . '&' . $colors . '&' . $id;
		$this->quoteLink['all']    = $base . '?' . $quote . '&' . $font . '&' . $colors . '&' . $id;

		$this->shareQuoteLink = $this->quoteLink['all'];
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
	protected function get_new_quote( $quoteText = false, $quoteAuthor = false, $quoteLink = false ) {
		if ( $quoteText && $quoteAuthor && $quoteLink ) {
			$quote              = new stdClass();
			$quote->quoteText   = urldecode( $quoteText );
			$quote->quoteAuthor = urldecode( $quoteAuthor );
			$quote->quoteLink   = 'http://forismatic.com/en/' . urldecode( $quoteLink );
			$this->quote        = $quote;
			return $this->quote;
		} else {
			$quote       = $this->fetch_quote();
			$this->quote = $quote;

			// Sometimes the quote is empty, if the quote is empty, try again.
			if ( $this->quote === null || $this->quote->quoteAuthor === null || $this->quote->quoteText === null ) {
				$this->get_new_quote();
			}

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
	 * @param string|bool $new_font Name of font to load.
	 *
	 * @return bool
	 */
	protected function get_new_font( $new_font = false ) {
		// Get list of available fonts.
		$url = 'https://www.googleapis.com/webfonts/v1/webfonts?key=AIzaSyD02NoBU2DFMfsCIXMq_Rrt9SvO7a-6xNg';
		$fonts = $this->getJson( $url );
		$fonts_list = $fonts->items;

		// If $new_font is set
		if ( $new_font ) {
			foreach ( $fonts_list as $font ) {
				if ( $font->family === $new_font ) {
					$this->font = $font->family;

					return $this->font;
				}
			}
		}

		// Get random font.
		$rand       = rand( 0, count( $fonts_list ) );
		$this->font = $fonts_list[ $rand ]->family;

		return $this->font;
	}

	/**
	 * Fetch colors to use.
	 *
	 * @param bool|string $background_color
	 * @param bool|string $foreground_color
	 */
	protected function get_new_colors( $background_color = false, $foreground_color = false  ) {
		if ( $foreground_color && $background_color ) {
			// Get specific colors.
			$this->background_color = $background_color;
			$this->foreground_color = $foreground_color;
		} else {
			$colors = $this->generate_colors();
			
			$this->background_color = $colors[0];
			$this->foreground_color = $colors[1];
		}
	}

	/**
	 * Generate an accessible color pair.
	 * 
	 * @return string[] Hex codes without #
	 */
	public function generate_colors() {
		$contrast = new ColorContrast();
		$combinations = $contrast->getCombinations( ColorContrast::MIN_CONTRAST_AA );

		while ( ! $combinations ) {
			$contrast->addColors( RandomColor::one(), RandomColor::one() );
			$combinations = $contrast->getCombinations( ColorContrast::MIN_CONTRAST_AA );
		}

		return [
			(string) $combinations[0]->getForeground(),
			(string) $combinations[0]->getBackground(),
		];
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

		// Cache files are stored in /data.
		$cacheFile = getcwd() . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . md5( $url );

		// Check if file exists.
		if ( $cache && file_exists( $cacheFile ) ) {
			$fh = fopen( $cacheFile, 'r' );

			// If file has been updated within the last hour, return cached version.
			if ( filemtime( $cacheFile ) > strtotime( '-60 minutes' ) ) {
				return json_decode( fread( $fh, filesize($cacheFile) ), false );
			}

			// Else, delete the file and get new data.
			fclose( $fh );
			unlink( $cacheFile );
		}

		// Get new data from url.
		$options = array(
			'ssl' => array(
				'verify_peer'      => false,
				'verify_peer_name' => false,
			),
		);

		$json = file_get_contents( $url, false, stream_context_create( $options ) );

		if ( $cache ) {
			// Save new data to file.
			$fh = fopen( $cacheFile, 'w' );
			fwrite( $fh, $json );
			fclose( $fh );
		}

		return json_decode( $json, false );
	}
}
