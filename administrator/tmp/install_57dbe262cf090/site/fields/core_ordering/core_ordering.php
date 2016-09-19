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

class JUCommentFieldCore_ordering extends JUCommentFieldBase
{
	protected $field_name = 'lft';

	public function getPredefinedValuesHtml()
	{
		return '<span class="readonly">' . JText::_('COM_JUCOMMENT_NOT_SET') . '</span>';
	}

	public function getOutput($options = array())
	{
		return "";
	}

	
	public function getBackendOutput()
	{
		return '';
	}

	public function PHPValidate($values)
	{
		return true;
	}

	
	public function storeValue($value)
	{
		return true;
	}

	public function canSubmit($userID = null)
	{
		return false;
	}

}

?>