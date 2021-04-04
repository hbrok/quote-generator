var App = (function () {

    // XMLHttpRequest type.
    var invocation = new XMLHttpRequest();
    var requestType = '';

    // Caches.
    var colorCache = [];
    var fontCache = [];
    var quoteResponse = [];

    // Buttons.
    var quoteText = document.getElementById('js-quote-text');
    var quoteAuthor = document.getElementById('js-quote-author');
    var quoteLink = document.getElementById('js-quote-link');

    var quoteSource = document.getElementById('js-quote-source');

    var fontLink = document.getElementById('js-font-link');

    var newFont = document.getElementById('js-new-font');
    var newQuote = document.getElementById('js-new-quote');
    var newColors = document.getElementById('js-new-colors');
    var backgroundColorHex = document.getElementById('js-background-color');
    var foregroundColorHex = document.getElementById('js-foreground-color');

    var stylesheet;
    var font;
    var backgroundColor;
    var foregroundColor;
    var loadedFont = false;

    /**
     * Initializes page for our script.
     *
     * Buttons are assigned event listeners, and our default font is set.
     */
    var init = function () {
        stylesheet = document.styleSheets[2];
        font = document.body.getAttribute('data-font');

        backgroundColor = document.body.getAttribute('data-background-color');
        foregroundColor = document.body.getAttribute('data-foreground-color');

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

            requestType = 'quote';
            _callOtherDomain();
        });

        newColors.addEventListener('click', function (e) {
            e.preventDefault();
            newColors.setAttribute( 'data-loading', '' );

            requestType = 'colors';
            _callOtherDomain();
        });
    };

    /**
     * Returns a random integer between min (included) and max (included).
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
     * @private
     */
    var _callOtherDomain = function () {
        if (invocation) {
            invocation.open('GET', 'ajax-getdata.php?function=' + requestType, true);
            invocation.onreadystatechange = _handler;
            invocation.send();
        }
    };

    /**
     * Depending on the value of requestType, gets a response and calls
     * a specific function based on which request type was sent.
     * @private
     */
    var _handler = function () {
        if (invocation.readyState === XMLHttpRequest.DONE) {
            if (invocation.status === 200) {
                /*
                 * Replace escaped single quotes with unescaped single quotes.
                 * (Forismatic escapes single quotes in their JSON response,
                 * however that is not valid JSON and causes errors.)
                 */
                var response = JSON.parse(invocation.responseText.replace("\\'", "'"));

                // Check type and request.
                switch (requestType) {
                    case 'font':
                        // Set font cache, and get new font.
                        fontCache = response.items;
                        getNewFont();
                        break;

                    case 'colors':
                        // Set color cache and get new colors.
                        colorCache = response;
                        getNewColors();
                        break;

                    case 'quote':
                        // Set quote as response.
                        quoteResponse = response;
                        getNewQuote();
                        break;


                    default:
                        alert('No request type set.');
                        break;
                }
            } else {
                // TODO: Print error to screen??
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
        var colors = 'bg=' + backgroundColor +
                     '&fg=' + foregroundColor;
        var quote = '&quote=' + quoteText.innerHTML +
                    '&author=' + quoteAuthor.innerHTML +
                    '&id=' + quoteLink.getAttribute('href').slice(
                        quoteLink.getAttribute('href').length - 11,
                        quoteLink.getAttribute('href').length - 1
                    );
        var fontPart = '&font=' + font;
        var link = encodeURI(base + colors + quote + fontPart);

        quoteSource.setAttribute('href', link);
    };

    /**
     * Load a font.
     *
     * @param font Font name.
     * @param fontVariant Font variant (must be specified because some fonts don't have a 'regular' variant).
     * @private
     */
    var _loadFont = function (font, fontVariant) {
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
     */
    var getNewQuote = function() {
        // Check if the current font has already been loaded.
        if( ! loadedFont ) {
            loadedFont = true;
            _loadFont(font);
        }

        // Set new quote text and author.
        quoteText.innerHTML = quoteResponse.quoteText;

        if (quoteResponse.quoteAuthor.length > 0) {
            // Set author.
            quoteAuthor.innerHTML = quoteResponse.quoteAuthor;
        } else {
            // Set author if author is empty.
            quoteAuthor.innerHTML = 'Anonymous';
        }

        // Update link to quote.
        quoteLink.setAttribute('href', quoteResponse.quoteLink);

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
        backgroundColor = colorCache.background_color;
        foregroundColor = colorCache.foreground_color;

        backgroundColorHex.innerHTML = `#${backgroundColor}`;
        foregroundColorHex.innerHTML = `#${foregroundColor}`;

        // Update CSS with new colors.
        stylesheet.cssRules[1].style.color           = `#${backgroundColor}`; // color: background
        stylesheet.cssRules[2].style.backgroundColor = `#${backgroundColor}`; // background-color: background
        stylesheet.cssRules[3].style.color           = `#${foregroundColor}`; // color: foreground
        stylesheet.cssRules[4].style.borderColor     = `#${foregroundColor}`; // border-color: foreground
        stylesheet.cssRules[5].style.backgroundColor = `#${foregroundColor}`; // background-color: foreground

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
        console.log(font);

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

        // Update font link & font name.
        fontLink.setAttribute('href', 'https://fonts.google.com/specimen/' + font);
        fontLink.innerHTML = font;

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
