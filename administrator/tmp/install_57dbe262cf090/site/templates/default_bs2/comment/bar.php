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

<?php
JUComment::trigger('onBeforeJUCommentBar', array('component' => $component, 'cid' => $cid, 'commentCount', &$commentCount));
?>

<?php
$readmore = false;
if ($component == 'com_content')
{
	// Check show readmore from com_content
	if ($system->params->get('frontpage_readmore_use_joomla', 0) == 0 && (($system->params->get('frontpage_readmore') == 2) || ($system->params->get('frontpage_readmore') == 1 && $article->params->get('show_readmore', 1) && $article->readmore)))
	{
		$readmore = true;
	}
}
else
{
	if ($system->params->get('frontpage_readmore', 1) != 0)
	{
		$readmore = true;
	}
}

if ($readmore || $system->params->get('frontpage_comment', 1) || $system->params->get('frontpage_hits', 1))
{ ?>
	<div class="jucomment-readon">
		<?php
		if ($readmore)
		{ ?>
			<span class="jucomment-readmore aligned-<?php echo $system->params->get('frontpage_alignment', 'right'); ?>">
				<a href="<?php echo $componentHelper->getContentPermalink();?>" title="<?php echo $this->escape($componentHelper->getContentTitle());?>">
					<?php echo JText::_('COM_JUCOMMENT_FRONTPAGE_READMORE');?>
				</a>
			</span>
		<?php
		} ?>

		<?php
		if ($system->params->get('frontpage_comment', 1))
		{ ?>
			<span class="jucomment-comment aligned-<?php echo $system->params->get('frontpage_alignment', 'right'); ?>">
				<a href="<?php echo $componentHelper->getContentPermalink() . '#section-jucomment'; ?>">
					<?php echo JText::_('COM_JUCOMMENT_FRONTPAGE_COMMENT');?> (<?php echo $commentCount;?>)
				</a>
			</span>
		<?php
		} ?>

		<?php
		if ($system->params->get('frontpage_hits', 1))
		{ ?>
			<span class="jucomment-hits aligned-<?php echo $system->params->get('frontpage_alignment', 'right'); ?>">
				<?php echo JText::_('COM_JUCOMMENT_FRONTPAGE_HITS');?>: <?php echo $componentHelper->getContentHits();?>
			</span>
		<?php
		} ?>
	</div>
<?php
}