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
?>
<div id="jucm-container" class="jubootstrap">
	<div id="jucm-dashboard">
		<?php echo $this->fetch( 'dashboard/default_toolbar.php' ); ?>
		<div class="quick-box-wrapper">
			<div class="quick-box">
				<div class="quick-box-head">
					<div class="quick-box-title"><?php echo JText::_('COM_JUCOMMENT_DASHBOARD_OVERVIEW'); ?></div>
				</div>
				<div class="quick-box-body clearfix">
					<ul class="stat-list">
						<li>
							<span class="stat-info"><?php echo $view->totalUserComments ?></span>
							<span>
								<a href="<?php echo $view->usercomments_link; ?>"><?php echo JText::_('COM_JUCOMMENT_COMMENTS'); ?></a>
							</span>
						</li>
						<li>
							<span class="stat-info"><?php echo $view->totalUserSubscriptions ?></span>
							<span>
								<a href="<?php echo $view->usersubscriptions_link; ?>"><?php echo JText::_('COM_JUCOMMENT_SUBSCRIPTIONS'); ?></a>
							</span>
						</li>
					</ul>
				</div>
			</div>
		</div>

		<?php
		if ($view->isModerator)
		{
			?>
			<div id="quick-box-wrapper">
				<div class="quick-box">
					<div class="quick-box-head">
						<div class="quick-box-title"><?php echo JText::_('COM_JUCOMMENT_MODERATOR_AREA'); ?></div>
					</div>
					<div class="quick-box-body clearfix">
						<ul class="stat-list">
							<li>
								<span class="stat-info"><?php echo $view->totalModComments; ?></span>
								<span>
									<a href="<?php echo $view->modcomments_link; ?>"><?php echo JText::_('COM_JUCOMMENT_COMMENTS'); ?></a>
								</span>
							</li>
							<li>
								<span>
									<a class="btn btn-default btn-xs"
									    href="<?php echo $view->modpermissions_link; ?>">
										<i class="icon-shield"></i> <?php echo JText::_('COM_JUCOMMENT_MODERATOR_PERMISSIONS'); ?>
									</a>
								</span>
							</li>
						</ul>
					</div>
				</div>
			</div>
		<?php
		} ?>
	</div>
</div>