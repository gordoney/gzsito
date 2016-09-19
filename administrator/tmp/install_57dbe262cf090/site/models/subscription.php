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

defined('_JEXEC') or die('Restricted access');

class JUCommentModelSubscription extends JModelAdmin
{
	public function getForm($data = array(), $loadData = true)
	{
		return true;
	}

	public function validate($form, $data, $type = null)
	{
		if ($type == 'subscription')
		{
			$validData = array();
			$user     = JFactory::getUser();
			if ($user->get('guest'))
			{
				if (!isset($data['guest_name']) || !$data['guest_name'])
				{
					$this->setError(JText::_('COM_JUCOMMENT_INVALID_NAME'));

					return false;
				}

				if (!isset($data['guest_email']) || !$data['guest_email'])
				{
					$this->setError(JText::_('COM_JUCOMMENT_INVALID_EMAIL'));

					return false;
				}

				$validData['guest_name']  = $data['guest_name'];
				$validData['guest_email'] = $data['guest_email'];
			}
			else
			{
				$validData['user_id'] = $user->id;
			}

			if (!isset($data['component']) || !$data['component'])
			{
				$this->setError(JText::_('COM_JUCOMMENT_INVALID_COMPONENT'));

				return false;
			}
			$validData['component'] = $data['component'];

			$validData['section'] = $data['section'];

			if (!isset($data['cid']) || !$data['cid'])
			{
				$this->setError(JText::_('COM_JUCOMMENT_INVALID_CID'));

				return false;
			}
			$validData['cid'] = $data['cid'];

			$table = $this->getTable();
			if ($table->load($validData))
			{
				$this->setError(JText::_('COM_JUCOMMENT_YOU_ARE_ALREADY_SUBSCRIBED'));

				return false;
			}

			$validData['id'] = 0;

			return $validData;
		}
		else
		{
			$validData = array();
			$user     = JFactory::getUser();
			if ($user->get('guest'))
			{
				$this->setError(JText::_('COM_JUCOMMENT_INVALID_USER'));

				return false;
			}

			if (!isset($data['component']) || !$data['component'])
			{
				$this->setError(JText::_('COM_JUCOMMENT_INVALID_COMPONENT'));

				return false;
			}

			$validData['component'] = $data['component'];

			$validData['section'] = $data['section'];

			if (!isset($data['cid']) || !$data['cid'])
			{
				$this->setError(JText::_('COM_JUCOMMENT_INVALID_CID'));

				return false;
			}

			$validData['cid'] = $data['cid'];

			return $validData;
		}
	}

	
	public function save($data)
	{
		$dispatcher = JEventDispatcher::getInstance();
		$table      = $this->getTable('Subscription', 'JUCommentTable');

		if ((!empty($data['tags']) && $data['tags'][0] != ''))
		{
			$table->newTags = $data['tags'];
		}

		$key   = $table->getKeyName();
		$pk    = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');
		$isNew = true;

		
		JPluginHelper::importPlugin('content');

		
		try
		{
			
			if ($pk > 0)
			{
				$table->load($pk);
				$isNew = false;
			}
			
			if (!$table->bind($data))
			{
				$this->setError($table->getError());

				return false;
			}

			
			$this->prepareTable($table);

			
			if (!$table->check())
			{
				$this->setError($table->getError());

				return false;
			}

			
			$result = $dispatcher->trigger($this->event_before_save, array($this->option . '.' . $this->name, $table, $isNew));

			if (in_array(false, $result, true))
			{
				$this->setError($table->getError());

				return false;
			}

			
			if (!$table->store())
			{
				$this->setError($table->getError());

				return false;
			}

			
			$this->cleanCache();

			
			$dispatcher->trigger($this->event_after_save, array($this->option . '.' . $this->name, $table, $isNew));
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		$pkName = $table->getKeyName();

		if (isset($table->$pkName))
		{
			$this->setState($this->getName() . '.id', $table->$pkName);
		}
		$this->setState($this->getName() . '.new', $isNew);

		return $table->id;
	}

	
	protected function prepareTable($table)
	{
		$date = JFactory::getDate();
		$user = JFactory::getUser();
		
		if (empty($table->id))
		{
			if (!$table->created)
			{
				$table->created = $date->toSql();
			}

			if (!$table->name || !$table->name)
			{
				$table->user_id = $user->id;
			}

			$table->published = 1;

			$app = JFactory::getApplication();

			$language = $app->getLanguage();

			$table->language = $language->getTag();

			$table->ip_address = JUComment::getIpAddress();
		}
	}

	public function unSubscribe(&$pks)
	{
		$table = $this->getTable();
		foreach ($pks as $pk)
		{
			if ($table->load($pk))
			{
				if ($table->delete())
				{
					return true;
				}
				else
				{
					$this->setError($table->getError());

					return false;
				}
			}
			else
			{
				$this->setError($table->getError());

				return false;
			}
		}

		return true;
	}

	
	public function getTable($type = 'Subscription', $prefix = 'JUCommentTable', $config = array())
	{
		return JUComment::getTable($type, $config);
	}
}
