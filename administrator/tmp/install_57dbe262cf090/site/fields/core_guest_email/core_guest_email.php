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

class JUCommentFieldCore_guest_email extends JUCommentFieldText
{
	protected $field_name = 'guest_email';
	protected $regex = "/^([a-z0-9_\.-]+)@([\da-z\.-]+)\.([a-z\.]{2,6})$/";

	public function getPredefinedValuesHtml()
	{
		return '<span class="readonly">' . JText::_('COM_JUCOMMENT_NOT_SET') . '</span>';
	}

	public function getInput($fieldValue = null)
	{
		$value = "";
		if ($fieldValue)
		{
			$value = $fieldValue;
		}
		elseif ($this->comment && $this->comment->user_id)
		{
			$user = JFactory::getUser($this->comment->user_id);
			if ($user->id > 0)
			{
				$value = $user->email;
				$this->addAttribute('readonly', 'readonly', 'input');
				$this->addAttribute('disabled', 'disabled', 'input');
			}
		}
		elseif ($this->comment && $this->comment->guest_email)
		{
			$value = $this->comment->guest_email;
		}
		else
		{
			$user = JFactory::getUser();
			if ($user->id > 0)
			{
				$value = $user->email;
				$this->addAttribute('readonly', 'readonly', 'input');
				$this->addAttribute('disabled', 'disabled', 'input');
			}
		}

		return parent::getInput($value);
	}

	public function PHPValidate($values)
	{
		$user = JFactory::getUser();
		if ($user->guest)
		{
			$validate = parent::PHPValidate($values);
			if ($validate === true)
			{
				JUComment::import('helper', 'comment');
				if (!JUCommentCommentHelper::checkEmailOfGuest($values))
				{
					return JText::_('COM_JUCOMMENT_EMAIL_HAS_BEEN_REGISTERED');
				}
				else
				{
					return true;
				}
			}
			else
			{
				return $validate;
			}
		}
		else
		{
			return true;
		}
	}

	public function storeValue($value)
	{
		$user = JFactory::getUser();
		if ($user->guest)
		{
			return parent::storeValue($value);
		}
		else
		{
			return true;
		}
	}
}

?>