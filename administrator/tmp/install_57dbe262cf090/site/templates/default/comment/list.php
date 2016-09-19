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
// Init order name && order direction
$optionsSort = JUCommentFieldHelper::getFrontEndOrdering($fieldgroup_id);
$optionsDir  = JUCommentFieldHelper::getFrontEndDirection();
?>
<div class="jucm-comment-list">
	<h3 class="total-comments clearfix">
		<span class="commentCounter"><?php echo JText::plural('COM_JUCOMMENT_N_COMMENT', $commentCount); ?></span>
		<?php
		if($system->permission->allow('subscribe'))
		{ ?>
			<a style="float: right; margin-top: 10px" class="btn btn-default btn-sm comment-subscription-btn"  role="button" class="btn" data-toggle="modal"
			    href="#comment-subscription-modal"
				title=" <?php echo JText::_('COM_JUCOMMENT_SUBSCRIBE_COMMENT'); ?>"
			>
				<i class="fa fa-envelope-o"></i>
			</a>
		<?php
		} ?>
	</h3>

	<?php
		if($system->permission->allow('subscribe'))
		{
			echo $this->fetch( 'comment/modal_subscription.php' );
		}
	?>
	<?php
	if($system->permission->allow('read'))
	{ ?>
		<div class="filter-sort pull-right">
            <select class="sort">
                <?php echo JHtml::_('select.options', $optionsSort, 'value', 'text', $options['sort']); ?>
            </select>
            <select class="direction">
                <?php echo JHtml::_('select.options', $optionsDir, 'value', 'text', $options['direction']); ?>
            </select>
		</div>
		<?php
			echo $this->fetch('comment/items.php');
			echo $this->fetch('comment/pagination.php');
		?>
	<?php
	}
	else
	{ ?>
		<?php echo JText::_('COM_JUCOMMENT_YOU_ARE_NOT_ALLOWED_TO_READ_COMMENT'); ?>
	<?php
	} ?>
</div>