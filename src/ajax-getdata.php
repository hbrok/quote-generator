<?php
header("content-type:application/json");

require __DIR__ . '/../vendor/autoload.php';
require_once 'class-website.php';

$quotes = new WebSite;

//var_dump( $_POST );

if ( isset( $_POST['function'] ) ) {
	switch ( $_POST['function'] ) {
		case 'font':
			$url = 'https://www.googleapis.com/webfonts/v1/webfonts?key=AIzaSyD02NoBU2DFMfsCIXMq_Rrt9SvO7a-6xNg';
			echo json_encode( $quotes->getJson( $url ) );
			exit();
			break;

		case 'quote':
			echo json_encode( $quotes->fetch_quote() );
			exit();
			break;

		case 'colors':
			$url         = 'http://www.randoma11y.com/stats/';
			$a11y_stats  = $quotes->getJson( $url );
			$count       = $a11y_stats['combos'];
			$color_index = rand( 1, $count );

			$url2 = 'http://randoma11y.com/combos?page=' . $color_index . '&per_page=1';
			echo json_encode( $quotes->getJson( $url2, false ) );
//			echo str_replace(array('[', ']'), '', htmlspecialchars(json_encode($quotes->getJson( $url2, false )), ENT_NOQUOTES));
			exit();
			break;
	}
} else {
	echo 'Nope.';
	exit();
}