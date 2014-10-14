/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$( document ).ready(function() {

    //If errors
    if($('#message_form_to').offsetParent().children().last().attr('id') != 'message_form_to'){

        var css = '';
        if($('#message_form_to').offsetParent().hasClass( "col-md-9" )){
            css = "col-md-9";
        } else {
            css = "col-md-6";
        }
        $('#message_form_to').offsetParent().after('<div id="message_form_to_error" class="' + css + '">' + 
            '<div class="help-block field-error">' + 
            $('#message_form_to').next().html()
            + '</div></div>');
        ;

        $('#message_form_to').next().remove();
    }

    $('#message_form_to').offsetParent().html(
        '<div class="input-group">' +
            $('#message_form_to').offsetParent().html() +
            '<span class="input-group-btn">' +
                '<button id="contacts-button" class="btn btn-primary" type="button">' +
                    '<i class="icon-user"></i>' +
                '</button>' +
            '</span>' +
        '</div>'
    );
    

});    

    var currentType = 'user';

    var users = [];
    var groups = [];
    var workspaces = [];

    var typeMap = {
        'user': [],
        'group': [],
        'workspace': []
    };

    function getPage(tab)
    {
        var page = 1;

        for (var i = 0; i < tab.length; i++) {
            if (tab[i] === 'page') {
                if (typeof(tab[i + 1]) !== 'undefined') {
                    page = tab[i + 1];
                }
                break;
            }
        }

        return page;
    }

    function getSearch(tab)
    {
        var search = '';

        for (var i = 0; i < tab.length; i++) {
            if (tab[i] === 'search') {
                if (typeof(tab[i + 1]) !== 'undefined') {
                    search = tab[i + 1];
                }
                break;
            }
        }

        return search;
    }

    function initTempTab()
    {
        typeMap['user'] = users.slice();
        typeMap['group'] = groups.slice();
        typeMap['workspace'] = workspaces.slice();
    }

    function displayCheckBoxStatus()
    {
        $('.contact-chk').each(function () {
            var contactId = $(this).attr('contact-id');

            if (typeMap[currentType].indexOf(contactId) >= 0) {
                $(this).attr('checked', 'checked');
            }
        });
    }

    function cleanToList(toList){
        //Clean meta information
        var reg = new RegExp("/\([^\)]*\)/", "g");
        return toList.replace(reg, toList);
    }

    function displayPager(type, normalRoute, searchRoute)
    {
        currentType = type;
        var toList = $('#message_form_to').val();

        var toList = cleanToList(toList);

        var toListArray = toList.split(';');
        var search = toListArray[toListArray.length - 1].trim();
        var route;

        if (search === '') {
            route = Routing.generate(normalRoute);
        } else {
            route = Routing.generate(
                searchRoute, {'search': search}
            );
        }

        $.ajax({
            url: route,
            type: 'GET',
            success: function (datas) {
                $('#contacts-list').empty();
                $('#contacts-list').append(datas);
                displayCheckBoxStatus();
            }
        });
    }

    function getUsersFromInput(route, elements, queryStringKey)
    {
        var parameters = {};

        if (elements.length > 0) {
            parameters[queryStringKey] = elements;
            route = Routing.generate(route);
            route += '?' + $.param(parameters);

            $.ajax({
                url: route,
                statusCode: {
                    200: function (datas) {
                        var currentValue = $('#message_form_to').attr('value');

                        if (currentValue === undefined) {
                            currentValue = '';
                        }

                        currentValue += datas;

                        //Fix selection of user
                        $('#message_form_to').attr('value', currentValue);
                        $('#message_form_to').val(currentValue);

                    }
                },
                type: 'GET',
                async: false
            });
        }
    }

    function updateContactInput()
    {
        $('#message_form_to').attr('value', '');
        getUsersFromInput('claro_usernames_from_users', users, 'userIds');
        getUsersFromInput('claro_names_from_groups', groups, 'groupIds');
        getUsersFromInput('claro_names_from_workspaces', workspaces, 'workspaceIds');
    }

$( document ).ready(function() {
    /**
     *
     * Click on user button to open Modal windows 
     *
     **/
    $('#contacts-button').click(function () {
        initTempTab($(this).val());
        displayPager(
            'user',
            'claro_message_contactable_users',
            'claro_message_contactable_users_search'
        );
        $('#contacts-box').modal('show');
    });

    $('#users-nav-tab').on('click', function () {
        $('#groups-nav-tab').attr('class', '');
        $('#workspaces-nav-tab').attr('class', '');
        $(this).attr('class', 'active');
        displayPager(
            'user',
            'claro_message_contactable_users',
            'claro_message_contactable_users_search'
        );
    });

    $('#groups-nav-tab').on('click', function () {
        $('#users-nav-tab').attr('class', '');
        $('#workspaces-nav-tab').attr('class', '');
        $(this).attr('class', 'active');
        displayPager(
            'group',
            'claro_message_contactable_groups',
            'claro_message_contactable_groups_search'
        );
    });

    $('#workspaces-nav-tab').on('click', function () {
        $('#groups-nav-tab').attr('class', '');
        $('#users-nav-tab').attr('class', '');
        $(this).attr('class', 'active');
        displayPager(
            'workspace',
            'claro_message_contactable_workspaces',
            'claro_message_contactable_workspaces_search'
        );
    });

});

    $('body').on('click', '.pagination > ul > li > a', function (event) {
        event.preventDefault();
        event.stopPropagation();
        var element = event.currentTarget;
        var url = $(element).attr('href');
        var route;

        if (url !== '#') {
            var urlTab = url.split('/');
            var page = getPage(urlTab);
            var search = getSearch(urlTab);

            if (currentType === 'user') {
                route = (search !== '') ?
                    Routing.generate('claro_message_contactable_users_search', {'page': page, 'search': search}):
                    Routing.generate('claro_message_contactable_users', {'page': page});
            }

            if (currentType === 'group') {
                route = (search !== '') ?
                    Routing.generate('claro_message_contactable_groups_search', {'page': page, 'search': search}):
                    Routing.generate('claro_message_contactable_groups', {'page': page});
            }

            if (currentType === 'workspace') {
                route = (search !== '') ?
                    Routing.generate('claro_message_contactable_workspaces', {'page': page}):
                    Routing.generate('claro_message_contactable_workspaces', {'page': page});
            }

            $.ajax({
                url: route,
                success: function (datas) {
                    $('#contacts-list').empty();
                    $('#contacts-list').append(datas);
                    displayCheckBoxStatus();
                },
                type: 'GET'
            });
        }
    });

    $('body').on('click', '.contact-chk', function () {
        var contactId = $(this).attr('contact-id');
        var checked = $(this).prop('checked');
        var index = typeMap[currentType].indexOf(contactId);

        if (checked && index < 0) {
            typeMap[currentType].push(contactId);
        }
        else {
            typeMap[currentType].splice(index, 1);
        }

        $pseudo = $( this ).parent().children(".contact-pseudo").val();
        $cleanpseudo = $pseudo.replace('.', '__DOT__');

        checkboxTester($( this ));

        $('.contact_selected_delete').on('click', function (e) {
            $pseudo = $( this ).parent().attr('contact-pseudo');
            $( "input[value='"+ $pseudo +"']" ).parent().parent().removeClass('selected');
            $( "input[value='"+ $pseudo +"']" ).parent().children('.contact-chk').removeAttr('checked');
            removeContact($pseudo);
            recalcul();
        });

        recalcul();

        console.log(typeMap);
    });

    $('#add-contacts-confirm-ok').click(function () {
        users = typeMap['user'].slice();
        groups = typeMap['group'].slice();
        workspaces = typeMap['workspace'].slice();
        updateContactInput();
        $('#contacts-box').modal('hide');
    });


        
    function removeContact($contact){
        $cleanpseudo = $contact.replace('.', '__DOT__');
        $( "#ctct_"+$cleanpseudo ).remove();
        /*$newList = $( "#contacts_selected_csv").val().replace($pseudo, '').replace(';;',';');
        if($newList.charAt(0) == ';'){
            $newList = $newList.substring(1);
        }
        if($newList.charAt($newList.length - 1) == ';'){
            $newList = $newList.substring(0, $newList.length - 1);
        }
        $( "#contacts_selected_csv").val($newList);*/
    }

    function recalcul(){

        cpt = typeMap['user'].length;

        //hide all
        $( '.counter_b' ).addClass('hide');

        //display one
        if(cpt == 0){$( '.counter0' ).removeClass('hide'); $('#add-contacts-confirm-ok').attr("disabled", "disabled"); }
        if(cpt == 1){$( '.counter1' ).removeClass('hide'); $('#add-contacts-confirm-ok').removeAttr("disabled");}
        if(cpt > 1){$( '.counterX' ).removeClass('hide'); $( '.counterValue' ).text(cpt); $('#add-contacts-confirm-ok').removeAttr("disabled");}
    }

    function checkboxTester($checkbox){

        $pseudo = $checkbox.parent().children(".contact-pseudo").val();
        $cleanpseudo = $pseudo.replace('.', '__DOT__');

        if ( $checkbox[0].checked) {

            $checkbox.parent().parent().addClass('selected');
            $( "#contacts_selected" ).append( "<li id='ctct_"+$cleanpseudo+"' class='contact_selected' contact-pseudo="+$pseudo+" >" + $pseudo + "<span class='contact_selected_delete'>X</span></li>" );
            
            if($( "#contacts_selected_csv").val() == ''){
                $( "#contacts_selected_csv").val($( "#contacts_selected_csv").val() + $pseudo);
            } else {
                $( "#contacts_selected_csv").val($( "#contacts_selected_csv").val() + ';' + $pseudo);
            }
            
        } else {
            $checkbox.parent().parent().removeClass('selected');      
            removeContact($pseudo);
        }
    }
