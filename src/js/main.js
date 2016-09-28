var App = (function () {

    // XMLHttpRequest type.
    var invocation = new XMLHttpRequest();
    var requestType = '';

    // Caches.
    var colorCache = [];
    var fontCache = [];

    // API URLs/info.
    var forismaticUrl = 'http://api.forismatic.com/api/1.0/?method=getQuote&lang=en&format=jsonp&jsonp=App.getNewQuote';

    // Buttons.
    var quoteText = document.getElementById('js-quote-text');
    var quoteAuthor = document.getElementById('js-quote-author');
    var quoteLink = document.getElementById('js-quote-link');

    var quoteSource = document.getElementById('js-quote-source');

    var fontLink = document.getElementById('js-font-link');

    var newFont = document.getElementById('js-new-font');
    var newQuote = document.getElementById('js-new-quote');
    var newColors = document.getElementById('js-new-colors');

    var stylesheet;
    var font;
    var loadedFont = false;

    /**
     * Initializes page for our script.
     *
     * Buttons are assigned event listeners, and our default font is set.
     */
    var init = function () {
        stylesheet = document.styleSheets[2];
        font = document.body.getAttribute('data-font');

        colorOne = document.body.getAttribute('data-colorone');
        colorTwo = document.body.getAttribute('data-colortwo');

        newFont.addEventListener('click', function (e) {
            e.preventDefault();
            newFont.setAttribute( 'data-loading', '' );

            if (fontCache.length === 0) {
                // Cache is empty, so set request type and send new request.
                requestType = 'font';
                _callOtherDomain();
            } else if (fontCache.length > 0) {
                // Cache exists so get a new font.
                getNewFont();
            }
        });

        newQuote.addEventListener('click', function (e) {
            e.preventDefault();
            newQuote.setAttribute( 'data-loading', '' );

            // jsonp request that calls getNewQuote() function.
            _loadScript(forismaticUrl);
        });

        newColors.addEventListener('click', function (e) {
            e.preventDefault();
            newColors.setAttribute( 'data-loading', '' );

            requestType = 'colors';
            _callOtherDomain();
        });
    };

    /**
     * Returns a random integer between min (included) and max (included)
     * Using Math.round() will give you a non-uniform distribution!
     * @link https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Math/random#Examples
     */
    var _getRandomInt = function (min, max) {
        min = Math.ceil(min);
        max = Math.floor(max);
        return Math.floor(Math.random() * (max - min)) + min;
    };

    /**
     * Given a URL loads a JS script dynamically.
     * @param {string} url URL to load via XMLHttpRequest.
     * @private
     */
    var _loadScript = function (url) {
        var wf = document.createElement('script'),
            s = document.scripts[0];
        wf.src = url;
        s.parentNode.insertBefore(wf, s);
    };

    /**
     * Opens new XMLHttpRequest from a given URL, and calls
     * _handler to deal with it.
     * @param url URL from which to get new XMLHttpRequest.
     * @private
     */
    var _callOtherDomain = function (url) {
        if (invocation) {
            invocation.open('GET', 'ajax-getdata.php?function=' + requestType, true);
            invocation.onreadystatechange = _handler;
            invocation.send(  );
        }
    };

    /**
     * Depending on the value of requestType, gets a response and calls
     * a specific function based on which request type was sent.
     * @param evtXHR
     * @private
     */
    var _handler = function (evtXHR) {
        if (invocation.readyState === XMLHttpRequest.DONE) {
            if (invocation.status === 200) {
                /*
                 * Get response and replace escaped single quotes with unescaped
                 * single quotes. (Forismatic escapes single quotes in their JSON
                 * response, however that is not valid JSON and it causes errors.)
                 */
                var response = JSON.parse(invocation.responseText.replace("\\'", "'"));

                // Check which request was made based on requestType value.
                switch (requestType) {
                    case 'font':
                        // Set font cache, and get new font.
                        fontCache = response.items;
                        getNewFont();
                        break;

                    case 'colors':
                        // Set color cache and get new colors.
                        colorCache = response[0];
                        getNewColors();
                        break;

                    default:
                        alert('No request type set.');
                        break;
                }
            } else {
                console.log('There was a problem with the request.');
            }
        }

    };

    /**
     * Generates a link back to the specific color/font/quote combo.
     * @private
     */
    var _generateQuoteLink = function () {
        var base = 'http://' + window.location.hostname + '/src/?';
        var colors = 'bg=' + colorOne +
                     '&fg=' + colorTwo;
        var quote = '&quote=' + quoteText.innerHTML +
                    '&author=' + quoteAuthor.innerHTML +
                    '&id=' + quoteLink.getAttribute('href').slice(
                        quoteLink.getAttribute('href').length - 11,
                        quoteLink.getAttribute('href').length - 1
                    );
        //var font = '&font=' + font; // TODO: why isn't this loading the value from the variable?
        var font = '&font=' + stylesheet.cssRules[0].style.fontFamily; // Get font family from stylesheet.

        console.log(font);

        var link = encodeURI(base + colors + quote + font.replace(' ', '+').replace('"', '').replace('"', '')); // TODO: this also sucks...

        quoteSource.setAttribute('href', link);
    };


    // TODO: Coda Caption, fonts with only bold styles aren giving an error when not being called with the correct font weight, so we need to also get the (first?) font weight available so they show up. OR only use the font if it has the normal 300/400 default weight....
// Load a Google font by name.
    var _loadFont = function (font, fontVariant) {
       // Explain here how I made the decision not to load only a subset of
        // a font after page interaction. If someone wants to get 10 new quotes,
        // they would load the same font data over and over again. Which doesn't make
        // that much sense... better to load the whole font after the initial page load.

        WebFontConfig = {
            google: {
                families: [font + ':' + fontVariant]
            },
            timeout: 2000 // Set the timeout to two seconds.
        };
        _loadScript('https://ajax.googleapis.com/ajax/libs/webfont/1.6.16/webfont.js');
    };

    /**
     * Sets new quote, quthor, and link/
     * @param {*} response JSON response from Forismatic.com
     */
    var getNewQuote = function(response) {
        // Check if the current font has already been loaded.
        if( ! loadedFont ) {
            loadedFont = true;
            _loadFont(font);
        }

        // Set new quote text and author.
        quoteText.innerHTML = response.quoteText;

        if (response.quoteAuthor.length > 0) {
            // Set author.
            quoteAuthor.innerHTML = response.quoteAuthor;
        } else {
            // Set author if author is empty.
            quoteAuthor.innerHTML = 'Anonymous';
        }

        // Update link to quote.
        quoteLink.setAttribute('href', response.quoteLink);

        // Get new link to this page.
        _generateQuoteLink();
        newQuote.removeAttribute('data-loading');
    };

    /**
     * Selects & updates CSS with new color combo.
     *
     * Updates link to quote.
     */
    var getNewColors = function() {
        // Get our colors.
        colorOne = colorCache.color_one;
        colorTwo = colorCache.color_two;

        // Update CSS with new colors.
        stylesheet.cssRules[1].style.color           = colorOne; // color: color-one
        stylesheet.cssRules[2].style.backgroundColor = colorOne; // background-color: color-one
        stylesheet.cssRules[3].style.color           = colorTwo; // color: color-two
        stylesheet.cssRules[4].style.borderColor     = colorTwo; // border-color: color-two
        stylesheet.cssRules[5].style.backgroundColor = colorTwo; // background-color: color-two

        // Set variable without the #.
        colorOne = colorOne.slice(1);
        colorTwo = colorTwo.slice(1);

        // Update color voting link without # from colors.
        var voteButton = document.getElementById('js-colors-vote-link');
        voteButton.setAttribute('href', 'http://randoma11y.com/#/?hex=' + colorOne + '&compare=' + colorTwo);

        // Get new link to this page.
        _generateQuoteLink();
        newColors.removeAttribute('data-loading');
    };


    /**
     * Selects & updates CSS with random font family.
     *
     * Updates links to font & quote.
     */
    var getNewFont = function() {
        var index = _getRandomInt(0, fontCache.length); // Get random number for font.
        var fontVariant = 'regular'; // Start with regular, since most fonts have the variant.
        var regular = false;

        // Get random font from cached list, and load it.
        font = fontCache[index].family;

        // Search for 'regular' in the variants.
        for (var i = 0; i < fontCache[index].variants.length; i++) {
            if (fontCache[index].variants[i] === 'regular') {
                regular = true;
            }
        }

        // Get the first variant if the font didn't have 'regular'.
        if (!regular) {
            fontVariant = fontCache[index].variants[0];
        }

        // Load new font.
        _loadFont(font, fontVariant);

        // Update CSS with new font.
        stylesheet.cssRules[0].style.fontFamily = font;

        // Update link to font source.
        fontLink.setAttribute('href', 'https://fonts.google.com/specimen/' + font);

        // Get link back to this page.
        _generateQuoteLink();
        newFont.removeAttribute('data-loading');
    };

    return {
        init: init,
        getNewQuote: getNewQuote,
        getNewColors: getNewColors,
        getNewFont: getNewFont
    }
}());
