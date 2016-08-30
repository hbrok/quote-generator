<?php
    require __DIR__ . '/../vendor/autoload.php';
    require_once 'functions.php';
    $site = new WebSite();
    $m = new Mustache_Engine;
    echo $m->render('Hello, {{planet}}!', array('planet' => 'World')); // "Hello, World!"
?>
<!doctype html>
<html class="no-js" lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <title>Quotes</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="stylesheet" href="css/main.css">
        <link rel="stylesheet" type="text/css" href="<?php $site->get_google_font(); ?>">

        <?php $site->get_css(); ?>
    </head>

    <body>
        <main class="main-content">
            <blockquote class="quote">
                <p class="quote-text"><?php $site->get_quote(); ?></p>
                <footer class="quote-attribution">
                    <a class="button" href="<?php $site->get_link(); ?>">
                        <span class="quote-author"><?php $site->get_author(); ?></span>
                    </a>
                </footer>
                <a href="<?php $site->get_quote_link(); ?>">link</a> |
                <a href="<?php $site->get_link(); ?>">source</a> |
                <a href="https://fonts.google.com/specimen/<?php $site->get_font_name( true ); ?>">font</a>
            </blockquote>

            <div class="actions">
                <p>Generate a new quote:</p>
                <a class="button hollow" href="<?php $site->get_quote_link('font'); ?>">New Font</a>
                <a class="button hollow" href="<?php $site->get_quote_link('colors'); ?>">New Colors</a>
                <a class="button hollow" href="<?php $site->get_quote_link('quote'); ?>">New Quote</a>

                <p>What do you think about this accessible color combination?</p>
                <a class="button" href="http://randoma11y.com/#/?hex=<?php $site->get_hex('color_one'); ?>&compare=<?php $site->get_hex('color_two'); ?>">Vote on these colors</a>
            </div>
        </main>

        <footer class="footer">
            <p class="colophon">
                Colors: <a href="http://www.randoma11y.com/">randoma11y.com</a> |
                Quotes: <a href="http://forismatic.com/">forismatic.com</a><br>
                Made for <a href="https://a-k-apart.com/">10k Apart</a>
            </p>
        </footer>
    </body>
</html>
