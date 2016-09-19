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

class JUCommentFieldCore_modified extends JUCommentFieldCore_approved_time
{
	protected $field_name = 'modified';
	protected $filter = 'UNSET';

	
	public function storeValue($value)
	{
		$date = JFactory::getDate();
		if (!$this->is_new)
		{
			$value = $date->toSql();

			return parent::storeValue($value);
		}

		return true;
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