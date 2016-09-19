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

class JUCommentFieldCore_section extends JUCommentFieldText
{
	protected $field_name = 'section';

	public function getPredefinedValuesHtml()
	{
		return '<span class="readonly">' . JText::_('COM_JUCOMMENT_NOT_SET') . '</span>';
	}

	
	public function getBackendOutput()
	{
		return $this->value;
	}

	public function getInput($fieldValue = null)
	{
		$app = JFactory::getApplication();
		if ($app->isSite())
		{
			$value = !is_null($fieldValue) ? $fieldValue : ($this->value ? $this->value : '');

			return '<input type="hidden" name="' . $this->getName() . '" id="' . $this->getId() . '" value="' . $value . '" />';
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