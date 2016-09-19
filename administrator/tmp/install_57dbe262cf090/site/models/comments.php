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

class JUCommentModelComments extends JModelLegacy
{
	public $_total = null;
	public $_comments = null;

	public function __construct()
	{
		$this->db = JFactory::getDbo();

		parent::__construct();
	}

	public function getCount($component = 'all', $section = 'all', $cid = 'all', $options = array())
	{
		
		$defaultOptions = array(
			'sort'       => 'default',
			'direction'  => 'ASC',
			'limit'      => 0,
			'limitstart' => 0,
			'search'     => '',
			'approved'   => 1,
			'published'  => 1,
			'userid'     => 'all',
			'parent_id'  => 'all',
			'recursive'  => 0,
			'max_level'  => 0,
			'filter_language' => 0
		);

		$options = JUComment::mergeOptions($defaultOptions, $options);

		$query = $this->db->getQuery(true);

		$query->select('COUNT(1)');
		$query->from('#__jucomment_comments AS cm');

		if ($component !== 'all')
		{
			$query->where('cm.component = ' . $this->db->quote($component));
		}

		if ($section !== 'all')
		{
			$query->where('cm.section = ' . $this->db->quote($section));
		}

		if ($cid !== 'all' && !empty($cid))
		{
			if (is_array($cid))
			{
				$cid = implode(',', $cid);
				$query->where('cm.cid IN (' . $cid . ')');
			}
			else
			{
				$query->where('cm.cid = ' . $this->db->quote($cid));
			}
		}

		if ($options['approved'] !== 'all')
		{
			$query->where('cm.approved = ' . $this->db->quote($options['approved']));
		}

		if ($options['published'] !== 'all')
		{
			$query->where('cm.published = ' . $this->db->quote($options['published']));
		}

		if ($options['userid'] !== 'all')
		{
			$query->where('cm.user_id = ' . $this->db->quote($options['userid']));
		}

		if ($options['parent_id'] !== 'all')
		{
			$query->where('cm.parent_id = ' . $this->db->quote($options['parent_id']));
		}

		if ($options['search'] !== '')
		{
			$query->where('cm.comment LIKE ' . $this->db->quote('%' . $options['search'] . '%'));
		}

		if ($options['filter_language'])
		{
			$language = JFactory::getLanguage();
			$tag      = $language->getTag();
			if ($tag && $tag != '*')
			{
				$query->where('cm.language IN (' . $this->db->quote($tag) . ',' . $this->db->quote('*') . ',' . $this->db->quote('') . ')');
			}
		}

		$query->where('cm.level > 0 ');

		$this->db->setQuery($query);

		return $this->db->loadResult();
	}

	public function getComments($component = 'all', $section = 'all', $cid = 'all', $options = array())
	{
		
		$defaultOptions = array(
			'sort'       => 'default',
			'direction'  => 'ASC',
			'limit'      => 0,
			'limitstart' => 0,
			'search'     => '',
			'approved'   => 1,
			'published'  => 1,
			'userid'     => 'all',
			'parent_id'  => 1,
			'recursive'  => 1,
			'max_level'  => 0,
			'filter_language'   => 0
		);

		
		$options = JUComment::mergeOptions($defaultOptions, $options);

		$query = $this->db->getQuery(true);

		$query->select('cm.*');

		$query->from('#__jucomment_comments AS cm');

		if ($component !== 'all')
		{
			$query->where('cm.component = ' . $this->db->quote($component));
		}

		if ($section !== 'all')
		{
			$query->where('cm.section = ' . $this->db->quote($section));
		}

		if ($cid !== 'all' && !empty($cid))
		{
			if (is_array($cid))
			{
				$cid = implode(',', $cid);
				$query->where('cm.cid IN (' . $cid . ')');
			}
			else
			{
				$query->where('cm.cid = ' . $this->db->quote($cid));
			}
		}

		if ($options['approved'] !== 'all')
		{
			$query->where('cm.approved = ' . $this->db->quote($options['approved']));
		}

		if ($options['published'] !== 'all')
		{
			$query->where('cm.published = ' . $this->db->quote($options['published']));
		}

		if ($options['userid'] !== 'all')
		{
			$query->where('cm.user_id = ' . $this->db->quote($options['userid']));
		}

		if ($options['parent_id'] !== 'all')
		{
			$query->where('cm.parent_id = ' . $this->db->quote($options['parent_id']));
		}

		if ($options['search'] !== '')
		{
			$query->where('cm.comment LIKE ' . $this->db->quote('%' . $options['search'] . '%'));
		}

		if ($options['filter_language'])
		{
			$language = JFactory::getLanguage();
			$tag      = $language->getTag();
			if ($tag && $tag != '*')
			{
				$query->where('cm.language IN (' . $this->db->quote($tag) . ',' . $this->db->quote('*') . ',' . $this->db->quote('') . ')');
			}
		}

		$limitstart = $limit = 0;
		if ($options['sort'] != 'random')
		{
			if ($options['sort'])
			{
				switch (strtolower($options['sort']))
				{
					case 'default' :
						$query->order('cm.lft DESC');
						break;
					default :
						$query->order($options['sort'] . ' ' . $options['direction']);
				}
			}

			if ($options['limit'] > 0)
			{
				$limit      = $options['limit'];
				$limitstart = $options['limitstart'];
			}
			else
			{
				$jLimit     = JFactory::getConfig()->get('list_limit');
				$params     = JUComment::getParams();
				$app        = JFactory::getApplication();
				$limit      = $app->input->get('jucomment-limit', null) !== null ? $app->input->get('jucomment-limit') : $params->get('comment_pagination', $jLimit);
				$limitstart = $app->input->get('jucomment-limitstart', null) !== null ? $app->input->get('jucomment-limitstart') : $options['limitstart'];
			}
		}

		$query->where('cm.level > 0');

		$this->db->setQuery($query, $limitstart, $limit);

		$comments = $this->db->loadObjectList();

		
		if ($options['sort'] == 'random')
		{
			$comments = $this->buildRandom($comments, $options);
		}

		if ($this->db->getErrorNum() > 0)
		{
			JError::raiseError($this->db->getErrorNum(), $this->db->getErrorMsg() . $this->db->stderr());
		}

		if ($comments && $options['parent_id'] !== 'all' && $options['recursive'])
		{
			$commentsRecursive = array();
			foreach ($comments AS $comment)
			{
				$commentsRecursive[] = $comment;
				$commentsRecursive   = array_merge($commentsRecursive, $this->getCommentRecursive($comment->id));
			}

			$comments = $commentsRecursive;
		}

		return $comments;
	}

	protected function getCommentRecursive($parent_id, $options = array())
	{
		
		$defaultOptions = array(
			'sort'       => 'default',
			'direction'  => 'ASC',
			'limit'      => 0,
			'limitstart' => 0,
			'approved'   => 1,
			'published'  => 1,
			'max_level'  => 0,
			'filter_language'   => 0
		);

		
		$options = JUComment::mergeOptions($defaultOptions, $options);

		$query = $this->db->getQuery(true);

		$query->select('cm.*');

		$query->from('#__jucomment_comments AS cm');

		if ($options['approved'] !== 'all')
		{
			$query->where('cm.approved = ' . $this->db->quote($options['approved']));
		}

		if ($options['published'] !== 'all')
		{
			$query->where('cm.published = ' . $this->db->quote($options['published']));
		}

		if ($options['sort'] != 'random' && $options['sort'])
		{
			switch (strtolower($options['sort']))
			{
				case '' :
				case 'default' :
					$query->order('cm.lft DESC');
					break;
				default :
					if (!JUCommentFieldHelper::appendFieldOrderingPriority($query, $options['sort'], $options['direction']))
					{
						$query->order('cm.lft DESC');
					}
			}
		}

		if ($options['filter_language'])
		{
			$language = JFactory::getLanguage();
			$tag      = $language->getTag();
			if ($tag && $tag != '*')
			{
				$query->where('cm.language IN (' . $this->db->quote($tag) . ',' . $this->db->quote('*') . ',' . $this->db->quote('') . ')');
			}
		}

		$query->where('parent_id = ' . $parent_id);
		$this->db->setQuery($query);

		$comments = $this->db->loadObjectList();

		
		if ($options['sort'] == 'random')
		{
			$comments = $this->buildRandom($comments, $options);
		}


		if ($this->db->getErrorNum() > 0)
		{
			JError::raiseError($this->db->getErrorNum(), $this->db->getErrorMsg() . $this->db->stderr());
		}

		$recursiveComments = array();
		if (count($comments) > 0)
		{
			foreach ($comments AS $comment)
			{
				if ($options['max_level'] && $comment->level > $options['max_level'])
				{
					continue;
				}

				$recursiveComments[] = $comment;
				
				if ($comment->rgt > $comment->lft + 1)
				{
					$recursiveComments = array_merge($recursiveComments, $this->getCommentRecursive($comment->id));
				}
			}
		}

		return $recursiveComments;
	}

	private function buildRandom($comments, $options = array())
	{
		if ($options['limit'] > 0)
		{
			$limit = $options['limit'];
		}
		else
		{
			$jLimit = JFactory::getConfig()->get('list_limit');
			$params = JUComment::getParams();
			$app    = JFactory::getApplication();

			$limit = $app->input->get('limit', null) !== null ? $app->input->get('limit') : $params->get('comment_pagination', $jLimit);
		}

		if (count($comments) <= 1)
		{
			return $comments;
		}

		$limit = $limit > count($comments) ? count($comments) : $limit;

		$indexes = array_rand($comments, $limit);

		$tmp = array();

		if (is_array($indexes))
		{
			foreach ($indexes as $index)
			{
				$tmp[] = $comments[$index];
			}
		}
		else
		{
			$tmp[] = $comments[$indexes];
		}

		return $tmp;
	}

	function getData($options = array())
	{
		$mainframe = JFactory::getApplication();
		$view      = JRequest::getVar('view');

		
		$defaultOptions = array(
			'no_tree'   => 0,
			'component' => '*',
			'published' => '*',
			'userid'    => '',
			'parent_id' => 0,
			'no_search' => 0,
			'no_child'  => 0
		);

		
		$options = JUComment::mergeOptions($defaultOptions, $options);

		$querySelect      = '';
		$querySelectCount = '';
		$queryWhere       = array();
		$queryOrder       = '';
		$queryLimit       = '';
		$queryTotal       = '';

		$filter_publish   = $mainframe->getUserStateFromRequest('com_jucomment.' . $view . '.filter_publish', 'filter_publish', $options['published'], 'string');
		$filter_component = $mainframe->getUserStateFromRequest('com_jucomment.' . $view . '.filter_component', 'filter_component', $options['component'], 'string');
		$filter_order     = $mainframe->getUserStateFromRequest('com_jucomment.' . $view . '.filter_order', 'filter_order', 'created', 'string');
		$filter_order_Dir = $mainframe->getUserStateFromRequest('com_jucomment.' . $view . '.filter_order_Dir', 'filter_order_Dir', 'DESC', 'word');
		$search           = $mainframe->getUserStateFromRequest('com_jucomment.' . $view . '.search', 'search', '', 'string');
		$limit            = $mainframe->getUserStateFromRequest('com_jucomment.' . $view . '.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart       = $mainframe->getUserStateFromRequest('com_jucomment.' . $view . '.limitstart', 'limitstart', 0, 'int');

		JUComment::import('helper', 'string');
		$search = JUCommentStringHelper::escape(trim(JString::strtolower($search)));

		
		
		if ($options['no_search'])
		{
			$search = '';
		}

		

		$querySelect = 'SELECT * FROM ' . $this->db->nameQuote('#__jucomment_comments');

		$querySelectCount = 'SELECT COUNT(1) FROM ' . $this->db->nameQuote('#__jucomment_comments');

		
		if ($filter_component != '*')
		{
			$queryWhere[] = $this->db->nameQuote('component') . ' = ' . $this->db->quote($filter_component);
		}

		
		if ($filter_publish != '*')
		{
			$queryWhere[] = $this->db->nameQuote('published') . ' = ' . $this->db->quote($filter_publish);
		}

		

		if ($search)
		{
			$queryWhere[] = 'LOWER( ' . $this->db->nameQuote('comment') . ' ) LIKE \'%' . $search . '%\' ';
		}
		else
		{
			if ($options['no_tree'] == 0)
			{
				$queryWhere[] = $this->db->nameQuote('parent_id') . ' = ' . $this->db->quote($options['parent_id']);
			}
		}

		if (count($queryWhere) > 0)
		{
			$queryWhere = ' WHERE ' . implode(' AND ', $queryWhere);
		}
		else
		{
			$queryWhere = '';
		}

		$queryOrder = ' ORDER BY ' . $filter_order . ' ' . $filter_order_Dir;

		if ($options['parent_id'] == 0 && $limit != 0)
		{
			$queryLimit = ' LIMIT ' . $limitstart . ',' . $limit;
		}

		$queryTotal = $querySelectCount . $queryWhere;

		
		$this->db->setQuery($queryTotal);
		$this->_total = $this->db->loadResult();

		jimport('joomla.html.pagination');
		$this->_pagination = new JPagination($this->_total, $limitstart, $limit);

		
		$query = $querySelect . $queryWhere . $queryOrder . $queryLimit;

		$this->db->setQuery($query);
		$result = $this->db->loadObjectList();

		if ($this->db->getErrorNum() > 0)
		{
			JError::raiseError($this->db->getErrorNum(), $this->db->getErrorMsg() . $this->db->stderr());
		}

		if (!empty($result) && $options['no_child'] == 0)
		{
			$ids = array();
			foreach ($result as $row)
			{
				$ids[] = $row->id;
			}

			$childCount = $this->getChildCount($ids);

			foreach ($result as &$row)
			{
				$row->childs = isset($childCount[$row->id]) ? $childCount[$row->id] : 0;
			}
		}

		return $result;
	}

	
	function getTotalComments($userId = 0)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('COUNT(1) AS total')
			->from('#__jucomment_comments')
			->where('published = 1');

		if (!empty($userId))
		{
			$query->where('created_by = ' . $userId);
		}

		$db->setQuery($query);
		$result = $db->loadResult();

		return (empty($result)) ? 0 : $result;
	}

	function getTotalReplies($userId = 0)
	{
		$where = array();

		$query = 'SELECT COUNT(1) FROM ' . $this->db->nameQuote('#__jucomment_comments');

		$where[] = 'parent_id != 0';

		if (!empty($userId))
			$where[] = 'created_by = ' . $this->db->Quote($userId);

		$extra = (count($where) ? ' WHERE ' . implode(' AND ', $where) : '');
		$query = $query . $extra;

		$this->db->setQuery($query);

		$result = $this->db->loadResult();

		return (empty($result)) ? 0 : $result;
	}

	function getLatestComment($component, $section, $cid, $parentId = 1)
	{
		$query = 'SELECT `id`, `lft`, `rgt` FROM `#__jucomment_comments`';
		$query .= ' WHERE `component` = ' . $this->db->Quote($component);
		$query .= ' WHERE `section` = ' . $this->db->Quote($section);
		$query .= ' AND `cid` = ' . $this->db->Quote($cid);
		$query .= ' AND `parent_id` = ' . $this->db->Quote($parentId);
		$query .= ' ORDER BY `lft` DESC LIMIT 1';

		$this->db->setQuery($query);
		$result = $this->db->loadObject();

		return $result;
	}

	public function updateVote($type, $comment_id, $action, $weight = 1)
	{
		if (!$comment_id || !$type)
		{
			return false;
		}

		switch ($type)
		{
			case 'vote-up':
				if ($action == 'add')
				{
					$fields = array(
						'helpful_votes = helpful_votes + ' . $weight,
						'total_votes = total_votes + ' . $weight
					);
				}
				else
				{
					$fields = array(
						'helpful_votes = helpful_votes - ' . $weight,
						'total_votes = total_votes - ' . $weight
					);
				}
				break;

			case 'vote-down':
				if ($action == 'add')
				{
					$fields = array(
						'total_votes = total_votes + ' . $weight
					);
				}
				else
				{
					$fields = array(
						'total_votes = total_votes - ' . $weight
					);
				}
				break;

			default:
				return false;
				break;
		}

		$query = $this->db->getQuery(true);
		$query->update('#__jucomment_comments')->set($fields)->where('id = ' . $comment_id);
		$this->db->setQuery($query);

		return $this->db->execute();
	}

	public function vote($type, $comment_id, $action)
	{
		$actionsModel = JUComment::getModel('actions');
		$userid       = JFactory::getUser()->id;

		$types   = explode(",", $type);
		$actions = explode(",", $action);
		foreach ($types as $key => $type)
		{
			$action = $actions[$key];
			if ($action == 'add')
			{
				$result = false;
				if ($userid > 0)
				{
					if (!$actionsModel->actionExists($type, $comment_id, $userid))
					{
						$result = $actionsModel->addAction($type, $comment_id, $userid);
					}
				}
				else
				{
					$result = $actionsModel->addAction($type, $comment_id, $userid);
				}

				if ($result)
				{
					$this->updateVote($type, $comment_id, $action);
				}
			}
			else
			{
				$result = $actionsModel->removeAction($type, $comment_id, $userid);

				if ($result)
				{
					$this->updateVote($type, $comment_id, $action, $result);
				}
			}
		}

		return true;
	}
}
