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

class JUCommentModelActions extends JModelLegacy
{
	public function __construct()
	{
		$this->db = JFactory::getDbo();

		parent::__construct();
	}

	
	public function clearReports($comments)
	{
		$allComments = implode(',', $comments);
		$query       = 'DELETE FROM ' . $this->db->namequote('#__jucomment_actions');
		$query .= ' WHERE ' . $this->db->namequote('comment_id') . ' IN (' . $allComments . ')';
		$query .= ' AND ' . $this->db->namequote('type') . ' = ' . $this->db->quote('report');

		$this->db->setQuery($query);

		if (!$this->db->query())
		{
			$this->setError($this->db->getErrorMsg());

			return false;
		}

		return true;
	}

	
	public function actionExists($type, $comment_id, $user_id)
	{
		$actionsTable = JUComment::getTable('Action');
		if ($actionsTable->load(array("type" => $type, "comment_id" => $comment_id, "action_by" => $user_id)))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function addAction($type, $comment_id, $user_id)
	{
		$now        = JFactory::getDate()->toSql();
		$ip_address = JUComment::getIpAddress();

		$actionsTable = JUComment::getTable('Action');

		$actionsTable->type       = $type;
		$actionsTable->comment_id = $comment_id;
		$actionsTable->action_by  = $user_id;
		$actionsTable->actioned   = $now;
		$actionsTable->ip_address = $ip_address;

		if (!$actionsTable->store())
		{
			return false;
		}

		JUComment::import('helper', 'log');
		JUCommentLogHelper::addLog($type, $comment_id);

		return $actionsTable->id;
	}

	public function removeAction($type = 'all', $comment_id, $user_id = 'all')
	{
		$query = $this->db->getQuery(true);
		$query->delete('#__jucomment_actions');

		if ($type !== 'all')
		{
			$query->where('`type` = ' . $this->db->quote($type));
		}

		if ($comment_id)
		{
			$query->where('`comment_id` = ' . $this->db->quote($comment_id));
		}

		if ($user_id !== 'all')
		{
			$query->where('`action_by` = ' . $this->db->quote($user_id));
		}

		if (JFactory::getUser()->id === 0)
		{
			$ip_address = JUComment::getIpAddress();

			$query->where('`ip_address` = ' . $this->db->quote($ip_address));

			$query = $query . ' LIMIT 1';
		}

		$this->db->setQuery($query);
		$this->db->execute($query);

		$row = $this->db->getAffectedRows();

		if ($row && $type != 'all' && $user_id !== 'all')
		{
			JUComment::import('helper', 'log');
			JUCommentLogHelper::addLog("un-" . $type, $comment_id);
		}

		return $row;
	}

	
	public function voted($commentId, $userId)
	{
		if ($commentId == 0 || $userId == 0)
		{
			return '';
		}

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('type');
		$query->from('#__jucomment_actions');
		$query->where('comment_id = ' . (int) $commentId);
		$query->where('action_by = ' . (int) $userId);
		$query->where('(type = "vote-up" OR type = "vote-down")');
		$db->setQuery($query);

		return $db->loadResult();
	}

	
	public function reported($commentId, $userId)
	{
		if ($commentId == 0 || $userId == 0)
		{
			return 0;
		}

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('COUNT(1)');
		$query->from('#__jucomment_actions');
		$query->where('comment_id = ' . (int) $commentId);
		$query->where('action_by = ' . (int) $userId);
		$query->where('type = "report"');
		$db->setQuery($query);

		return $db->loadResult();
	}
}
