/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


var currentType = 'user';

var users = [];
var groups = [];
var workspaces = [];

var typeMap = {
    'user': [],
    'group': [],
    'workspace': []
};

var typeMapLabel = {
    'user': [],
    'group': [],
    'workspace': []
};

var isSearch = false;
var initialData = null;

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
            $(this).parent().parent().addClass('selected');
        }
    });
    listSelected();
    checkboxWatcher();
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

    //var toList = cleanToList(toList);

    var toListArray = toList.split(';');
    var search = '';
    if(isSearch) {
        search = toListArray[toListArray.length - 1].trim();
    }
    if(initialData !== null){
        typeMap = {
            'user': [],
            'group': [],
            'workspace': []
        };
        typeMapLabel = {
            'user': [],
            'group': [],
            'workspace': []
        };
    }
    /*if(!isSearch && initialData !== null){
        console.log('clic bouton ' + toListArray);
        var label;

        typeMap = {
            'user': [],
            'group': [],
            'workspace': []
        };

        for (var cpt in toListArray) {
            label = toListArray[cpt].trim();

            //Match workspace
            if(label.match("^\[(.)*\]$")){
                console.log(label + "is a workspace");
                typeMap['workspace'].push(label);
            //Match groupe
            } else if(label.match("^\{(.)*\}$")){
                console.log(label + "is a group");
                typeMap['group'].push(label);
            // so it's user
            } else {
                console.log(label + "is a user");
                typeMap['user'].push(label);
            }
        };

        console.log('typeMap ' + typeMap['user']);
        console.log('typeMap ' + typeMap['group']);
        console.log('typeMap ' + typeMap['workspace']);

    } else {
        console.log('switch dans modale');
    }*/
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
                    var currentValue = $('#message_form_to').val();

                    if (currentValue === undefined) {
                        currentValue = '';
                    }

                    if(isSearch && currentValue.charAt(currentValue.length - 1) != ';') {
                        //We must remove the last part of search
                        console.log(currentValue);
                        lastpos = currentValue.lastIndexOf(";");
                        if(lastpos == -1){
                            lastpos = 0;
                        }
                        currentValue = currentValue.substr(0, lastpos);
                        console.log(currentValue);
                    }

                    if(currentValue != '' && currentValue.charAt(currentValue.length - 1) != ';'){
                        currentValue += ';';   
                    }



                    currentValue += datas;

                    //Fix selection of user
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
    getUsersFromInput('claro_usernames_from_users', users, 'userIds');
    getUsersFromInput('claro_names_from_groups', groups, 'groupIds');
    getUsersFromInput('claro_names_from_workspaces', workspaces, 'workspaceIds');

    //Delete doublon
    currentValue = $('#message_form_to').val();
    labels = currentValue.split(';');
    uniqArray = new Array();
    $.each(labels, function(i, el){
    if($.inArray(el, uniqArray) === -1) 
        uniqArray.push(el);
    });
    $('#message_form_to').val(uniqArray.join(";"));
}

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
                '<button id="contacts-button-search" class="btn btn-primary contacts-button" type="button">' +
                    '<i class="icon-search"></i>' +
                '</button>' +
                '<button id="contacts-button-add" class="btn btn-primary contacts-button" type="button">' +
                    '<i class="icon-plus"></i>' +
                '</button>' +
            '</span>' +
        '</div>'
    );

    $('#message_form_to').on('propertychange keyup input paste change click', function () {
        statusSearch();
    });

    /**
     *
     * Click on user button to open Modal windows 
     *
     **/
    $('.contacts-button').on('click', function () {
        if( $( this ).attr("id") == 'contacts-button-search') {
            isSearch = true;
            initialData = $("#message_form_to").val();
        } else {
            isSearch = false;
        }
        initialData = $("#message_form_to").val();

        initTempTab();
        displayPager(
            'user',
            'claro_message_contactable_users',
            'claro_message_contactable_users_search'
        );
        $('#contacts-box').modal('show');
        listSelected();
    });

    $('#users-nav-tab').on('click', function () {
        $('#groups-nav-tab').attr('class', '');
        $('#workspaces-nav-tab').attr('class', '');
        $(this).attr('class', 'active');
        initialData = null;
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
        initialData = null;
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
        initialData = null;
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

    var label = $(this).parent().children(".contact-label").val();

    if (checked && index < 0) {
        typeMap[currentType].push(contactId);
        typeMapLabel[currentType].push([contactId, label]);
        $( this ).parent().parent().addClass('selected');
    }
    else {
        typeMap[currentType].splice(index, 1);
        typeMapLabel[currentType].splice(index, 1);
        $( this ).parent().parent().removeClass('selected');
    }

    recalcul();
    listSelected();
    checkboxWatcher();
});

$('#add-contacts-confirm-ok').click(function () {
    users = typeMap['user'].slice();
    groups = typeMap['group'].slice();
    workspaces = typeMap['workspace'].slice();
    updateContactInput();
    statusSearch();
    $('#contacts-box').modal('hide');
});

function recalcul(){
    cpt = 0;
    for (var type in typeMap){
        cpt += typeMap[type].length;
    }

    //hide all
    $( '.counter_b' ).addClass('hide');

    //display one
    if(cpt == 0){$( '.counter0' ).removeClass('hide'); $('#add-contacts-confirm-ok').attr("disabled", "disabled"); }
    if(cpt == 1){$( '.counter1' ).removeClass('hide'); $('#add-contacts-confirm-ok').removeAttr("disabled");}
    if(cpt > 1){$( '.counterX' ).removeClass('hide'); $( '.counterValue' ).text(cpt); $('#add-contacts-confirm-ok').removeAttr("disabled");}
}

function checkboxWatcher(){
    $('.contact_selected_delete').on('click', function (e) {
        label = $( this ).parent().attr('data-contact-label');
        type = $( this ).parent().attr('data-contact-type');

        $( "input[value='"+ label +"']" ).parent().parent().removeClass('selected');
        $( "input[value='"+ label +"']" ).parent().children('.contact-chk').removeAttr('checked');

        var contactId = $(this).parent().attr('contact-id');
        var index = typeMap[type].indexOf(contactId);

        typeMap[type].splice(index, 1);
        typeMapLabel[type].splice(index, 1);

        recalcul();
        listSelected();
        checkboxWatcher();
    });
}

function listSelected(){

    $('.contact_selected').remove();

    for (var type in typeMapLabel){
        for (var key in typeMapLabel[type]){
            key = typeMapLabel[type][key];

            var cleanedKey = key[1].replace('.', '__DOT__');

            $( "#contacts_selected_wrapper" ).append( "<span id='ctct_"+cleanedKey+"' class='tag label label-info contact_selected contact_selected_"+type+"' contact-id='"+key[0]+"' data-contact-label='"+key[1]+"' data-contact-type='"+type+"' >" + key[1] + "<span class='contact_selected_delete' data-role='remove'></span></span>" );
        }
    }
}

function statusSearch(){
    currentValue = $('#message_form_to').val();
    if(currentValue.charAt(currentValue.length - 1) != ';') {
        $('#contacts-button-search').removeAttr('disabled');
    } else {
        $('#contacts-button-search').attr('disabled','disabled');
    }
}
