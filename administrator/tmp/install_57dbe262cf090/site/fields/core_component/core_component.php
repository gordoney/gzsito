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

class JUCommentFieldCore_component extends JUCommentFieldText
{
	protected $field_name = 'component';

	public function __construct($field = null, $comment = null)
	{
		parent::__construct($field, $comment);
		$this->required = true;
	}

	public function getPredefinedValuesHtml()
	{
		return '<span class="readonly">' . JText::_('COM_JUCOMMENT_NOT_SET') . '</span>';
	}

	public function getOutput($options = array())
	{
		if (!$this->isPublished())
		{
			return "";
		}

		if (!$this->value)
		{
			return "";
		}

		$this->setVariable('value', $this->value);

		return $this->fetch('input.php', __CLASS__);
	}

	
	public function getBackendOutput()
	{
		return $this->value;
	}

	public function getPreview()
	{
		$this->getInput(null);
	}

	public function getInput($fieldValue = null)
	{
		$app = JFactory::getApplication();
		if ($app->isSite())
		{
			$value = !is_null($fieldValue) ? $fieldValue : ($this->value ? $this->value : '');
			$this->setVariable('value', $value);

			return $this->fetch('output.php', __CLASS__);
		}
		else
		{
			return parent::getInput($fieldValue);
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