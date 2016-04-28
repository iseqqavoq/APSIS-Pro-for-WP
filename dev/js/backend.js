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

        $generatedCode = ' [apsispro id="'

        $(".apsispro_select_mailing_list option:selected").each(function () {
            $generatedCode += $(this).val() + '"';
        });

        if ($('.apsispro_checkbox_name').is(':checked')) {
            $generatedCode += ' name="true"';
        }

        if ($('.apsispro_input_thank_you_msg').val() != '') {
            $generatedCode += ' thankyou="' + $('.apsispro_input_thank_you_msg').val() + '"';
        }

        $generatedCode += ']';

        $('#apsispro-generated-code').val($generatedCode);
    });

});
