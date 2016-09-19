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

$fields = JUCommentFieldHelper::getFields($row, 'comment');
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
		$('.edit-form').validate({
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

<div class="edit-comment-wrapper" <?php echo $style; ?>>
	<form name="edit-form" class="edit-form jucm-form form-horizontal" method="POST"
		action="<?php echo JRoute::_('index.php?option=com_jucomment&id=' . (int) $row->id); ?>"
		id="adminForm" >
		<legend><?php echo JText::_('COM_JUCOMMENT_EDIT_COMMENT'); ?></legend>
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
				<button name="submit_edit_comment" type="submit"
				        class="btn btn-primary submit-edit-comment-btn"><?php echo JText::_('COM_JUCOMMENT_SUBMIT'); ?></button>
				<button name="cancel_edit_comment" type="button" onclick="window.parent.jQuery.fancybox.close();"
				        class="btn btn-danger cancel-edit-comment-btn"><?php echo JText::_('COM_JUCOMMENT_CANCEL'); ?></button>
			</div>

			<div>
				<?php
					foreach($hiddenFields as $field)
					{
						echo $field->getInput();
					}

					if(isset($task) && $task)
					{
						echo '<input type="hidden" name="task" value="' . $task . '" />';
					}

					if(isset($token) && $token)
					{
						echo JHtml::_('form.token');
					}

					if(isset($return) && $return)
					{
						echo '<input type="hidden" name="return" value="' . $return . '" />';
					}
				?>
				<input id="comment_id" type="hidden" value="<?php echo $row->id ?>">
			</div>
		</div>
	</form>
</div>