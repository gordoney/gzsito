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
<div class="block-main slider mod-slider-gr">
	<div class="slick-slider js-slick-slider">
		<?php foreach ($slides as $key => $slide) { ?>
			<div class="item clearfix">
				<div class="img">
					<img src="<?php echo $slide['img']; ?>" alt="">
				</div>
				<div class="desc">
					<div class="name"><?php echo $slide['text']; ?></div>
					<div class="desc-name"><?php echo $slide['main-text']; ?></div>
					<div class="dots">
						<?php for ($i=1; $i <= count($slides); $i++) { ?>
							<div class="dot js-dot <?php if ($i == 1) { echo 'active'; } ?>"></div>
						<?php } ?>
					</div>
				</div>
			</div>
		<?php } ?>
	</div>
</div>