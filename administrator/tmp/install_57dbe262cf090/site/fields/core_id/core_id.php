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

class JUCommentFieldCore_id extends JUCommentFieldBase
{
	protected $field_name = 'id';

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

		$this->setAttribute("type", "hidden", "input");

		$value = !is_null($fieldValue) ? $fieldValue : ($this->value ? $this->value : 0);

		if ((int) $this->params->get("size", 32))
		{
			$this->setAttribute("size", (int) $this->params->get("size", 32), "input");
		}

		if ($this->params->get("placeholder", ""))
		{
			$placeholder = htmlspecialchars($this->params->get("placeholder", ""), ENT_COMPAT, 'UTF-8');
			$this->setAttribute("placeholder", $placeholder, "input");
		}

		$this->addAttribute("class", "readonly", "input");
		$this->setAttribute("readonly", "readonly", "input");

		$this->setVariable('value', $value);

		return $this->fetch('input.php', __CLASS__);
	}

	public function onSearch(&$query, &$where, $search, $forceModifyQuery = false)
	{
		if ($search !== "")
		{
			$where[] = "comment.id = " . (int) $search;
		}
	}

	
	public function canSubmit($userID = null)
	{
		return true;
	}

	
	public function canEdit($userID = null)
	{
		return true;
	}
}

?>