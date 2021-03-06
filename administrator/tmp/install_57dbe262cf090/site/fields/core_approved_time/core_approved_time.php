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

class JUCommentFieldCore_approved_time extends JUCommentFieldDateTime
{
	protected $field_name = 'approved_time';
	protected $filter = 'UNSET';

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

		$this->setAttribute("type", "text", "input");
		$this->addAttribute("class", "readonly", "input");

		if ((int) $this->params->get("size", 32))
		{
			$this->setAttribute("size", (int) $this->params->get("size", 32), "input");
		}
		$this->setAttribute("readonly", "readonly", "input");

		$value = !is_null($fieldValue) ? $fieldValue : $this->value;

		$this->setVariable('value', $value);

		return $this->fetch('input.php', __CLASS__);
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
}

?>