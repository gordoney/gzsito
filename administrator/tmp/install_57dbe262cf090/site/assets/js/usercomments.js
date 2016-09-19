jQuery(document).ready(function ($) {
	$('.privew-comment').fancybox();

    // Checked all and count
    $("#jucm-cbAll").click(function () {
        $("input.jucm-cb","form.jucm-form").prop("checked", $(this).is(":checked"));
    });

	/*$(".jucm-action").on("change", function (event) {
		event.preventDefault();
		var n = $(".jucm-cb:checked").length;
		var action = $(this).val();
		if (n > 0 && action) {
			var x = '';
			switch (action){
				case 'publish':
					x = confirm(Joomla.JText._('COM_JUCOMMENT_ARE_YOU_SURE_YOU_WANT_TO_PUBLISH_THESE_COMMENTS', 'Are you sure you want to publish these comments?'));
					break;
				case 'unpublish':
					x = confirm(Joomla.JText._('COM_JUCOMMENT_ARE_YOU_SURE_YOU_WANT_TO_UNPUBLISH_THESE_COMMENTS', 'Are you sure you want to unpublish these comments?'));
					break;
				case 'approve':
					x = confirm(Joomla.JText._('COM_JUCOMMENT_ARE_YOU_SURE_YOU_WANT_TO_APPROVE_THESE_COMMENTS', 'Are you sure you want to approve these comments?'));
					break;
				case 'delete':
					x = confirm(Joomla.JText._('COM_JUCOMMENT_ARE_YOU_SURE_YOU_WANT_TO_DELETE_THESE_COMMENTS', 'Are you sure you want to delete these comments?'));
					break;
			}
			if (x) {
				$("#jucm-form-comments #task").val("modcomments."+action);
				$("form#jucm-form-comments").submit();
			}
		} else {
			alert(Joomla.JText._('COM_JUCOMMENT_NO_ITEM_SELECTED', 'No item selected!'));
		}
	});*/

	$(".jucm-comment-action").on("click", function (event) {
		event.preventDefault();
		var action = $(this).data('action');
		if(action){
			$(".jucm-cb").prop("checked", false);
			$(this).closest('tr').find('input[name="cid[]"]').prop("checked", true);
			$("#task").val("usercomments."+action);
			$("#jucm-form-comments").submit();
		}
	});
});
			
		