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

class JUCommentFieldCore_guest_name extends JUCommentFieldText
{
	protected $field_name = 'guest_name';
	protected $fieldvalue_column = 'comment.guest_name';

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
				$value = $user->username;
				$this->addAttribute('readonly', 'readonly', 'input');
				$this->addAttribute('disabled', 'disabled', 'input');
			}
		}
		elseif ($this->comment && $this->comment->guest_name)
		{
			$value = $this->comment->guest_name;
		}
		else
		{
			$user = JFactory::getUser();
			if ($user->id > 0)
			{
				$value = $user->username;
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
				if (!JUCommentCommentHelper::checkNameOfGuest($values))
				{
					return JText::sprintf('COM_JUCOMMENT_NAME_X_HAS_BEEN_BANNED', $values);
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