var App = (function () {

    // XMLHttpRequest type.
    var invocation = new XMLHttpRequest();
    var requestType = '';

    // Caches.
    var colorCache = [];
    var fontCache = [];

    // API URLs/info.
    var combos = 113592;
    //var colorsURL = 'http://www.randoma11y.com/stats/';
    var colorsPageURL = 'http://randoma11y.com/combos?';
    //var randoma11yUrl = 'http://www.randoma11y.com/stats/';
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
    var loadedFont = false;

    /**
     * Initializes page for our script.
     *
     * Buttons are assigned event listeners, and our default font is set.
     */
    var init = function () {
        stylesheet = document.styleSheets[2];
        //font = stylesheet.cssRules[0].style.fontFamily;
        //font = font.replace('"', '').replace('"', '');


        font = document.body.getAttribute('data-font');

        //console.log(stylesheet.cssRules[0]);
        //colorOne = stylesheet.cssRules[1].style.color; // returns RGB which is no good to anyone
        //colorTwo = stylesheet.cssRules[3].style.color;

        colorOne = document.body.getAttribute('data-colorone');
        colorTwo = document.body.getAttribute('data-colortwo');

        //console.log(font);
        //console.log(colorOne);
        //console.log(colorTwo);
        //
        newFont.addEventListener('click', function (e) {
            e.preventDefault();

            if (fontCache.length === 0) {
                // Cache is empty, so set request type and send new request.
                requestType = 'font';
                _callOtherDomain(fontsUrl);
            } else if (fontCache.length > 0) {
                // Cache exists so get a new font.
                getNewFont();
            }
        });

        newQuote.addEventListener('click', function (e) {
            e.preventDefault();

            // jsonp request that calls getNewQuote() function.
            _loadScript(forismaticUrl);
        });

        newColors.addEventListener('click', function (e) {
            e.preventDefault();

            //if (colorCache.length === 0) {
                // Cache is empty, set request type and get colors.
                requestType = 'colors';
                //_callOtherDomain(colorsPageURL);
                _callOtherDomain();
            //} else if (colorCache.length > 0) {
            //    getNewColors();
            //}
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
    //var _unique = function (str) {
    //    var arr = [];
    //    for (var i = 0; i < str.length; i++) {
    //        if (!_contains(arr, str[i])) {
    //            arr.push(str[i]);
    //        }
    //    }
    //    return arr;
    //};

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
            //invocation.open('GET', url, true);
            //invocation.onreadystatechange = _handler;
            //invocation.send();

            invocation.open('POST', 'ajax-getdata.php', true);
            var params = "function=" + requestType;
            invocation.onreadystatechange = _handler;
            invocation.setRequestHeader("Content-type",
                "application/x-www-form-urlencoded");
            //invocation.setRequestHeader("Content-length",
            //    params.length);
            //invocation.setRequestHeader("Connection", "close");
            invocation.send( params );
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
                console.log(invocation);

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
        var font = '&font=' + stylesheet.cssRules[0].style.fontFamily;

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

        console.log(font);
        WebFontConfig = {
            google: {
                families: [font] // todo: get latin/font weight too.
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
        // Only load the whole new font once.
        // TODO: Am I doing this in a good way?
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
    };

    /**
     * Selects & updates CSS with new color combo.
     *
     * Updates link to quote.
     */
    var getNewColors = function() {
        console.log(colorCache);
        // Get random color pairing, with color values without the #.
        var pair = colorCache[_getRandomInt(0, colorCache.length)];
        var pair = colorCache[0];
        colorOne = pair.color_one;
        colorTwo = pair.color_two;

        console.log(stylesheet);

        // Update CSS with new colors.
        stylesheet.cssRules[1].style.color           = colorOne; // color: color-one
        stylesheet.cssRules[2].style.backgroundColor = colorOne; // color: color-one
        stylesheet.cssRules[3].style.color           = colorTwo; // color: color-one
        stylesheet.cssRules[4].style.borderColor     = colorTwo; // color: color-one
        stylesheet.cssRules[5].style.backgroundColor = colorTwo; // color: color-one

        // Set variable without the #.
        colorOne = colorOne.slice(1);
        colorTwo = colorTwo.slice(1);

        // Update color voting link. Remove # from colors.
        var voteButton = document.getElementById('js-colors-vote-link');
        voteButton.setAttribute('href', 'http://randoma11y.com/#/?hex=' + colorOne + '&compare=' + colorTwo);

        // Get new link to this page.
        _generateQuoteLink();
    };


    /**
     * Selects & updates CSS with random font family.
     *
     * Updates links to font & quote.
     */
    var getNewFont = function() {
        //console.log(fontCache);

        //for (var i = 0; i < fontCache.length; i++) {
        //    var regular = false;
        //
        //    for (var j = 0; j < fontCache[i].variants.length; j++) {
        //        if (fontCache[i].variants[j] === 'regular') {
        //            regular = true;
        //        }
        //    }
        //
        //    if(!regular) {
        //        console.log(fontCache[i].family);
        //        console.log(fontCache[i].variants);
        //    }
        //}
        var index = _getRandomInt(0, fontCache.length);
        var fontVariant = 'regular';


        // Get random font from cached list, and load it.
        font = fontCache[index].family;
        var regular = false;

            for (var j = 0; j < fontCache[index].variants.length; j++) {
                if (fontCache[index].variants[j] === 'regular') {
                    regular = true;
                }
            }

        if (!regular) {
            fontVariant = fontCache[index].variants[0];
        }


        font = 'Buda';
        // Load the entire new font.
        _loadFont(font, fontVariant);

        // Update CSS with new font.
        stylesheet.cssRules[0].style.fontFamily = font;

        // Update link to font source.
        fontLink.setAttribute('href', 'https://fonts.google.com/specimen/' + font);

        // Get link back to this page.
        _generateQuoteLink();
    };

    return {
        init: init,
        getNewQuote: getNewQuote,
        getNewColors: getNewColors,
        getNewFont: getNewFont
    }
}());
