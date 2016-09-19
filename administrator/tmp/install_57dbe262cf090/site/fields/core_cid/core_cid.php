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

class JUCommentFieldCore_cid extends JUCommentFieldBase
{
	protected $field_name = 'cid';
	protected $regex = "/^\d+$/";

	public function __construct($field = null, $comment = null)
	{
		parent::__construct($field, $comment);
		$this->required = true;
	}

	public function getPredefinedValuesHtml()
	{
		return '<span class="readonly">' . JText::_('COM_JUCOMMENT_NOT_SET') . '</span>';
	}

	public function getPreview()
	{
		$this->getInput(null);
	}

	public function getInput($fieldValue = null)
	{
		$app = JFactory::getApplication();
		if ($app->isSite())
		{
			$value = !is_null($fieldValue) ? $fieldValue : ($this->value ? $this->value : 0);

			$this->setVariable('value', $value);

			return $this->fetch('input.php', __CLASS__);
		}
		else
		{
			return parent::getInput($fieldValue);
		}
	}

	public function getBackendOutput()
	{
		$JUCMApplication = JUComment::loadApplication($this->comment->component, $this->comment->section);
		$JUCMApplication = $JUCMApplication->load($this->comment->cid);

		
		echo '<a target="_blank" href="' . $JUCMApplication->getContentPermalink() . '" title="View ' . $JUCMApplication->getContentTitle() . '">' . $JUCMApplication->getContentTitle() . '</a>';
	}

	public function getOutput($options = array())
	{
		if ($this->comment)
		{
			if (!isset($this->comment->processed) || !$this->comment->processed)
			{
				JUComment::import('helper', 'comment');
				$comment = JUCommentCommentHelper::process($this->comment);
			}
			else
			{
				$comment = $this->comment;
			}

			$this->setVariable('comment', $comment);

			return $this->fetch('output.php', __CLASS__);
		}
	}

	
	public function canSubmit($userID = null)
	{
		return true;
	}

	
	public function canEdit($userID = null)
	{
		return true;
	}
}

?>