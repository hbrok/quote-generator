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
			$colors = $quotes->generate_colors();
			echo json_encode( [
				'background_color' => $colors[0],
				'foreground_color' => $colors[1],
			] );
			exit();
			break;
	}
} else {
	echo 'Nope.';
	exit();
}
