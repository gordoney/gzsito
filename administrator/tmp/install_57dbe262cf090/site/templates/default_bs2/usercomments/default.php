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
JHtml::addIncludePath(JUCOMMENT_HELPERS . '/html');

JHTML::_('behavior.modal');

?>
<div id="jucm-container" class="jubootstrap comments-manager clearfix">
<h2 class="jucm-view-title"><?php echo JText::_('COM_JUCOMMENT_MANAGE_COMMENTS'); ?></h2>
<p><i><?php echo 'Total '.$view->model->getTotal().' Comments'; ?></i></p>

<form name="jucm-form-comments" id="jucm-form-comments" class="jucm-form" method="post" action="">

	<div class="jucm-filter-sort clearfix">
		<div class="jucm-filter-search input-group col-sm-6 pull-left">
			<input type="text" name="filter_search" id="filter_search" class="form-control"
			       placeholder="<?php echo JText::_('COM_JUCOMMENT_FILTER_SEARCH'); ?>"
			       value="<?php echo $view->escape($view->state->get('filter.search')); ?>"
			       title="<?php echo JText::_('COM_JUCOMMENT_FILTER_SEARCH'); ?>"/>
			<span class="input-group-btn">
				<button type="submit" class="btn btn-default"><?php echo JText::_('COM_JUCOMMENT_FILTER_SUBMIT'); ?></button>
				<button type="button" class="btn btn-default"
						onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('COM_JUCOMMENT_FILTER_CLEAR'); ?></button>
			</span>
		</div>
		<div class="clearfix"></div>
		<div class="filter-sort pull-left">
			<select class="input-medium sort-by" name="filter_component" onchange="this.form.submit()">
				<?php echo JHtml::_('select.options', $view->model->getComponents(), 'value', 'text', $view->state->get('filter.component')); ?>
			</select>
			<select name="filter_order" class="jucm-order-sort input-medium" onchange="this.form.submit()">
				<?php echo JHtml::_('select.options', $view->getSortFields(), 'value', 'text', $view->state->get('list.ordering', '')); ?>
			</select>
			<select name="filter_order_Dir" class="jucm-order-dir input-small" onchange="this.form.submit()">
				<?php echo JHtml::_('select.options', $view->getSortDirection(), 'value', 'text', $view->state->get('list.direction', 'ASC')); ?>
			</select>
			<?php
				echo $view->pagination->getLimitBox();
			?>
			<a href="<?php echo JRoute::_('index.php?option=com_jucomment&view=usercomments&layout=full', false); ?>" class="btn btn-default btn-sm"
				title="<?php echo JText::_('COM_JUCOMMENT_FULL_VIEW') ?>" ><?php echo JText::_('COM_JUCOMMENT_FULL_VIEW') ?>
			</a>
		</div>
	</div>

	<table class="table table-striped table-bordered">
		<thead>
		<tr>
			<th style="width:5%" class="center">
				<input type="checkbox" name="jucm-cbAll" id="jucm-cbAll" value=""/>
			</th>
			<th style="width:20%" class="center">
				<?php echo JText::_('COM_JUCOMMENT_FIELD_TITLE'); ?>
			</th>
			<th style="width:20%" class="center">
				<?php echo JText::_('COM_JUCOMMENT_FIELD_ARTICLE_TITLE'); ?>
			</th>
			<th style="width:15%" class="center">
				<?php echo JText::_('COM_JUCOMMENT_FIELD_NAME'); ?>
			</th>
			<th style="width:5%" class="center">
				<?php echo JText::_('COM_JUCOMMENT_FIELD_APPROVED') ?>
			</th>
			<th style="width:5%" class="center">
				<?php echo JText::_('COM_JUCOMMENT_FIELD_PUBLISHED'); ?>
			</th>
			<th style="width:5%" class="center">
				<?php echo JText::_('COM_JUCOMMENT_FIELD_DELETE'); ?>
			</th>
		</tr>
		</thead>

		<tbody>
		<?php
			foreach ($view->items as $i => $row)
			{
				$row = JUCommentCommentHelper::process($row);
				$class = "level-".$row->level;
				$class .= $row->published == 0 ? " unpublished" : " published";
				$class .= $row->approved == 0 ? " unapproved" : " approved";
				$class .= $row->total_reports ? " reported" : "";

				if($row->checked_out > 0){
					$checkoutUser 	= JFactory::getUser($row->checked_out);
					$date         	= JHtml::_('date', $row->checked_out_time);
				}

				?>
				<tr class="<?php echo $class; ?>">
					<td class="center">
						<input type="checkbox" class="jucm-cb" name="cid[]"
						       value="<?php echo $row->id; ?>" id="jucm-cb-<?php echo $i; ?>"/>
					</td>
					<td>
						<?php
						if ($row->checked_out > 0)
						{
							$tooltip = JText::_('COM_JUCOMMENT_CHECKED_IN_BY') . ':' . $checkoutUser->name . ' <br /> ' . $date;
							?>
							<a href="<?php echo $row->checkout_link; ?>" title="<?php echo $tooltip; ?>" class="hasTooltip"><i class="fa fa-lock"></i></a>
						<?php
						}
						?>

						<a href="<?php echo JRoute::_('index.php?option=com_jucomment&task=comment.edit&id='.$row->id) ?>">
							<?php
								if($view->state->get('list.ordering') == 'cm.lft'){
									echo str_repeat('<span class="gi">|&mdash;</span>', $row->level - 1).$row->title;
								}else{
									echo $row->title;
								}
							?>
						</a>

						<a class="privew-comment fancybox.ajax" href="<?php echo JRoute::_('index.php?option=com_jucomment&view=comment&layout=preview&id=' . $row->id . '&tmpl=component'); ?>" data-facybox-type="ajax">
							<i class="fa fa-search-plus"></i> <?php echo JText::_('Preview'); ?>
						</a>
					</td>
					<td>
						<?php
							$tooltips = "Component : ". $row->componenttitle;
							if($row->section){
								$tooltips .= "<br/>Section : ". $row->section;
							}
						?>
						<a href="<?php echo $row->permalink ?>" class="hasTooltip" title="<?php echo $tooltips; ?>">
							<?php echo $row->contenttitle; ?>
						</a>
					</td>

					<td>
						<?php echo $row->name; ?>
					</td>

					<td class="center">
						<?php if ($row->approved == 1)
						{
							$icon = 'fa fa-check';
						}else{
							$icon = 'fa fa-minus-circle';
						}
						?>
						<?php if($row->approved == 0 && $system->moderator->allow('comment_approve', $row)){ ?>
							<a href="#" class="jucm-comment-action" data-action="approve">
								<i class="<?php echo $icon; ?>"></i>
							</a>
						<?php }else{ ?>
							<i class="<?php echo $icon; ?>"></i>
						<?php } ?>
					</td>
					<td class="center">
						<?php if ($row->published == 1)
						{
							$icon = 'fa fa-check';
							$action = 'unpublish';
						}else{
							$icon = 'fa fa-minus-circle';
							$action = 'publish';
						}
						?>
						<?php if($system->moderator->allow('comment_edit_state', $row)){ ?>
							<a href="#" class="jucm-comment-action" data-action="<?php echo $action; ?>">
								<i class="<?php echo $icon; ?>"></i>
							</a>
						<?php }else{ ?>
							<i class="<?php echo $icon; ?>"></i>
						<?php } ?>
					</td>
					<td class="center">
						<?php if($system->moderator->allow('comment_delete', $row)){ ?>
							<a href="#" class="jucm-comment-action" data-action="delete">
								<i class="fa fa-trash-o"></i>
							</a>
						<?php }else{ ?>
							<i class="fa fa-trash-o"></i>
						<?php } ?>
					</td>
				</tr>
			<?php
			}
		?>
		</tbody>
	</table>

	<div class="jucm-pagination">
		<?php echo $view->pagination->getListFooter(); ?>
	</div>

	<div>
		<input type="hidden" name="task" id="task" value=""/>
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>

</div>