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
JHtml::addIncludePath(JPATH_SITE . '/components/com_jucomment/helpers/html');
$row = JUCommentCommentHelper::process($row);
$fields = JUCommentFieldHelper::getFields($row);
?>

<li class="comment-item <?php echo ($row->published == 1) ? 'published' : 'unpublished'; ?> level-<?php echo $row->level; ?>"
    id="comment-item-<?php echo $row->id; ?>">
	<div itemprop="review" itemscope itemtype="http://schema.org/Review">
		<div id="<?php echo "comment-box-".$row->id; ?>" class="comment-box clearfix">
			<div class="comment-user">
				<img class="comment-avatar" itemprop="image" alt="<?php echo $row->name; ?>"
				     src="<?php echo $row->author->getAvatar($row->email); ?>"/>

				<h3 class="comment-username">
					<span itemprop="author">
						<?php echo $row->name;?>
					</span>
				</h3>
			</div>
			<!-- /.comment-user -->

			<div class="comment-text">
				<?php
					if(isset($fields['title']) && $fields['title']->canView())
					{ ?>
					<h4 class="comment-title" itemprop="name"><?php echo $fields['title']->getOutput(); ?></h4>
				<?php
					} ?>

				<div class="comment-metadata clearfix">
					<div class="comment-created" itemprop="datePublished">
						<i class="fa fa-calendar"></i>
						<?php
							if(isset($fields['created']) && $fields['created']->canView())
							{
								echo JText::_('COM_JUCOMMENT_POST_ON') . ": " . $fields['created']->getOutput();
							}
						?>
					</div>
					<?php
						if(isset($fields['website']) && $fields['website']->canView())
						{ ?>
						<div class="comment-website">
						<?php echo JText::_('COM_JUCOMMENT_COMMENT_WEBSITE') . ': '. $fields['website']->getOutput(); ?>
						</div>
					<?php
						} ?>
				</div>

				<!-- /.comment-metadata -->
				<?php
					if(isset($fields['comment']) && $fields['comment']->canView()){
						echo $fields['comment']->getOutput();
					}
				?>

				<div class="field-box">
					<ul class="fields">
					<?php
					$ignoredField = array('title', 'created', 'website', 'comment', 'modified');
					foreach($fields AS $field)
					{
						if(!in_array($field->field_name, $ignoredField) && $field->canView())
						{
							echo '<li class="field field-' . $field->id . '">';
							echo $field->getCaption() . ": ";
							echo $field->getOutput();
							echo '</li>';
						}
					}?>
					</ul>
				</div>

				<div class="comment-actions clearfix">
					<?php
						$vote_counter = $row->helpful_votes - ($row->total_votes - $row->helpful_votes);
						$vote_counter = $vote_counter > 0 ? "+" . $vote_counter : $vote_counter;

						$vote_up_class = $vote_down_class = '';
						if(!$system->permission->allow('vote', $row))
						{
							$vote_up_class = $vote_down_class = 'disabled';
						}else{
							if($row->voted == 'vote-up'){
								$vote_up_class = 'voted';
							}elseif($row->voted == 'vote-down'){
								$vote_down_class = 'voted';
							}
						}
					?>

					<a class="btn btn-mini vote-up <?php echo $vote_up_class; ?>"
						href="#"><i class="fa fa-thumbs-o-up"></i></a>
						<span class="btn btn-mini vote-counter"><?php echo $vote_counter; ?> </span>
					<a class="btn btn-mini vote-down <?php echo $vote_down_class; ?>"
						href="#"><i class="fa fa-thumbs-o-down"></i></a>

					<?php if ($system->permission->allow('reply', $row))
					{
						$url = "index.php?option=com_jucomment&task=comment.getform&formtype=reply&comment_id=".$row->id."&tmpl=component&".JSession::getFormToken()."=1";
						?>
						<a class="btn btn-mini reply-comment-btn" href="<?php echo $url; ?>" data-fancybox-type="iframe">
							<i class="fa fa-reply"></i> <?php echo JText::_('COM_JUCOMMENT_REPLY_COMMENT'); ?>
						</a>
					<?php
					}
					 ?>

					<?php if($system->permission->allow('create' , $row))
					{
						?>
						<a class="btn btn-mini quote-comment-btn" href="#">
							<i class="fa fa-quote-left"></i> <?php echo JText::_('COM_JUCOMMENT_QUOTE'); ?>
						</a>
					<?php
					}
					 ?>

					<?php if ($system->permission->allow('report', $row))
					{
						$classDisabled = $row->reported ? ' disabled' : '';
						$url = "index.php?option=com_jucomment&task=comment.getform&formtype=report&comment_id=".$row->id."&tmpl=component&".JSession::getFormToken()."=1";
						?>
						<a class="btn btn-mini report-comment-btn<?php echo $classDisabled; ?>" href="<?php echo $url; ?>" data-fancybox-type="iframe">
							<i class="fa fa-warning"></i> <?php echo JText::_('COM_JUCOMMENT_REPORT'); ?>
						</a>
					<?php
					}
					?>

					<?php
					if ($system->permission->allow('edit', $row))
					{
						if ($row->checked_out > 0)
						{
							$checkedOutUser = JFactory::getUser($this->comment_obj->checked_out);
							$checkedOutTime = JHtml::_('date', $this->comment_obj->checked_out_time);
							$tooltip  = JText::_('COM_JUCOMMENT_EDIT_COMMENT');
							$tooltip .= '<br/>';
							$tooltip .= JText::sprintf('COM_JUCOMMENT_CHECKED_OUT_BY', $checkedOutUser->name) . ' <br /> ' . $checkedOutTime;
							?>
							<a class="hasTooltip btn btn-mini comment-edit" title="<?php echo $tooltip; ?>" href="<?php echo $row->checkout_link; ?>">
								<i class="fa fa-lock"></i><?php echo JText::_('COM_JUCOMMENT_EDIT_COMMENT'); ?>
							</a>
							<?php
						}
						else
						{
							$url = "index.php?option=com_jucomment&task=comment.getform&formtype=edit&comment_id=".$row->id."&tmpl=component&".JSession::getFormToken()."=1";
							?>
							<a class="btn btn-mini edit-comment-btn" href="<?php echo $url; ?>" data-fancybox-type="iframe">
								<i class="fa fa-edit"></i> <?php echo JText::_('COM_JUCOMMENT_EDIT_COMMENT'); ?>
							</a>
						<?php
						}
					}
					?>

					<?php
					if ($system->permission->allow('delete', $row))
					{
						?>
							<a class="btn btn-mini delete-comment-btn" role="button" data-toggle="modal"
								href="#jucm-comment-delete-alert-<?php echo $row->id; ?>"
								title="<?php echo JText::_('COM_JUCOMMENT_DELETE_COMMENT'); ?>">
								<i class="fa fa-times"></i> <?php echo JText::_('COM_JUCOMMENT_DELETE_COMMENT'); ?>
							</a>

							<div id="jucm-comment-delete-alert-<?php echo $row->id; ?>"
								 class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
								<div class="modal-dialog">
									<div class="modal-content">
										<div class="modal-body">
											<?php echo JText::sprintf('COM_JUCOMMENT_DELETE_COMMENT_X_CONFIRM', $row->title); ?>
										</div>
										<div class="modal-footer">
											<button class="btn btn-primary submit-delete-comment-btn"
													data-dismiss="modal"
													data-comment_id="<?php echo $row->id; ?>"
												>
												<?php echo JText::_("COM_JUCOMMENT_DELETE"); ?>
											</button>
											<button class="btn" data-dismiss="modal" aria-hidden="true">
												<?php echo JText::_("COM_JUCOMMENT_CANCEL"); ?>
											</button>
										</div>
									</div>
								</div>
							</div>
						<?php
					}
					?>
				</div>
			</div>

			<?php
			if($system->permission->allow('edit_state', $row))
			{ ?>
				<!-- Modal alert delete document -->
				<div id="jucm-comment-unpublish-alert-<?php echo $row->id; ?>"
				     class="modal hide fade" tabindex="-1" role="dialog" aria-hidden="true">
					<div class="modal-body">
						<?php echo JText::sprintf('COM_JUCOMMENT_UNPUBLISH_COMMENT_X_CONFIRM', $row->title); ?>
					</div>
					<div class="modal-footer">
						<button class="btn btn-primary submit-unpublish-comment-btn"
						        data-dismiss="modal"
						        data-comment_id="<?php echo $row->id; ?>"
							>
							<?php echo JText::_("COM_JUCOMMENT_UNPUBLISH"); ?>
						</button>
						<button class="btn" data-dismiss="modal" aria-hidden="true">
							<?php echo JText::_("COM_JUCOMMENT_CANCEL"); ?>
						</button>
					</div>
				</div>
			<?php
			} ?>
		</div>

		<?php
			// Load comment recursive if is not leaf
			if(isset($comments) && ($row->rgt - $row->lft) > 1)
			{
				$this->set('parent',$row->id);
				echo $this->fetch('comment/items.php');
			}
		?>
	</div>
	<!-- /.comment-content -->
</li>