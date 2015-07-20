/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


function supports_local_storage() {
    try {
        return 'localStorage' in window && window['localStorage'] !== null;
    } catch(e) {
        return false;
    }
}

if ( ! supports_local_storage() ) {
    // define cookie functions
    function setCookie(cname, cvalue, exdays) {
        var d = new Date();
        d.setTime(d.getTime() + (exdays*24*60*60*1000));
        var expires = "expires="+d.toUTCString();
        document.cookie = cname + "=" + cvalue + "; " + expires;
    }

    function getCookie(cname) {
        var name = cname + "=";
        var ca = document.cookie.split(';');
        for(var i=0; i<ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0)==' ') c = c.substring(1);
            if (c.indexOf(name) == 0) return c.substring(name.length, c.length);
        }
        return "";
    }
}

function rememberAcceptance() {
    if ( supports_local_storage() ) {
        cookieWarnAccepted = localStorage.setItem('cookieWarnAccepted', 1);
    } else {
        cookieWarnAccepted = setCookie( 'cookieWarnAccepted', 1, 999 );
    }
    removeCookieWarning();
}

function removeCookieWarning() {
     jQuery('body').removeClass('displayCookieWarning');
}

function displayCookieWarning() {

    var translator = window.Translator;
    var CGUUrl = jQuery('.js-link_cgu').attr('href') + '#cookies';

    var CookieWarnMessage = translator.get('platform:cookie_warn_message');
    var CookieWarnButtonMore = translator.get('platform:know_more_cookies');

    var warningCookieDOM = jQuery('<div class="cookie-warning-container-wrapper"><div class="container cookie-warning-container"><div class="row cookie-warning-ribbon">' +
            '<div class="cookie-warning-text">'
                + CookieWarnMessage
            + '</div>'
            + '<div class="btn-row"><a href="'
                + CGUUrl
                + '" class="btn btn-gris">'
                + CookieWarnButtonMore
            + '</a>'
            + '<button class="btn btn-primary js-warn-cookie-trigger">OK</button>'
            + '</div></div></div>');

    jQuery('body')
        .addClass('displayCookieWarning')
        .prepend( warningCookieDOM );

    jQuery('.js-warn-cookie-trigger')
        .on('click', function() {
            rememberAcceptance();
    });
}

// Choose source of acceptance (browser dependant) and check existance
function isCookieWarningAccepted() {
    if ( supports_local_storage() ) {
        cookieWarnAccepted = localStorage.getItem('cookieWarnAccepted');
    } else {
        cookieWarnAccepted = getCookie( 'cookieWarnAccepted' );
    }
    return cookieWarnAccepted;
}

//  Display message if no acceptance
if ( ! isCookieWarningAccepted() ) {
    displayCookieWarning();
}