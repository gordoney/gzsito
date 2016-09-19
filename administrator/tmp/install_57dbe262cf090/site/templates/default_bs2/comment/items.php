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
$parent = isset($parent) ? $parent : 1;
?>
<ul class="comment-list clearfix">
	<?php
		foreach ($comments AS $row)
		{
			if ($row->parent_id == $parent)
			{
				$this->set('row', $row);
				echo $this->fetch('comment/item.php');
			}
		}
	?>
</ul>