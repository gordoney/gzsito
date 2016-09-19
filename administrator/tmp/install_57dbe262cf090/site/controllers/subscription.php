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


class JUCommentControllerSubscription extends JControllerForm
{
	
	protected $text_prefix = 'COM_JUCOMMENT_SUBSCRIPTION';

	
	public function getModel($name = 'Subscription', $prefix = 'JUCommentModel', $config = array('ignore_request' => true))
	{
		return JUComment::getModel('Subscription');
	}

	public function save($key = 'id', $urlVar = null)
	{
		
		JSession::checkToken('get') or jexit(JText::_('JINVALID_TOKEN'));

		$ajax    = JUComment::getHelper('ajax');
		$model   = $this->getModel();
		$table   = $model->getTable();
		$data    = $this->input->post->get('jform', array(), 'array');
		$checkin = property_exists($table, 'checked_out');

		$data['published'] = -1;

		$result = array();

		
		if (!$this->allowSave($data, $key))
		{
			$result['message'] = JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED');
			$ajax->fail($result);
			$ajax->send();
		}

		
		$validData = $model->validate('', $data, 'subscription');
		
		if ($validData === false)
		{
			
			$errors            = $model->getErrors();
			$result['message'] = array();

			
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof Exception)
				{
					$result['message'][] = $errors[$i]->getMessage();
				}
				else
				{
					$result['message'][] = $errors[$i];
				}
			}

			$result['message'] = implode("<br/>", $result['message']);

			$ajax->fail($result);
			$ajax->send();
		}

		if (!isset($validData['tags']))
		{
			$validData['tags'] = null;
		}

		$subscriptionId = $model->save($validData);
		if (!$subscriptionId)
		{
			$result['message'] = JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError());
			$ajax->fail($result);
			$ajax->send();
		}

		
		if ($checkin && $model->checkin($validData[$key]) === false)
		{
			$result['message'] = JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError());
			$ajax->fail($result);
			$ajax->send();
		}

		$this->postSaveHook($model, $validData);

		$table->load($subscriptionId);

		$result['message'] = 'Success';

		$user = JFactory::getUser();
		if (!$user->get('guest'))
		{
			$template = JUComment::getTemplate();
			$template->set('subscription', JUComment::getSubscription($table->component, $table->section, $table->cid));
			$template->set('component', $table->component);
			$template->set('section', $table->section);
			$template->set('cid', $table->cid);

			$result['html'] = $template->fetch('comment/modal_subscription.php');
		}
		else
		{
			$result['html'] = '';
		}

		$ajax->success($result);
		$ajax->send();
	}

	protected function allowSave($data, $key = 'id')
	{
		return true;
	}
}
