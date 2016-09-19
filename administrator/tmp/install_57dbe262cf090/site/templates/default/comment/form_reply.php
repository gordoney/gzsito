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

$fields = JUCommentFieldHelper::getFields(null, 'reply');
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

$style = isset($width) ? 'style="width: '.$width.'px; height: auto"' : '';
?>

<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('.reply-form').validate({
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

<div class="reply-comment-wrapper jucm-comments" <?php echo $style; ?> >
	<form name="reply_form" class="reply-form jucm-form form-horizontal" method="POST" action="">
		<fieldset>
			<legend><?php echo JText::_('COM_JUCOMMENT_REPLY_COMMENT'); ?></legend>
			<div id="comment-message-container"></div>
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
							<div class="form-group">
								<?php
								echo $field->getLabel();
								?>
								<div class="col-sm-<?php echo $field->hideLabel() ? '12' : '10'; ?>">
									<?php echo $field->getInput();?>
									<?php echo $field->getInvalidHtml();?>
								</div>
							</div>
						<?php
						}?>
					<?php
					}?>
				</div>

				<div class="form-action clearfix">
					<button name="submit_reply_comment" type="submit"
					        class="btn btn-primary submit-reply-comment-btn"><?php echo JText::_('COM_JUCOMMENT_SUBMIT'); ?></button>
					<button name="cancel_reply_comment" type="button" onclick="window.parent.jQuery.fancybox.close();"
					        class="btn btn-danger cancel-reply-comment-btn"><?php echo JText::_('COM_JUCOMMENT_CANCEL'); ?></button>
				</div>

				<div>
					<?php
						foreach($hiddenFields as $field)
						{
							switch($field->field_name)
							{
								case 'id':
									echo $field->getInput(0);
									break;

								case 'parent_id':
									echo $field->getInput($row->id);
									break;

								case 'fieldgroup_id':
									echo $field->getInput($fieldgroup_id);
									break;

								default :
									$fieldName = $field->field_name;
									echo $field->getInput($row->{$fieldName});
									break;
							}
						}
					?>
					<input id="comment_id" type="hidden" value="<?php echo $row->id ?>">
				</div>
			</div>
		</fieldset>
	</form>
</div>