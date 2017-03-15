jQuery(document).ready(function ($) {
    if (ajax_object.verified === '1') {
        $('#apsispro-shortcode-generator').show();
        $('#apsispro-verified-msg').show();
    } else {
        $('#apsispro-shortcode-generator').hide();
        $('#apsispro-verified-msg').hide();
    }

    $('#generate-shortcode-button').click(function (event) {
        event.preventDefault(); // cancel default behavior

        $generatedCode = '[apsispro id="'

        $firstMailinglist = true;

        $('.apsispro_mailinglist_checkboxes input').each(function () {

            if ($(this).is(':checked')) {
                if ($firstMailinglist) {
                    $generatedCode += $(this).val();
                    $firstMailinglist = false;
                } else {
                    $generatedCode += ',' + $(this).val();
                }
            }
        });

        $generatedCode += '"';

        $generatedCode += ' text="'

        $firstMailinglist = true;

        $('.apsispro_mailinglist_checkboxes input').each(function () {

            if ($(this).is(':checked')) {
                if ($firstMailinglist) {
                    $generatedCode += $(this).attr('name');
                    $firstMailinglist = false;
                } else {
                    $generatedCode += ',' + $(this).attr('name');
                }
            }
        });

        $generatedCode += '"';

        if ($('.apsispro_checkbox_name').is(':checked')) {
            $generatedCode += ' name="true"';
        }

        if ($('.apsispro_input_thank_you_msg').val() != '') {
            $generatedCode += ' thankyou="' + $('.apsispro_input_thank_you_msg').val() + '"';
        }
        
        if ($('.apsispro_input_submit_name').val() != '') {
            $generatedCode += ' submitname="' + $('.apsispro_input_submit_name').val() + '"';
        }

        $generatedCode += ']';

        $('#apsispro-generated-code').val($generatedCode);
    });

});
