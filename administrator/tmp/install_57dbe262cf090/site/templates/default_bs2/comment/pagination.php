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

//page = 1
//total_page = ?
if($total_pages > 1)
{
	$cur_page = $page;
	$previous_btn = true;
	$next_btn = true;
	$first_btn = true;
	$last_btn = true;
	//
	if ($cur_page >= 7)
	{
		$start_loop = $cur_page - 3;
		if ($total_pages > $cur_page + 3)
		{
			$end_loop = $cur_page + 3;
		}
		elseif ($cur_page <= $total_pages && $cur_page > $total_pages - 6)
		{
			$start_loop = $total_pages - 6;
			$end_loop = $total_pages;
		}
		else
		{
			$end_loop = $total_pages;
		}
	}
	else
	{
		$start_loop = 1;
		if ($total_pages > 7)
		{
			$end_loop = 7;
		}
		else
		{
			$end_loop = $total_pages;
		}
	}
	?>
	<div class='jucm-pagination'>
		<ul>
			<!--FOR ENABLING THE FIRST BUTTON-->
			<?php
			if ($first_btn && $cur_page > 1)
			{ ?>
				<li><a class="btn btn-small" href="1"><?php echo JText::_('COM_JUCOMMENT_FIRST'); ?></a></li>
			<?php
			}
			elseif ($first_btn)
			{ ?>
				<li><a class="btn btn-small disabled" href="#"><?php echo JText::_('COM_JUCOMMENT_FIRST'); ?></a></li>
			<?php
			} ?>

			<!--FOR ENABLING THE PREVIOUS BUTTON-->
			<?php
			if ($previous_btn && $cur_page > 1)
			{ ?>
				<?php	$pre = $cur_page - 1; ?>
				<li><a class="btn btn-small" href="<?php echo $pre; ?>"><?php echo JText::_('COM_JUCOMMENT_PREV'); ?></a></li>
			<?php
			}
			elseif ($previous_btn)
			{ ?>
				<li><a class="btn btn-small disabled" href="#"><?php echo JText::_('COM_JUCOMMENT_PREV'); ?></a></li>
			<?php
			} ?>

			<?php
			for ($i = $start_loop; $i <= $end_loop; $i++)
			{ ?>
				<?php
				if ($cur_page == $i)
				{ ?>
					<li><a href="<?php echo $i; ?>" class="btn btn-small active"><b><?php echo $i; ?></a></b></li>
				<?php
				}
				else
				{ ?>
					<li><a href="<?php echo $i; ?>" class="btn btn-small"><?php echo $i; ?></a></li>
				<?php
				} ?>
			<?php
			} ?>

			<!--TO ENABLE THE NEXT BUTTON-->
			<?php
			if ($next_btn && $cur_page < $total_pages)
			{ ?>
				<?php $nex = $cur_page + 1; ?>
				<li><a class="btn btn-small" href="<?php echo $nex; ?>"><?php echo JText::_('COM_JUCOMMENT_NEXT'); ?></a></li>
			<?php
			}
			elseif ($next_btn)
			{ ?>
				<li><a class="btn btn-small disabled" href="#"><?php echo JText::_('COM_JUCOMMENT_NEXT'); ?></a></li>

			<?php
			} ?>

			<!--TO ENABLE THE END BUTTON-->
			<?php
			if ($last_btn && $cur_page < $total_pages)
			{ ?>
				<li><a class="btn btn-small" href="<?php echo $total_pages; ?>"><?php echo JText::_('COM_JUCOMMENT_LAST'); ?></a></li>
			<?php
			} elseif ($last_btn)
			{ ?>
				<li><a class="btn btn-small disabled" href="#"><?php echo JText::_('COM_JUCOMMENT_LAST'); ?></a></li>
			<?php
			} ?>
		</ul>
		<span class="goto-box">
			<input type="text" class="input input-mini goto-page" size="1" style="margin-bottom: 0"/>
			<input type="button" id="go_btn" class="btn btn-small goto-page-btn" value="<?php echo JText::_('COM_JUCOMMENT_GO'); ?>"/>
		</span>
		<span class="btn btn-small total-pages" data-total_pages="<?php echo $total_pages; ?>">
			<?php echo JText::sprintf('COM_JUCOMMENT_PAGE_X_OF_Y', $cur_page, $total_pages); ?>
		</span>
	</div>
<?php
} ?>