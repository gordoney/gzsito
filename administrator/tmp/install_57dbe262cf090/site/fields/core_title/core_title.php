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

class JUCommentFieldCore_title extends JUCommentFieldText
{
	protected $field_name = 'title';

	public function __construct($field = null, $comment = null)
	{
		parent::__construct($field, $comment);
		$this->required = true;
	}

	public function getPredefinedValuesHtml()
	{
		return '<span class="readonly">' . JText::_('COM_JUCOMMENT_NOT_SET') . '</span>';
	}

	public function getOutput($options = array())
	{
		if (!$this->isPublished())
		{
			return "";
		}

		if (!$this->value)
		{
			return "";
		}

		$title = $this->value;

		if ($this->params->get('max_length_list_view', 0) > 0)
		{
			$title = substr($title, 0, $this->params->get('max_length_list_view', 0));
			if (strlen($title) < strlen($this->value))
			{
				$title .= "...";
			}
		}
		
		$html = '<a href="' . JRoute::_('#comment-box-' . $this->comment_id) . '">' . $title . '</a>';

		return $html;
	}

	
	public function getBackendOutput()
	{
		return '';
	}

	
	public function canSubmit($userID = null)
	{
		return true;
	}
}

?>