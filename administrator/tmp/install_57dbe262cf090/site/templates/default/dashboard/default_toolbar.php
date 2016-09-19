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
	<nav class="navbar navbar-default">
		<div class="container-fluid">
			<ul class="nav navbar-nav">
				<li class="go-home">
					<a class="hasTooltip" title="<?php echo JText::_('COM_JUCOMMENT_DASHBOARD_HOME'); ?>"
					   href="<?php echo $view->dashboard_link; ?>">
						<i class="fa fa-home"></i>
					</a>
				</li>

				<li class="subscriptions">
					<a class="hasTooltip" title="<?php echo JText::_('COM_JUCOMMENT_USER_SUBSCRIPTIONS'); ?>"
					   href="<?php echo $view->usersubscriptions_link; ?>">
						<i class="fa fa-bookmark"></i>
					</a>
				</li>

				<li class="comments">
					<a class="hasTooltip" title="<?php echo JText::_('COM_JUCOMMENT_DASHBOARD_COMMENTS'); ?>"
					   href="<?php echo $view->usercomments_link; ?>">
						<i class="fa fa-comments-o"></i>
					</a>
				</li>
			</ul>
		</div>
	</nav>

	<div class="dashboard-head clearfix">
		<div class="dashboard-avatar pull-left">
			<img alt="<?php echo $view->own_dashboard->name ?>" src="<?php echo $view->own_dashboard->getAvatar() ?>"/>
		</div>
		<h3><?php echo $view->own_dashboard->name ?></h3>
	</div>
