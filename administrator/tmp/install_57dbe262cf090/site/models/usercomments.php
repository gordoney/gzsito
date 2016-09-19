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

class JUCommentModelUserComments extends JUCMModelList
{
	
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'cm.title',
				'cm.created',
				'cm.helpful_votes',
				'cm.total_votes',
				'cm.lft'
			);
		}

		parent::__construct($config);
	}

	
	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication();
		
		if ($app->input->post->get('filter_component') !== null)
		{
			$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string');
			$this->setState('filter.search', $search);

			$component = $this->getUserStateFromRequest($this->context . '.filter.component', 'filter_component', '', 'string', true);
			$this->setState('filter.component', $component);

			parent::populateState();
		}
		else
		{
			parent::populateState();

			$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string');
			$this->setState('filter.search', $search);

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

		$user = JFactory::getUser();
		
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('cm.*');
		$query->from('#__jucomment_comments AS cm');
		$query->where('cm.user_id = ' . $user->id);

		$search = $this->getState('filter.search');
		if ($search)
		{
			$search = '%' . $db->escape($search, true) . '%';
			$query->where("cm.title LIKE '{$search}'");
		}

		$component = $this->getState('filter.component', '');
		if ($component)
		{
			if (strpos($component, '.') !== false)
			{
				$component = explode('.', $component);
				$query->where('cm.component = ' . $db->quote($component[0]) . ' AND cm.section = ' . $db->quote($component[1]));
			}
			else
			{
				$query->where('cm.component = ' . $db->quote($component));
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

	
	protected function getListQuery()
	{
		$db     = $this->getDbo();
		$params = JUComment::getParams();

		$user = JFactory::getUser();
		if ($user->id <= 0)
		{
			return null;
		}

		$query = $db->getQuery(true);
		$query->select('cm.*');
		$query->select('(SELECT COUNT(1) FROM #__jucomment_reports AS r WHERE r.comment_id = cm.id) AS total_reports');

		$query->from('#__jucomment_comments AS cm');

		
		$query->select('cp.title AS parent_title');
		$query->join('LEFT', '#__jucomment_comments AS cp ON cp.id = cm.parent_id');

		
		$query->where('cm.user_id = ' . $user->id);

		$search = $this->getState('filter.search');

		if ($search)
		{
			$search = '%' . $db->escape($search, true) . '%';
			$query->where("cm.title LIKE '{$search}'");
		}

		$component = $this->getState('filter.component', '');
		if ($component)
		{
			if (strpos($component, '.') !== false)
			{
				$component = explode('.', $component);
				$query->where('cm.component = ' . $db->quote($component[0]) . ' AND cm.section = ' . $db->quote($component[1]));
			}
			else
			{
				$query->where('cm.component = ' . $db->quote($component));
			}
		}

		$commentOrdering = $params->get('comment_ordering', 'cm.created');
		$ordering        = $this->getState('list.ordering', $commentOrdering);

		$commentDirection = $params->get('comment_direction', 'DESC');
		$direction        = $this->getState('list.direction', $commentDirection);

		
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
		$query->select('cm.component, cm.section');

		$query->from('#__jucomment_comments AS cm');

		
		$query->where('cm.user_id = ' . $user->id);
		$query->where('cm.level > 0');
		$query->group('cm.component, cm.section');
		$query->order('cm.component, cm.section');
		$db->setQuery($query);

		$items   = $db->loadObjectList();
		$options = array();
		foreach ($items as $item)
		{
			if ($item->section != '')
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

	public function getStart()
	{
		return $this->getState('list.start');
	}
} 