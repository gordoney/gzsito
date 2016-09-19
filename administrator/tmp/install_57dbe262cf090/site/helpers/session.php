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

defined('_JEXEC') or die('Restricted access');


class JUCommentSessionHelper
{
	var $session;

	function __construct()
	{
		$this->session = JFactory::getSession();
		return $this->session;
	}

	function getLastReplyTime()
	{
		return unserialize( $this->session->get('jucommento_last_reply') );
	}

	function setReplyTime()
	{
		return $this->session->set('jucommento_last_reply', serialize( JFactory::getDate()->toUnix() ) );
	}
}
