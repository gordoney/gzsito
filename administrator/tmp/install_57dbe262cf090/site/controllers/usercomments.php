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

class JUCommentControllerUserComments extends JControllerAdmin
{
	
	protected $text_prefix = 'COM_JUCOMMENT_COMMENTS';

	
	public function getModel($name = 'UserComment', $prefix = 'JUCommentModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	public function checkin()
	{
		
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));

		
		$app = JFactory::getApplication();
		$ids = $app->input->get('cid', array(), 'array');

		$model  = $this->getModel();
		$return = $model->checkin($ids);
		if ($return === false)
		{
			
			$message = JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError());
			$this->setRedirect($this->getReturnPage(), $message, 'error');

			return false;
		}
		else
		{
			
			$message = JText::plural($this->text_prefix . '_N_ITEMS_CHECKED_IN', count($ids));
			$this->setRedirect($this->getReturnPage(), $message);

			return true;
		}
	}

	
	public function approve()
	{
		
		JSession::checkToken('request') or die(JText::_('JINVALID_TOKEN'));

		
		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');
		if (empty($cid))
		{
			JLog::add(JText::_($this->text_prefix . '_NO_ITEM_SELECTED'), JLog::WARNING, 'jerror');
		}
		else
		{
			
			$model = $this->getModel();

			
			JArrayHelper::toInteger($cid);

			
			try
			{
				$model->approve($cid);
				$ntext = $this->text_prefix . '_N_ITEMS_APPROVED';
				$this->setMessage(JText::plural($ntext, count($cid)));
			}
			catch (Exception $e)
			{
				$this->setMessage($e->getMessage(), 'error');
			}

		}

		$this->setRedirect($this->getReturnPage());
	}

	
	public function publish()
	{
		
		JSession::checkToken('request') or die(JText::_('JINVALID_TOKEN'));

		
		$cid   = JFactory::getApplication()->input->get('cid', array(), 'array');
		$data  = array('publish' => 1, 'unpublish' => 0, 'archive' => 2, 'trash' => -2, 'report' => -3);
		$task  = $this->getTask();
		$value = JArrayHelper::getValue($data, $task, 0, 'int');

		if (empty($cid))
		{
			JLog::add(JText::_($this->text_prefix . '_NO_ITEM_SELECTED'), JLog::WARNING, 'jerror');
		}
		else
		{
			
			$model = $this->getModel();

			
			JArrayHelper::toInteger($cid);

			
			try
			{
				$model->publish($cid, $value);

				if ($value == 1)
				{
					$ntext = $this->text_prefix . '_N_ITEMS_PUBLISHED';
				}
				elseif ($value == 0)
				{
					$ntext = $this->text_prefix . '_N_ITEMS_UNPUBLISHED';
				}
				elseif ($value == 2)
				{
					$ntext = $this->text_prefix . '_N_ITEMS_ARCHIVED';
				}
				else
				{
					$ntext = $this->text_prefix . '_N_ITEMS_TRASHED';
				}
				$this->setMessage(JText::plural($ntext, count($cid)));
			}
			catch (Exception $e)
			{
				$this->setMessage($e->getMessage(), 'error');
			}

		}

		$this->setRedirect($this->getReturnPage());
	}

	
	public function delete()
	{
		
		JSession::checkToken('request') or die(JText::_('JINVALID_TOKEN'));

		
		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');

		if (!is_array($cid) || count($cid) < 1)
		{
			JLog::add(JText::_($this->text_prefix . '_NO_ITEM_SELECTED'), JLog::WARNING, 'jerror');
		}
		else
		{
			
			$model = $this->getModel();

			
			jimport('joomla.utilities.arrayhelper');
			JArrayHelper::toInteger($cid);

			
			if ($model->delete($cid))
			{
				$this->setMessage(JText::plural($this->text_prefix . '_N_ITEMS_DELETED', count($cid)));
			}
			else
			{
				$this->setMessage($model->getError());
			}
		}
		
		$this->postDeleteHook($model, $cid);

		$this->setRedirect($this->getReturnPage());
	}

	
	protected function getReturnPage()
	{
		$app    = JFactory::getApplication();
		$return = $app->input->get('return', null, 'base64');

		if (empty($return) || !JUri::isInternal(base64_decode($return)))
		{
			return JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false);
		}
		else
		{
			return JRoute::_(base64_decode($return), false);
		}
	}

}
