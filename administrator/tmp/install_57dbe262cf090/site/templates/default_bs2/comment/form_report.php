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
$comment_report_subjects = $system->params->get('comment_report_subjects', "Spam\nInappropriate");
if (count($comment_report_subjects) > 0)
{
	$subject_array = explode("\n", $comment_report_subjects);
}

$subject_array    = array_filter($subject_array);
$reportSubject = array();
if (count($subject_array) > 0)
{
	foreach ($subject_array as $subject)
	{
		if (trim($subject))
		{
			$reportSubject[$subject] = $subject;
		}
	}
}

$style = isset($width) ? 'style="width: '.$width.'px; height: auto"' : '';
?>

<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('.report-form').validate({
			errorClass: "error",
			errorElement: "span",
			ignore : ".ignoreValidate",
			highlight: function(element, errorClass, validClass){
				$(element).closest('.control-group').addClass('error');
			},
			unhighlight: function(element, errorClass, validClass){
				$(element).closest('.control-group').removeClass('error');
			},
			rules: {
				"jform[other_subject]": {
					required: {
						depends:function(element){
							return  $('#report-subject').val() == 'other';
						}
					}
				}
			}
		});
	});
</script>

<div class="report-comment-wrapper" <?php echo $style; ?>>
	<form class="report-form jucm-form form-horizontal" method="POST"	action="">
		<legend><?php echo JText::sprintf('COM_JUCOMMENT_REPORT_COMMENT_X', $row->title); ?></legend>
		<?php
		if ($system->my->get('guest'))
		{
			?>
			<div class="control-group">
				<label class="control-label" for="report-username">
					<?php echo JText::_('COM_JUCOMMENT_NAME'); ?>
					<span class="required">*</span>
				</label>

				<div class="controls">
					<input type="text" id="report-username" data-rule-required="true" name="jform[guest_name]" value="" />
				</div>
			</div>

			<div class="control-group">
				<label class="control-label" for="report-email">
					<?php echo JText::_('COM_JUCOMMENT_EMAIL'); ?>
					<span class="required">*</span>
				</label>

				<div class="controls">
					<input type="text" id="report-email" data-rule-required="true" data-rule-email="true" name="jform[guest_email]" value="" />
				</div>
			</div>
		<?php
		}
		else
		{
			?>
			<div class="control-group">
				<label class="control-label" for="report-username">
					<?php echo JText::_('COM_JUCOMMENT_NAME'); ?>
					<span class="required">*</span>
				</label>

				<div class="controls">
					<input type="text" data-rule-required="true"  value="<?php echo $system->my->name; ?>" id="report-username" readonly="readonly"/>
				</div>
			</div>

			<div class="control-group">
				<label class="control-label" for="report-email">
					<?php echo JText::_('COM_JUCOMMENT_EMAIL'); ?>
					<span class="required">*</span>
				</label>

				<div class="controls">
					<input type="text" data-rule-required="true" data-rule-email="true"
					       value="<?php echo $system->my->email; ?>" id="report-email" readonly="readonly"/>
				</div>
			</div>
		<?php
		}
		?>

		<div class="control-group">
			<label class="control-label" for="report-subject"><?php echo JText::_('COM_JUCOMMENT_SUBJECT'); ?>
				<span class="required">*</span>
			</label>

			<div class="controls">
				<?php
				if (count($reportSubject) > 0)
				{
					$beginSubject  = array('' => JText::_('COM_JUCOMMENT_SELECT'));
					$otherSubject  = array('other' => JText::_('COM_JUCOMMENT_OTHER'));
					$reportSubject = array_merge($beginSubject, $reportSubject, $otherSubject);
					?>
					<select name="jform[subject]" data-rule-required="true" id="report-subject">
						<?php echo JHtml::_('select.options', $reportSubject, 'value', 'text', ''); ?>
					</select>
				<?php
				}
				else
				{
					?>
					<input type="text" name="jform[subject]" id="report-subject" data-rule-required="true"/>
				<?php
				}
				?>
			</div>
		</div>

		<div class="control-group" id="report-other" style="display:none">
			<label class="control-label" for="report-other-subject">
				<?php echo JText::_('COM_JUCOMMENT_OTHER_SUBJECT'); ?>
				<span class="required">*</span>
			</label>

			<div class="controls">
				<input type="text" name="jform[other_subject]" id="report-other-subject" data-rule-required="true"/>
				<span class="add-on"><i class="icon-edit"></i></span>
			</div>
		</div>

		<div class="control-group">
			<label class="control-label" for="report-content">
				<?php echo JText::_('COM_JUCOMMENT_CONTENT'); ?>
				<span class="required">*</span>
			</label>

			<div class="controls">
				<textarea name="jform[content]" cols="7" rows="5" id="report-content" data-rule-required="true"></textarea>
			</div>
		</div>

		<div class="control-group">
			<label class="control-label">
			</label>

			<div class="controls">
				<button class="btn btn-primary submit-report-comment-btn" type="submit" name="submit"><?php echo JText::_('COM_JUCOMMENT_SUBMIT'); ?></button>
				<button class="btn cancel-report-comment-btn" type="button" onclick="window.parent.jQuery.fancybox.close();">
					<?php echo JText::_('COM_JUCOMMENT_CANCEL'); ?>
				</button>
		</div>
		</div>
		<div>
			<input id="comment_id" type="hidden" value="<?php echo $row->id ?>">
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>