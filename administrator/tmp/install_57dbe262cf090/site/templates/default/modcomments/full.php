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

?>
<h2 class="jucm-view-title"><?php echo JText::_('COM_JUCOMMENT_MANAGE_COMMENTS'); ?></h2>
<p><i><?php echo 'Total '.$view->model->getTotal().' Comments'; ?></i></p>

<form action="" method="post" name="jucm-form-comments full-view" id="jucm-form-comments">
	<div class="clearfix">
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
			<select class="sort-by" name="filter_component" onchange="this.form.submit()">
				<?php echo JHtml::_('select.options', $view->model->getComponents(), 'value', 'text', $view->state->get('filter.component')); ?>
			</select>
			<select name="filter_order" class="sort-by" onchange="this.form.submit()">
				<?php echo JHtml::_('select.options', $view->getSortFields(), 'value', 'text', $view->state->get('list.ordering', '')); ?>
			</select>
			<select name="filter_order_Dir" class="sort-direction" onchange="this.form.submit()">
				<?php echo JHtml::_('select.options', $view->getSortDirection(), 'value', 'text', $view->state->get('list.direction', 'ASC')); ?>
			</select>
			<?php
				echo $view->pagination->getLimitBox();
			?>
			<a href="<?php echo JRoute::_('index.php?option=com_jucomment&view=modcomments', false); ?>" class="btn btn-default btn-sm"
				title="<?php echo JText::_('COM_JUCOMMENT_DEFAULT_VIEW') ?>" ><?php echo JText::_('COM_JUCOMMENT_DEFAULT_VIEW') ?>
			</a>
		</div>
	</div>

	<div class="jucm-comments">
		<ul class="comment-list">
			<?php
			$uri = JUri::getInstance();
			foreach ($view->items as $row)
			{
				$row = JUCommentCommentHelper::process($row);
				$margin_left = 0;
				if($view->state->get('list.ordering', '') == 'cm.lft'){
					$margin_left = ($row->level - 1) * 25;
				}

				if($row->checked_out > 0){
					$checkoutUser 	= JFactory::getUser($row->checked_out);
					$date         	= JHtml::_('date', $row->checked_out_time);
				}

				$class = "level-".$row->level;
				$class .= $row->published == 0 ? " unpublished" : " published";
				$class .= $row->approved == 0 ? " unapproved" : " approved";
				$class .= $row->total_reports ? " reported" : "";

				?>
				<li class="comment-item <?php echo $class; ?>"
					<?php echo $margin_left > 0 ? 'style="margin-left : '.$margin_left.'px"' : '' ?>
				    id="comment-item-<?php echo $row->id; ?>">
					<div itemprop="review" itemscope itemtype="http://schema.org/Review">
						<div class="comment-box clearfix">
							<div class="comment-user">
								<?php
								?>
								<img class="comment-avatar" itemprop="image" src="<?php echo $row->author->getAvatar($row->email); ?>"/>

								<h3 class="comment-username">
									<span itemprop="author">
										<?php echo $row->name;?>
									</span>
								</h3>
							</div>
							<!-- end .comment-user -->

							<div class="comment-text">
								<div class="jucm-metadata clearfix">
									<?php
										$titleField = JUCommentFieldHelper::getField('title', $row);
										if($titleField){
									?>
										<h4 class="comment-title" itemprop="name">
											<?php $return = base64_encode('index.php?option=com_jucomment&view=modcomments&layout=full'); ?>
											<a href="<?php echo JRoute::_('index.php?option=com_jucomment&task=comment.edit&id='.$row->id, false) ?>&return=<?php echo $return; ?>">
												<?php
													echo $row->title;
												?>
											</a>
										</h4>
									<?php } ?>

									<?php
										$createdField = JUCommentFieldHelper::getField('created', $row);
										if($createdField){
									?>
										<div class="comment-created" itemprop="datePublished">
											<?php echo JText::_('COM_JUCOMMENT_POST_ON') . " : " . $createdField->getOutput() ?>
										</div>
									<?php } ?>

									<?php
										$websiteField = JUCommentFieldHelper::getField('website', $row);
										if ($websiteField)
										{
											?>
											<div class="comment-website">
												<?php echo JText::_('COM_JUCOMMENT_COMMENT_WEBSITE') . " : " . $websiteField->getOutput(); ?>
											</div>
									<?php } ?>

									<?php
										$cidField = JUCommentFieldHelper::getField('cid', $row);
										if ($cidField)
										{
									?>
										<div>
											<?php echo JText::_("COM_JUCOMMENT_ARTICLE"); ?>
											<?php
												$tooltips = "Component : ". $row->componenttitle;
												if($row->section){
													$tooltips .= "<br/>Section : ". $row->section;
												}
											?>
											<a href="<?php echo $row->permalink ?>" class="hasTooltip" title="<?php echo $tooltips; ?>">
												<?php echo $row->contenttitle; ?>
											</a>
										</div>
									<?php }	?>

									<?php
										if($row->level > 1){
											$parentField = JUCommentFieldHelper::getField('parent_id', $row);
											if ($parentField)
											{
												?>
												<div>
													<?php echo JText::_("COM_JUCOMMENT_REPLY_COMMENT"); ?>
													<?php echo $parentField->getOutput(); ?>
												</div>
										<?php } ?>
									<?php } ?>
								</div>
								<!-- End div.jucm-metadata -->

								<?php
									$commentField = JUCommentFieldHelper::getField('comment', $row);
									if($commentField){
								?>
									<div class="see-more" itemprop="description">
										<?php echo $commentField->getOutput(); ?>
									</div>
								<?php } ?>

							</div>
							<!-- End div.comment-primary -->
							<!-- Only show star rating and rating tooltip for comment level 0 -->

							<?php if($system->permission->allow('approve', $row) || $system->permission->allow('delete', $row) || $system->permission->allow('edit_state', $row)){ ?>
								<div class="private-actions btn-group pull-right">
									<a href="#" data-toggle="dropdown" class="btn btn-default btn-sm dropdown-toggle" target="_self"><span class="icon-cog"></span> <span class="caret"></span></a>
									<ul class="dropdown-menu">
										<?php //@todo can edit state
										if($row->approved == 0 && $system->permission->allow('approve', $row))
										{
											?>
											<li>
												<?php
												if ($row->checked_out > 0)
												{
													$tooltip = JText::_('COM_JUCOMMENT_CHECKED_IN_BY') . ':' . $checkoutUser->name . ' <br /> ' . $date;
												?>
													<a href="<?php echo $row->checkout_link; ?>" title="<?php echo $tooltip; ?>" class="hasTooltip">
														<i class="fa fa-lock"></i> <?php echo JText::_('COM_JUCOMMENT_APPROVE_COMMENT'); ?>
													</a>
												<?php
												}
												else
												{
													$url = 'index.php?option=com_jucomment&task=modcomments.approve&cid=' . $row->id .
														'&'. JSession::getFormToken() . '=1&return=' . base64_encode($uri);
												?>
													<a href="<?php echo $url; ?>" title="<?php echo JText::_('COM_JUCOMMENT_APPROVE_COMMENT'); ?>">
														<i class="fa fa-check"></i> <?php echo JText::_('COM_JUCOMMENT_APPROVE_COMMENT'); ?>
													</a>
												<?php
												}
												?>
											</li>
										<?php
										}
										?>

										<?php //@todo can edit state
										if($system->permission->allow('edit_state', $row))
										{
											?>
											<li>
												<?php
												if ($row->checked_out > 0)
												{
													$text = $row->published == 1 ? JText::_('COM_JUCOMMENT_UNPUBLISH_COMMENT') : JText::_('COM_JUCOMMENT_PUBLISH_COMMENT');
													$tooltip = JText::_('COM_JUCOMMENT_CHECKED_IN_BY') . ':' . $checkoutUser->name . ' <br /> ' . $date;
													?>
													<a href="<?php echo $row->checkout_link; ?>" title="<?php echo $tooltip; ?>">
														<i class="fa fa-lock"></i> <?php echo $text; ?>
													</a>
												<?php
												}
												elseif($row->published == 1)
												{
													$url = 'index.php?option=com_jucomment&task=modcomments.unpublish&cid=' . $row->id
														. '&' . JSession::getFormToken() . '=1' . '&return=' . base64_encode($uri);
													?>
													<a href="<?php echo $url; ?>" title="<?php echo JText::_('COM_JUCOMMENT_UNPUBLISH_COMMENT'); ?>">
														<i class="fa fa-minus-circle"></i> <?php echo JText::_('COM_JUCOMMENT_UNPUBLISH_COMMENT'); ?>
													</a>
												<?php
												}else{
													$url = 'index.php?option=com_jucomment&task=modcomments.publish&cid=' . $row->id .
														'&'. JSession::getFormToken() . '=1' . '&return=' . base64_encode($uri);
												?>
													<a href="<?php echo $url; ?>" title="<?php echo JText::_('COM_JUCOMMENT_PUBLISH_COMMENT'); ?>">
														<i class="fa fa-check"></i> <?php echo JText::_('COM_JUCOMMENT_PUBLISH_COMMENT'); ?>
													</a>
												<?php
												}
												?>
											</li>
										<?php
										}
										?>

										<?php //@todo can delete
										if($system->permission->allow('delete', $row))
										{
											$url = 'index.php?option=com_jucomment&task=modcomments.delete&cid=' . $row->id
												. '&' . JSession::getFormToken() . '=1' . '&return=' . base64_encode($uri);
											?>
											<li>
												<a href="<?php echo $url; ?>" title="<?php echo JText::_('COM_JUCOMMENT_DELETE_COMMENT'); ?>">
													<i class="fa fa-trash"></i> <?php echo JText::_('COM_JUCOMMENT_DELETE_COMMENT'); ?>
												</a>
											</li>
										<?php
										}
										?>
									</ul>
								</div>
							<?php } ?>
						</div>

					</div>
					<!-- end div.comment-content -->
				</li>
			<?php
			}
			?>
		</ul>
	</div>

	<div class="jucm-pagination">
		<?php echo $view->pagination->getListFooter(); ?>
	</div>
</form>
