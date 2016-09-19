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

jimport('joomla.application.component.modellist');

JUComment::import('class', 'jucmmodellist');

class JUCommentModelUserSubscriptions extends JUCMModelList
{
	
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				's.component',
				's.cid',
				's.created'
			);
		}

		parent::__construct($config);
	}

	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication();
		
		if ($app->input->post->get('filter_component') !== null)
		{
			$component = $this->getUserStateFromRequest($this->context . '.filter.component', 'filter_component', '', 'string');
			$this->setState('filter.component', $component);

			parent::populateState();
		}
		else
		{
			parent::populateState();

			$component = $this->getUserStateFromRequest($this->context . '.filter.component', 'filter_component', '', 'string');
			$this->setState('filter.component', $component);
		}
	}

	public function getTotal()
	{
		
		$store = $this->getStoreId('getTotal');

		
		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		$userId = JUCommentFrontHelper::getDashboardUserId();
		
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('s.*');
		$query->from('#__jucomment_subscriptions AS s');
		$query->where('s.user_id = ' . $userId);
		$component = $this->getState('filter.component', '');
		if ($component)
		{
			if (strpos($component, '.') !== false)
			{
				$component = explode('.', $component);
				$query->where('s.component = ' . $db->quote($component[0]) . ' AND s.section = ' . $db->quote($component[1]));
			}
			else
			{
				$query->where('s.component = ' . $db->quote($component));
			}
		}
		try
		{
			$total = (int) $this->_getListCount($query);
		}
		catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		
		$this->cache[$store] = $total;

		return $this->cache[$store];
	}

	public function getListQuery()
	{
		$userId = JUCommentFrontHelper::getDashboardUserId();
		$db     = $this->getDbo();

		$query = $db->getQuery(true);
		$query->select('s.*');
		$query->from('#__jucomment_subscriptions AS s');
		$query->where('s.user_id = ' . $userId);
		$component = $this->getState('filter.component', '');
		if ($component)
		{
			if (strpos($component, '.') !== false)
			{
				$component = explode('.', $component);
				$query->where('s.component = ' . $db->quote($component[0]) . ' AND s.section = ' . $db->quote($component[1]));
			}
			else
			{
				$query->where('s.component = ' . $db->quote($component));
			}
		}
		$ordering  = $this->getState('list.ordering', 's.component');
		$direction = $this->getState('list.direction', 'DESC');
		$query->order($ordering . ' ' . $direction);

		return $query;
	}


	
	public function getComponents()
	{
		$db   = $this->getDbo();
		$user = JFactory::getUser();
		if ($user->id <= 0)
		{
			return null;
		}

		$query = $db->getQuery(true);
		$query->select('s.component, s.section');

		$query->from('#__jucomment_subscriptions AS s');

		
		$query->where('s.user_id = ' . $user->id);
		$query->group('s.component, s.section');
		$query->order('s.component, s.section');

		$db->setQuery($query);

		$items = $db->loadObjectList();

		$options = array();
		foreach ($items as $item)
		{
			if ($item->section)
			{
				$options[$item->component . '.' . $item->section] = '|-- ' . $item->section;
			}
			else
			{
				$JUCMApplication            = JUComment::loadApplication($item->component);
				$options[$item->component] = $JUCMApplication->getComponentName();
			}
		}

		array_unshift($options, array('value' => '', 'text' => JText::_('COM_JUCOMMENT_SELECT_COMPONENT')));

		return $options;
	}

}