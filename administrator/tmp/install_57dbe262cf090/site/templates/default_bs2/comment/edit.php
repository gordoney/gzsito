<?php
/**
 * ------------------------------------------------------------------------
 * JUComment for Joomla 3.x
 * ------------------------------------------------------------------------
 *
 * @copyright      Copyright (C) 2010-2016 JoomUltra Co., Ltd. All Rights Reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @author         JoomUltra Co., Ltd
 * @website        http://www.joomultra.com
 * @----------------------------------------------------------------------@
 */

defined('_JEXEC') or die('Restricted access');
?>

<script type="text/javascript">
	jQuery(document).ready(function($){
		Joomla.submitbutton = function (task) {
			if (task == 'comment.cancel' || document.formvalidator.isValid(document.getElementById("adminForm"))) {
				Joomla.submitform(task, document.getElementById('adminForm'));
			}
		};

		$('.submit-edit-comment-btn').click(function(event){
			event.preventDefault();
			Joomla.submitbutton('comment.save');
		});

		$('.cancel-edit-comment-btn').click(function(event){
			event.preventDefault();
			Joomla.submitbutton('comment.cancel');
		});

		setTimeout(function(){
			var self = $('.comment-editor').data('wbb');
			if(self){
				$(self.body).bind("blur", function (e) {
					$(self.txtArea).trigger('blur');
				});

				$(self.body).bind("keypress", function (e) {
					$(self.txtArea).trigger('blur');
					self.sync(true);
					self.characterFilter();
				});

				self.sync(true);
				self.characterFilter();
			}
		}, 1);
	});
</script>

<?php echo $this->fetch('comment/form_edit.php'); ?>
