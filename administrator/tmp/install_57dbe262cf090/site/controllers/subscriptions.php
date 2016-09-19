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


jimport('joomla.application.component.controllerform');


class JUCommentControllerSubscriptions extends JControllerAdmin
{
	
	protected $text_prefix = 'COM_JUCOMMENT_SUBSCRIPTIONS';

	public function getModel($name = 'Subscription', $prefix = 'JUCommentModel', $config = array())
	{
		return JUComment::getModel('Subscription', false, $config);
	}

	public function unSubscribe()
	{
		JSession::checkToken('get') or jexit(JText::_('JINVALID_TOKEN'));

		$ajax   = JUComment::getHelper('ajax');
		$model  = $this->getModel();
		$data   = $this->input->post->get('jform', array(), 'array');
		$sub_id = $data['sub_id'];
		
		if (!$this->allowUnSubscribe($sub_id))
		{
			$result['message'] = JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED');
			$ajax->fail($result);
			$ajax->send();
		}

		$pks = (array) $sub_id;
		if (!$model->unSubscribe($pks))
		{
			$result['message'] = $model->getError();
			$ajax->fail($result);
			$ajax->send();
		}

		$result['message'] = JText::_('COM_JUCOMMENT_UNSUBSCRIBE_SUCCESSFULLY');

		$user = JFactory::getUser();
		if (!$user->get('guest'))
		{
			$template = JUComment::getTemplate();
			$template->set('subscription', false);
			$template->set('component', $data['component']);
			$template->set('section', $data['section']);
			$template->set('cid', $data['cid']);

			$result['html'] = $template->fetch('comment/modal_subscription.php');
		}
		else
		{
			$result['html'] = '';
		}

		$ajax->success($result);
		$ajax->send();
	}

	protected function allowUnSubscribe($sub_id)
	{
		if (!$sub_id)
		{
			return false;
		}

		$user = JFactory::getUser();
		if ($user->get('guest'))
		{
			return false;
		}

		$table = JUComment::getTable('Subscription');
		if (!$table->load($sub_id))
		{
			return false;
		}

		if ($table->user_id == 0 || $user->id != $table->user_id)
		{
			return false;
		}

		return true;
	}
}
