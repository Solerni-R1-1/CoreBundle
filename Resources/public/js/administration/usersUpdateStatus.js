
jQuery(document).ready(function(jQuery) {
    jQuery('.js-status-link').on( 'click', function(event) {
        event.preventDefault();
        var target = jQuery(this);
        var targetInner = target.find('i');
        var validateMessage = targetInner.data('is-validated');
        var notValidateMessage = targetInner.data('is-not-validated');
        jQuery.ajax({
            url:        target.attr('href'),
            dataTtype:  'json',
            success:    function(data) {
                target.attr('href', data.newUrl);
                if ( data.activationStatus == 1 ) {
                    targetInner
                            .attr('class', 'icon-unlock')
                            .tooltip('hide')
                            .attr('data-original-title', validateMessage)
                            .tooltip('show');
                } else {
                    targetInner
                            .attr('class', 'icon-lock pink_link')
                            .tooltip('hide')
                            .attr('data-original-title', notValidateMessage)
                            .tooltip('fixTitle')
                            .tooltip('show');
                }
            },
            error:      function(data) {
                alert('There was an error during the update user process. Please try to reload the page and if the error repeats itself, report to administrator');
            }
        });
        
    });

});


