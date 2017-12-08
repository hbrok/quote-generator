<?php
header( 'content-type:application/json' );

require __DIR__ . '/../vendor/autoload.php';
require_once 'class-website.php';

$quotes = new WebSite;

if ( isset( $_GET['function'] ) ) {
	switch ( $_GET['function'] ) {
		case 'font':
			$url = 'https://www.googleapis.com/webfonts/v1/webfonts?key=AIzaSyD02NoBU2DFMfsCIXMq_Rrt9SvO7a-6xNg';
			echo json_encode( $quotes->getJson( $url ) );
			exit();
			break;

		case 'quote':
			$url = 'http://api.forismatic.com/api/1.0/?method=getQuote&lang=en&format=json';
			echo json_encode( $quotes->getJson( $url, false ) );
			exit();
			break;

		case 'colors':
			$url         = 'http://www.randoma11y.com/stats/';
			$a11y_stats  = $quotes->getJson( $url );
			$count       = $a11y_stats->combos;
			$color_index = rand( 1, $count );

			$url2 = 'http://randoma11y.com/combos?page=' . $color_index . '&per_page=1';
			echo json_encode( $quotes->getJson( $url2, false ) );
			exit();
			break;
	}
} else {
	echo 'Nope.';
	exit();
}