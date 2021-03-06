/**
 * jQuery Form Validator Module: Security
 * ------------------------------------------
 * Created by Victor Jonsson <http://victorjonsson.se>
 *
 * This module adds validators typically used in registration forms.
 * This module adds the following validators:
 *  - spamcheck
 *  - confirmation
 *  - strength
 *  - backend
 *
 * @website http://formvalidator.net/#security-validators
 * @license Dual licensed under the MIT or GPL Version 2 licenses
 * @version 2.1.38
 */
(function($) {

    /*
     * Simple spam check
     */
    $.formUtils.addValidator({
        name : 'spamcheck',
        validatorFunction : function(val, $el, config) {
            var attr = $el.valAttr('captcha');
            return attr === val;
        },
        errorMessage : '',
        errorMessageKey: 'badSecurityAnswer'
    });


    /*
     * Validate confirmation
     * Modified for Solerni - Do not Update or change the find method
     */
    $.formUtils.addValidator({
        name : 'confirmation',
        validatorFunction : function(value, $el, config, language, $form) {
            var conf = '',
                confInputName = $el.attr('data-name') + '_confirmation',
                confInput = $form.find('input[data-name="' +confInputName+ '"]').eq(0);
            if (confInput) {
                conf = confInput.val();
            } else {
                console.warn('Could not find an input with name "'+confInputName+'"');
            }

            return value === conf;
        },
        errorMessage : '',
        errorMessageKey: 'notConfirmed'
    });

    /*
     * Validate password strength
     */
    $.formUtils.addValidator({
        name : 'strength',
        validatorFunction : function(val, $el, conf) {
            var requiredStrength = $el.valAttr('strength')

            if(requiredStrength && requiredStrength > 3) {
                requiredStrength = 3;
            }

            var strengthValue = $.formUtils.validators.validate_strength.calculatePasswordStrength(val);

            return strengthValue.score >= requiredStrength;
        },
        errorMessage : '',
        errorMessageKey: 'badStrength',

        /**
         * Code more or less borrowed from jQuery plugin "Password Strength Meter"
         * written by Darren Mason (djmason9@gmail.com), myPocket technologies (www.mypocket-technologies.com)
         * @param {String} password
         * @return {Number}
         */
        calculatePasswordStrength : function(password) {

            var translator = window.Translator;
            var score = 0;
            var message = [translator.get('platform:password_hint_intro')];
            var message_out = [];

            var checkRepetition = function (pLen, str) {
                var res = "";
                for (var i = 0; i < str.length; i++) {
                    var repeated = true;

                    for (var j = 0; j < pLen && (j + i + pLen) < str.length; j++) {
                        repeated = repeated && (str.charAt(j + i) == str.charAt(j + i + pLen));
                    }
                    if (j < pLen) {
                        repeated = false;
                    }
                    if (repeated) {
                        i += pLen - 1;
                        repeated = false;
                    }
                    else {
                        res += str.charAt(i);
                    }
                }
                return res;
            };

            //password is just a numbers or chars
            passLength = password.length || 0;
            if (passLength >= 8 ) {
                score += 10;
            } else {
                var missingCar = 8 - passLength;
                if ( missingCar == 1 ) {
                    numberMissing = missingCar + translator.get('platform:password_hint_morecar');
                } else {
                    numberMissing = missingCar + translator.get('platform:password_hint_morecars');
                }
                message.push( numberMissing );
            }

            //password has 1 numbers
            if (password.match(/([0-9])/)) {
                score += 10;
            } else {
                message.push(translator.get('platform:password_hint_onenumber'));
            }

            //password has 1 symbols
            if (password.match(/([!@#$%^&\*?_~])/)) {
                score += 10;
            } else {
                message.push(translator.get('platform:password_hint_onesymbol'));
            }

            //password has Upper and Lower chars
            if (password.match(/([a-z])/)) {
                score += 10;
            } else {
                message.push(translator.get('platform:password_hint_onemin'));
            }

            if (password.match(/([A-Z])/)) {
                score += 10;
            } else {
                message.push(translator.get('platform:password_hint_onemaj'));
            }

            // Pas d'espace
            if ( ! password.match(/([\s])/) ) {
                score += 10;
            } else {
                message_out.push(translator.get('platform:password_hint_nowhitespace'));
            }

            //verifying 0 < score < 100
            if (score < 0) {
                score = 0;
            }

            if (score > 100) {
                score = 100;
            }

            if (score < 12) {
                score = 0;
            }
            else if (score < 36) {
                score = 1;
            }
            else if(score < 60) {
                score = 2;
            }
            else {
                score = 3;
            }

            if ( message.length == 1 ) {
                message = ""
            }

            return {
                'score': score,
                'message': message,
                'message_out': message_out
            }
        },

        strengthDisplay : function( $el, options ) {

            var elementTitle = $el[0].id; // just IE8 stuff which loose object properties somewhere in the call

            var config = {
                fontSize: '12pt',
                padding: '4px',
                bad : 'Very bad',
                weak : 'Weak',
                good : 'Good',
                strong : 'Strong'
            };

            if (options) {
                $.extend(config, options);
            }

            $(document).on('keyup', function(event) {

                $el = jQuery('#'+elementTitle);

                if ( $el.attr('id') !== event.target.id ) {
                    return false; // Only use instanciated element in the first place. Cannot bind to input element because IE8
                }

                var val = $el.val();
                var $parent = typeof config.parent == 'undefined' ? $el.parent() : $(config.parent);
                var $displayContainer = $parent.find('.strength-meter');
                if($displayContainer.length == 0) {
                    $displayContainer = $('<span></span>');
                    $displayContainer
                        .addClass('strength-meter')
                        .appendTo($parent);
                }

                if( !val ) {
                    $displayContainer.hide();
                } else {
                    $displayContainer.show();
                }

                var strength = $.formUtils.validators.validate_strength.calculatePasswordStrength(val);
                var css = {
                    background: 'transparent',
                    color : 'rgb( 255, 0, 80 )',
                    fontWeight : 'bold',
                    //border : 'red solid 1px',
                    //borderWidth : '0px 0px 4px',
                    display : 'inline-block',
                    fontSize : config.fontSize,
                    padding : config.padding
                };

                var text = config.bad;

                if(strength.score == 1) {
                    //css.borderColor = 'rgb( 255, 0, 79 )';
                    css.color = 'rgb( 255, 0, 80 )';
                    //text = strength.message;
                }
                else if(strength.score == 2) {
                    //css.background = 'lightyellow';
                    //css.borderColor = 'goldenrod';
                    css.color = '#FF8800';
                    //text = config.good;
                }
                else if(strength.score >= 3) {
                    //css.background = 'lightgreen';
                    //css.borderColor = '#C9D200';
                    css.color = '#C9D200';
                    //text = config.strong;
                }
                var message = "";
                if ( strength.message.length > 0 ) {
                    for ( var i = 0; i < strength.message.length; i++ ) {
                        endof = ", ";
                        // Do not add comma on first and last iteration
                        if ( i == strength.message.length - 1 || i == 0 ) {
                            endof = "";
                        }

                        message += strength.message[i] + endof;
                    }
                } else {
                    css.display = "none";
                }

                if ( strength.message_out.length > 0 ) {
                    jQuery('.strength-out').text(strength.message_out[0]).css(css);
                } else {
                    jQuery('.strength-out').text('');
                }

                $displayContainer
                    .css(css)
                    .text(message);
            });
        }
    });

    var requestServer = function(serverURL, $element, val, conf, callback) {
        $.ajax({
            url : serverURL,
            type : 'POST',
            cache : false,
            data : $element.attr('name')+'='+val,
            dataType : 'json',
            success : function(response) {

                if(response.valid) {
                    $element.valAttr('backend-valid', 'true');
                }
                else {
                    $element.valAttr('backend-invalid', 'true');
                    if(response.message)
                        $element.attr(conf.validationErrorMsgAttribute, response.message);
                    else
                        $element.removeAttr(conf.validationErrorMsgAttribute);
                }

                if( !$element.valAttr('has-keyup-event') ) {
                    $element
                        .valAttr('has-keyup-event', '1')
                        .bind('keyup', function(evt) {
                            if( evt.keyCode != 9 && evt.keyCode != 16 ) {
                                $(this)
                                    .valAttr('backend-valid', false)
                                    .valAttr('backend-invalid', false)
                                    .removeAttr(conf.validationErrorMsgAttribute);
                            }
                        });
                }

                callback();
            }
        });
    },
    disableFormSubmit = function() {
        return false;
    };

    /*
     * Server validation
     * Flow (form submission):
     *  1) Check if the value already has been validated on the server. If so, display the validation
     *     result and continue the validation process, otherwise continue to step 2
     *  2) Return false as if the value is invalid and set $.formUtils.haltValidation to true
     *  3) Disable form submission on the form being validated
     *  4) Request the server with value and input name and add class 'validating-server-side' to the form
     *  5) When the server responds an attribute will be added to the element
     *      telling the validator that the input has a valid/invalid value and enable form submission
     *  6) Run form submission again (back to step 1)
     */
    $.formUtils.addValidator({
        name : 'server',
        validatorFunction : function(val, $el, conf, lang, $form) {

            var backendValid = $el.valAttr('backend-valid'),
                backendInvalid = $el.valAttr('backend-invalid'),
                serverURL = document.location.href;

            if($el.valAttr('url')) {
                serverURL = $el.valAttr('url');
            } else if('serverURL' in conf) {
                serverURL = conf.backendUrl;
            }

            if(backendValid)
                return true;
            else if(backendInvalid)
                return false;

            if($.formUtils.isValidatingEntireForm) {
                $form
                    .bind('submit', disableFormSubmit)
                    .addClass('validating-server-side')
                    .addClass('on-blur');

                $el.addClass('validating-server-side');

                requestServer(serverURL, $el, val, conf, function() {
                    $form
                        .removeClass('validating-server-side')
                        .removeClass('on-blur')
                        .get(0).onsubmit = function() {};

                    $form.unbind('submit', disableFormSubmit);
                    $el.removeClass('validating-server-side');

                    // fire submission again!
                    $form.trigger('submit');
                });

                $.formUtils.haltValidation = true;
                return false;

            } else {
                // validaiton on blur
                $form.addClass('validating-server-side');
                $el.addClass('validating-server-side');
                requestServer(serverURL, $el, val, conf, function() {
                    $form.removeClass('validating-server-side');
                    $el.removeClass('validating-server-side');
                    $el.trigger('blur');
                });
                return true;
            }
        },
        errorMessage : '',
        errorMessageKey: 'badBackend',
        validateOnKeyUp : false
    });

    $.fn.displayPasswordStrength = function(conf) {
        new $.formUtils.validators.validate_strength.strengthDisplay( this, conf );
        return this;
    };

})(jQuery);