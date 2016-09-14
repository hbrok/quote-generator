var App = (function () {

    // XMLHttpRequest type.
    var invocation = new XMLHttpRequest();
    var requestType = '';

    // Caches.
    var colorCache = [];
    var fontCache = [];

    // Font.
    //var bodyStyles = document.documentElement.style;
    var bodyStyles = getComputedStyle(document.documentElement);
    var font = bodyStyles.getPropertyValue('--font').trim();
    console.log(font);

    //var colorOne = bodyStyles.getPropertyValue('--colorone');
    //var colorTwo = bodyStyles.getPropertyValue('--colortwo');


    //console.log(bodyStyles);
    //console.log(document.documentElement.style);

    //var quoteStyles = getComputedStyle(document.getElementById('js-quote-text'));
    //console.log(quoteStyles);
    //console.log(quoteStyles.getPropertyValue('font-family'));
    //console.log(bodyStyles);
    //console.log(colorOne);
    //console.log(colorTwo);
    //console.log(font);

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

    var init = function () {
        newFont.addEventListener('click', function (e) {
            e.preventDefault();

            // Send new request if cache is empty, or load from cache.
            if (fontCache.length === 0) {
                console.log('1');
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
     * foreach function for querySelectorAll elements.
     */
    var _forEach = function (array, callback, scope) {
        for (var i = 0; i < array.length; i++) {
            callback.call(scope, i, array[i]); // passes back stuff we need
        }
    };

    /**
     * Check if array contains a value.
     */
    var _contains = function (arr, v) {
        for (var i = 0; i < arr.length; i++) {
            if (arr[i] === v) return true;
        }
        return false;
    };

    /**
     * Get all unique characters in a string.
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

    var _loadScript = function (url) {
        var wf = document.createElement('script'),
            s = document.scripts[0];
        wf.src = url;
        s.parentNode.insertBefore(wf, s);
    };


    var _callOtherDomain = function (url) {
        if (invocation) {
            invocation.open('GET', url, true);
            invocation.onreadystatechange = _handler;
            invocation.send();
        }
    };


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

    var _getUniqueChars = function () {
        return _unique(quoteText.innerHTML).join('');
    };


    var _generateQuoteLink = function () {
        // Get new colors wihtout # in front..
        colorOne = bodyStyles.getPropertyValue('--colorone');
        colorOne = colorOne.slice(1);

        colorTwo = bodyStyles.getPropertyValue('--colortwo');
        colorTwo = colorTwo.slice(1);

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


    var getNewQuote = function(response) {
        // Set new quote, and characters needed for it.
        quoteText.innerHTML = response.quoteText;
        _loadFont(font);
        console.log(font);

        // Don't display blank author.
        if (response.quoteAuthor.length > 0)
            quoteAuthor.innerHTML = response.quoteAuthor;
        else
            quoteAuthor.innerHTML = 'Anonymous';

        quoteLink.setAttribute('href', response.quoteLink);
        _generateQuoteLink();
    };


    var getNewColors = function() {
        // Get random color pairing.
        var numColorPairs = colorCache.length;
        var pair = colorCache[_getRandomInt(0, numColorPairs)];
        colorOne = pair.color_one;
        colorTwo = pair.color_two;

        // Set new color CSS vars on root.
        document.documentElement.style.setProperty('--colorone', colorOne, '');
        document.documentElement.style.setProperty('--colortwo', colorTwo, '');

        // Update color voting link. Remove # from colors.
        var voteButton = document.getElementById('js-colors-vote-link');
        voteButton.setAttribute('href', 'http://randoma11y.com/#/?hex=' + colorOne.slice(1) + '&compare=' + colorTwo.slice(1));
        _generateQuoteLink();
    };


    var getNewFont = function() {
        font = fontCache[_getRandomInt(0, fontCache.length)].family;
        _loadFont(font);
        document.documentElement.style.setProperty('--font', font, '');
        fontLink.setAttribute('href', 'https://fonts.google.com/specimen/' + font);
        _generateQuoteLink();
    };

    return {
        init: init,
        getNewQuote: getNewQuote,
        getNewColors: getNewColors,
        getNewFont: getNewFont
    }
}());
