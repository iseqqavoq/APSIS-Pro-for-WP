jQuery(document).ready(function ($) {

    /*
     Handle clicks on submit button in subscription form
     */
    $('.apsispro-form').submit(function (e) {
        var $currentForm = $(this);
        var listid = $currentForm.find('.apsispro-signup-mailinglist-id').val();
        var email = $(this).find('.apsispro-signup-email').val();
        var name = $(this).find('.apsispro-signup-name').val();
        var thankyou = $(this).find('.apsispro-signup-thank-you').val();

        var data = {
            'action': 'apsispro_action',
            'listid': listid,
            'email': email,
            'name': name
        };

        $.post(ajax_object.ajax_url, data, function (response) {

            if (response !== undefined || response !== -1) {
                var obj = jQuery.parseJSON(response);

                if (obj['Code'] === 1) { //Subscription successful
                    $currentForm.next('.apsispro-signup-response').text(thankyou);
                    $currentForm.hide();
                } else if (obj['Message'].indexOf('is not a valid e-mail address') > -1) { //E-mail address invalid
                    $currentForm.next('.apsispro-signup-response').text(ajax_object.error_msg_email);
                } else { //Error
                    $currentForm.next('.apsispro-signup-response').text(ajax_object.error_msg_standard);
                }
            } else { //Error
                $currentForm.next('.apsispro-signup-response').text(ajax_object.error_msg_standard);
            }

        });

        return false;

    });

});
