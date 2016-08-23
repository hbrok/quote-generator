<?php
    require_once 'functions.php';
    $site = new WebSite();
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
    <body class="site">

        <header class="main-header">

        </header>

        <main class="main-content">
            <blockquote class="quote">
                <p class="quote-text"><?php $site->get_quote(); ?></p>
              <footer class="quote-attribution">
                  <a class="quote-link" href="<?php $site->get_link(); ?>">
                      <span class="quote-author"><?php $site->get_author(); ?></span>
                  </a>
              </footer>
              <a href="<?php $site->get_quote_link(); ?>">link</a> |
              <a href="<?php $site->get_link(); ?>">source</a>
            </blockquote>

            <div class="actions">
                <div class="new-quote">
                    <a class="button" href="<?php $site->get_quote_link('font'); ?>">New Font</a>
                    <a class="button" href="<?php $site->get_quote_link('colors'); ?>">New Colors</a>
                    <a class="button" href="<?php $site->get_quote_link('quote'); ?>">New Quote</a>
                </div>
                <br>
                <!-- Like this color combo? Hate this color combo? -->
                <a class="button" href="http://randoma11y.com/#/?hex=<?php $site->get_hex('bg'); ?>&compare=<?php $site->get_hex('color'); ?>">
                    colors
                </a>

                <a class="button" href="https://fonts.google.com/specimen/<?php urlencode( $site->get_font_name() ); ?>">
                    font
                </a>
            </div>
        </main>

        <footer class="footer-main">
          <p class="colophon">
              Colors: <a href="http://www.randoma11y.com/">randoma11y.com</a> |
              Quotes: <a href="http://forismatic.com/">forismatic.com</a><br>
              Made for <a href="https://a-k-apart.com/">10k Apart</a>
          </p>
        </footer>
    </body>
</html>
