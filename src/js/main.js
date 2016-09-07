
    // console.log('hi');

    // HELPER FUNCTIONS

    /**
     * foreach function for querySelectorAll elements.
     */
    function forEach(array, callback, scope) {
      for (var i = 0; i < array.length; i++) {
        callback.call(scope, i, array[i]); // passes back stuff we need
      }
    };

    function contains(arr, v) {
        for(var i = 0; i < arr.length; i++) {
            if(arr[i] === v) return true;
        }
        return false;
    };

    function unique(str) {
        var arr = [];
        for(var i = 0; i < str.length; i++) {
            if(!contains(arr, str[i])) {
                arr.push(str[i]);
            }
        }
        return arr;
    }


    /**
     * Returns a random integer between min (included) and max (included)
     * Using Math.round() will give you a non-uniform distribution!
     * @link https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Math/random#Examples
     */
    function getRandomInt(min, max) {
      min = Math.ceil(min);
      max = Math.floor(max);
      return Math.floor(Math.random() * (max - min)) + min;
    }


    ///////////////////

    // THE REAL STUFF

    // Set default font.
    var font = document.documentElement.style.getPropertyValue('--font');

    // XMLHttpRequest type.
    var invocation = new XMLHttpRequest();
    var requestType = '';

    // Caches.
    var colorCache = [];
    var fontCache = [];

    // API URLs/info.
    var randoma11yUrl = 'http://www.randoma11y.com/stats/';
    var forismaticUrl = 'https://crossorigin.me/http://api.forismatic.com/api/1.0/?method=getQuote&lang=en&format=json';

    var fontsUrl = '/src/font-list.json';

    var combos = 113592;
    var colorsURL = 'http://www.randoma11y.com/stats/';
    var colorsPageURL = 'http://randoma11y.com/combos?';

    // Buttons.
    var quoteText = document.getElementById('js-quote-text');
    var quoteAuthor = document.getElementById('js-quote-author');
    var quoteLink = document.getElementById('js-quote-link');
    var buttons = document.querySelectorAll('.button');

    // Prevent link clicking for all buttons.
    // forEach(buttons, function (index, value) {
    //   console.log(index, value); // passes index + value back!
    //   buttons[index].addEventListener('click', function(e){ //say this is an anchor
               //do something
            //   e.preventDefault();
        //  });
    // });

    // Set functions for 'Get New Font' button.
    var newFont = document.getElementById('js-new-font');
    // console.log(newFont);
    newFont.addEventListener('click', function(e) {
        e.preventDefault();

        // Send new request if cache is empty, or load from cache.
        if (fontCache.length === 0) {
            console.log('1');
            requestType = 'font';
            callOtherDomain( fontsUrl );
        } else if (fontCache.length > 0) {
            console.log('2');
            getNewFont();
        }
    });


    // 'Get New Quote' button.
    var newQuote = document.getElementById('js-new-quote');
    // console.log(newQuote);
    newQuote.addEventListener('click', function(e) {
        e.preventDefault();

        requestType = 'quote';
        callOtherDomain( forismaticUrl );
    });

    // 'Get New Colors' button.
    var newColors = document.getElementById('js-new-colors');
    // console.log(newQuote);
    newColors.addEventListener('click', function(e) {
        e.preventDefault();

        // Send new request if cache is empty, or load from cache.
        if (colorCache.length === 0) {
            requestType = 'colors';
            callOtherDomain( colorsPageURL );
        } else if (colorCache.length > 0) {
            getNewColors();
        }
    });




    function callOtherDomain( url ) {
        if (invocation) {
            invocation.open('GET', url, true);
            invocation.onreadystatechange = handler;
            invocation.send();
        }
    }

    function handler( evtXHR ) {
        if (invocation.readyState === XMLHttpRequest.DONE) {
            if (invocation.status === 200) {
                // Get response and replace escaped single quotes with
                // unescaped single quotes. (Forismatic escapes single
                // quotes in their JSON response, however that is not
                // valid JSON).
                var response = JSON.parse(invocation.responseText.replace("\\'", "'"));

                // Different functions based on which button was pressed.
                switch (requestType) {
                    case 'quote':
                        // Get new quote from response.
                        getNewQuote(response);
                        break;

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

    }


    function getNewQuote(response) {
        // Set new quote, and characters needed for it.
        quoteText.innerHTML = response.quoteText;
        loadFont(font);

        // Don't display blank author.
        if (response.quoteAuthor.length > 0)
            quoteAuthor.innerHTML = response.quoteAuthor;
        else
            quoteAuthor.innerHTML = 'Anonymous';
    }


    function getNewColors() {
        // Get random color pairing.
        var numColorPairs = colorCache.length;
        var pair = colorCache[getRandomInt(0, numColorPairs)];
        var colorOne = pair.color_one;
        var colorTwo = pair.color_two;

        // Set new color CSS vars on root.
        document.documentElement.style.setProperty('--colorone', colorOne);
        document.documentElement.style.setProperty('--colortwo', colorTwo);

        // Update color voting link. Remove # from colors.
        var voteButton = document.getElementById('js-colors-vote-link');
        voteButton.setAttribute('href', 'http://randoma11y.com/#/?hex=' + colorOne.slice(1) + '&compare=' + colorTwo.slice(1));
    }


    function getNewFont() {
        font = fontCache[getRandomInt(0, fontCache.length)].family;
        loadFont(font);
        document.documentElement.style.setProperty('--font', font);
    }


    function getUniqueChars() {
        return unique(quoteText.innerHTML).join('');
    }


    // Load a Google font by name.
    function loadFont(font) {
      WebFontConfig = {
          google: {
            families: [font],
            text: getUniqueChars()
          },
          timeout: 2000 // Set the timeout to two seconds
        };

      (function(d) {
          var wf = d.createElement('script'), s = d.scripts[0];
          wf.src = 'https://ajax.googleapis.com/ajax/libs/webfont/1.6.16/webfont.js';
          s.parentNode.insertBefore(wf, s);
       })(document);
    };
