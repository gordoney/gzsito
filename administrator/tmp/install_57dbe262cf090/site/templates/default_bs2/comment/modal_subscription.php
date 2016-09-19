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
<div id="comment-subscription-modal"
     class="modal hide fade comment-subscription-modal" tabindex="-1" role="dialog"
     aria-hidden="true">
	<div class="modal-body">
		<form id="subscription-from" class="subscription-from form-validate" >
		<div class="alert alert-info">
			<?php
			if($subscription)
			{
				echo JText::_('COM_JUCOMMENT_YOU_ARE_ALREADY_SUBSCRIBED');
			}
			else
			{
				echo JText::_('COM_JUCOMMENT_FILL_THE_FORM_TO_SUBSCRIBE');
			} ?>
		</div>
		<div id="subscription-message-container"></div>
		<div class="form-horizontal" >
			<?php
			if(!$subscription)
			{ ?>
				<div class="control-group">
					<label class="control-label" for="subscription-name">
						<?php echo JText::_('COM_JUCOMMENT_USERNAME'); ?><span class="required">&nbsp;*</span>
					</label>
					<div class="controls">
						<?php
							$username = $system->my->get('guest') ? '' : $system->my->name;
							$readonly = $system->my->get('guest') ? '' : 'readonly="readonly"';
						?>
						<input type="text" class="required" name="jform[guest_name]" value="<?php echo $username; ?>" <?php echo $readonly;?> id="subscription-name">
					</div>
				</div>

				<div class="control-group">
					<label class="control-label" for="subscription-email">
						<?php echo JText::_('COM_JUCOMMENT_EMAIL'); ?><span class="required">&nbsp;*</span>
					</label>
					<div class="controls">
						<?php
							$email = $system->my->get('guest') ? '' : $system->my->email;
							$readonly = $system->my->get('guest') ? '' : 'readonly="readonly"';
						?>
						<input type="text" class="required validate-email" name="jform[guest_email]" value="<?php echo $email; ?>" <?php echo $readonly;?> id="subscription-email">
					</div>
				</div>
			<?php
			}
			else
			{ ?>
				<input type="hidden" name="jform[sub_id]" value="<?php echo $subscription->id; ?>">
			<?php
			} ?>
				<div class="control-group">
					<div class="control-label">
					</div>
					<div class="controls">
						<button class="btn btn-primary <?php echo $subscription ? 'unsubscribe' : 'subscribe'; ?> submit-subscription">
							<?php echo $subscription ? JText::_("COM_JUCOMMENT_UNSUBSCRIBE") : JText::_("COM_JUCOMMENT_SUBSCRIBE"); ?>
						</button>
						<button class="btn" data-dismiss="modal">
							<?php echo JText::_("COM_JUCOMMENT_CANCEL"); ?>
						</button>
					</div>
				</div>
			</div>

			<div>
				<input type="hidden" name="jform[component]" value="<?php echo $component; ?>">
				<input type="hidden" name="jform[section]" value="<?php echo $section; ?>">
				<input type="hidden" name="jform[cid]" value="<?php echo $cid; ?>">
				<?php echo JHtml::_('form.token'); ?>
			</div>
		</form>
	</div>
</div>