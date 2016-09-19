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

jimport('joomla.html.html');
JHtml::addIncludePath(JPATH_SITE . '/administrator/components/com_jucomment/helpers/html');

class JUCommentFieldDateTime extends JUCommentFieldBase
{
	public function __construct($field = null, $comment = null)
	{
		parent::__construct($field, $comment);
		
		$this->params->set('rule', 'date');
	}

	public function getDefaultPredefinedValues()
	{
		$app   = JFactory::getApplication();
		$value = $this->getPredefinedValues();

		
		if ($app->input->get('view') != 'field' && strtoupper(trim($value)) == "NOW")
		{
			$date  = JFactory::getDate();
			$value = $date->toSql();
		}

		return $value;
	}

	public function parseValue($value)
	{
		if (!$this->isPublished())
		{
			return null;
		}

		$config = JFactory::getConfig();
		$user   = JFactory::getUser();
		
		$filter = strtoupper((string) $this->getFilter());
		switch ($filter)
		{
			case 'SERVER_UTC':
				
				if (intval($value))
				{
					
					$date = JFactory::getDate($value, 'UTC');
					$date->setTimezone(new DateTimeZone($config->get('offset')));

					
					$value = $date->format('Y-m-d H:i:s', true, false);
				}
				break;

			case 'USER_UTC':
				
				if (intval($value))
				{
					
					$date = JFactory::getDate($value, 'UTC');
					$date->setTimezone(new DateTimeZone($user->getParam('timezone', $config->get('offset'))));

					
					$value = $date->format('Y-m-d H:i:s', true, false);
				}
				break;
		}

		return $value;
	}

	public function getPreview()
	{
		$this->setAttribute("type", "checkbox", "input");

		$value   = (array) $this->value;
		$options = $this->getPredefinedValues();
		$this->setVariable('value', $value);
		$this->setVariable('options', $options);

		return $this->fetch('preview.php', __CLASS__);
	}

	public function getInput($fieldValue = null)
	{
		if (!$this->isPublished())
		{
			return "";
		}

		$value  = !is_null($fieldValue) ? $fieldValue : $this->value;
		$format = $this->params->get('format', 'm/d/Y H:i:s');

		$this->setVariable('value', $value);
		$this->setVariable('format', $format);

		$this->setupCalender();

		$this->registerTriggerForm();

		return $this->fetch('input.php', __CLASS__);
	}

	public function getSearchInput($defaultValue = "")
	{
		if (!$this->isPublished())
		{
			return "";
		}

		
		if ((int) $this->params->get("size", 32))
		{
			$this->setAttribute("size", (int) $this->params->get("size", 32), "search");
		}

		$this->setAttribute("class", "input-medium", "search");

		$this->setVariable('value', $defaultValue);

		return $this->fetch('searchinput.php', __CLASS__);
	}

	public function getOutput($options = array())
	{
		if (!$this->isPublished())
		{
			return "";
		}

		if (intval($this->value) == 0)
		{
			return $this->value;
		}

		$format = $this->params->get('output_format', '');

		if ($format == "custom")
		{
			$format = $this->params->get('custom_dateformat', '') ? $this->params->get('custom_dateformat', '') : '';
		}

		if ($format == '')
		{
			$format = $this->params->get('format', 'm/d/Y H:i:s');
		}

		$this->setVariable('value', $this->value);
		$this->setVariable('format', $format);

		return $this->fetch('output.php', __CLASS__);
	}

	public function loadDefaultAssets($loadJS = true, $loadCSS = true)
	{
		static $loaded = array();

		if ($this->folder && !isset($loaded[$this->folder]))
		{
			$format = $this->params->get('format', 'm/d/Y H:i:s');
			$this->loadDatetimePicker();
			$document = JFactory::getDocument();
			$script   = "
					jQuery(document).ready(function($){
						$('#jform_predefined_values').datetimepicker({
							format:'" . $format . "',
							timepicker: false,
							closeOnDateSelect: true
						});

						$('#jform_predefined_values_show').click(function(){
							$('#jform_predefined_values').datetimepicker('show');
						});
					});
				";

			$document->addScriptDeclaration($script);

			$loaded[$this->folder] = true;
		}
	}

	public function getPredefinedValuesHtml()
	{
		$this->loadDefaultAssets();

		$predefined_value = $this->getPredefinedValues(1);
		$predefined_value = @htmlspecialchars($predefined_value, ENT_COMPAT, 'UTF-8');

		$format = $this->params->get('format', 'm/d/Y H:i:s');

		$html = '<div class="input-append">';
		$html .= '<input type="text" title="' . (0 !== (int) $predefined_value ? JHtml::_('date', $predefined_value, null, null) : '') . '" name="jform[predefined_values]" id="jform_predefined_values" value="' . (0 !== (int) $predefined_value ? JHtml::_('date', $predefined_value, $format, null) : '') . '" />';
		$html .= '<span class="add-on icon-calendar fa fa-calendar" id="jform_predefined_values_show"></span>';
		$html .= '</div>';

		return $html;
	}

	public function onSearch(&$query, &$where, $search, $forceModifyQuery = false)
	{
		if (is_array($search) && !empty($search))
		{
			
			$storeId = md5(__METHOD__ . "::" . $this->id);
			if (!isset(self::$cache[$storeId]) || $forceModifyQuery)
			{
				$query->join('LEFT', '#__jucomment_fields_values AS field_values_' . $this->id . ' ON (comment.id = field_values_' . $this->id . '.comment_id AND field_values_' . $this->id . '.field_id = ' . $this->id . ')');

				self::$cache[$storeId] = true;
			}

			$db = JFactory::getDbo();
			if ($search['from'] !== "" && $search['to'] !== "")
			{
				
				if (!strtotime($search['from']) || !strtotime($search['to']))
				{
					return;
				}

				$from = $db->quote($search['from']);
				$to   = $db->quote($search['to']);
				if ($from > $to)
				{
					$this->swap($from, $to);
				}

				$where[] = $this->fieldvalue_column . " BETWEEN $from AND $to";
			}
			elseif ($search['from'] !== "")
			{
				
				if (!strtotime($search['from']))
				{
					return;
				}

				$from = $db->quote($search['from']);

				$where[] = $this->fieldvalue_column . " >= $from";
			}
			elseif ($search['to'] !== "")
			{
				
				if (!strtotime($search['to']))
				{
					return;
				}

				$to = $db->quote($search['to']);

				$where[] = $this->fieldvalue_column . " <= $to";
			}
		}
	}

	public function onSimpleSearch(&$query, &$where, $search, $forceModifyQuery = false)
	{
		$search = strtotime($search);
		if ($search && $search !== '')
		{
			
			$storeId = md5(__METHOD__ . "::" . $this->id);
			if (!isset(self::$cache[$storeId]) || $forceModifyQuery)
			{
				$query->join('LEFT', '#__jucomment_fields_values AS field_values_' . $this->id . ' ON (comment.id = field_values_' . $this->id . '.comment_id AND field_values_' . $this->id . '.field_id = ' . $this->id . ')');

				self::$cache[$storeId] = true;
			}

			$search  = date('Y-m-d H:i:s', $search);
			$db      = JFactory::getDbo();
			$where[] = "(" . $this->fieldvalue_column . " = " . $db->quote($search) . ")";
		}
	}

	protected function setupCalender()
	{
		static $done;

		if ($done === null)
		{
			$done = array();
		}

		$id      = $this->getId();
		$attribs = $this->getAttribute(null, null, "input", "array");

		$readonly = isset($attribs['readonly']) && $attribs['readonly'] == 'readonly';
		$disabled = isset($attribs['disabled']) && $attribs['disabled'] == 'disabled';
		if (!$readonly && !$disabled)
		{
			
			if (!in_array($id, $done))
			{
				$this->loadDatetimePicker();
				
				$format   = $this->params->get('format', 'm/d/Y H:i:s');
				$document = JFactory::getDocument();
				$script   = "
					jQuery(document).ready(function($){
						$('#" . $this->getId() . "').datetimepicker({
							format:'" . $format . "',
							timepicker: false,
							closeOnDateSelect: true,
							mask: true,
							onChangeDateTime : function(current_time, input){
								input.valid();
							}
						});

						$('#" . $this->getId() . "_show').click(function(){
							$('#" . $this->getId() . "').datetimepicker('show');
						});
					});
				";
				$document->addScriptDeclaration($script);
				$done[] = $id;
			}
		}
	}

	protected function loadDatetimePicker()
	{
		static $loaded = false;

		if (!$loaded)
		{
			$document = JFactory::getDocument();
			$document->addScript(JUri::root(true) . '/components/com_jucomment/assets/js/jquery.datetimepicker.js');
			$document->addStyleSheet(JUri::root(true) . '/components/com_jucomment/assets/css/jquery.datetimepicker.css');
			$loaded = true;
		}
	}

	
	public function PHPValidate($values)
	{
		$valid = parent::PHPValidate($values);
		if ($valid !== true)
		{
			return $valid;
		}

		if ($this->params->get('rule') == 'date')
		{
			$format = $this->params->get('format', 'm/d/Y H:i:s');
			if (DateTime::createFromFormat($format, $values) !== false)
			{
				return true;
			}
			else
			{
				return $this->getValidateMessage('date');
			}
		}

		return true;
	}
}

?>