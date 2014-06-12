/* 
 * This file is part of Solerni.
 * 
 * Copyright (C) 2014 Orange
 * 
 * Description: Inscription button behavior when user is not registered in the MOOC workspace
 * 
 * This source file is licensed under the terms of the MIT licence: http://spdx.org/licences/MIT
 */

jQuery( document ).ready(function() {
    // CACHING
    inscriptionButton = jQuery( '.js-btn-inscrire' );
    inscriptionButtonUrl = inscriptionButton.data('action-url');
    redirectUrl = inscriptionButton.data('redirect-url');
   
    // ACTIVATE BUTTON
    inscriptionButton.removeAttr( 'disabled' );
    // ACTIVATE BEHAVIOR
    inscriptionButton.on ( 'click', function() { 
        if ($(this).hasClass('js-btn-login')) {
            window.location.replace(inscriptionButtonUrl);
        } else {
            sendInscription();
        }
    });
    

    
    function sendInscription() {
        buttonAjaxSuccess = function( data ) {
            // UPDATE STYLE (TOGGLE)
            inscriptionButton.toggleClass('is-registered');
            window.location.replace(redirectUrl);
         }
        buttonAjaxError = function( jqXHR, textStatus ) {
            // QUICK IMPLEMENTATION
            // todo: add messages in macros.flashbox twig (if possible)
            alert( 'L\'opération de mise à jour de vos informations ne s\est pas effectué normalement pour la raison : "' + textStatus + '" . Cette page va se recharger. Merci d\'essayer à nouveau');
            location.reload();
         }
        jQuery.ajax({
            type: 'POST',
            url: inscriptionButtonUrl,
            success: function ( data ) {
                buttonAjaxSuccess( data );
            },
            dataType: 'json',
            async: true,
            error: function( jqXHR, textStatus ) { 
                buttonAjaxError( jqXHR, textStatus );
            }
        });
    }
});