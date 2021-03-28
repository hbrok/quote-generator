<?php
    require __DIR__ . '/../vendor/autoload.php';
    require_once 'class-website.php';

    $quotes = new WebSite;

    $mustache = new Mustache_Engine( array(
        'loader' => new Mustache_Loader_FilesystemLoader( dirname( __FILE__ ) . '/views' ),
    ) );

    // Helper functions.
    $mustache->addHelper('urlencode', function( $string ) { return urlencode( ( string ) $string ); } );
    $mustache->addHelper('urldecode', function( $string ) { return urldecode( ( string ) $string ); } );
    $mustache->addHelper('inline_css', function() { return file_get_contents('css/main.css'); } );

    // Load & render template..
    echo $mustache->render( 'index', $quotes );
