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

jimport('joomla.application.component.controlleradmin');

class JUCommentControllerUserSubscriptions extends JControllerAdmin
{
	
	protected $view_item = 'COM_JUCOMMENT_USER_SUBSCRIPTIONS';

	
	public function getModel($name = 'Subscription', $prefix = 'JUCommentModel', $config = array())
	{
		return parent::getModel($name, $prefix, $config);
	}

	
	public function unSubscribe()
	{
		
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));

		JUComment::import('helper', 'permission');

		
		if (!JUCommentPermissionHelper::isOwnDashboard())
		{
			
			JError::raiseWarning(500, JText::_('COM_JUCOMMENT_YOU_ARE_NOT_AUTHORIZED_TO_ACCESS_THIS_PAGE'));

			return false;
		}

		$app = JFactory::getApplication();
		$ids = $app->input->get('cid', array(), 'array');

		if (!$ids)
		{
			$ids = (array) $app->input->getInt('sub_id', 0);
		}

		
		JArrayHelper::toInteger($ids);

		
		try
		{
			$model = $this->getModel();
			$model->unSubscribe($ids);

			$this->setMessage(JText::plural($this->text_prefix . '_N_ITEMS_UNSUBSCRIBED', count($ids)));
		}
		catch (Exception $e)
		{
			$this->setMessage($e->getMessage(), 'error');
		}

		$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));

		return false;
	}
}