jQuery(document).ready(function() {

    function setCaptchaCookie () {
        var ids = new Array();

        jQuery('.mod_form_gr').find('form').each(function () {
            ids.push(jQuery(this).attr('data-captcha'));
        });

        var idsJSON = JSON.stringify(ids);

        var siteKey = jQuery('.siteKey').val();

        var date = new Date(new Date().getTime() + 60 * 60 * 1000);
        document.cookie = "siteKey="+ siteKey +"; expires=" + date.toUTCString();

        document.cookie = "idsJSON="+ idsJSON +"; expires=" + date.toUTCString();
    }

    function formGrSend (json) {

        var id = "form_back_"+json.id;
        var form = document.getElementById(id);
        var formData = new FormData(form);

        var error = 0;

        for (var i=0; i < json.fields.length; i++) {
            if (json.fields[i]['title']) {
                formData.append("namefield"+i, json.fields[i]['title']);
            } else if (json.fields[i]['placeholder']) {
                formData.append("namefield"+i, json.fields[i]['placeholder']);
            }

            var field = jQuery('#'+id).find('[name=field'+i+']').val();

            if (json.fields[i]['required']) {
                if (field == '') {
                    jQuery('#'+id).find('[name=field'+i+']').addClass('border_red');
                    error = 1;
                } else {
                    jQuery('#'+id).find('[name=field'+i+']').removeClass('border_red');
                }
            }
        }

        formData.append("mailSubject", json.mailHead);
        formData.append("recipient", json.recipient);
        formData.append("quantityFields", json.quantityFields);
        formData.append("captchaSecretKey", json.captchaSecretKey);


        if (json.captchaOn) {
            formData.append("captcha", true);

            if (!grecaptcha.getResponse(captcha[json.id])) {
                error = 1;
            }
        }

        var capfield = jQuery('[name=capfield]', this.form).val();
        if (capfield != '') {
            error = 1;
        }

        if (!error) {
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "/modules/mod_form_gr/mailer.php",true);
            xhr.onreadystatechange = function() {
                if(xhr.responseText == 'success'){
                    jQuery('#answer'+json.id).modal();
                }
            }
            xhr.send(formData);
        }

    }

    jQuery('.js-form-send').click(function(e) {
        e.preventDefault();

        var form = jQuery(this).closest('form').attr("id");

        var json = jQuery.parseJSON(jQuery('.'+form).find('.js-form-gr-json').val());

        formGrSend(json);
    });


    setCaptchaCookie ();
});

function captchaCallBack () {
    var siteKey = getCookie('siteKey');
    var idsJSON = JSON.parse(getCookie('idsJSON'));

    captcha = new Array();
    jQuery.each(idsJSON, function (index, value) {
        captcha[value] = grecaptcha.render('g-recaptcha'+value, {
            'sitekey' : siteKey, //Replace this with your Site key
            'theme' : 'light'
        });
    });
}


function getCookie (name) {
    var matches = document.cookie.match(new RegExp(
        "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
    ));
    return matches ? decodeURIComponent(matches[1]) : undefined;
}