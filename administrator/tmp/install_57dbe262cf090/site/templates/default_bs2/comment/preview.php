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

$row = JUCommentCommentHelper::process($row);
$fields = JUCommentFieldHelper::getFields($row);
?>
<div class="jucm-comments">
	<ul class="comment-list clearfix">
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
					if(isset($fields['title']))
					{
						?>
						<h4 class="comment-title" itemprop="name"><?php echo $fields['title']->getOutput(); ?></h4>
					<?php
					} ?>

					<div class="comment-metadata clearfix">
						<div class="comment-created" itemprop="datePublished">
							<i class="fa fa-calendar"></i>
							<?php
							if(isset($fields['created']))
							{
								echo JText::_('COM_JUCOMMENT_POST_ON') . ": " . $fields['created']->getOutput();
							}
							?>
						</div>
						<?php
						if(isset($fields['website']))
						{
							?>
							<div class="comment-website">
								<?php JText::_('COM_JUCOMMENT_COMMENT_WEBSITE') . ': '. $fields['website']->getOutput(); ?>
							</div>
						<?php
						} ?>
					</div>

					<!-- /.comment-metadata -->
					<?php
					if(isset($fields['comment']))
					{
						echo $fields['comment']->getOutput();
					}
					?>

					<div class="field-box">
						<ul class="fields">
						<?php foreach($fields as $field){
							if(!$field->field_name && $field->canView())
							{
								echo '<li class="field field-' . $field->id . '">';
								echo $field->getCaption().": ";
								echo $field->getOutput();
								echo '</li>';
							}
						}?>
						</ul>
					</div>
				</div>
			</div>
		</div>
		<!-- /.comment-content -->
		</li>
	</ul>
</div>