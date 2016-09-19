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

class JUCommentFieldCore_fieldgroup_id extends JUCommentFieldBase
{
	protected $field_name = 'fieldgroup_id';

	public function getPredefinedValuesHtml()
	{
		$options = $this->getOptions();

		return JHtml::_("select.genericlist", $options, "jform[predefined_values]", null, 'value', 'text', $this->value, $this->getId());
	}

	public function getInput($fieldValue = null)
	{
		if (!$this->isPublished())
		{
			return "";
		}

		$value = !is_null($fieldValue) ? $fieldValue : $this->value;
		$app   = JFactory::getApplication();

		if ($app->isAdmin())
		{
			$script = '
				jQuery(document).ready(function($){
					var changeFieldgroup = function(fieldgroup_id){
						$("#field-list > li[id!=\'fieldgroup-" + fieldgroup_id + "\']").hide().find("input, textarea").attr("disabled", true);
						$("#field-list > li[id=\'fieldgroup-" + fieldgroup_id + "\']").show().find("input, textarea").attr("disabled", false);
					}

					$("#' . $this->getId() . '").change(function(){
						var old_fieldgroup_id = "' . $this->value . '";
						var fieldgroup_id = $(this).val();
						if(old_fieldgroup_id && old_fieldgroup_id != fieldgroup_id){
							alert("' . JText::_('Warrning when change fieldgroup value') . '");
						}
						changeFieldgroup(fieldgroup_id);
					});

					changeFieldgroup("' . $this->value . '");
				});
			';

			JFactory::getDocument()->addScriptDeclaration($script);

			$options = $this->getOptions();

			return JHtml::_("select.genericlist", $options, $this->getName(), $this->getAttribute(null, null, "input"), 'value', 'text', $value, $this->getId());
		}
		else
		{
			return '<input type="hidden" name="' . $this->getName() . '" id="' . $this->getId() . '" value="' . $value . '" />';
		}

	}

	public function getSearchInput($defaultValue = "")
	{
		if (!$this->isPublished())
		{
			return "";
		}

		$options = $this->getOptions();

		return JHtml::_("select.genericlist", $options, $this->getName(), $this->getAttribute(null, null, "search"), 'value', 'text', $defaultValue, $this->getId());
	}

	public function onSearch(&$query, &$where, $search, $forceModifyQuery = false)
	{
		if ($search !== "")
		{
			$db  = JFactory::getDbo();
			$app = JFactory::getApplication();
			if ($app->isSite())
			{
				$tag     = JFactory::getLanguage()->getTag();
				$where[] = $this->fieldvalue_column . ' IN (' . $db->quote($search) . ',' . $db->quote($tag) . ',' . $db->quote('*') . ',"")';
			}
			else
			{
				$where[] = $this->fieldvalue_column . ' IN (' . $db->quote($search) . ',' . $db->quote('*') . ',"")';
			}
		}
	}

	public function getBackendOutput()
	{
		return $this->getOutput();
	}

	public function getOutput($options = array())
	{
		if (!$this->isPublished())
		{
			return "";
		}

		return $this->value;
	}

	protected function getOptions()
	{
		$options = JUCommentHelper::getFieldGroupOptions();
		array_unshift($options, JHtml::_('select.option', '', JText::_('COM_JUCOMMENT_SELECT_GROUP')));

		return $options;
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