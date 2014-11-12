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
        '<div class="">' +
            $('#message_form_to').offsetParent().html() +
            '<div class="suggest-box"><ul class="suggest-box-list"></ul></div>' +
        '</div>'
    );

	//Mapping on each change
    //$('#message_form_to').on('propertychange keyup input paste change click', function () {
    $('#message_form_to').on('keyup input change', function () {
        suggestContact($(this).val());
    });

});

page = 1;
var slot1 = null;
var slot2 = null;
var contacts = {'users':[], 'moocs':[]};


function suggestContact(search){

	//console.debug('start suggestContact('+search+')');
	search = search.trim();

	if(search.length < 3) {
		//console.debug('too small')
		return;
	}
	if(search.length > 10) {
		//console.debug('too big')
		return;
	}

	route = Routing.generate('claro_message_suggest', {'page': page, 'search': search});

	if(slot1 == null){
		slot1 = {route : route, page : page, search : search};
		//console.log("1 : " + slot1.search);

		//We avoid multi-same search
	} else if((search != slot1.search || page != slot1.page) && 
		((slot2 == null) || (slot2 != null && (search != slot2.search || page != slot2.page)))) {
		//We replace slot2
		slot2 = {route : route, page : page, search : search};
		//console.log("2 : " + slot2.search);
		//And do nothing else
		return;
	} else {
		//Just don't do it
		//console.log("nothing new : "+search+' : ');
		return;
	}

	//console.log("exec slot1 ");

	$.ajax({
            url: route,
            statusCode: {
                200: function (datas) {
                	datas = JSON.parse(datas);
                    //console.log("Retour Query : " + datas.length);
                   	
                   	data_tmp = datas;

                    if(slot2 != null){
						processData(datas, slot2);

                    	nextSearch = slot2.search;
	                    slot2 = null;
	                    slot1 = null;

	                    //There was a query waiting
	                    //console.log("exec new search : " + nextSearch);
	                    suggestContact(nextSearch);


                    } else {
                    	//console.log('no more query');

                    	processData(datas, slot1);
                    	slot1 = null;
                    }

	                
                }
            },
            type: 'GET',
            async: true
        });
}

var data_tmp;
function processData(datas, slot){
	//console.log("ProcessData()" + slot);
/*
	if (/^[\],:{}\s]*$/.test(datas.replace(/\\["\\\/bfnrtu]/g, '@').
replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, ']').
replace(/(?:^|:|,)(?:\s*\[)+/g, ''))) {
		console.debug("ok");
}else {
		console.debug("nok");
}*/

	$('.suggest-box-list').empty(); 
	//transfo = Routing.generate('claro_transfo_fltr', {'filters': ""});
//alert(transfo);
	html = '';
	users = datas.users;
	if(users == null){
		//console.debug('no users');
		return;
	}
	for (var i = users.length - 1; i >= 0; i--) {
		user = users[i];
		if(user.picture == null){
			pic = urlTransfo + '?img_uri=bundles/clarolinecore/images/avatar.jpg';
		} else {
			pic = urlTransfo + '?img_uri=uploads/pictures/' + user.picture;
		}

		html += '<li class="selectable" data-id="'+user.id+'" data-username="'+user.username+'">' +
					'<span class="user-avatar">' +
						'<img alt="ss" width="30px" height="30px" src="'+pic+'" class="user-avatar-img"/>' +
					'</span>' +
					'<span class="user-data">' +
						'<span class="user-line">'+mark(truncate(user.firstname), slot)+' '+mark(truncate(user.lastname), slot)+'</span>' +
						'<span class="user-line">'+mark(truncate(user.username,50), slot)+'</span>' +
					'</span>' +
				 '</li>';

		//alert($('#jqote2_simple').jqote({user:user}));
	};
	//console.log(html);
	$('.suggest-box-list').html(html);


    $('.selectable').on('click', function () {
        contacts.users[$(this).attr('data-id')] = $(this).attr('data-username');
        $('.suggest-box-list').empty();
        drawContacts();
    });
}

function truncate(string, size, complement){
	size = typeof size !== 'undefined' ? size : 30;

	if(string.length <= size){
		return string;
	} 

	complement = typeof complement !== 'undefined' ? complement : '...';

	string = string.substr(0, size - complement.length);
	string += complement;
	return string;
}

function mark(string, slot){
	string = string.replace(slot.search, '<mark>'+slot.search+'</mark>');
	return string;
}

function drawContacts(){
	//console.debug('drawContacts()');
    $('.contact_selected').remove();

    for (var type in contacts){
    	console.debug(type);
        for (var key in contacts[type]){
            username = contacts[type][key];
            //console.debug('>'+key);

            var cleanedUsername = username.replace('.', '__DOT__');

            $( "#contacts_selected_wrapper" ).append( "<span id='ctct_"+cleanedUsername+"' class='tag label label-info contact_selected contact_selected_"+type+"' contact-id='"+key+"' contact-username='"+cleanedUsername+"' data-contact-type='"+type+"' >" + username + "<span class='contact_selected_delete' data-role='remove'></span></span>" );
        }
    }

    $('.contact_selected_delete').on('click', function (e) {
    	//console.debug('remove contact');
        var contactId = $(this).parent().attr('contact-id');
        var cleanedUsername = $(this).parent().attr('contact-username');

        contacts.users.splice(contacts.users.indexOf(contactId),1)
        $("#ctct_"+cleanedUsername).remove();
    });
}