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


class JUCommentControllerReport extends JControllerForm
{
	
	protected $text_prefix = 'COM_JUCOMMENT_REPORT';

	
	public function getModel($name = 'Report', $prefix = 'JUCommentModel', $config = array('ignore_request' => true))
	{
		return JUComment::getModel('Report');
	}

	public function save($key = null, $urlVar = null)
	{
		
		JSession::checkToken('get') or jexit(JText::_('JINVALID_TOKEN'));

		$ajax               = JUComment::getHelper('ajax');
		$model              = $this->getModel();
		$data               = $this->input->post->get('jform', array(), 'array');
		$data['comment_id'] = $this->input->get('comment_id');
		
		$result = array();
		
		if (!$this->allowSave($data, $key))
		{
			$result['message'] = JText::_('JLIB_APPLICATION_ERROR_REPORT_NOT_PERMITTED');
			$ajax->fail($result);
			$ajax->send();
		}

		
		
		$form = $model->getForm();

		$validData = $model->validate($form, $data);

		
		if ($validData === false)
		{
			
			$errors  = $model->getErrors();
			$message = array();

			
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof Exception)
				{
					$message[] = $errors[$i]->getMessage();
				}
				else
				{
					$message[] = $errors[$i];
				}
			}

			$result['message'] = implode("<br/>", $message);
			$ajax->fail($result);
			$ajax->send();
		}

		if (!isset($validData['tags']))
		{
			$validData['tags'] = null;
		}

		
		if (!$model->save($validData))
		{
			$result['message'] = JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError());
			$ajax->fail($result);
			$ajax->send();
		}

		

		$actionsModel = JUComment::getModel('actions');
		$actionsModel->addAction('report', $data['comment_id'], JFactory::getUser()->id);

		JUCommentLogHelper::addLog("report", $data['comment_id']);

		$result['message'] = JText::_('COM_JUCOMMENT_REPORT_SUCCESSFULLY');
		$ajax->success($result);
		$ajax->send();
	}

	protected function allowSave($data, $key = 'id')
	{
		return true;
	}
}
