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

class JUCommentFieldCore_website extends JUCommentFieldBase
{
	protected $field_name = 'website';

	public function __construct($field = null, $comment = null)
	{
		parent::__construct($field, $comment);
		$this->params->set('rule', 'pattern');
	}

	public function getOutput($option = array())
	{
		return $this->value;
	}
}

?>