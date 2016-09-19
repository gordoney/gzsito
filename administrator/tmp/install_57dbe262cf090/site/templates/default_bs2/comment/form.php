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

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
JHTML::_( 'behavior.modal');

JText::script('COM_JUCOMMENT_PLEASE_ENTER_AT_LEAST_N_CHARACTERS');
if($system->permission->allow('create'))
{
	JLoader::register('JUCommentEditorHelper', JPATH_SITE . '/components/com_jucomment/helpers/editor.php', false);
	JLoader::register('JUCommentCaptchaHelper', JPATH_SITE . '/components/com_jucomment/helpers/captcha.php', false);

	$fields = JUCommentFieldHelper::getFields();
	$hiddenFields = array();
	$hiddenFieldNames = array('id', 'parent_id', 'component', 'section', 'cid', 'fieldgroup_id');

	foreach ($fields as $key => $field)
	{
		if (in_array($key, $hiddenFieldNames))
		{
			$hiddenFields[$key] = $field;
			unset($fields[$key]);
		}
	}
}
?>

<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#jucm-comment-form').validate({
			errorClass: "error",
			errorElement: "span",
			ignore : ".ignoreValidate",
			highlight: function(element, errorClass, validClass){
				$(element).closest('.form-group').addClass('error');
			},
			unhighlight: function(element, errorClass, validClass){
				$(element).closest('.form-group').removeClass('error');
			}
		});
	});
</script>

<div class="jucm-form clearfix">
	<form name="jucm-comment-form" id="jucm-comment-form" class="comment-form jucm-form form-horizontal" method="post" action="">
		<fieldset>
			<legend><?php echo JText::_('COM_JUCOMMENT_LEAVE_COMMENT'); ?></legend>
			<div id="comment-message-container"></div>
			<?php
			if($system->permission->allow('create'))
			{
			?>
				<div class="jucm-comment-wrapper clearfix">
					<!-- div.jucm-comment -->
					<div class="jucm-comment">
						<p class="note-required">
							<?php echo JText::sprintf('COM_JUCOMMENT_ALL_FIELDS_HAVE_STAR_ARE_REQUIRED', '<span class="required">*</span>'); ?>
						</p>

						<div class="comment-fields">
							<?php foreach($fields as $field)
							{?>
								<?php
								if($field->canSubmit())
								{ ?>
									<div class="control-group">
										<?php
											echo $field->getLabel();
											?>
											<div class="controls">
												<?php echo $field->getInput();?>
												<?php echo $field->getInvalidHtml();?>
											</div>
									</div>
								<?php
								}?>
							<?php
							}?>
						</div>
					</div>
					<!--end div.jucm-comment -->

					<div class="comment-form-submit clearfix">
						<button id="submit-comment-btn" name="submit_comment" type="submit"
						        class="btn btn-primary submit-comment-btn"><?php echo JText::_('COM_JUCOMMENT_SUBMIT'); ?></button>
						<button id="reset-comment-btn" name="reset_comment" type="reset"
						        class="btn reset-comment-btn"><?php echo JText::_('COM_JUCOMMENT_RESET'); ?></button>

						<?php
							foreach($hiddenFields as $field)
							{
								// Field name to get variable value set in commentify with variable name is the same as field_name
								$fieldName = $field->field_name;
								echo $field->getInput($$fieldName);
							}
						?>
					</div>
				</div>
			<?php
			}
			else
			{
				echo JText::_('COM_JUCOMMENT_YOU_ARE_NOT_ALLOWED_TO_ADD_COMMENT');
			} ?>
		</fieldset>
	</form>
</div>
