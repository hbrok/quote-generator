<?php
    require_once 'functions.php';
    $site = new WebSite();
?>
<!doctype html>
<html class="no-js" lang="">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <title>Quotes</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="apple-touch-icon" href="apple-touch-icon.png">
        <!-- Place favicon.ico in the root directory -->

        <link rel="stylesheet" href="css/main.css">

        <link rel="stylesheet" type="text/css" href="<?php $site->get_google_font(); ?>">

        <?php $site->get_css(); ?>
    </head>
    <body class="site">

        <header class="main-header">

        </header>

        <main class="main-content">
            <blockquote class="quote">
                <p class="quote-text">
                    <?php $site->get_quote(); ?>
                </p>
              <footer class="quote-attribution">
                <div class="vote">
                    <button id="vote-up" type="button" name="button">Vote Up</button>
                    <button id="vote-down" type="button" name="button">Vote Down</button>
                </div>
                  —
                  <a class="quote-link" href="<?php $site->get_link(); ?>">
                      <span class="quote-author"><?php $site->get_author(); ?></span>
                  </a>
              </footer>
              <?php // $site->get_quote_link(); ?>
            </blockquote>

            <div class="new-quote">
                <ul class="quote-actions">
                    <li><a href="<?php $site->get_quote_link('colors') ?>">New Colors</a></li>
                    <li><a href="<?php $site->get_quote_link('font') ?>">New Font</a></li>
                    <li><a href="<?php $site->get_quote_link('quote') ?>">New Quote</a></li>
                </ul>
            </div>
        </main>

        <footer class="footer-main">
          <div class="colophon">attributions, etc.</p>
        </footer>
    </body>
</html>
