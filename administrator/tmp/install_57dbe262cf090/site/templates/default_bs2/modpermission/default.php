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
<div id="jucm-container" class="jubootstrap comments-manager clearfix">

<h2 class="jucm-view-title"><?php echo JText::_('COM_JUCOMMENT_MODERATOR_PERMISSION'); ?></h2>

	<table class="table table-striped table-bordered">
		<thead>
			<tr>
				<th style="width: 200px" class="center">
					<?php echo JText::_('COM_JUCOMMENT_FIELD'); ?>
				</th>

				<th class="center">
					<?php echo JText::_('COM_JUCOMMENT_VALUE'); ?>
				</th>
			</tr>
		</thead>

		<tbody>
			<tr>
				<td>
					<?php echo JText::_('COM_JUCOMMENT_FIELD_NAME'); ?>
				</td>
				<td>
					<?php echo $view->item->name; ?>
				</td>
			</tr>

			<tr>
				<td>
					<?php echo JText::_('COM_JUCOMMENT_FIELD_DESCRIPTION'); ?>
				</td>
				<td>
					<?php echo $view->item->description; ?>
				</td>
			</tr>

			<tr>
				<td>
					<?php echo JText::_('COM_JUCOMMENT_FIELD_COMPONENTS'); ?>
				</td>
				<td>
					<?php
						echo $view->item->assignedComponents;
					?>
				</td>
			</tr>

			<tr>
				<td>
					<?php echo JText::_('COM_JUCOMMENT_FIELD_COMMENT_EDIT'); ?>
				</td>
				<td>
					<?php
					if ($view->item->comment_edit)
					{
						echo JText::_('JYES');
					}
					else
					{
						echo JText::_('JNO');
					}
					?>
				</td>
			</tr>

			<tr>
				<td>
					<?php echo JText::_('COM_JUCOMMENT_FIELD_COMMENT_EDIT_STATE'); ?>
				</td>
				<td>
					<?php
					if ($view->item->comment_edit_state)
					{
						echo JText::_('JYES');
					}
					else
					{
						echo JText::_('JNO');
					}
					?>
				</td>
			</tr>

			<tr>
				<td>
					<?php echo JText::_('COM_JUCOMMENT_FIELD_COMMENT_DELETE'); ?>
				</td>
				<td>
					<?php
					if ($view->item->comment_delete)
					{
						echo JText::_('JYES');
					}
					else
					{
						echo JText::_('JNO');
					}
					?>
				</td>
			</tr>

			<tr>
				<td>
					<?php echo JText::_('COM_JUCOMMENT_FIELD_COMMENT_APPROVE'); ?>
				</td>
				<td>
					<?php
					if ($view->item->comment_approve)
					{
						echo JText::_('JYES');
					}
					else
					{
						echo JText::_('JNO');
					}
					?>
				</td>
			</tr>
		</tbody>
	</table>
</div>
