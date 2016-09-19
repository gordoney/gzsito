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

class JUCommentFieldBase
{
	
	protected $comment = null;
	
	protected $id = null;
	
	protected $field = null;
	
	
	protected $params = null;
	
	
	protected $output_attributes = null;
	protected $input_attributes = null;
	protected $search_attributes = null;
	protected $label_attributes = null;
	
	protected $filter = null;
	
	protected $errors = array();
	
	protected $fieldvalue_column = null;
	
	protected $fields_data = array();
	
	protected $is_new = null;
	
	protected $name = null;
	
	protected $id_suffix = null;
	
	
	protected static $cache = array();
	
	public $vars = array();

	
	public function __construct($field = null, $comment = null)
	{
		
		if (is_null($field))
		{
			$field = $this->field_name;
		}

		if (is_object($field))
		{
			
			JUCommentFieldHelper::getFieldById($field->id, $field);
		}
		else
		{
			
			$field = JUCommentFieldHelper::getFieldById($field);
		}

		if (!is_object($field))
		{
			
			return false;
		}

		$this->id = $field->id;

		$this->params = new JRegistry($field->params);

		
		if (is_null($this->fieldvalue_column))
		{
			
			if ($this->isCore())
			{
				$this->fieldvalue_column = "comment." . $this->field_name;
			}
			else
			{
				$this->fieldvalue_column = "field_values_" . $this->id . ".value";
			}
		}

		
		if (!$this->isCore())
		{
			$this->loadLanguage($this->folder);
		}

		
		

		$this->loadComment($comment);

		$this->name = "fields[" . $this->id . "]";

		if ($this->params->get('auto_suggest', 0))
		{
			$app = JFactory::getApplication();
			if (($app->isAdmin() && ($app->input->get('view', '') == 'comment' || $app->input->get('view', '') == 'field')) || ($app->isSite() && $app->input->get('view', '') == 'form'))
			{
				$document = JFactory::getComment();
				$document->addStyleSheet(JUri::root() . "components/com_jucomment/assets/css/typeahead.css");

				JUCommentFrontHelper::loadjQuery();
				$document->addScript(JUri::root(true) . "/components/com_jucomment/assets/js/handlebars.min.js");
				$document->addScript(JUri::root(true) . "/components/com_jucomment/assets/js/typeahead.bundle.min.js");
				$document->addScript(JUri::root(true) . "/components/com_jucomment/assets/js/typeahead.config.js");
				$script = "var juri_root = '" . JUri::root(true) . "/';";
				$document->addScriptDeclaration($script);
			}
		}

		return true;
	}

	
	public function __get($property)
	{
		switch ($property)
		{
			case 'comment_id':
				if (isset($this->comment->id))
				{
					return $this->comment->id;
				}
				else
				{
					return null;
				}
				break;
			case 'params':
			case 'filter':
			case 'output_attributes':
			case 'input_attributes':
			case 'search_attributes':
			case 'label_attributes':
			case 'fields_data':
			case 'is_new':
			case 'comment':
			case 'name':
			case 'id_suffix':
				return $this->$property;
				break;
			case 'value':
				
				$storeId = md5("FieldValue::" . $this->comment_id . "::" . $this->id);
				if (!isset(self::$cache[$storeId]))
				{
					if ($this->comment_id)
					{
						$value       = $this->getValue();
						$this->value = $this->parseValue($value);
						unset($value);
					}
					
					else
					{
						$this->value = $this->getDefaultPredefinedValues();
					}

					self::$cache[$storeId] = $this->value;
				}

				$this->value = self::$cache[$storeId];

				return $this->value;

				break;
			default:
				
				if (isset($this->field->$property))
				{
					return $this->field->$property;
				}
				
				else
				{
					$field = JUCommentFieldHelper::getFieldById($this->id);
					if (isset($field->$property))
					{
						return $field->$property;
					}
					else
					{
						return null;
					}
				}
				break;
		}
	}

	
	public function __set($property, $value)
	{
		switch ($property)
		{
			case 'comment_id':
				$this->comment->id = (int) $value;
				break;
			case 'params':
			case 'filter':
			case 'output_attributes':
			case 'input_attributes':
			case 'search_attributes':
			case 'label_attributes':
			case 'value':
			case 'fields_data':
			case 'predefined':
			case 'is_new':
			case 'name':
			case 'id_suffix':
				$this->$property = $value;
				break;
			default:
				
				if (!is_object($this->field))
				{
					$this->field = new stdClass();
				}

				$this->field->$property = $value;
				break;
		}
	}

	
	public function __clone()
	{
		if (is_object($this->comment))
		{
			$this->comment = clone $this->comment;
		}

		if (is_object($this->field))
		{
			$this->field = clone $this->field;
		}

		if (is_object($this->params))
		{
			$this->params = clone $this->params;
		}
	}

	
	public function loadComment($comment, $resetCache = false)
	{
		
		if (is_numeric($comment) && $comment > 0)
		{
			$comment = clone JUCommentHelper::getCommentById($comment, $resetCache);
		}

		if (is_object($comment) || is_null($comment))
		{
			$this->comment = $comment;
		}
	}

	protected function getValue()
	{
		$value = null;

		
		if (!$this->isCore())
		{
			
			$field_column = "field_values_" . $this->id;
			if (isset($this->comment->$field_column) && !is_null($this->comment->$field_column))
			{
				$value = $this->comment->$field_column;
			}
			else
			{
				$db = JFactory::getDbo();

				$query = "SELECT value FROM #__jucomment_fields_values WHERE comment_id=" . (int) $this->comment_id . " AND field_id=" . (int) $this->id;
				$db->setQuery($query);

				$value = $db->loadResult();
			}
		}
		else
		{
			$field_name = $this->field_name;
			if (isset($this->comment->$field_name))
			{
				$value = $this->comment->$field_name;
			}
		}

		if ($this->params->get('is_numeric', 0))
		{
			$value = (float) $value;
		}

		return $value;
	}

	
	public function parseValue($value)
	{
		
		

		

		return $value;
	}

	
	public function getPlaceholderValue(&$email = null)
	{
		return $this->value;
	}

	
	public function getCounter()
	{
		if ($this->comment_id)
		{
			$db    = JFactory::getDbo();
			$query = "SELECT counter FROM #__jucomment_fields_values WHERE field_id = " . $this->id . " AND comment_id = " . $this->comment_id;
			$db->setQuery($query);

			return $db->loadResult();
		}
		else
		{
			return null;
		}
	}

	
	protected function parseAttributes($attributes = '')
	{
		if ($attributes)
		{
			$attr_str      = html_entity_decode($attributes, ENT_QUOTES, 'UTF-8');
			$regex_pattern = "#\s*([^=\s]+)\s*=\s*('([^']*)'|\"([^\"]*)\"|([^\s]*))#msi";
			preg_match_all($regex_pattern, $attr_str, $matches);

			$attribute_array = array();
			if (count($matches))
			{
				for ($i = 0; $i < count($matches[1]); $i++)
				{
					$key                   = $matches[1][$i];
					$val                   = $matches[3][$i] ? $matches[3][$i] : ($matches[4][$i] ? $matches[4][$i] : $matches[5][$i]);
					$attribute_array[$key] = $val;
				}
			}

			$attribute_registry = new JRegistry($attribute_array);

			return $attribute_registry;
		}

		return new JRegistry;
	}

	
	public function storeValue($value)
	{
		
		if (!$this->comment_id)
		{
			return false;
		}

		$db = JFactory::getDbo();

		$result = true;

		if ($this->isCore())
		{
			$query = "UPDATE #__jucomment_comments SET " . $db->quoteName($this->field_name) . " = " . $db->quote($value) . " WHERE id = " . $this->comment_id;
			$db->setQuery($query);
			$result = $db->execute();
		}
		else
		{
			$query = "SELECT COUNT(*) FROM #__jucomment_fields_values WHERE field_id = " . $this->id . " AND comment_id = " . $this->comment_id;
			$db->setQuery($query);
			$countData = $db->loadResult();
			
			if ($countData > 0)
			{
				
				if ($value !== "" && !is_null($value))
				{
					$query = "UPDATE #__jucomment_fields_values SET value=" . $db->quote($value) . " WHERE field_id = " . $this->id . " AND comment_id = " . $this->comment_id;
					$db->setQuery($query);
					$result = $db->execute();
				}
				
				else
				{
					$query = "DELETE FROM #__jucomment_fields_values WHERE field_id = " . $this->id . " AND comment_id = " . $this->comment_id;
					$db->setQuery($query);
					$result = $db->execute();
				}
			}
			
			else
			{
				if ($value !== "" && !is_null($value))
				{
					$query = "INSERT INTO #__jucomment_fields_values (field_id, comment_id, value, counter) VALUES ($this->id, $this->comment_id, " . $db->quote($value) . ", 0)";
					$db->setQuery($query);
					$result = $db->execute();
				}
			}
		}

		return $result;
	}

	
	public function getPredefinedValues($predefined_values_type = 'auto')
	{
		
		$storeId = md5(__METHOD__ . "::" . $this->id . "::" . $predefined_values_type);
		if (!isset(self::$cache[$storeId]))
		{
			
			if ($predefined_values_type == 1)
			{
				$predefinedValues = $this->predefined_values;
			}
			
			elseif ($predefined_values_type == 2)
			{
				$predefinedValues = $this->getPredefinedFunction();
			}
			
			else
			{
				
				if ($this->predefined_values_type == 1)
				{
					$predefinedValues = $this->predefined_values;
				}
				else
				{
					$predefinedValues = $this->getPredefinedFunction();
				}
			}

			self::$cache[$storeId] = $this->parsePredefinedValues($predefinedValues);
		}

		return self::$cache[$storeId];
	}

	
	protected function parsePredefinedValues($predefinedValues)
	{
		if ($predefinedValues === "")
		{
			return "";
		}
		elseif (is_numeric($predefinedValues))
		{
			return $predefinedValues;
		}
		elseif (is_string($predefinedValues))
		{
			if (json_decode($predefinedValues))
			{
				return json_decode($predefinedValues);
			}
			elseif (strpos($predefinedValues, "|"))
			{
				return explode("|", $predefinedValues);
			}
			
			else
			{
				return $predefinedValues;
			}
		}
		
		else
		{
			return $predefinedValues;
		}
	}

	
	public function getPredefinedFunction()
	{
		$phpCode = $this->php_predefined_values;
		if (trim($phpCode))
		{
			return eval($phpCode);
		}

		return null;
	}

	
	public function getDefaultPredefinedValues()
	{
		$values = $this->getPredefinedValues();

		
		return $values;
	}

	
	public function getPredefinedValuesHtml()
	{
		$predefined_value = $this->getPredefinedValues(1);
		$html             = "<input type=\"text\" name=\"jform[predefined_values]\" value=\"" . @htmlspecialchars($predefined_value, ENT_COMPAT, 'UTF-8') . "\"/>";

		return $html;
	}

	
	public function getName()
	{
		return $this->name;
	}

	
	public function getId()
	{
		return 'field_' . $this->id . $this->id_suffix;
	}

	
	public function isCore()
	{
		if ($this->field_name != "")
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	
	public function isRequired()
	{
		if ($this->required)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	
	public function hasCaption()
	{
		if (!$this->caption || $this->hide_caption)
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	
	public function hideLabel()
	{
		if ($this->hide_label)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	
	public function getCaption($forceShow = false)
	{
		if ($this->hide_caption && !$forceShow)
		{
			return "";
		}
		else
		{
			
			if ($this->caption == strtoupper($this->caption))
			{
				return JText::_($this->caption);
			}

			return (string) $this->caption;
		}
	}

	
	protected function initAttribute($type = 'output')
	{
		$attributesProperty = $type . '_attributes';

		switch ($type)
		{
			
			case 'output':
				$field                     = JUCommentFieldHelper::getFieldById($this->id);
				$this->$attributesProperty = $this->parseAttributes($field->attributes);
				break;

			
			case 'input':
				$this->$attributesProperty = $this->parseAttributes($this->params->get('input_attributes', ''));
				break;

			
			case 'search':
				$this->$attributesProperty = $this->parseAttributes($this->params->get('search_attributes', ''));
				break;

			default:
				$this->$attributesProperty = new JRegistry;
				break;
		}
	}

	
	public function setAttribute($name, $value, $type = 'output')
	{
		$name              = strtolower($name);
		$ignoredAttributes = array('id', 'name');
		if (in_array($name, $ignoredAttributes))
		{
			return false;
		}

		$attributesProperty = $type . '_attributes';

		
		if (!$this->$attributesProperty)
		{
			$this->initAttribute($type);
		}

		
		if (is_null($value))
		{
			$attributeArray = $this->$attributesProperty->toArray();
			unset($attributeArray[$name]);

			$this->$attributesProperty = new JRegistry($attributeArray);

			return true;
		}
		else
		{
			$value = trim($value);

			return $this->$attributesProperty->set($name, $value);
		}
	}

	
	public function addAttribute($name, $value, $type = 'output')
	{
		$name              = strtolower($name);
		$ignoredAttributes = array('id', 'name');
		if (in_array($name, $ignoredAttributes))
		{
			return false;
		}

		$value = trim($value);
		if (!$value)
		{
			return true;
		}

		$attributesProperty = $type . '_attributes';

		
		if (!$this->$attributesProperty)
		{
			$this->initAttribute($type);
		}

		$currentAttribute = trim($this->$attributesProperty->get($name, ""));

		if ($currentAttribute)
		{
			if ($name == 'style')
			{
				
				if (substr($value, -1) != ";")
				{
					$currentAttribute .= ";";
				}
			}

			if ($name == 'class')
			{
				
				$currentAttributeArray = array_map("trim", explode(" ", $currentAttribute));
				$pos                   = array_search($value, $currentAttributeArray);
				if ($pos !== false)
				{
					unset($currentAttributeArray[$pos]);
					$currentAttribute = implode(" ", $currentAttributeArray);
				}
			}

			$newAttribute = implode(" ", array($currentAttribute, $value));
		}
		else
		{
			$newAttribute = $value;
		}

		return $this->$attributesProperty->set($name, $newAttribute);
	}

	
	public function getAttribute($name = null, $default = null, $type = 'output', $returnType = 'string')
	{
		$attributesProperty = $type . '_attributes';

		
		if (!$this->$attributesProperty)
		{
			$this->initAttribute($type);
		}

		$ignoredAttributes = array('id', 'name');

		if ($name)
		{
			$name = strtolower($name);

			if (in_array($name, $ignoredAttributes))
			{
				return null;
			}

			return $this->$attributesProperty->get($name, $default);
		}
		else
		{
			if ($returnType == 'registry')
			{
				return $this->$attributesProperty;
			}
			elseif ($returnType == 'array')
			{
				return $this->$attributesProperty->toArray();
			}
			else
			{
				return $this->$attributesProperty->toString('ini');
			}
		}
	}

	
	public function getModPrefixText($wrap = true)
	{
		if (!$this->isPublished())
		{
			return "";
		}

		$prefix_text_mod = $this->prefix_text_mod;
		if (empty($prefix_text_mod))
		{
			return "";
		}
		else
		{
			if ($wrap)
			{
				return '<span class="prefix_mod">' . $prefix_text_mod . '</span>';
			}
			else
			{
				return $prefix_text_mod;
			}
		}
	}

	
	public function getModSuffixText($wrap = true)
	{
		if (!$this->isPublished())
		{
			return "";
		}

		$suffix_text_mod = $this->suffix_text_mod;
		if (empty($suffix_text_mod))
		{
			return false;
		}
		else
		{
			if ($wrap)
			{
				return '<span class="suffix_mod">' . $suffix_text_mod . '</span>';
			}
			else
			{
				return $suffix_text_mod;
			}
		}
	}

	
	public function getDisplayPrefixText($wrap = null)
	{
		if (!$this->isPublished())
		{
			return "";
		}

		$prefix_text_display = $this->prefix_text_display;
		if (empty($prefix_text_display))
		{
			return '';
		}
		else
		{
			if (is_null($wrap))
			{
				$wrap = $this->prefix_suffix_wrapper;
			}

			if ($wrap)
			{
				return '<span class="prefix_display">' . $prefix_text_display . '</span>';
			}
			else
			{
				return $prefix_text_display;
			}
		}
	}

	
	public function getDisplaySuffixText($wrap = null)
	{
		if (!$this->isPublished())
		{
			return "";
		}

		$suffix_text_display = $this->suffix_text_display;
		if (empty($suffix_text_display))
		{
			return '';
		}
		else
		{
			if (is_null($wrap))
			{
				$wrap = $this->prefix_suffix_wrapper;
			}

			if ($wrap)
			{
				return '<span class="suffix_display">' . $suffix_text_display . '</span>';
			}
			else
			{
				return $suffix_text_display;
			}
		}
	}

	public function getCustomRule()
	{
		$customRule = '';
		$custom     = $this->params->get('custom_rule', 'function(value, element) {}');
		if ($custom && $custom != 'function(value, element) {}')
		{
			$customRule = $custom;
		}

		return $customRule;
	}

	public function getPatternRule()
	{
		$regex = $this->params->get('regex', '');

		if ($regex == 'custom_regex')
		{
			$regex = trim($this->params->get('custom_regex', ''));
		}

		return $regex;
	}

	public function getValidateData()
	{
		$validateData = array();
		$rule         = $this->params->get('rule', '');
		$required     = $this->isRequired();
		$restrict     = $this->getRestrictValidate();

		if (!$rule && !$required && !$restrict)
		{
			return '';
		}

		if ($required)
		{
			$validateData[] = 'data-rule-required="true"';

			$message = $this->getValidateMessage();
			if ($message)
			{
				$validateData[] = 'data-msg-required="' . $message . '"';
			}
		}

		if ($rule)
		{
			if ($rule == 'custom')
			{
				$customRule = $this->getCustomRule();
				if ($customRule)
				{
					$document = JFactory::getDocument();
					$script   = "
						jQuery.validator.addMethod('field" . $this->id . "', '" . $customRule . "' , 'Field invalid');
					";

					$document->addScriptDeclaration($script);

					$validateData[] = 'data-rule-field' . $this->id . '="true"';

					$message = $this->getValidateMessage('rule');
					if ($message)
					{
						$validateData[] = 'data-msg-field' . $this->id . '="' . $message . '"';
					}
				}
			}
			elseif ($rule == 'regex')
			{
				$pattern = $this->getPatternRule();
				if ($pattern)
				{
					$validateData[] = 'data-rule-pattern="' . $pattern . '"';
				}

				$message = $this->getValidateMessage('rule');
				if ($message)
				{
					$validateData[] = 'data-msg-pattern="' . $message . '"';
				}
			}
			else
			{
				$validateData[] = 'data-rule-' . $rule . '="true"';
				$message        = $this->getValidateMessage($rule);
				if ($message)
				{
					$validateData[] = 'data-msg-' . $rule . '="' . $message . '"';
				}
			}
		}

		if ($restrict)
		{
			$validateData[] = $restrict;
		}

		$validateData[] = 'aria-describedby="' . $this->getId() . '-error"';

		return implode(' ', $validateData);
	}

	public function getRestrictValidate()
	{
		$restrictType  = $this->params->get('restrict_type', '');
		$restrictValue = $this->params->get('restrict_value', '');
		if ($restrictType && $restrictValue)
		{
			if ($restrictType == 'min' || $restrictType == 'max')
			{
				$restrictValue = (int) $restrictValue;
				if ($restrictValue < 1)
				{
					return '';
				}
			}
			else
			{
				$restrictValue = explode(',', $restrictValue);
				if (count($restrictValue) != 2)
				{
					return '';
				}

				$restrictValue[0] = (int) $restrictValue[0];
				$restrictValue[1] = (int) $restrictValue[1];

				if ($restrictValue[0] < 1 || $restrictValue[1] < 1 || $restrictValue[0] >= $restrictValue[1])
				{
					return '';
				}
			}

			$validateData = array();
			if ($this->params->get('is_numeric', 0))
			{
				if ($restrictType == 'min')
				{
					$validateData[] = 'data-rule-min="' . $restrictValue . '"';
					$validateData[] = 'data-msg-min="' . JText::sprintf('COM_JUCOMMENT_PLEASE_ENTER_A_VALUE_LESS_THAN_OR_EQUAL_TO_X', $restrictValue) . '"';
				}
				elseif ($restrictType == 'max')
				{
					$validateData[] = 'data-rule-max="' . $restrictValue . '"';
					$validateData[] = 'data-msg-max="' . JText::sprintf('COM_JUCOMMENT_PLEASE_ENTER_A_VALUE_GREATER_THAN_OR_EQUAL_TO_X', $restrictValue) . '"';
				}
				else
				{
					$validateData[] = 'data-rule-range="[' . $restrictValue[0] . ',' . $restrictValue[1] . ']"';
					$validateData[] = 'data-msg-range="' . JText::sprintf('COM_JUCOMMENT_PLEASE_ENTER_A_VALUE_BETWEEN_X_AND_Y', $restrictValue[0], $restrictValue[1]) . '"';
				}
			}
			else
			{
				if ($restrictType == 'min')
				{
					$validateData[] = 'data-rule-minlength="' . $restrictValue . '"';
					$validateData[] = 'data-msg-minlength="' . JText::sprintf('COM_JUCOMMENT_PLEASE_ENTER_AT_LEAST_X_CHARACTERS', $restrictValue) . '"';
				}
				elseif ($restrictType == 'max')
				{
					$validateData[] = 'data-rule-maxlength="' . $restrictValue . '"';
					$validateData[] = 'data-msg-maxlength="' . JText::sprintf('COM_JUCOMMENT_PLEASE_ENTER_NO_MORE_THAN_X_CHARACTERS', $restrictValue) . '"';
				}
				else
				{
					$validateData[] = 'data-rule-rangelength="[' . $restrictValue[0] . ',' . $restrictValue[1] . ']"';
					$validateData[] = 'data-msg-rangelength="' . JText::sprintf('COM_JUCOMMENT_PLEASE_ENTER_A_VALUE_BETWEEN_X_AND_Y_CHARACTERS_LONG', $restrictValue[0], $restrictValue[1]) . '"';
				}
			}

			return implode(' ', $validateData);
		}

		return '';
	}

	public function getValidateMessage($type = 'required')
	{
		if ($type == 'required')
		{
			$message = (string) $this->params->get('required_message');
			if ($message)
			{
				$message = JText::sprintf($message, $this->getCaption(true));
			}
			else
			{
				$message = JText::sprintf('COM_JUCOMMENT_FIELD_IS_REQUIRED', $this->getCaption(true));
			}
		}
		else
		{
			$message = (string) $this->params->get('invalid_message');

			if ($message)
			{
				$message = JText::sprintf($message, $this->getCaption(true));
			}
			else
			{
				$message = JText::sprintf('COM_JUCOMMENT_FIELD_VALUE_IS_INVALID', $this->getCaption(true));
			}
		}

		if ($message)
		{
			$message = htmlspecialchars($message, ENT_COMPAT, 'UTF-8');
		}

		return $message;
	}

	
	public function PHPValidate($values)
	{
		
		if (($values === "" || $values === null) && !$this->isRequired())
		{
			return true;
		}

		

		$validate = (string) $this->params->get("validate", "");

		
		if (strpos($validate, '::') !== false && is_callable(explode('::', $validate)))
		{
			
		}
		
		elseif (function_exists($validate))
		{
			return call_user_func($validate, $values);
		}

		if ($values === "")
		{
			if ($this->isRequired())
			{
				return $this->getValidateMessage();
			}
			else
			{
				return true;
			}
		}
		else
		{
			$validateRestrict = $this->PHPValidateRestrict($values);
			if ($validateRestrict !== true)
			{
				return $validateRestrict;
			}

			
			$rule = $this->params->get('rule');

			if (!$rule || $rule == 'custom')
			{
				return true;
			}

			switch ($rule)
			{
				case 'regex' :
					$regex_pattern = $this->getPatternRule();
					$regex_pattern = "/" . $regex_pattern . "/ms";
					if (!$regex_pattern || preg_match($regex_pattern, $values))
					{
						return true;
					}
					else
					{
						return $this->getValidateMessage($rule);
					}

					break;
			}
		}

		return true;
	}

	protected function PHPValidateRestrict($values)
	{
		$restrictType  = $this->params->get('restrict_type', '');
		$restrictValue = $this->params->get('restrict_value', '');
		if ($restrictType && $restrictValue)
		{
			if ($restrictType == 'min' || $restrictType == 'max')
			{
				$restrictValue = (int) $restrictValue;
				if ($restrictValue < 1)
				{
					return true;
				}
			}
			else
			{
				$restrictValue = explode(',', $restrictValue);
				if (count($restrictValue) != 2)
				{
					return true;
				}

				$restrictValue[0] = (int) $restrictValue[0];
				$restrictValue[1] = (int) $restrictValue[1];

				if ($restrictValue[0] < 1 || $restrictValue[1] < 1 || $restrictValue[0] >= $restrictValue[1])
				{
					return true;
				}
			}

			if ($this->params->get('is_numeric', 0))
			{
				$values = (float) $values;
				if ($restrictType == 'min')
				{
					if ($values < $restrictValue)
					{
						return JText::sprintf('COM_JUCOMMENT_PLEASE_ENTER_A_VALUE_LESS_THAN_OR_EQUAL_TO_X', $restrictValue);
					}
				}
				elseif ($restrictType == 'max')
				{
					if ($values > $restrictValue)
					{
						return JText::sprintf('COM_JUCOMMENT_PLEASE_ENTER_A_VALUE_GREATER_THAN_OR_EQUAL_TO_X', $restrictValue);
					}
				}
				else
				{
					if ($values < $restrictValue[0] || $values > $restrictValue[1])
					{
						return JText::sprintf('COM_JUCOMMENT_PLEASE_ENTER_A_VALUE_BETWEEN_X_AND_Y', $restrictValue[0], $restrictValue[1]);
					}
				}
			}
			else
			{
				if (is_string($values))
				{
					$values = strlen($values);

					if ($restrictType == 'min')
					{
						if ($values < $restrictValue)
						{
							return JText::sprintf('COM_JUCOMMENT_PLEASE_ENTER_AT_LEAST_X_CHARACTERS', $restrictValue);
						}
					}
					elseif ($restrictType == 'max')
					{
						if ($values > $restrictValue)
						{
							return JText::sprintf('COM_JUCOMMENT_PLEASE_ENTER_NO_MORE_THAN_X_CHARACTERS', $restrictValue);
						}
					}
					else
					{
						if ($values < $restrictValue[0] || $values > $restrictValue[1])
						{
							return JText::sprintf('COM_JUCOMMENT_PLEASE_ENTER_A_VALUE_BETWEEN_X_AND_Y_CHARACTERS_LONG', $restrictValue[0], $restrictValue[1]);
						}
					}
				}
				else
				{
					$values = count($values);

					if ($restrictType == 'min')
					{
						if ($values < $restrictValue)
						{
							return JText::sprintf('COM_JUCOMMENT_PLEASE_SELECT_AT_LEAST_X_OPTIONS', $restrictValue);
						}
					}
					elseif ($restrictType == 'max')
					{
						if ($values > $restrictValue)
						{
							return JText::sprintf('COM_JUCOMMENT_PLEASE_SELECT_NO_MORE_THAN_X_OPTIONS', $restrictValue);
						}
					}
					else
					{
						if ($values < $restrictValue[0] || $values > $restrictValue[1])
						{
							return JText::sprintf('COM_JUCOMMENT_PLEASE_SELECT_FROM_X_TO_Y_OPTIONS', $restrictValue[0], $restrictValue[1]);
						}
					}
				}
			}
		}

		return true;
	}

	
	public function setError($error)
	{
		array_push($this->errors, $error);
	}

	
	public function getError($i = null, $toString = true)
	{
		
		if ($i === null)
		{
			
			$error = end($this->errors);
		}
		elseif (!array_key_exists($i, $this->errors))
		{
			
			return false;
		}
		else
		{
			$error = $this->errors[$i];
		}

		
		if ($error instanceof Exception && $toString)
		{
			return (string) $error;
		}

		return $error;
	}

	
	public function getErrors()
	{
		return $this->errors;
	}

	
	protected function getFilter()
	{
		$filter = $this->params->get('filter', '');
		if ($filter)
		{
			return $filter;
		}
		else
		{
			return $this->filter;
		}
	}

	
	public function filterField($value)
	{
		
		$filter = (string) $this->getFilter();

		
		$return = null;

		switch (strtoupper($filter))
		{
			
			case 'UNSET':
				break;

			
			case 'RAW':
				$return = $value;
				break;

			
			case 'INT_ARRAY':
				
				if (is_object($value))
				{
					$value = get_object_vars($value);
				}
				$value = is_array($value) ? $value : array($value);

				JArrayHelper::toInteger($value);
				$return = $value;
				break;

			
			case 'SAFEHTML':
				$return = JFilterInput::getInstance(null, null, 1, 1)->clean($value, 'string');
				break;

			
			case 'SERVER_UTC':
				if (intval($value) > 0)
				{
					
					$format = $this->params->get("input_dateformat", "Y-m-d H:i:s");

					
					$datetime = DateTime::createFromFormat($format, $value);
					if ($datetime)
					{
						
						$value = $datetime->format('Y-m-d H:i:s');

						
						$offset = JFactory::getConfig()->get('offset');

						
						$return = JFactory::getDate($value, $offset)->toSql();
					}
					else
					{
						$return = '';
					}
				}
				else
				{
					$return = '';
				}
				break;

			
			case 'USER_UTC':
				if (intval($value) > 0)
				{
					
					$format = $this->params->get("input_dateformat", "Y-m-d H:i:s");

					
					$datetime = DateTime::createFromFormat($format, $value);
					if ($datetime)
					{
						
						$value = $datetime->format('Y-m-d H:i:s');

						
						$offset = JFactory::getUser()->getParam('timezone', JFactory::getConfig()->get('offset'));

						
						$return = JFactory::getDate($value, $offset)->toSql();
					}
					else
					{
						$return = '';
					}
				}
				else
				{
					$return = '';
				}
				break;

			
			
			case 'URL':
				if (empty($value))
				{
					return false;
				}
				$value = JFilterInput::getInstance()->clean($value, 'html');
				$value = trim($value);

				
				$value = str_replace(array('<', '>', '"'), '', $value);

				
				$protocol = parse_url($value, PHP_URL_SCHEME);

				if (!$protocol)
				{
					$host = JUri::getInstance('SERVER')->gethost();

					
					if (substr($value, 0) == $host)
					{
						$value = 'http://' . $value;
					}
					
					else
					{
						$value = JUri::root() . $value;
					}
				}

				$return = $value;
				break;

			case 'TEL':
				$value = trim($value);
				
				if (preg_match('/^(?:\+?1[-. ]?)?\(?([2-9][0-8][0-9])\)?[-. ]?([2-9][0-9]{2})[-. ]?([0-9]{4})$/', $value) == 1)
				{
					$number = (string) preg_replace('/[^\d]/', '', $value);
					if (substr($number, 0, 1) == 1)
					{
						$number = substr($number, 1);
					}
					if (substr($number, 0, 2) == '+1')
					{
						$number = substr($number, 2);
					}
					$result = '1.' . $number;
				}
				
				elseif (preg_match('/^\+(?:[0-9] ?){6,14}[0-9]$/', $value) == 1)
				{
					$countrycode = substr($value, 0, strpos($value, ' '));
					$countrycode = (string) preg_replace('/[^\d]/', '', $countrycode);
					$number      = strstr($value, ' ');
					$number      = (string) preg_replace('/[^\d]/', '', $number);
					$result      = $countrycode . '.' . $number;
				}
				
				elseif (preg_match('/^\+[0-9]{1,3}\.[0-9]{4,14}(?:x.+)?$/', $value) == 1)
				{
					if (strstr($value, 'x'))
					{
						$xpos  = strpos($value, 'x');
						$value = substr($value, 0, $xpos);
					}
					$result = str_replace('+', '', $value);

				}
				
				elseif (preg_match('/[0-9]{1,3}\.[0-9]{4,14}$/', $value) == 1)
				{
					$result = $value;
				}
				
				else
				{
					$value = (string) preg_replace('/[^\d]/', '', $value);
					if ($value != null && strlen($value) <= 15)
					{
						$length = strlen($value);
						
						if ($length <= 12)
						{
							$result = '.' . $value;

						}
						else
						{
							
							$cclen  = $length - 12;
							$result = substr($value, 0, $cclen) . '.' . substr($value, $cclen);
						}
					}
					
					else
					{
						$result = '';
					}
				}
				$return = $result;
				break;
			default:
				
				if (strpos($filter, '::') !== false && is_callable(explode('::', $filter)))
				{
					$return = call_user_func(explode('::', $filter), $value);
				}
				
				elseif (function_exists($filter))
				{
					$return = call_user_func($filter, $value);
				}
				
				else
				{
					$return = JFilterInput::getInstance()->clean($value, $filter);
				}
				break;
		}

		return $return;
	}

	
	public function getLabel($required = true, $forceShow = false)
	{
		if (!$this->isPublished())
		{
			return "";
		}

		if ($required && $this->isRequired())
		{
			$this->addAttribute("class", "required", "label");
		}

		$this->setVariable('required', $required);
		$this->setVariable('forceShow', $forceShow);

		return $this->fetch('label.php');
	}

	
	public function getInput($fieldValue = null)
	{
		if (!$this->isPublished())
		{
			return "";
		}

		
		if ($this->getAttribute("type", "", "input") == "")
		{
			$this->setAttribute("type", "text", "input");
		}

		if ($this->params->get('auto_suggest', 0))
		{
			$this->addAttribute("class", "autosuggest", "input");
		}

		$value = !is_null($fieldValue) ? $fieldValue : $this->value;

		if ((int) $this->params->get("size", 32))
		{
			$this->setAttribute("size", (int) $this->params->get("size", 32), "input");
		}

		if ($this->params->get("placeholder", ""))
		{
			$placeholder = htmlspecialchars($this->params->get("placeholder", ""), ENT_COMPAT, 'UTF-8');
			$this->setAttribute("placeholder", $placeholder, "input");
		}

		$this->registerTriggerForm();

		$this->setVariable('value', $value);

		return $this->fetch('input.php', __CLASS__);
	}

	public function getPreview()
	{
		
		if ($this->getAttribute("type", "", "input") == "")
		{
			$this->setAttribute("type", "text", "input");
		}

		if ((int) $this->params->get("size", 32))
		{
			$this->setAttribute("size", (int) $this->params->get("size", 32), "input");
		}

		if ($this->params->get("placeholder", ""))
		{
			$placeholder = htmlspecialchars($this->params->get("placeholder", ""), ENT_COMPAT, 'UTF-8');
			$this->setAttribute("placeholder", $placeholder, "input");
		}

		$value = $this->value;

		$this->setVariable('value', $value);

		return $this->fetch('preview.php', __CLASS__);
	}

	
	public function getCountryFlag()
	{
		if (!$this->isPublished())
		{
			return "";
		}

		$lang = $this->language;
		$flag = '';
		if ($lang != '*' && $lang != '')
		{
			$lang_arr     = explode('-', $lang);
			$country_code = strtolower($lang_arr[0]);
			$flag         = '<span class="flag flag-' . $country_code . '"><img src="' . JUri::root() . 'media/mod_languages/images/' . $country_code . '.gif" alt="' . $lang . '"/></span>';
		}

		return $flag;
	}

	
	public function getOutput($options = array())
	{
		if (!$this->isPublished())
		{
			return "";
		}

		$this->setVariable('values', $this->value);
		$this->setVariable('options', $options);

		return $this->fetch('output.php', __CLASS__);
	}

	
	public function getBackendOutput()
	{
		$values = $this->value;
		
		if (is_array($values))
		{
			$html = '';
			if ($values)
			{
				$html = '<ul class="nav">';
				foreach ($values AS $value)
				{
					$html .= '<li>' . $value . '</li>';
				}
				$html .= '</ul>';
			}
		}
		
		else
		{
			if ($this->params->get("is_numeric", 0))
			{
				$totalNumbers  = $this->params->get("digits_in_total", 11);
				$decimals      = $this->params->get("decimals", 2);
				$dec_point     = $this->params->get("dec_point", ".");
				$thousands_sep = $this->params->get("use_thousands_sep", 0) ? $this->params->get("thousands_sep", ",") : "";
				
				$values = $this->numberFormat($values, $totalNumbers, $decimals, $dec_point, $thousands_sep);
			}

			$html = $values;
		}

		return $html;
	}

	
	public function getSearchInput($defaultValue = "")
	{
		if (!$this->isPublished())
		{
			return "";
		}

		
		if ($this->getAttribute("type", "", "search") == "")
		{
			$this->setAttribute("type", "text", "search");
		}

		if ((int) $this->params->get("size", 32))
		{
			$this->setAttribute("size", (int) $this->params->get("size", 32), "search");
		}

		$this->setVariable('defaultValue', $defaultValue);

		return $this->fetch('searchinput.php');
	}

	
	public function loadDefaultAssets($loadJS = true, $loadCSS = true)
	{
		static $loaded = array();

		if ($this->folder && !isset($loaded[$this->folder]))
		{
			$document = JFactory::getDocument();
			
			if ($loadJS)
			{
				$js_path = JPATH_SITE . "/components/com_jucomment/fields/" . $this->folder . "/" . $this->folder . ".js";
				if (JFile::exists(JPath::clean($js_path)))
				{
					$document->addScript(JUri::root() . "components/com_jucomment/fields/" . $this->folder . "/" . $this->folder . ".js");
				}
			}

			
			if ($loadCSS)
			{
				$css_path = JPATH_SITE . "/components/com_jucomment/fields/" . $this->folder . "/" . $this->folder . ".css";
				if (JFile::exists(JPath::clean($css_path)))
				{
					$document->addStyleSheet(JUri::root() . "components/com_jucomment/fields/" . $this->folder . "/" . $this->folder . ".css");
				}
			}

			$loaded[$this->folder] = true;
		}
	}

	
	public function getRawData()
	{
		return null;
	}

	
	public function onSave($data)
	{
		return $data;
	}

	
	public function onSaveComment($value = '')
	{
		if (is_array($value))
		{
			$value = implode("|", $value);
		}

		if (is_object($value))
		{
			$value = json_encode($value);
		}

		return $value;
	}

	
	public function onDelete($deleteAll = false)
	{
		if ($this->isCore())
		{
			return false;
		}

		$commentIds = array();
		if ($this->comment_id)
		{
			$commentIds = (array) $this->comment_id;
		}
		elseif ($deleteAll)
		{
			$db    = JFactory::getDbo();
			$query = "SELECT comment_id FROM #__jucomment_fields_values WHERE field_id = " . $this->id;
			$db->setQuery($query);
			$commentIds = $db->loadColumn();
		}

		if (!$commentIds)
		{
			return false;
		}

		foreach ($commentIds as $commentId)
		{
			$this->deleteExtraData($commentId);

			
			$db    = JFactory::getDbo();
			$query = "DELETE FROM #__jucomment_fields_values WHERE field_id = " . (int) $this->id . " AND comment_id = " . (int) $commentId;
			$db->setQuery($query);

			$db->execute();
		}

		return true;
	}

	
	public function deleteExtraData($commentId = null)
	{
		return true;
	}

	
	public function onCopy($toCommentId, &$fieldsData = array())
	{
		if ($this->isCore())
		{
			return false;
		}

		$commentId = $this->comment_id;
		if (!$commentId)
		{
			return false;
		}

		$this->copyExtraData($toCommentId);

		
		$db    = JFactory::getDbo();
		$query = "INSERT INTO `#__jucomment_fields_values` (field_id, comment_id, value, counter) SELECT field_id, $toCommentId, value, counter FROM `#__jucomment_fields_values` WHERE field_id = $this->id AND comment_id = $commentId";
		$db->setQuery($query);

		return $db->execute();
	}

	
	public function copyExtraData($toCommentId)
	{
		return true;
	}

	
	public function onSearch(&$query, &$where, $search, $forceModifyQuery = false)
	{
		if ($search === "" || (is_array($search) && empty($search)))
		{
			return false;
		}

		if (!$this->isCore())
		{
			
			$storeId = md5(__METHOD__ . "::" . $this->id);
			if (!isset(self::$cache[$storeId]) || $forceModifyQuery)
			{
				$query->join('LEFT', '#__jucomment_fields_values AS field_values_' . $this->id . ' ON (comment.id = field_values_' . $this->id . '.comment_id AND field_values_' . $this->id . '.field_id = ' . $this->id . ')');

				self::$cache[$storeId] = true;
			}
		}

		
		if (is_string($search))
		{
			
			if ($this->params->get("is_numeric", 0))
			{
				$search = (int) $search;

				$where[] = "(CONVERT(" . $this->fieldvalue_column . ", DECIMAL(" . $this->params->get("digits_in_total", 11) . "," . $this->params->get("decimals", 2) . ") ) = $search )";
			}
			
			else
			{
				$db = JFactory::getDbo();

				$where[] = $this->fieldvalue_column . " LIKE '%" . $db->escape($search, true) . "%'";
			}
		}
		
		elseif (is_array($search))
		{
			
			if ($this->params->get("is_numeric", 0))
			{
				if ($search['from'] !== "" && $search['to'] !== "")
				{
					$from = (int) $search['from'];
					$to   = (int) $search['to'];
					if ($from > $to)
					{
						$this->swap($from, $to);
					}

					$where[] = "(CONVERT(" . $this->fieldvalue_column . ", DECIMAL(" . $this->params->get("digits_in_total", 11) . "," . $this->params->get("decimals", 2) . ") ) BETWEEN $from AND $to )";
				}
				elseif ($search['from'] !== "")
				{
					$from = (int) $search['from'];

					$where[] = "(CONVERT(" . $this->fieldvalue_column . ", DECIMAL(" . $this->params->get("digits_in_total", 11) . "," . $this->params->get("decimals", 2) . ") ) >= $from )";
				}
				elseif ($search['to'] !== "")
				{
					$to = (int) $search['to'];

					$where[] = "(CONVERT(" . $this->fieldvalue_column . ", DECIMAL(" . $this->params->get("digits_in_total", 11) . "," . $this->params->get("decimals", 2) . ") ) <= $to )";
				}
			}
			
			else
			{
				$db     = JFactory::getDbo();
				$_where = array();
				foreach ($search AS $value)
				{
					if ($value !== "")
					{
						
						if ($this->folder == 'text')
						{
							$separator = ",";
						}
						else
						{
							$separator = "|";
						}

						
						$_where[] = "( " . $this->fieldvalue_column . " = " . $db->quote($value) .
							" OR " . $this->fieldvalue_column . " LIKE '" . $db->escape($value, true) . $separator . "%'" .
							" OR " . $this->fieldvalue_column . " LIKE '%" . $separator . $db->escape($value, true) . $separator . "%'" .
							" OR " . $this->fieldvalue_column . " LIKE '%" . $separator . $db->escape($value, true) . "' )";
					}
				}

				if (!empty($_where))
				{
					
					$search_operator = " " . $this->params->get("search_operator", "OR") . " ";
					$where[]         = "(" . implode($search_operator, $_where) . ")";
				}
			}
		}
	}

	
	public function onSimpleSearch(&$query, &$where, $search, $forceModifyQuery = false)
	{
		
		if (is_string($search))
		{
			$search = JUCommentFrontHelper::UrlDecode($search);
		}

		$this->onSearch($query, $where, $search, $forceModifyQuery);
	}

	
	public function onTagSearch(&$query, &$where, $tag = null)
	{
		
		if (!$this->params->get("tag_search", 0))
		{
			return false;
		}

		if (is_null($tag))
		{
			$app = JFactory::getApplication();
			$tag = $app->input->get("tag", "", "string");
		}

		if (!$this->isCore())
		{
			$query->join('', '#__jucomment_fields_values AS field_values_' . $this->id . ' ON (comment.id = field_values_' . $this->id . '.comment_id AND field_values_' . $this->id . '.field_id = ' . $this->id . ')');
		}

		if ($tag !== "")
		{
			
			$tag = JUCommentFrontHelper::UrlDecode($tag);

			
			$db = JFactory::getDbo();
			
			$_where = "(( " . $this->fieldvalue_column . " = " . $db->quote($tag) .
				" OR " . $this->fieldvalue_column . " LIKE '" . $db->escape($tag, true) . "|%'" .
				" OR " . $this->fieldvalue_column . " LIKE '%|" . $db->escape($tag, true) . "|%'" .
				" OR " . $this->fieldvalue_column . " LIKE '%|" . $db->escape($tag, true) . "' )";
			$_where .= " OR ( " . $this->fieldvalue_column . " LIKE '" . $db->escape($tag, true) . ",%'" .
				" OR " . $this->fieldvalue_column . " LIKE '%," . $db->escape($tag, true) . ",%'" .
				" OR " . $this->fieldvalue_column . " LIKE '%," . $db->escape($tag, true) . "' ))";
			$where[] = $_where;
		}
	}

	
	public function getTextByValue($value)
	{
		$options = $this->getPredefinedValues();
		if (is_array($options))
		{
			foreach ($options AS $option)
			{
				if ($option->value == $value)
				{
					return $option->text;
				}
			}
		}

		return $value;
	}

	
	public function onAutoSuggest($string)
	{
		
		if (!$this->params->get('auto_suggest', 0))
		{
			return false;
		}

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		if ($this->isCore())
		{
			$query->select('DISTINCT ' . $db->quoteName($this->field_name));
			$query->from('#__jucomment_comments');
			$query->where($db->quoteName($this->field_name) . " LIKE '%" . $db->escape($string, true) . "%'");
		}
		else
		{
			$query->select('DISTINCT value');
			$query->from('#__jucomment_fields_values');
			$query->where('field_id = ' . $this->id);
			$query->where("value LIKE '%" . $db->escape($string, true) . "%'");
		}

		$db->setQuery($query);

		$result = $db->loadColumn();

		return $result;
	}

	
	public function isPublished()
	{
		$storeId = md5(__METHOD__ . "::" . $this->id);
		if (!isset(self::$cache[$storeId]))
		{
			if (!$this->published)
			{
				self::$cache[$storeId] = false;

				return self::$cache[$storeId];
			}

			$date = JFactory::getDate();
			if (intval($this->publish_down) > 0 && $this->publish_down <= $date->toSql())
			{
				self::$cache[$storeId] = false;

				return self::$cache[$storeId];
			}

			if ($this->publish_up > $date->toSql())
			{
				self::$cache[$storeId] = false;

				return self::$cache[$storeId];
			}

			
			$fieldGroupObj = JUCommentFieldHelper::getFieldGroupById($this->group_id);
			if (!$fieldGroupObj->published)
			{
				self::$cache[$storeId] = false;

				return self::$cache[$storeId];
			}

			self::$cache[$storeId] = true;

			return self::$cache[$storeId];
		}

		return self::$cache[$storeId];
	}

	
	public function canView($options = array())
	{
		$storeId = md5(__METHOD__ . "::" . $this->comment_id . "::" . $this->id . "::" . serialize($options));

		if (!isset(self::$cache[$storeId]))
		{
			
			if (!$this->isPublished())
			{
				self::$cache[$storeId] = false;

				return self::$cache[$storeId];
			}

			
			$app            = JFactory::getApplication();
			$languageFilter = $app->getLanguageFilter();
			if ($languageFilter)
			{
				$languageTag = JFactory::getLanguage()->getTag();
				if (($this->language != $languageTag && $this->language != '*' && $this->language != ''))
				{
					self::$cache[$storeId] = false;

					return self::$cache[$storeId];
				}
			}

			
			if (!$this->details_view)
			{
				self::$cache[$storeId] = false;

				return self::$cache[$storeId];
			}

			
			$params = JUComment::getParams();

			
			$show_empty_field = $params->get('show_empty_field', 0);
			if ($this->comment_id && !$show_empty_field)
			{
				$field_value = $this->value;

				if (is_null($field_value))
				{
					self::$cache[$storeId] = false;

					return self::$cache[$storeId];
				}

				if (is_string($field_value) && trim($field_value) === '')
				{
					self::$cache[$storeId] = false;

					return self::$cache[$storeId];
				}

				if (is_array($field_value) && count($field_value) == 0)
				{
					self::$cache[$storeId] = false;

					return self::$cache[$storeId];
				}
			}

			$user = JFactory::getUser();
			if ($user)
			{
				$viewLevels    = JAccess::getAuthorisedViewLevels($user->id);
				$fieldGroupObj = JUCommentFieldHelper::getFieldGroupById($this->group_id);
				
				if (!in_array($fieldGroupObj->access, $viewLevels))
				{
					self::$cache[$storeId] = false;

					return self::$cache[$storeId];
				}
				else
				{
					if (in_array($this->access, $viewLevels))
					{
						self::$cache[$storeId] = true;

						return self::$cache[$storeId];
					}
				}
			}

			self::$cache[$storeId] = false;

			return self::$cache[$storeId];
		}

		return self::$cache[$storeId];
	}

	
	public function canSubmit($userID = null)
	{
		if (!$this->isPublished())
		{
			return false;
		}

		$app = JFactory::getApplication();

		
		if ($app->isAdmin())
		{
			return true;
		}
		else
		{
			if ($userID)
			{
				$user = JFactory::getUser($userID);
			}
			else
			{
				$user = JFactory::getUser();
			}

			
			
			

			
			if ($user)
			{
				$assetName = 'com_jucomment.field.' . (int) $this->id;

				if ($user->authorise("field.submit", $assetName))
				{
					return true;
				}
			}

			
			$dispatcher = JDispatcher::getInstance();
			JPluginHelper::importPlugin('jucomment');
			$pluginTriggerResults = $dispatcher->trigger('canSubmitField', array($this->id, $this->comment_id, $userID));

			if (in_array(true, $pluginTriggerResults, true))
			{
				return true;
			}
		}

		return false;
	}

	
	public function canEdit($userID = null)
	{
		if (!$this->isPublished())
		{
			return false;
		}

		$app = JFactory::getApplication();
		
		if ($app->isAdmin())
		{
			return true;
		}
		else
		{
			if ($userID)
			{
				$user = JFactory::getUser($userID);
			}
			else
			{
				$user = JFactory::getUser();
			}

			if ($user)
			{
				$assetName = 'com_jucomment.field.' . (int) $this->id;

				$canEdit = $user->authorise("field.edit", $assetName);

				if ($canEdit)
				{
					return true;
				}
				else
				{
					if (!$user->get('guest'))
					{
						if (is_object($this->comment) && $this->comment->approved == 1)
						{
							if ($user->id == $this->comment->created_by)
							{
								if ($user->authorise("field.edit.own", $assetName))
								{
									return true;
								}
							}
						}
					}
				}
			}

			
			$dispatcher = JDispatcher::getInstance();
			JPluginHelper::importPlugin('jucomment');
			$pluginTriggerResults = $dispatcher->trigger('canEditField', array($this->id, $this->comment_id, $userID));

			if (in_array(true, $pluginTriggerResults, true))
			{
				return true;
			}
		}

		return false;
	}

	
	public function canSearch($userID = null)
	{
		if (!$this->isPublished())
		{
			return false;
		}

		if (!$this->advanced_search && !$this->filter_search)
		{
			return false;
		}

		if ($userID)
		{
			$user = JFactory::getUser($userID);
		}
		else
		{
			$user = JFactory::getUser();
		}

		if ($user)
		{
			$assetName = 'com_jucomment.field.' . (int) $this->id;

			return $user->authorise("field.search", $assetName);
		}

		return false;
	}

	
	public function setVariable($variable, $value)
	{
		$this->vars[$variable] = $value;
	}

	
	protected function getTmplFile($file = 'output.php', $class = null)
	{
		if (is_null($class))
		{
			$class = 'JUCommentFieldBase';
		}

		$folder = str_replace('jucommentfield', '', strtolower($class));

		
		$templatePaths   = array();
		$templatePaths[] = JPATH_SITE . '/components/com_jucomment/fields/' . $folder . '/tmpl/';
		$app             = JFactory::getApplication();
		if ($app->isSite())
		{
			$template        = JUComment::getTemplate();
			$templatePaths[] = JPATH_SITE . '/components/com_jucomment/templates/' . $template->getName() . '/fields/' . $folder . '/';
			$templatePaths[] = JPATH_THEMES . '/' . $app->getTemplate() . '/html/com_jucomment/' . $template->getName() . '/fields/' . $folder . '/';
		}

		$templatePaths = array_reverse($templatePaths);
		foreach ($templatePaths AS $templatePath)
		{
			$path = $templatePath . $file;
			if (JFile::exists($path))
			{
				return $path;
			}
		}

		return $file;
	}

	
	public function fetch($file = 'output.php', $class = null)
	{
		if (!JFile::exists($file))
		{
			$file = $this->getTmplFile($file, $class);
		}

		
		unset($class);

		if ($this->vars)
		{
			extract($this->vars);
		}

		ob_start();

		if (JFile::exists($file))
		{
			include($file);
		}
		else
		{
			echo JText::sprintf('Template file not found: %s', $file);
		}

		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	
	public function orderingPriority(&$query = null)
	{
		if ($this->isCore())
		{
			return array('ordering' => 'comment.' . $this->field_name, 'direction' => $this->priority_direction);
		}
		else
		{
			if ($this->params->get('is_numeric', 0))
			{
				$this->appendQuery($query, "select", "CONVERT(field_values_" . $this->id . ".value, DECIMAL(" . $this->params->get("digits_in_total", 6) . "," . $this->params->get("decimals", 2) . ")) AS field_value_" . $this->id);
			}
			else
			{
				$this->appendQuery($query, "select", "field_values_" . $this->id . ".value AS field_value_" . $this->id);
			}

			$this->appendQuery($query, "left join", '#__jucomment_fields_values AS field_values_' . $this->id . ' ON (comment.id = field_values_' . $this->id . '.comment_id AND field_values_' . $this->id . '.field_id = ' . $this->id . ')');

			return array('ordering' => 'field_value_' . $this->id, 'direction' => $this->priority_direction);
		}
	}

	
	protected function appendQuery(&$query, $type, $element)
	{
		switch (strtolower($type))
		{
			case 'select':
				if (!$this->checkQueryExists($element, $query->select->getElements()))
				{
					$query->select($element);
				}
				break;
			case 'where':
				if (!$this->checkQueryExists($element, $query->where->getElements()))
				{
					$query->where($element);
				}
				break;

			case 'join':
			case 'left join':
			case 'right join':
				$append = true;
				foreach ($query->join AS $join)
				{
					if ($this->checkQueryExists($element, $join->getElements()))
					{
						$append = false;
						break;
					}
				}

				if ($append == true)
				{
					if ($type == 'join')
					{
						$query->join('', $element);
					}
					elseif ($type == 'left join')
					{
						$query->join('LEFT', $element);
					}
					else
					{
						$query->join('RIGHT', $element);
					}
				}
				break;
		}
	}

	
	protected function checkQueryExists($needle, $haystack)
	{
		if (!$needle)
		{
			return true;
		}

		if (!$haystack)
		{
			return false;
		}

		$needle = strtolower(preg_replace('/\s+/', ' ', trim($needle)));

		foreach ($haystack AS $element)
		{
			$element = strtolower(preg_replace('/\s+/', ' ', trim($element)));
			if ($element == $needle)
			{
				return true;
			}
		}

		return false;
	}

	
	public function loadLanguage($fieldFolder)
	{
		
		$storeId = md5(__METHOD__ . "::" . $fieldFolder);

		if (!isset(self::$cache[$storeId]))
		{
			$fieldXmlPath = JPATH_SITE . '/components/com_jucomment/fields/' . $fieldFolder . '/' . $fieldFolder . '.xml';

			if (JFile::exists($fieldXmlPath))
			{
				$field_xml = JFactory::getXML($fieldXmlPath, true);

				
				if ($field_xml->languages->count())
				{
					foreach ($field_xml->languages->children() AS $language)
					{
						$languageFile = (string) $language;
						
						$first_pos       = strpos($languageFile, '.');
						$last_pos        = strrpos($languageFile, '.');
						$languageExtName = substr($languageFile, $first_pos + 1, $last_pos - $first_pos - 1);

						
						$client = JApplicationHelper::getClientInfo((string) $language->attributes()->client, true);
						$path   = isset($client->path) ? $client->path : JPATH_BASE;

						JUComment::loadLanguageFile($languageExtName, $path);
					}
				}
			}

			self::$cache[$storeId] = true;

			return self::$cache[$storeId];
		}

		return self::$cache[$storeId];
	}

	
	protected function numberFormat($number, $totalNumbers = 11, $decimals = 0, $dec_point = '.', $thousands_sep = ',')
	{
		$number         = (float) $number;
		$int            = $totalNumbers - $decimals;
		$number         = number_format($number, $decimals);
		$number         = preg_replace("/[^0-9\.]+/", "", $number);
		$numberArr      = explode(".", $number);
		$spNumberArray0 = str_split($numberArr[0]);
		if (count($spNumberArray0) > $int)
		{
			$spNumberArray0 = array();
			for ($i = 0; $i < $int; $i++)
			{
				$spNumberArray0[] = 9;
			}
		}

		$integerArr    = array_reverse($spNumberArray0);
		$newIntegerArr = array();
		for ($i = 0; $i < count($integerArr); $i++)
		{
			$newIntegerArr[] = $integerArr[$i];
			if (($i + 1) % 3 == 0 && $i < count($integerArr) - 1)
			{
				$newIntegerArr[] = $thousands_sep;
			}
		}

		$number = implode("", array_reverse($newIntegerArr));
		if (isset($numberArr[1]))
		{
			if (count(str_split($numberArr[0])) > $int)
			{
				$number = $number . $dec_point . str_repeat(9, count($numberArr[1]) + 1);
			}
			else
			{

				$number = $number . $dec_point . $numberArr[1];
			}
		}

		return $number;
	}

	
	public function swap(&$value1, &$value2)
	{
		if ($value1 > $value2)
		{
			$temp   = $value1;
			$value1 = $value2;
			$value2 = $temp;
		}
	}

	public function canImport()
	{
		return true;
	}

	
	public function onImportComment($value)
	{
		return $value;
	}

	public function getInvalidHtml()
	{
		return '<span class="help-block"><span id="' . $this->getId() . '-error" class="error label label-important label-danger"></span></span>';
	}

	public function registerTriggerForm()
	{
		$document = JFactory::getDocument();

		$script = '
			if(typeof juCommentFomTrigger === "undefined"){
				var	juCommentFomTrigger = [];
			}

			juCommentFomTrigger["' . $this->getId() . '"] = function(form, type, result){
				if(type == "reset" || (type == "submit" && result.type == "success")){
					jQuery("#' . $this->getId() . '").val("");
				}
			}
		';

		$document->addScriptDeclaration($script);
	}

}