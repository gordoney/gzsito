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

class JUCommentFieldCore_total_votes extends JUCommentFieldBase
{
	protected $field_name = 'total_votes';
	protected $regex = "/^\d+$/";

	public function getPredefinedValuesHtml()
	{
		return '<span class="readonly">' . JText::_('COM_JUCOMMENT_NOT_SET') . '</span>';
	}

	public function getInput($fieldValue = null)
	{
		if (!$this->isPublished())
		{
			return "";
		}

		$this->setAttribute("type", "text", "input");
		$value = !is_null($fieldValue) ? $fieldValue : $this->value;
		$value = htmlspecialchars($value, ENT_COMPAT, 'UTF-8');
		$this->setAttribute("value", $value, "input");

		if ((int) $this->params->get("size", 32))
		{
			$this->setAttribute("size", (int) $this->params->get("size", 32), "input");
		}

		if ($this->params->get("placeholder", ""))
		{
			$placeholder = htmlspecialchars($this->params->get("placeholder", ""), ENT_COMPAT, 'UTF-8');
			$this->setAttribute("placeholder", $placeholder, "input");
		}

		$app = JFactory::getApplication();
		if ($app->isSite())
		{
			$this->setAttribute("readonly", "readonly", "input");
		}

		$html = "<input id=\"" . $this->getId() . "\" name=\"" . $this->getName() . "\" " . $this->getAttribute(null, null, "input") . " />";

		return $html;
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
}

?>