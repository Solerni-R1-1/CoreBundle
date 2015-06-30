/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

(function($) {
    "use strict";

    $(function() {
        var form                                = $('#public_profile_preferences');
        var formName                            = 'user_public_profile_preferences_form';
        var basicInformationPublicProfile       = $('#user_public_profile_preferences_form_display_base_informations', form);
        var optionalInformationPublicProfile    = $('#user_public_profile_preferences_form_display_optional_information', form);
        var userPublicProfileNotVisibleBlock    = $('#user_public_profile_not_visible');
        var userPublicProfileVisibleBlocks      = $(".profil_visible");
        var preferencesField                    = $('.preferences input[type=checkbox][name!="user_public_profile_preferences_form[display_base_informations]"]');
        var currentUserPublicProfilePreferences = {};
        var sharedPolicyLinkPlatformUsers       = $('.shared_policy_link_platform_users');
        var sharedPolicyLinkPlatformEverybody   = $('.shared_policy_link_everybody');
        var preferencesWrapper                  = $('.preferences_wrapper');

        preferencesField.each(function() {
            var preferenceName = parseFieldName($(this).attr('name'));

            currentUserPublicProfilePreferences[preferenceName] = $('#' + formName + '_' + preferenceName).attr('checked');
        });

        sharedPolicyLinkPlatformUsers.click(function() {
            $('#user_public_profile_preferences_form_share_policy_1').click();
        });
        sharedPolicyLinkPlatformEverybody.click(function() {
            $('#user_public_profile_preferences_form_share_policy_2').click();
        });

        var currentSharedPolicy = parseFormValue($(form).serializeArray()).share_policy;

        form.change(function() {
            manageVisibility(parseFormValue($(this).serializeArray()));
        });

        if ( $('#user_public_profile_preferences_form_share_policy_0').is(':checked') ) {
            preferencesWrapper.addClass('hidden');
        }

        function manageVisibility(data)
        {
            if (data['share_policy'] != undefined && data['share_policy'] != currentSharedPolicy) {
                sharedPolicyUpdated(data['share_policy']);
                currentSharedPolicy = data['share_policy'];

                preferencesField.each(function() {
                    var preferenceName = parseFieldName($(this).attr('name'));
                    updateFieldVisibility(preferenceName, currentUserPublicProfilePreferences[preferenceName]);
                });
            }
            else {
                // Case where share policy is nobody but we want to display a field, changing shared policy to platform users
                if (0 == data['share_policy']) {
                    for (var currentUserPublicProfilePreferenceIndex in currentUserPublicProfilePreferences) {
                        if (data[currentUserPublicProfilePreferenceIndex] == undefined) {
                            currentUserPublicProfilePreferences[currentUserPublicProfilePreferenceIndex] = false;
                        }
                        else {
                            currentUserPublicProfilePreferences[currentUserPublicProfilePreferenceIndex] = 'checked';
                        }
                    }
                    $("input[name='" + formName + "[share_policy]'][value=1]", form).click();
                }
                else {
                    preferencesField.each(function() {
                        var preferenceName = parseFieldName($(this).attr('name'));
                        updateFieldVisibility(preferenceName, data[preferenceName] != undefined);
                    });
                }
            }
        }

        function sharedPolicyUpdated(sharedPolicy) {
            if (0 == sharedPolicy) {
                userPublicProfileNotVisibleBlock.removeClass('hidden');
                userPublicProfileVisibleBlocks.addClass('hidden');
                preferencesWrapper.addClass('hidden');

                basicInformationPublicProfile
                    .prop('disabled', false)
                    .prop('checked', false);

                preferencesField.each(function() {
                    $(this).attr('checked', false);
                });
            }
            else {
                userPublicProfileVisibleBlocks.removeClass('hidden');
                userPublicProfileNotVisibleBlock.addClass('hidden');
                preferencesWrapper.removeClass('hidden');

                basicInformationPublicProfile
                    .prop('checked', true)
                    .prop('disabled', true);

                preferencesField.each(function() {
                    $(this).attr('checked', currentUserPublicProfilePreferences[parseFieldName($(this).attr('name'))]);
                });
            }
        }


        function updateFieldVisibility(field, visibility) {
            //console.log(field+"-"+visibility);
            var block = $('.' + field);
            if ( ! block.length ) {
                block = $('#' + field);
            }
            if (visibility) {
                block.removeClass('hidden');
                currentUserPublicProfilePreferences[field] = 'checked';
            }
            else {
                block.addClass('hidden');
                currentUserPublicProfilePreferences[field] = false;
            }
        }

        function parseFormValue(formValue)
        {
            var parsedFormValue = {};
            $.each(formValue, function(index, element) {
                var parsedName = element.name;
                var parsedName = parsedName.substring(formName.length + 1, parsedName.length - 1);
                if ('_token' != parsedName) {
                    parsedFormValue[parsedName] = element.value;
                }
            });

            return parsedFormValue;
        }

        function parseFieldName(name) {
            return name.substring(formName.length + 1, name.length - 1);
        }

        var moocCheckboxes = jQuery('.mooc_preferences input[type=checkbox]');

        function hideOrShowMooc(trigger, target) {
            if ( trigger.prop('checked') ) {
                target.removeClass('hide');
            } else {
                target.addClass('hide');
            }

            if ( jQuery('.slrn-profile-mooc').not('.hide').length == 0 ) {
                jQuery('.no_mooc_visible').removeClass('hide');
            } else {
                jQuery('.no_mooc_visible').addClass('hide')
            }
        }

        moocCheckboxes
                .each( function() {
                    hideOrShowMooc(jQuery(this), jQuery('.' + jQuery(this).attr('id') ));
                })
                .on( 'click', function() {
                    hideOrShowMooc(jQuery(this), jQuery('.' + jQuery(this).attr('id') ));
                });
    });
})(jQuery);