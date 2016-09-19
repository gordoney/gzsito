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

class JUCommentFieldCore_parent_id extends JUCommentFieldText
{
	protected $field_name = 'parent_id';

	public function __construct($field = null, $comment = null)
	{
		parent::__construct($field, $comment);
		$this->required = true;
	}

	public function getPredefinedValuesHtml()
	{
		return '<span class="readonly">' . JText::_('COM_JUCOMMENT_NOT_SET') . '</span>';
	}

	public function getInput($fieldValue = null)
	{
		$app = JFactory::getApplication();
		if ($app->isSite())
		{
			$value = !is_null($fieldValue) ? $fieldValue : ($this->value ? $this->value : 0);

			return '<input type="hidden" name="' . $this->getName() . '" id="' . $this->getId() . '" value="' . $value . '" />';
		}
		else
		{
			JHtml::_('behavior.modal', 'a.modal');

			
			$script = 'function jSelectComment_' . $this->getId() . '(id, title, level) {
			        	if(id != document.id("' . $this->getId() . '").value){
							document.id("' . $this->getId() . '").value = id;
		        			document.id("' . $this->getId() . '_name").value = title;
		        			level = parseInt(level) + 1
		        			document.id("jform_level").value = level;
		        		}
				        SqueezeBox.close();
					}';

			
			JFactory::getDocument()->addScriptDeclaration($script);

			
			$html = array();

			
			$html  = array();
			$value = !is_null($fieldValue) ? $fieldValue : $this->value;
			$link  = 'index.php?option=com_jucomment&amp;view=comments&amp;layout=modal&amp;tmpl=component&amp;function=jSelectComment_' . $this->getId();

			$title = '';
			if ($value)
			{
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->select('title');
				$query->from('#__jucomment_comments');
				$query->where('id = ' . (int) $value);
				$db->setQuery($query);
				$title = $db->loadResult();
			}

			if (!$title)
			{
				$rootComment = JUCommentCommentHelper::getRootComment();
				$title       = $rootComment->title;
				$value       = $rootComment->id;
			}

			$title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

			
			$html[] = '<div class="input-append">';
			$html[] = '<input type="text" id="' . $this->getId() . '_name" value="' . $title . '" disabled="disabled" size="35" />';
			$html[] = '<a onclick="commentURL();" id="commenturl" class="btn modal" title="' . JText::_('COM_JUCOMMENT_SELECT_COMMENT')
				. '"  href="' . $link . '&amp;' . JSession::getFormToken() . '=1" rel="{handler: \'iframe\', size: {x: 800, y: 450}}"><i class="icon-list"></i> ' . JText::_('COM_JUCOMMENT_SELECT_COMMENT') . '</a>';
			$html[] = '</div>';

			
			$class = '';
			if ($this->isRequired())
			{
				$class = ' class="required modal-value"';
			}

			$html[] = '<input type="hidden" id="' . $this->getId() . '"' . $class . ' name="' . $this->getName() . '" value="' . $value . '" />';

			return implode("\n", $html);
		}
	}

	
	public function getBackendOutput()
	{
		if ($this->value)
		{
			$comment = JUCommentHelper::getCommentById($this->value);
			if ($comment)
			{
				return $comment->title;
			}
		}

		return '';
	}

	public function PHPValidate($values)
	{
		
		if ($values === "")
		{
			return JText::_('COM_JUCOMMENT_PARENT_ID_MUST_NOT_BE_EMPTY');
		}

		return true;
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