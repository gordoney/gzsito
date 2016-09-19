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

class JUCommentFieldCore_approved extends JUCommentFieldCore_published
{
	
	protected $field_name = 'approved';

	
	public function getBackendOutput($details = true)
	{
		return JHtml::_('grid.boolean', $this->comment_id, $this->value);
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

		$options    = array();
		$obj        = new stdClass();
		$obj->value = 1;
		$obj->text  = JText::_("JYES");
		$options[]  = $obj;
		$obj        = new stdClass();
		$obj->value = 0;
		$obj->text  = JText::_("JNO");
		$options[]  = $obj;

		$this->setAttribute("type", "radio", "input");

		$this->setVariable('value', $value);
		$this->setVariable('options', $options);

		return $this->fetch('input.php', __CLASS__);
	}

	
	public function canSubmit($userID = null)
	{
		return false;
	}

	
	public function canEdit($userID = null)
	{
		return false;
	}

	
	public function storeValue($value)
	{
		return true;
	}

}

?>