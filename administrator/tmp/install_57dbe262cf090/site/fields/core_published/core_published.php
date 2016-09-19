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

class JUCommentFieldCore_published extends JUCommentFieldRadio
{
	protected $field_name = 'published';

	public function onSave($data)
	{
		return $data;
	}

	public function getDefaultPredefinedValues()
	{
		return $this->getPredefinedValues();
	}

	public function getPredefinedValuesHtml()
	{
		$obj        = new stdClass();
		$obj->value = 0;
		$obj->text  = JText::_("JNO");
		$options[]  = $obj;
		$obj        = new stdClass();
		$obj->value = 1;
		$obj->text  = JText::_("JYES");
		$options[]  = $obj;

		$this->addAttribute("class", "radio btn-group", "input");

		$html = "";
		$html .= "<fieldset id=\"" . $this->getId() . "\" " . $this->getAttribute(null, null, "input") . ">";

		if ($options)
		{
			$predefined_value = $this->getPredefinedValues(1);

			foreach ($options AS $key => $option)
			{
				if ($option->text == strtoupper($option->text))
				{
					$text = JText::_($option->text);
				}
				else
				{
					$text = $option->text;
				}
				$text = htmlspecialchars($text, ENT_COMPAT, 'UTF-8');

				$value   = htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8');
				$checked = $option->value == $predefined_value ? 'checked="checked"' : '';
				$html .= "<input id=\"" . $this->getId() . $key . "\" type=\"radio\" name=\"jform[predefined_values]\" value=\"$value\" $checked /> <label for=\"" . $this->getId() . $key . "\">$text</label>";
			}
		}
		$html .= "</fieldset>";

		return $html;
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

	public function getSearchInput($defaultValue = "")
	{
		if (!$this->isPublished())
		{
			return "";
		}

		$options    = array();
		$obj        = new stdClass();
		$obj->value = "";
		$obj->text  = JText::_("COM_JUCOMMENT_SELECT_OPTION");
		$options[]  = $obj;
		$obj        = new stdClass();
		$obj->value = 1;
		$obj->text  = JText::_("JYES");
		$options[]  = $obj;
		$obj        = new stdClass();
		$obj->value = 0;
		$obj->text  = JText::_("JNO");
		$options[]  = $obj;

		$this->setVariable('value', $defaultValue);
		$this->setVariable('options', $options);

		return $this->fetch('searchinput.php', __CLASS__);
	}

	public function onSearch(&$query, &$where, $search, $forceModifyQuery = false)
	{
		if ($search !== "")
		{
			$where[] = $this->fieldvalue_column . " = " . (int) $search;
		}
	}

	public function getOutput($options = array())
	{
		if (!$this->isPublished())
		{
			return "";
		}

		$this->setVariable('value', $this->value);

		return $this->fetch('output.php', __CLASS__);
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

			
			if ($this->comment_id && is_object($this->comment) && $this->comment->approved <= 0)
			{
				return false;
			}

			
			return parent::canSubmit();
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
			
			if ($this->comment_id && is_object($this->comment) && $this->comment->approved <= 0)
			{
				return false;
			}

			return parent::canEdit($userID);
		}
	}

	public function storeValue($value)
	{
		$app = JFactory::getApplication();
		
		if ($app->isSite())
		{
			return false;
		}
		else
		{

			$approveOption      = $app->input->post->get("approval_option");
			$approveOptionArray = array("ignore", "approve", "delete");
			if (in_array($approveOption, $approveOptionArray))
			{
				return true;
			}

			return parent::storeValue($value);
		}
	}

}

?>