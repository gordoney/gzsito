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

class JUCommentFieldCore_helpful_votes extends JUCommentFieldCore_total_votes
{
	protected $field_name = 'total_votes';
	protected $regex = "/^\d+$/";

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