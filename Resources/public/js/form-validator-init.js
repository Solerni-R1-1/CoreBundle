/*
 * This file is part of Solerni.
 *
 * Copyright (C) 2014 Orange
 *
 * Description: Init form verification jQuery plugin
 *
 * This source file is licensed under the terms of the MIT licence: http://spdx.org/licences/MIT
 */

jQuery(document).ready(function(jQuery) {

    var SolerniMsg = {
      errorTitle : 'Le formulaire n\'est pas valide !',
      requiredFields : 'Ce champ est requis',
      badTime : 'You have not given a correct time',
      badEmail : 'Vous devez saisir une adresse email valide',
      badTelephone : 'You have not given a correct phone number',
      badSecurityAnswer : 'You have not given a correct answer to the security question',
      badDate : 'You have not given a correct date',
      lengthBadStart : 'You must give an answer between ',
      lengthBadEnd : ' caractères',
      lengthTooLongStart : 'Votre réponse est trop longue de ',
      lengthTooShortStart : 'Votre réponse est trop courte de ',
      notConfirmed : 'Les valeurs ne correspondent pas',
      badDomain : 'Incorrect domain value',
      badUrl : 'The answer you gave was not a correct URL',
      badCustomVal : 'You gave an incorrect answer',
      badInt : 'The answer you gave was not a correct number',
      badSecurityNumber : 'Your social security number was incorrect',
      badUKVatAnswer : 'Incorrect UK VAT Number',
      badStrength : 'The password isn\'t strong enough',
      badNumberOfSelectedOptionsStart : 'You have to choose at least ',
      badNumberOfSelectedOptionsEnd : ' answers',
      badAlphaNumeric : 'Votre réponse ne doit contenir que des lettres ou des chiffres ',
      badAlphaNumericExtra: ' et ',
      wrongFileSize : 'The file you are trying to upload is too large',
      wrongFileType : 'The file you are trying to upload is of wrong type',
      groupCheckedRangeStart : 'Please choose between ',
      groupCheckedTooFewStart : 'Please choose at least ',
      groupCheckedTooManyStart : 'Please choose a maximum of ',
      groupCheckedEnd : ' item(s)'
    };

    /* USING security.dev because I changed the method to find password confirmation */
    jQuery.validate({
        borderColorOnError: '#FF004F',
        showHelpOnFocus:    false,
        addSuggestions:     false,
        langage:            SolerniMsg
    });

    // Init Strength Display as callback doesn't work properly in IE8
    passInit = function() {
        var config = {
            fontSize:   '.9em',
            padding:    '.5em .3em .2em .3em',
            bad :       'Complexité très faible',
            weak :      'Complexité faible',
            good :      'Complexité insuffisante',
            strong :    'Complexité optimale'
        };
        // Creation de compte publique par l'utilisateur
        if ( jQuery('#profile_form_plainPassword_first').length > 0 ) {
            jQuery('#profile_form_plainPassword_first').displayPasswordStrength( config );
        }
        // Modification de mot de passe par l'utilisateur
        if ( jQuery('#reset_pwd_form_plainPassword_first').length > 0 ) {
            jQuery('#reset_pwd_form_plainPassword_first').displayPasswordStrength( config );
        }
        // Creation d'un utilisateur par l'administrateur
        if ( jQuery('#profile_form_creation_plainPassword_first').length > 0 ) {
            jQuery('#profile_form_creation_plainPassword_first').displayPasswordStrength( config );
        }
    }

    // Launch real-time display of pwd strenght
    passInit();

});