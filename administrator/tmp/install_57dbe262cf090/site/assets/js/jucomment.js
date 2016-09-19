jQuery(document).ready(function($){
     /*************************quote comment ************/
    $('#section-jucomment').on('click','.quote-comment-btn', function () {
        var comment_item = $(this).closest('.comment-item');
        var comment_id = comment_item.attr('id');
        comment_id = comment_id.split('-');
        comment_id = comment_id[2];

        var url = "index.php?option=com_jucomment&task=comment.quoteComment&tmpl=component&"+jucomment.token+"=1";
        $.ajax({
            type: "POST",
            url : url,
            data: {'comment_id' : comment_id},
            dataType: 'json'
        }).done(function (result) {
            if(result){
                if(result.type == 'success'){
                    var textarea   = $('#jucm-comment-form').find('textarea.comment-editor');
                    textarea.insertAtCursor(result.data.html);
                    textarea.data('wbb').modeSwitch();
                    textarea.data('wbb').modeSwitch();
                    textarea.characterFilter();
	                textarea.data('wbb').sync();
	                textarea.trigger('blur');
                }
            }
        });

        return false;
    });

    /*************************reply comment ************/
    $(".reply-comment-btn").fancybox();

    /*Reply comment*/
    $('.submit-reply-comment-btn').click(function(event){
        event.preventDefault();
        var form = $(this).closest('form');

        if(!form.valid()){
            return false;
        }

	    var comment_id = form.find('#comment_id').val();
	    var li = $(window.parent.document).find('li#comment-item-'+comment_id);

        var comment_box = li.find('.comment-box');
        var hasUl = comment_box.next('ul').length ? 1 : 0;

	    var data = form.serializeArray();
	    data.push({name : 'component', value : jucomment.component});
	    data.push({name : 'section', value : jucomment.section});
	    data.push({name : 'cat_id', value : jucomment.cat_id});

        $.ajax({
            type: "POST",
            url : "index.php?option=com_jucomment&task=comment.replyComment&tmpl=component&"+jucomment.token+"=1",
            data: data,
            dataType : 'json'
        }).done(function (result) {
            if(result){
                if(result.type = 'success'){
                    if(result.data.html){
                        if(hasUl){
                            comment_box.next('ul').append($(result.data.html).children());
                        }else{
                            comment_box.after(result.data.html);
                        }

                        window.parent.jQuery.fancybox.close();

                        goToByScroll(result.data.comment_id, true);
                    }
                }else{
                    $('#system-message-container').html('<div class="alert alert-danger">'+result.data.message+'</div>');
                }
            }else{
	            //trigger error
            }
        });
    });

    /*************************edit comment ************/
    $(".edit-comment-btn").fancybox();

    /*Edit comment*/
    $(".submit-edit-comment-btn").click(function(event){
	    event.preventDefault();

        var form = $(this).closest('form');

        if(!form.valid()){
            return false;
        }

        var comment_id = form.find('#comment_id').val();
        var li = $(window.parent.document).find('li#comment-item-'+comment_id);

	    var data = form.serializeArray();
	    data.push({name : 'component', value : jucomment.component});
	    data.push({name : 'section', value : jucomment.section});
	    data.push({name : 'cat_id', value : jucomment.cat_id});

        $.ajax({
            type: "POST",
            url : "index.php?option=com_jucomment&task=comment.editComment&tmpl=component&"+jucomment.token+"=1",
            data: data,
            dataType : 'json'
        }).done(function (result) {
            if(result){
                if(result.type = 'success'){
                    if(result.data.html){
                        li.find('.comment-box:first').html($(result.data.html).find('.comment-box').children());
                    }

                    window.parent.jQuery.fancybox.close();
                }else{
                    $('#system-message-container').html('<div class="alert alert-danger">'+result.data.message+'</div>');
                }
            }else{
	            // trigger error
            }
        });
    });

    /*************************report comment ************/
    $(".report-comment-btn").fancybox();

    $('.submit-report-comment-btn').click(function(event){
        event.preventDefault();

        var form = $(this).closest('.report-form');

        if(!form.valid()){
		    return false;
	    }

	    var comment_id = form.find('#comment_id').val();
	    var li = $(window.parent.document).find('li#comment-item-'+comment_id);
	    var iframe = li.find('iframe:first');

        $.ajax({
            type: "POST",
            url : "index.php?option=com_jucomment&task=comment.report&tmpl=component&"+jucomment.token+"=1",
            data: form.serialize()+'&comment_id='+comment_id,
            dataType : 'json'
        }).done(function (result) {
            if(result){
                if(result.type == 'success'){
                    li.find('.report-comment-btn').addClass('disabled');
                    window.parent.jQuery.fancybox.close();
                }else{
                    $('#system-message-container').html('<div class="alert alert-danger">'+result.data.message+'</div>');
                }
            }else{
	            // trigger error
            }
        });
    });

    $('#report-subject').change(function(){
        if($(this).val() == 'other'){
            $('#report-other').show();
        }else{
           $('#report-other').hide();
        }
    });

    /*************************submit new comment ************/
    $('#jucm-comment-form #submit-comment-btn').click(function(event){
        event.preventDefault();

        var form = $('#jucm-comment-form');

        if(!form.valid()){
            return false;
        }

	   //var formData = new FormData(form[0]);
		//console.log(formData);return;
	    var sortDirection = getSortDirectionValue();
	    var data = form.serializeArray();
	    data.push({name : 'sort', value : sortDirection.sort});
	    data.push({name : 'direction', value : sortDirection.direction});
	    data.push({name : 'contentLink', value : window.location.href});
	    data.push({name : 'component', value : jucomment.component});
	    data.push({name : 'section', value : jucomment.section});
	    data.push({name : 'cat_id', value : jucomment.cat_id});
	    data.push({name : 'cid', value : jucomment.cid});
        $.ajax({
            type: "POST",
            url : "index.php?option=com_jucomment&task=comment.addComment&tmpl=component&"+jucomment.token+"=1",
            data: data,
            dataType : 'json'
        }).done(function (result) {
            if(result){
	            if(result.type == 'success'){
                    $('#section-jucomment .jucm-comment-wrapper').before('<div class="jucomment-alert alert alert-success">'+result.data.message+'</div>');
                    if(result.data.html){
                       $('#section-jucomment .jucm-comment-list').html($(result.data.html).children());
                       goToByScroll(result.data.comment_id);
                    }

		            //form.find('.comment-editor').clean();
                }else{
                    $('#section-jucomment .jucm-comment-wrapper').before('<div class="jucomment-alert alert alert-error">'+result.data.message+'</div>');
                }

                setTimeout(function(){
                    $('#section-jucomment .jucomment-alert').remove();
                }, 10000);
            }

	        //reload captcha
	        /*$('#jucm-comment-form .jucomment-captcha-reload').trigger('click');*/

	        if(juCommentFomTrigger){
		        for(var name in juCommentFomTrigger)
		        {
			        if (juCommentFomTrigger.hasOwnProperty(name))
			        {
                        juCommentFomTrigger[name](form, "submit", result);
			        }
		        }
	        }
        });
    });


	$('#reset-comment-btn').click(function(event){
		event.preventDefault();
		var form = $('#jucm-comment-form');
		form.trigger('reset');

		if(juCommentFomTrigger){
			for(var name in juCommentFomTrigger)
			{
				if (juCommentFomTrigger.hasOwnProperty(name))
				{
                    juCommentFomTrigger[name](form, "reset");
				}
			}
		}
	});

    /*************************delete comment ************/
    $("#section-jucomment").on('click', '.submit-delete-comment-btn', function(event){
        event.preventDefault();
        var comment_id = $(this).data('comment_id');
	    var page = getPage();
	    var sortDirection = getSortDirectionValue();
        $.ajax({
            type: "POST",
            url : "index.php?option=com_jucomment&task=comment.delete&component=component&"+jucomment.token+"=1",
            data: {'comment_id' : comment_id ,  'page' : page , 'sort' : sortDirection.sort , 'direction' : sortDirection.direction,
	            'contentLink' : window.location.href, 'component' : jucomment.component, 'section' : jucomment.section, 'cid' : jucomment.cid},
            dataType : 'json'
        }).done(function (result) {
            if(result){
	            if(result.type == 'success'){
		            $('#section-jucomment .jucm-comment-list').html($(result.data.html).children());
                }else{
	                //trigger error
                }
            }
        });
    });

	/*************************edit state comment ************/
	$("#section-jucomment").on('click', '.submit-unpublish-comment-btn', function(event){
	    event.preventDefault();
	    var comment_id = $(this).data('comment_id');
		var page = getPage();
		var sortDirection = getSortDirectionValue();
		var contentLink = window.location.href;

	    $.ajax({
	        type: "POST",
	        url : "index.php?option=com_jucomment&task=comment.unpublish&tmpl=component&"+jucomment.token+'=1',
	        data: {'comment_id' : comment_id ,  'page' : page , 'sort' : sortDirection.sort , 'direction' : sortDirection.direction,
		        'contentLink' : contentLink, 'component' : jucomment.component, 'section' : jucomment.section, 'cid' : jucomment.cid},
	        dataType : 'json'
	    }).done(function (result) {
	        if(result){
	            if(result.type == 'success'){
			        $('#section-jucomment .jucm-comment-list').html($(result.data.html).children());
	            }else{
	                // trigger error
	            }
	        }
	    });
	});

    /*************************subscription cid ************/
    $("#section-jucomment").on('click', '.submit-subscription', function(event){
        event.preventDefault();
	    if(!document.formvalidator.isValid('.subscription-from')){
		    $('#subscription-message-container').html($('#system-message-container').html());
		    $('#system-message-container').html('');
		    return false;
	    }
	    var form = $(this).closest('#subscription-from');
        var type = $(this).hasClass('subscribe') ? 'subscribe' : 'unsubscribe';
        var comment_subscription_modal =  $('#section-jucomment').find('.comment-subscription-modal');
        var comment_subscription_btn =  $('#section-jucomment').find('.comment-subscription-btn');

        $.ajax({
            type: "POST",
            url : "index.php?option=com_jucomment&task=comment.subscribe&tmpl=component&"+jucomment.token+"=1",
            data: form.serialize()+'&type='+type,
            dataType : 'json'
        }).done(function (result) {
            if(result){
	            $('#comment-subscription-modal').modal('hide');
	            if(result.type == 'success'){
                    if(type == 'subscribe'){
                        if(result.data.html){
                            comment_subscription_modal.html($(result.data.html).children());
                        }else{
                            comment_subscription_modal.remove();
                            comment_subscription_btn.remove();
                        }
                    }else if(result.data.html){
                        comment_subscription_modal.html($(result.data.html).children());
                    }
                }else{
	                // trigger error
                }
            }
        });
    });

    /*************************sort comment ************/
    $('#section-jucomment').on('change', '.sort, .direction', function(){
            var sort = $('#section-jucomment .sort').val(),
            direction = $('#section-jucomment .direction').val(),
	        page = $('#section-jucomment .jucm-pagination li a.active').attr('href');
	        page = page ? parseInt(page) : 1;
	        getCommentList(page, sort, direction);
    });

	$('#section-jucomment').on('click', '.jucm-pagination a',function(event){
		event.preventDefault();
		if($(this).hasClass('disabled') || $(this).hasClass('active')){
			return;
		}
		var sort = $('#section-jucomment .sort').val(),
			direction = $('#section-jucomment .direction').val(),
			page = parseInt($(this).attr('href'));

		if(page <=0 ){
			return;
		}

		getCommentList(page, sort, direction);
	});

	$('#section-jucomment').on('click', '.goto-page-btn',function(event){
		event.preventDefault();
		var goto_page = parseInt($('#section-jucomment .goto-page').val());
		var total_pages = parseInt($('#section-jucomment .total-pages').data('total_pages'));

		if(goto_page > 0 && goto_page <= total_pages){
			var sort = $('#section-jucomment .sort').val(),
				direction = $('#section-jucomment .direction').val();

			getCommentList(goto_page, sort, direction);
		}
	});

	function getCommentList( page, sort, direction){
		var contentLink = window.location.href;
		$.ajax({
			type: "POST",
			url : "index.php?option=com_jucomment&task=comment.getCommentList&tmpl=component&"+jucomment.token+"=1",
			data: {'component' : jucomment.component, 'section' : jucomment.section, 'cid' : jucomment.cid,
				'sort' : sort, 'direction' : direction, 'contentLink' : contentLink, page : page
			},
			dataType : 'json'
		}).done(function (result) {
			if(result){
				if(result.type == 'success'){
					if(result.data.html){
						$('#section-jucomment').find('.jucm-comment-list').html($(result.data.html).children());
					}
				}else{
					// trigger error
				}
			}
		});
	}
    /*************************vote comment ************/
    $('#section-jucomment').on('click', '.vote-up, .vote-down', function(event){
        event.preventDefault();
        var self = $(this);
	    if(self.hasClass('disabled')){
		    return false;
	    }

	    self.addClass('disabled');
	    var comment_item = self.closest('.comment-item');
        var comment_id = comment_item.attr('id');
        comment_id = comment_id.split('-');
        comment_id = comment_id[2];
        var type = self.hasClass('vote-up') ? 'vote-up' : 'vote-down';
        var action = self.hasClass('voted') ? 'remove' : 'add';
        var ori_action = action;
        if(!self.hasClass('voted') && (comment_item.find('.vote-up').hasClass('voted') || comment_item.find('.vote-down').hasClass('voted'))){
            type = (type == 'vote-up') ? 'vote-down,vote-up' : 'vote-up,vote-down';
            action = (action == 'remove') ? 'add,remove' : 'remove,add';
        }

        $.ajax({
            type: "POST",
            url : "index.php?option=com_jucomment&task=comment.vote&tmpl=component&"+jucomment.token+'=1',
            data: {'comment_id' : comment_id, 'type' : type, 'action' : action },
            dataType : 'json'
        }).done(function (result) {
            if(result){
	            if(result.type == 'success'){
                    if(comment_item.find('.vote-up').hasClass('voted') || comment_item.find('.vote-down').hasClass('voted')){
                        comment_item.find('.vote-up, .vote-down').removeClass('voted');
                    }
                    if(ori_action == 'add'){
                        self.addClass('voted');
                    }

                    comment_item.find('.vote-counter').html(result.data.vote_counter);
                }else{
	                // trigger error
                }
            }
	        self.removeClass('disabled');
        });
    });

    // This is a functions that scrolls to #{blah}link
    function goToByScroll(id, parent){
        // Scroll
        if(parent){
            $(window.parent.document).find('html').animate({
                    scrollTop: $(window.parent.document).find('li#comment-item-'+id).offset().top},
                'slow');
        }else{
            $('html, body').animate({
                    scrollTop: $('li#comment-item-'+id).offset().top},
                'slow');
        }
    }

	$('.jucomment-captcha-reload').on('click', function(event){
		event.preventDefault();
		parent = $(this).closest('.captcha-box');
		var captcha_id = parent.find('.captcha-id').val();
		$.ajax({
			type: "POST",
			url : "index.php?option=com_jucomment&task=comment.reloadCaptcha&tmpl=component&"+jucomment.token+'=1',
			data : 'captcha_id='+captcha_id,
			dataType : 'json'
		}).done(function (result) {
			if(result){
				if(result.type == 'success'){
					parent.find('.captcha-image').attr('src' ,result.data.image);
					parent.find('.captcha-response').val('');
					parent.find('.captcha-id').val(result.data.captcha_id);
				}else{

				}
			}
		});

		return false;
	});

	function getSortDirectionValue(useUrl){
		if(useUrl){
			var url = window.location.href;
			var patt = /(?!#comment-box-\d+&|#page-\d+|#)sort-(\w*)&direction-(\w*)/;
			var result = url.match(patt);

			if(result){
				var value = {};
				value.sort = result[1];
				value.direction = result[2];
				return value;
			}

			return false;
		}else{
			var value = {};
			value.sort = $('#section-jucomment .sort').val();
			value.direction = $('#section-jucomment .direction').val();

			return value;
		}
	}

	function getPage(useUrl){
		if(useUrl){
			var url = window.location.href;
			var patt = /#page-(\d+)/;
			var result = url.match(patt);

			if(result){
				return result[1];
			}

			return false;
		}else{
			return $('#section-jucomment .jucm-pagination li a.active').attr('href');
		}
	}

	function addForm(iframe_box, form_type, comment_id){
        if(iframe_box.hasClass(form_type)){
            removeForm(iframe_box, form_type)
        }else{
            var url = "index.php?option=com_jucomment&task=comment.getform&formtype="+form_type+"&comment_id="+comment_id+"&tmpl=component&width="+iframe_box.width()+"&"+jucomment.token+"=1"
            iframe_box.addClass(form_type).html('<iframe style="border: none;" scrolling="no" src="'+url+'"></iframe>');
            var iframe = iframe_box.find('iframe');
            console.log(111);
            /*iframe.height(0).one('load', function(){
                iframe.contents().find('body').css({overflow : 'hidden'});
                iframe.contents().find('body').css({'padding' : 0, 'margin' : 0});
                var width = iframe.contents().outerWidth(true);
                iframe.width(width);
                var height = iframe.contents().outerHeight(true);
                iframe.animate({'height': height}, 250);
            });*/
        }
	}

	/*setInterval(function(){
		$('.jucm-comment-list').find('.iframe-wrapper iframe').each(function(){
			iframe = $(this);
			var width = iframe.contents().outerWidth(true);
			iframe.width(width);
			var height = iframe.contents().outerHeight(true);
			iframe.animate({'height': height}, 200);
		});
	}, 500);*/

	function removeForm(iframe_box, form_type){
		if(form_type){
			if(iframe_box.hasClass(form_type)){
				iframe_box.removeClass(form_type).html('');
			}
		}else{
			iframe_box.attr('class', 'iframe-wrapper').html('');
		}
	}

	//if url has #comment-box-{id}" then auto show item with ajax
	function autoDetectItem(){
		var url = window.location.href;
		var patt = /#comment-box-(\d+)/;
		var result = url.match(patt);

		if(!result){
			return false;
		}

		var comment_id = result[1];
		var sort = '';
		var direction = '';
		var sortDirectionURL = getSortDirectionValue(true);
		var sortDirection = getSortDirectionValue();

		if(sortDirection){
			sort = sortDirectionURL.sort;
			direction = sortDirectionURL.direction;
		}else{
			sort = sortDirection.sort;
			direction = sortDirection.direction;
		}

		if(sort == sortDirection.sort && direction == sortDirection.direction && $(result[0]).length > 0){
			return false;
		}

		$.ajax({
			type: "POST",
			url : "index.php?option=com_jucomment&task=comment.gotoItem&tmpl=component&"+jucomment.token+"=1",
			data: {'component' : jucomment.component, 'section' : jucomment.section, 'cid' : jucomment.cid,
					'sort' : sort, 'direction' : direction, 'contentLink' : url, comment_id : comment_id
			},
			dataType : 'json'
		}).done(function (result) {
			if(result.type == 'success'){
				if(result.data.html){
					$('#section-jucomment').find('.jucm-comment-list').html($(result.data.html).children());
				}
			}else{
				// trigger error
			}
		});

		return true;
	}

	function autoDetectPage(){
		var page = getPage(true);
		var sortDirectionURL = getSortDirectionValue(true);
		if(!page && !sortDirectionURL){
			return false;
		}

		var sortDirection = getSortDirectionValue();

		if(!page){
			page = 1;
		}

		if(sortDirectionURL){
			var sort = sortDirectionURL.sort;
			var direction = sortDirectionURL.direction;
		}else{
			var sort = sortDirection.sort;
			var direction = sortDirection.direction;
		}

		var current_page = getPage();

		if(page == current_page && sort == sortDirection.sort && direction == sortDirection.direction ){
			return false;
		}

		getCommentList(page, sort, direction);

		return true;
	}

	if(!autoDetectItem()){
		autoDetectPage();
	}
});