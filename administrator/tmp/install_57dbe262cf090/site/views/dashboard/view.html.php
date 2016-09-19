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

class JUCommentViewDashboard extends JViewLegacy
{
	public function display($tpl = null)
	{
		
		$this->userId           = JUComment::getDashboardUserId();
		$this->own_dashboard      = JUComment::getProfile( $this->userId );
		JUComment::import('helper', 'permission');
		JUComment::import('helper', 'moderator');
		$this->isModerator = JUCommentModeratorHelper::isModerator();
		$this->is_own_dashboard = JUCommentPermissionHelper::isOwnDashboard();
		if (JUCommentPermissionHelper::canViewDashboard() == false)
		{
			$uri      = JUri::getInstance();
			$loginUrl = JRoute::_('index.php?option=com_users&view=login&return=' . base64_encode($uri), false);
			$app      = JFactory::getApplication();
			$app->redirect($loginUrl, JText::_('COM_JUCOMMENT_YOU_ARE_NOT_AUTHORIZED_TO_ACCESS_THIS_PAGE'), 'warning');

			return false;
		}

		$userCommentModel = JUComment::getModel('UserComments');
		$modCommentModel = JUComment::getModel('ModComments');
		$userSubscriptionModel = JUComment::getModel('UserSubscriptions');
		$this->totalUserSubscriptions = $userSubscriptionModel->getTotal();
		$this->totalUserComments = $userCommentModel->getTotal();
		$this->totalModComments = $modCommentModel->getTotal();

		
		$this->dashboard_link   = JRoute::_('index.php?option=com_jucomment&view=dashboard&id=' . $this->userId);
		$this->usercomments_link    = JRoute::_('index.php?option=com_jucomment&view=usercomments&id=' . $this->userId);
		$this->modcomments_link    = JRoute::_('index.php?option=com_jucomment&view=modcomments&id=' . $this->userId);
		$this->usersubscriptions_link    = JRoute::_('index.php?option=com_jucomment&view=usersubscriptions&id=' . $this->userId);
		$this->modpermissions_link   = JRoute::_('index.php?option=com_jucomment&view=modpermissions');

		

		$state        = $this->get('State');
		$this->params = $state->params;

		
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		$this->_setDocument();
		$this->_prepareDocument();

		$template = JUComment::getTemplate();
		$template->set( 'view', $this );

		echo $template->fetch( 'dashboard/default.php' );

		
	}

	protected function _setDocument()
	{
		JUComment::getHelper( 'Document' )->load('view.dashboard', 'css');
	}

	protected function _prepareDocument()
	{
		JUComment::import('helper', 'breadcrumb');
		JUCommentBreadcrumbHelper::breadcrumbWithDashboard();
	}
}
