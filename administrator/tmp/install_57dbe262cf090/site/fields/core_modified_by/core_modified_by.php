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

class JUCommentFieldCore_modified_by extends JUCommentFieldCore_approved_by
{
	protected $field_name = 'modified_by';
	protected $filter = "UNSET";
	protected $fieldvalue_column = "ua1.name";

	public function getPreview()
	{
		$this->getInput(null);
	}

	public function getInput($fieldValue = null)
	{
		if (!$this->isPublished())
		{
			return "";
		}

		$value = !is_null($fieldValue) ? $fieldValue : $this->value;
		if ($value > 0)
		{
			$user = JFactory::getUser($this->value);
		}
		else
		{
			$user       = new stdClass();
			$user->id   = 0;
			$user->name = '';
		}

		$this->setAttribute("type", "text", "input");
		$this->setAttribute("readonly", "readonly", "input");

		$this->setVariable('value', $value);
		$this->setVariable('user', $user);

		return $this->fetch('input.php', __CLASS__);
	}

	public function onSearch(&$query, &$where, $search, $forceModifyQuery = false)
	{
		if ($search !== "")
		{
			
			$storeId = md5(__METHOD__ . "::" . $this->id);
			if (!isset(self::$cache[$storeId]) || $forceModifyQuery)
			{
				$query->JOIN("LEFT", "#__users AS ua1 ON comment.created_by = ua1.id");

				self::$cache[$storeId] = true;
			}

			$app = JFactory::getApplication();
			if ($app->isSite())
			{
				$db      = JFactory::getDbo();
				$where[] = $this->fieldvalue_column . " LIKE '%" . $db->escape($search, true) . "%'";
			}
			else
			{
				$where[] = "ua1.id = " . (int) $search;
			}
		}
	}

	public function onSimpleSearch(&$query, &$where, $search, $forceModifyQuery = false)
	{
		if ($search !== "")
		{
			
			$storeId = md5(__METHOD__ . "::" . $this->id);
			if (!isset(self::$cache[$storeId]) || $forceModifyQuery)
			{
				$query->JOIN("LEFT", "#__users AS ua1 ON comment.created_by = ua1.id");

				self::$cache[$storeId] = true;
			}

			$db      = JFactory::getDbo();
			$where[] = $this->fieldvalue_column . " LIKE '%" . $db->escape($search, true) . "%'";
		}
	}

	
	public function storeValue($value)
	{
		$user = JFactory::getUser();
		if (!$this->is_new)
		{
			$value = $user->id;

			return parent::storeValue($value);
		}

		return true;
	}

	public function orderingPriority(&$query = null)
	{
		$this->appendQuery($query, 'select', 'ua1.name AS modified_by_name');
		$this->appendQuery($query, 'left join', '#__users AS ua1 ON cm.modified_by = ua1.id');

		return array('ordering' => 'modified_by_name', 'direction' => $this->priority_direction);
	}
}

?>