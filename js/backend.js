jQuery(document).ready(function ($) {
console.log(typeof ajax_object.verified);
    if (ajax_object.verified === '1') {
        $('#apsispro-shortcode-generator').show();
        $('#apsispro-verified-msg').show();
    } else {
        $('#apsispro-shortcode-generator').hide();
        $('#apsispro-verified-msg').hide();
    }

    $('#generate-shortcode-button').click(function (event) {
        event.preventDefault(); // cancel default behavior

        $generatedCode = ' [apsis-pro id="'

        $(".apsispro_select_mailing_list option:selected").each(function () {
            $generatedCode += $(this).val() + '"';
        });

        if ($('.apsispro_checkbox_name').is(':checked')) {
            $generatedCode += ' name="true"';
        }

        $generatedCode += ' thank-you="' + $('.apsispro_input_thank_you_msg').val() + '"]';

        $('#apsispro-generated-code').val($generatedCode);
    });

});