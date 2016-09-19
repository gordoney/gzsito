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

class JUCommentFieldCore_ip_address extends JUCommentFieldBase
{
	protected $field_name = 'ip_address';

	
	public function getBackendOutput()
	{
		if ($this->value)
		{
			return "<a href='http://whois.domaintools.com/" . $this->value . "' target='_blank'>" . $this->value . "</a>";
		}

		return '';
	}

	public function canSubmit($userID = null)
	{
		return false;
	}

	public function canEdit($userID = null)
	{
		return false;
	}
}

?>