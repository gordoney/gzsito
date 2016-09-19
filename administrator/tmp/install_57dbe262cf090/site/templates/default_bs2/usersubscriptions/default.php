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

?>
<div id="jucm-container" class="jubootstrap">
	<h2><?php echo JText::sprintf('COM_JUCOMMENT_ALL_SUBSCRIPTIONS_OF_X', $system->my->name); ?></h2>
	<p><i><?php echo JText::sprintf('COM_JUCOMMENT_TOTAL_X_SUBSCRIPTIONS', $view->model->getTotal()); ?></i></p>

	<form action="" method="post" name="adminForm" id="adminForm">
		<div class="sort-pagination clearfix">
			<?php if ($view->is_own_dashboard): ?>
				<div class="pull-left">
					<button class="btn btn-default" onclick="Joomla.submitbutton('usersubscriptions.unSubscribe');">
						<?php echo JText::_('COM_JUCOMMENT_UNSUBSCRIBE_SELECTED_ITEMS'); ?>
					</button>
				</div>
			<?php endif ?>
			<div class="clearfix" style="margin-bottom: 6px"></div>
			<div class="filter-sort pull-left">
				<select class="input-medium sort-by" name="filter_component" onchange="this.form.submit()">
					<?php echo JHtml::_('select.options', $view->model->getComponents(), 'value', 'text', $view->state->get('filter.component')); ?>
				</select>
				<select class="input-medium sort-by" name="filter_order" onchange="this.form.submit()">
					<?php echo JHtml::_('select.options', $view->getSortFields(), 'value', 'text', $view->state->get('list.ordering')); ?>
				</select>
				<select class="input-medium sort-direction" name="filter_order_Dir" onchange="this.form.submit()">
					<?php echo JHtml::_('select.options', $view->getSortDirection(), 'value', 'text', $view->state->get('list.direction')); ?>
				</select>
				<?php echo $view->pagination->getLimitBox(); ?>
			</div>
			<!-- end div #sort-by-->
		</div>

		<table class="table table-striped table-bordered">
			<thead>
			<tr>
				<?php if ($view->is_own_dashboard): ?>
					<th align="center">
						<input type="checkbox" onclick="Joomla.checkAll(this)"
						       title="<?php echo JText::_('COM_JUCOMMENT_CHECK_ALL'); ?>" value=""
						       name="checkall-toggle"/>
					</th>
				<?php endif ?>
				<th align="center"><?php echo JText::_('COM_JUCOMMENT_FIELD_TITLE'); ?></th>
				<th align="center"><?php echo JText::_('COM_JUCOMMENT_FIELD_COMPONENT'); ?></th>
				<?php if ($view->is_own_dashboard): ?>
					<th align="center"><?php echo JText::_('COM_JUCOMMENT_ACTION'); ?></th>
				<?php endif ?>
			</tr>
			</thead>

			<tbody>
			<?php
				foreach ($view->items as $key => $subscription)
				{
					$JUCMApplication = JUComment::loadApplication($subscription->component, $subscription->section);
					$JUCMApplication = $JUCMApplication->load($subscription->cid);
					if ($view->is_own_dashboard)
					{
						$unsubscribeLink = JRoute::_('index.php?option=com_jucomment&task=usersubscriptions.unSubscribe&sub_id=' . $subscription->id . '&' . JSession::getFormToken() . '=1');
					}
					?>

					<tr>
						<?php if ($view->is_own_dashboard): ?>
							<td align="center"><?php echo JHtml::_('grid.id', $key, $subscription->id); ?></td>
						<?php endif ?>
						<td class="title">
							<a href="<?php echo $JUCMApplication->getContentPermalink() ?>"><?php echo $JUCMApplication->getContentTitle(); ?></a>
						</td>
						<td align="center"><?php echo $JUCMApplication->getComponentName(); ?></td>
						<?php if ($view->is_own_dashboard): ?>
							<td align="center">
								<a href="<?php echo $unsubscribeLink ?>"
									class="buttons"><?php echo JText::_('COM_JUCOMMENT_UNSUBSCRIBE'); ?></a>
							</td>
						<?php endif ?>
					</tr>
				<?php
				}
			?>
			</tbody>
		</table>

		<div class="jucm-pagination clearfix">
			<?php echo $view->pagination->getListFooter(); ?>
		</div>

		<div>
			<input type="hidden" name="task" value=""/>
			<input type="hidden" name="boxchecked" value="0"/>
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>