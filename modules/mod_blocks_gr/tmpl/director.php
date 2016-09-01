<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_articles_popular
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
?>
<div class="block-main director">
    <div class="prof"><?php echo $blocks[0]['prof']; ?></div>
    <div class="quote"><span class="open-quote">“</span><?php echo $blocks[0]['quote']; ?><span class="close-quote">”</span></div>
    <div class="fio"><?php echo $blocks[0]['fio']; ?></div>
</div>

<div class="block-main director img" style="background-image: url(<?php echo $blocks[0]['img']; ?>);">

</div>