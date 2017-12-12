<?php
    require('/../vendor/autoload.php');
    require('./class-website.php');

    var_dump(__DIR__);

    $quotes = new WebSite;

    $mustache = new Mustache_Engine( array(
        'loader' => new Mustache_Loader_FilesystemLoader( dirname( __FILE__ ) . '/views' ),
    ) );

    // Helper functions.
    $mustache->addHelper('urlencode', function( $string ) { return urlencode( ( string ) $string ); } );
    $mustache->addHelper('urldecode', function( $string ) { return urldecode( ( string ) $string ); } );

    // Load & render template..
    echo $mustache->render( 'index', $quotes );
