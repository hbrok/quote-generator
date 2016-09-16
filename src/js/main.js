var App = (function () {

    // XMLHttpRequest type.
    var invocation = new XMLHttpRequest();
    var requestType = '';

    // Caches.
    var colorCache = [];
    var fontCache = [];

    // Font.
    var bodyStyles = getComputedStyle(document.documentElement);

    // API URLs/info.
    var combos = 113592;
    var colorsURL = 'http://www.randoma11y.com/stats/';
    var colorsPageURL = 'http://randoma11y.com/combos?';
    var randoma11yUrl = 'http://www.randoma11y.com/stats/';
    var forismaticUrl = 'http://api.forismatic.com/api/1.0/?method=getQuote&lang=en&format=jsonp&jsonp=App.getNewQuote';
    var fontsUrl = '/src/font-list.json';

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

    /**
     * Initializes page for our script.
     *
     * Buttons are assigned event listeners, and our default font is set.
     */
    var init = function () {
        stylesheet = document.styleSheets[2];
        font = stylesheet.cssRules[0].style.fontFamily;
        colorOne = stylesheet.cssRules[1].style.color;
        colorTwo = stylesheet.cssRules[3].style.color;

        console.log(font);
        console.log(colorOne);
        console.log(colorTwo);

        // TODO: set default colorOne and colorTwo here too, if needed.

        newFont.addEventListener('click', function (e) {
            e.preventDefault();

            // Send new request if cache is empty, or load from cache.
            if (fontCache.length === 0) {
                console.log('1');

                // Set request type, so out handler knows what function to call.
                requestType = 'font';
                _callOtherDomain(fontsUrl);
            } else if (fontCache.length > 0) {
                console.log('2');
                getNewFont();
            }
        });

        newQuote.addEventListener('click', function (e) {
            e.preventDefault();
            //_loadFont(font);
            _loadScript(forismaticUrl);
        });

        newColors.addEventListener('click', function (e) {
            e.preventDefault();

            // Send new request if cache is empty, or load from cache.
            if (colorCache.length === 0) {
                requestType = 'colors';
                _callOtherDomain(colorsPageURL);
            } else if (colorCache.length > 0) {
                getNewColors();
            }
        });
    };

    /**
     * Check if a given value is contained in an array.
     * @param arr Array to check.
     * @param v Value to check for.
     * @returns {boolean} True if found, false if not.
     * @private
     */
    var _contains = function (arr, v) {
        for (var i = 0; i < arr.length; i++) {
            if (arr[i] === v) return true;
        }
        return false;
    };

    /**
     * Get all uniqie characters in a given string.
     * @param {string} str String to get unique characters from.
     * @returns {Array}
     * @private
     */
    var _unique = function (str) {
        var arr = [];
        for (var i = 0; i < str.length; i++) {
            if (!_contains(arr, str[i])) {
                arr.push(str[i]);
            }
        }
        return arr;
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
            invocation.open('GET', url, true);
            invocation.onreadystatechange = _handler;
            invocation.send();
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
                // Get response and replace escaped single quotes with
                // unescaped single quotes. (Forismatic escapes single
                // quotes in their JSON response, however that is not
                // valid JSON).
                var response = JSON.parse(invocation.responseText.replace("\\'", "'"));

                // Different functions based on which button was pressed.
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

                    default:
                        alert('No request type set.');
                        break;
                }
            } else {
                alert('There was a problem with the request.');
            }
        }

    };

    /**
     * Returns a string with each unique character.
     * @returns {string}
     * @private
     */
    var _getUniqueChars = function () {
        return _unique(quoteText.innerHTML).join('');
    };

    /**
     * Generates a link back to the specific color/font/quote combo.
     * @private
     */
    var _generateQuoteLink = function () {
        // Get new colors wihtout # in front..
        //colorOne = bodyStyles.getPropertyValue('--colorone');
        //colorOne = colorOne.slice(1);
        //
        //colorTwo = bodyStyles.getPropertyValue('--colortwo');
        //colorTwo = colorTwo.slice(1);

        // TODO: Get colors from cssstylesheet...

        var base = 'http://' + window.location.hostname + '/src/?';
        var colors = 'bg=' + colorOne + '&fg=' + colorTwo;
        //console.log(quoteLink);
        var quote = '&quote=' + quoteText.innerHTML + '&author=' + quoteAuthor.innerHTML + '&id=' + quoteLink.getAttribute('href').slice(quoteLink.getAttribute('href').length - 11, quoteLink.getAttribute('href').length - 1);
        var font = '&font=' + font;

        var link = encodeURI(base + colors + quote + font);

        quoteSource.setAttribute('href', link);
    };


    // TODO: Coda Caption, fonts with only bold styles aren giving an error when not being called with the correct font weight, so we need to also get the (first?) font weight available so they show up. OR only use the font if it has the normal 300/400 default weight....
// Load a Google font by name.
    var _loadFont = function (font) {
        console.log(font);
        WebFontConfig = {
            google: {
                families: [font],
                text: _getUniqueChars()
            },
            timeout: 2000 // Set the timeout to two seconds
        };
        _loadScript('https://ajax.googleapis.com/ajax/libs/webfont/1.6.16/webfont.js');
    };

    /**
     * Sets new quote, quthor, and link/
     * @param {*} response JSON response from Forismatic.com
     */
    var getNewQuote = function(response) {
        // Set new quote, and load font characters needed for it.
        quoteText.innerHTML = response.quoteText;
        _loadFont(font);

        // Don't display blank author. Use 'Anonymous' if author is blank.
        if (response.quoteAuthor.length > 0) {
            quoteAuthor.innerHTML = response.quoteAuthor;
        } else {
            quoteAuthor.innerHTML = 'Anonymous';
        }

        // Update link to quote.
        quoteLink.setAttribute('href', response.quoteLink);
        _generateQuoteLink();
    };

    /**
     * Selects & updates CSS with new color combo.
     *
     * Updates link to quote.
     */
    var getNewColors = function() {
        // Get random color pairing.
        var numColorPairs = colorCache.length;
        var pair = colorCache[_getRandomInt(0, numColorPairs)];
        colorOne = pair.color_one;
        colorTwo = pair.color_two;

        // Update CSS with new colors.
        stylesheet.cssRules[1].style.color = colorOne; // color: color-one
        stylesheet.cssRules[2].style.backgroundColor = colorOne; // color: color-one
        stylesheet.cssRules[3].style.color = colorTwo; // color: color-one
        stylesheet.cssRules[4].style.borderColor = colorTwo; // color: color-one
        stylesheet.cssRules[5].style.backgroundColor = colorTwo; // color: color-one

        // Update color voting link. Remove # from colors.
        var voteButton = document.getElementById('js-colors-vote-link');
        voteButton.setAttribute('href', 'http://randoma11y.com/#/?hex=' + colorOne.slice(1) + '&compare=' + colorTwo.slice(1));

        _generateQuoteLink();
    };


    /**
     * Selects & updates CSS with random font family.
     *
     * Updates links to font & quote.
     */
    var getNewFont = function() {
        // Get random font from cached list, and load it.
        font = fontCache[_getRandomInt(0, fontCache.length)].family;
        _loadFont(font);

        // Update CSS with new font.
        stylesheet.cssRules[0].style.fontFamily = font;

        // Update link to font source.
        fontLink.setAttribute('href', 'https://fonts.google.com/specimen/' + font);

        // Get link back to this color/font/quote combination.
        _generateQuoteLink();
    };

    return {
        init: init,
        getNewQuote: getNewQuote,
        getNewColors: getNewColors,
        getNewFont: getNewFont
    }
}());
