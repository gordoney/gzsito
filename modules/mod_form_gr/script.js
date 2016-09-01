jQuery(document).ready(function() {
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
            
            var field = jQuery('[name=field'+i+']', this.form).val();
            
            if (json.fields[i]['required']) {
                if (field == '') {
                    jQuery('[name=field'+i+']', this.form).addClass('border_red');
					error = 1;
                } else {
                    jQuery('[name=field'+i+']', this.form).removeClass('border_red');	
                }
            }
        }
				
        formData.append("mailSubject", json.mailHead);
        formData.append("recipient", json.recipient);
		formData.append("quantityFields", json.quantityFields);
		formData.append("captchaSecretKey", json.captchaSecretKey);	
        
        if (json.captchaOn) {
            formData.append("captcha", true);
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

        var json = jQuery.parseJSON(jQuery(this).closest('.mod_form_gr').find('.js-form-gr-json').val());
         
        formGrSend(json);
    });
});
