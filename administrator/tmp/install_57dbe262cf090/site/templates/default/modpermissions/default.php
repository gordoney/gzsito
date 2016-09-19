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

	<h2 class="jucm-view-title"><?php echo JText::_('COM_JUCOMMENT_MODERATOR_PERMISSIONS'); ?></h2>

	<?php
	if (count($view->items) > 0)
	{
		?>
		<table class="table table-striped table-bordered">
			<thead>
			<tr>
				<th class="center">
					<?php echo JText::_('COM_JUCOMMENT_COMPONENTS'); ?>
				</th>

				<th style="width: 80px" class="center">
					<?php echo JText::_('COM_JUCOMMENT_VIEW_DETAIL'); ?>
				</th>
			</tr>
			</thead>

			<tbody>
			<?php foreach ($view->items as $i => $item)
			{
				?>
				<tr>
					<td>
						<?php
						echo $item->assignedComponents;
						?>
					</td>

					<td class="center">
						<a href="<?php echo JRoute::_('index.php?option=com_jucomment&view=modpermission&id=' . $item->id, false); ?>"
						   title="<?php echo JText::_('COM_JUCOMMENT_VIEW_PERMISSION_DETAIL'); ?>"><?php echo JText::_('COM_JUCOMMENT_DETAIL'); ?></a>
					</td>
				</tr>
			<?php
			} ?>
			</tbody>
		</table>
	<?php
	} ?>
</div>