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
<div class="mod_blocks_gr catalog">
    <div class="container">
        <div class="blocks row clearfix">
            <?php foreach ($blocks as $block) { ?>
                <div class="block">
                    <a class="background-overlay clearfix" href="<?php echo $block['url']; ?>">
                        <div class="name"><?php echo $block['block-text']; ?></div>
                        <div class="img" style="background-image: url(<?php echo $block['img']; ?>);"></div>
                    </a>
                </div>
            <? } ?>
        </div>
    </div>
</div>