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

class JUCommentFieldCore_user_id extends JUCommentFieldBase
{
	protected $field_name = 'user_id';
	protected $fieldvalue_column = 'ua.name';

	public function getPredefinedValuesHtml()
	{
		return '<span class="readonly">' . JText::_('COM_JUCOMMENT_NOT_SET') . '</span>';
	}

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
			$user     = new stdClass();
			$user->id = 0;
			$app      = JFactory::getApplication();
			if ($app->isAdmin())
			{
				$user->name = JText::_('COM_JUCOMMENT_SELECT_USER');
			}
			else
			{
				$user->name = '';
			}
		}

		$this->setAttribute("disabled", "disabled", "input");

		$app = JFactory::getApplication();
		if ($app->isAdmin())
		{
			return JHtml::_('jucommentadministrator.user', $user, $this->getName(), $this->getId(), $this->getAttribute(null, null, "input"));
		}
		else
		{
			$this->setAttribute("type", "text", "input");

			$this->setVariable('user', $user);

			return $this->fetch('input.php', __CLASS__);
		}
	}

	public function getSearchInput($defaultValue = "")
	{
		if (!$this->isPublished())
		{
			return "";
		}

		$this->setAttribute("type", "text", "search");

		$app = JFactory::getApplication();
		if ($app->isSite())
		{
			$this->setVariable('value', $defaultValue);

			return $this->fetch('searchinput.php', __CLASS__);
		}
		else
		{
			JHtml::_('behavior.modal');
			$document = JFactory::getDocument();
			$script   = '
				function jSelectUser_' . $this->getId() . '(id, title) {
					var old_id = document.getElementById("' . $this->getId() . '").value;
					if (old_id != id) {
						document.getElementById("' . $this->getId() . '").value = id;
						document.getElementById("' . $this->getId() . '_name").value = title;
					}
					SqueezeBox.close();
				}';
			$document->addScriptDeclaration($script);

			$user       = new stdClass();
			$user->id   = "";
			$user->name = JText::_('COM_JUCOMMENT_SELECT_USER');

			$this->setAttribute("disabled", "disabled", "search");
			$this->setAttribute("value", $user->name, "search");

			return JHtml::_('jucommentadministrator.user', $user, $this->getName(), $this->getId(), $this->getAttribute(null, null, "input"));
		}
	}

	public function onSearch(&$query, &$where, $search, $forceModifyQuery = false)
	{
		if ($search !== "")
		{
			
			$storeId = md5(__METHOD__ . "::" . $this->id);
			if (!isset(self::$cache[$storeId]) || $forceModifyQuery)
			{
				$query->JOIN('LEFT', '#__users AS ua ON ua.id = comment.created_by');

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
				$where[] = "ua.id = " . (int) $search;
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
				$query->JOIN('LEFT', '#__users AS ua ON ua.id = comment.created_by');

				self::$cache[$storeId] = true;
			}

			$db      = JFactory::getDbo();
			$where[] = $this->fieldvalue_column . " LIKE '%" . $db->escape($search, true) . "%'";
		}
	}

	public function getBackendOutput()
	{
		$user = JFactory::getUser($this->value);
		if ($user)
		{
			if (!$user->get('guest'))
			{
				return $user->name;
			}
			else
			{
				return JText::_('COM_JUCOMMENT_GUEST');
			}
		}
		else
		{
			return JText::_("COM_JUCOMMENT_UNDEFINED");
		}
	}

	public function getOutput($options = array())
	{
		if (!$this->isPublished())
		{
			return "";
		}

		$username = "";
		if ($this->value)
		{
			$user = JFactory::getUser($this->value);
			if ($user)
			{
				if ($user->id)
				{
					$username = $user->name;
				}
				else
				{
					$username = JText::_('COM_JUCOMMENT_GUEST');
				}
			}
			else
			{
				$username = JText::_("COM_JUCOMMENT_UNDEFINED");
			}
		}

		$this->setVariable('username', $username);

		return $this->fetch('output.php', __CLASS__);
	}

	
	public function storeValue($value)
	{
		$app = JFactory::getApplication();

		if ($app->isAdmin())
		{
			if ($this->is_new)
			{
				
				if (!$value)
				{
					$user  = JFactory::getUser();
					$value = $user->id;
				}
			}

			return parent::storeValue($value);
		}
		else
		{
			return true;
		}
	}

	
	public function canSubmit($userID = null)
	{
		$app = JFactory::getApplication();
		if ($app->isSite())
		{
			return false;
		}
		else
		{
			return parent::canSubmit($userID);
		}
	}

	
	public function canEdit($userID = null)
	{
		$app = JFactory::getApplication();
		if ($app->isSite())
		{
			return false;
		}
		else
		{
			return parent::canEdit($userID);
		}
	}

	public function orderingPriority(&$query = null)
	{
		$this->appendQuery($query, 'select', 'ua.name AS created_by_name');
		$this->appendQuery($query, 'left join', '#__users AS ua ON cm.created_by = ua.id');

		return array('ordering' => 'created_by_name', 'direction' => $this->priority_direction);
	}

	public function canView($options = array())
	{
		$params           = JUComment::getParams();
		$show_empty_field = $params->get('show_empty_field', 0);
		if ($this->value == 0 && !$show_empty_field)
		{
			return false;
		}
		else
		{
			return parent::canView($options);
		}
	}
}

?>