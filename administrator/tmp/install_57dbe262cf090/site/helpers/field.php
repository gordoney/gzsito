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

jimport('joomla.utilities.utility');

class JUCommentFieldHelper
{

	
	protected static $cache = array();

	
	public static function getFieldGroupById($fieldGroupId)
	{
		$storeId = md5(__METHOD__ . "::" . (int) $fieldGroupId);
		if (!isset(self::$cache[$storeId]))
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from('#__jucomment_fields_groups');
			$query->where('id = ' . $fieldGroupId);
			$db->setQuery($query);
			self::$cache[$storeId] = $db->loadObject();
		}

		return self::$cache[$storeId];
	}

	
	public static function appendFieldOrderingPriority(&$query = null, $ordering = null, $direction = null)
	{
		if ($ordering)
		{
			$storeId = md5(__METHOD__ . "::$ordering");
			if (!isset(self::$cache[$storeId]))
			{
				$db             = JFactory::getDbo();
				$nullDate       = $db->getNullDate();
				$nowDate        = JFactory::getDate()->toSql();
				$ordering_query = $db->getQuery(true);
				$ordering_query->select("field.*");
				$ordering_query->from("#__jucomment_fields AS field");
				$ordering_query->select("plg.folder");
				$ordering_query->join("", "#__jucomment_plugins AS plg ON field.plugin_id = plg.id ");
				$ordering_query->join("", "#__jucomment_fields_groups AS field_group ON field.group_id = field_group.id");
				if ($ordering)
				{
					$ordering_query->where("(field.id = '$ordering' OR field.field_name = '$ordering')");
					$app = JFactory::getApplication();
					if ($app->isSite())
					{
						$ordering_query->where("field.frontend_ordering = 1");
					}
					else
					{
						$ordering_query->where("field.backend_list_view >= 1");
					}
					$ordering_query->where("field_group.published = 1");
					$ordering_query->where("field.published = 1");
					$ordering_query->where("field.publish_up <= " . $db->quote($nowDate));
					$ordering_query->where("(field.publish_down = " . $db->quote($nullDate) . " OR field.publish_down >= " . $db->quote($nowDate) . ")");
				}

				$db->setQuery($ordering_query);

				self::$cache[$storeId] = $db->loadObject();
			}

			$orderingField = self::$cache[$storeId];

			if ($orderingField)
			{
				$ordering_str = "";
				$priority     = $orderingField->orderingPriority($query);
				if ($priority)
				{
					$ordering_str = $priority['ordering'] . " " . $direction;
					$query->order($ordering_str);
				}

				return $ordering_str;
			}
		}

		return "";
	}

	
	public static function getFieldById($fieldId, $fieldObj = null)
	{
		if (!$fieldId)
		{
			return null;
		}

		
		$storeId = md5(__METHOD__ . "::$fieldId");

		if (!isset(self::$cache[$storeId]))
		{
			if (!is_object($fieldObj))
			{
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->select('field.*, p.folder')
					->from('#__jucomment_fields AS field')
					->join('LEFT', '#__jucomment_plugins AS p ON p.id = field.plugin_id');

				if (is_numeric($fieldId))
				{
					$query->where('field.id = ' . $fieldId);
				}
				
				else
				{
					$query->where('field.field_name = ' . $db->quote($fieldId));
				}

				$db->setQuery($query);

				$fieldObj = $db->loadObject();
			}

			
			unset($fieldObj->allow_priority);
			unset($fieldObj->backend_list_view);
			unset($fieldObj->backend_list_view_ordering);
			unset($fieldObj->checked_out);
			unset($fieldObj->checked_out_time);
			unset($fieldObj->asset_id);
			unset($fieldObj->ordering);
			unset($fieldObj->frontend_ordering);
			unset($fieldObj->ignored_options);
			unset($fieldObj->created);
			unset($fieldObj->created_by);
			unset($fieldObj->modified);
			unset($fieldObj->modified_by);

			self::$cache[$storeId] = $fieldObj;
		}

		return self::$cache[$storeId];
	}

	
	public static function getField($field, $comment = null, $resetCommentCache = false)
	{
		if (!$field)
		{
			return null;
		}

		if (is_object($field))
		{
			if ($field->field_name != "")
			{
				$fieldId = $field->field_name;
			}
			else
			{
				$fieldId = $field->id;
			}
		}
		else
		{
			$fieldId = $field;
		}

		
		$storeId = md5("JUCMField::" . $fieldId);
		if (!isset(self::$cache['fields'][$storeId]))
		{
			
			if (!is_object($field))
			{
				$field = JUCommentFieldHelper::getFieldById($field);
			}

			if (!$field)
			{
				return false;
			}

			
			if (!$field->folder)
			{
				$fieldClassName = 'JUCommentField';
			}
			else
			{
				$fieldClassName = 'JUCommentField' . $field->folder;
			}
			$_fieldObj = clone $field;

			$fieldClass = null;
			if (class_exists($fieldClassName))
			{
				$fieldClass = new $fieldClassName($_fieldObj);
			}

			self::$cache['fields'][$storeId] = $fieldClass;
		}

		
		$fieldClass = self::$cache['fields'][$storeId];
		if ($fieldClass)
		{
			$fieldClassWithComment = clone $fieldClass;
			$fieldClassWithComment->loadComment($comment, $resetCommentCache);

			return $fieldClassWithComment;
		}
		else
		{
			return $fieldClass;
		}
	}

	
	public static function getFields($comment = null, $type = 'comment', $includedOnlyFields = array(), $ignoredFields = array(), $additionFields = array())
	{
		$component = JUComment::getCurrent('component');
		$section   = JUComment::getCurrent('section');
		$cat_id    = JUComment::getCurrent('cat_id');

		$params = JUComment::getParams();

		$fieldgroup_id_name       = $type . "_fieldgroup_id";
		$field_ordering_type_name = $type . "_field_ordering_type";

		if (is_numeric($comment))
		{
			$comment = JUComment::getComment($comment);
		}

		if ($comment)
		{
			$fieldgroup_id = $comment->fieldgroup_id;
		}
		else
		{
			$fieldgroup_id = $params->get($fieldgroup_id_name, 0);
		}

		$getFieldStoreId = md5(__METHOD__ . "::$fieldgroup_id");
		if (!isset(self::$cache[$getFieldStoreId]))
		{
			$item = $item_type = '';
			if ($fieldgroup_id > 1)
			{
				if ($params->get($field_ordering_type_name, 0))
				{
					if ($cat_id)
					{
						$configs = JUComment::getConfigs($component, $section, $cat_id);
						if ($configs && isset($configs->$field_ordering_type_name) && $configs->$field_ordering_type_name
							&& isset($configs->$fieldgroup_id_name) && $configs->$fieldgroup_id_name == $fieldgroup_id
						)
						{
							$item = $component;
							$item .= $section ? '.' . $section : '';
							$item .= '.' . $cat_id;
							$item_type = 'integration.' . $type;
						}
					}

					if (!$item && $section)
					{
						$configs = JUComment::getConfigs($component, $section);
						if ($configs && isset($configs->$field_ordering_type_name) && $configs->$field_ordering_type_name
							&& isset($configs->$fieldgroup_id_name) && $configs->$fieldgroup_id_name == $fieldgroup_id
						)
						{
							$item      = $component . '.' . $section;
							$item_type = 'integration.' . $type;
						}
					}

					if (!$item)
					{
						$configs = JUComment::getConfigs($component);
						if ($configs && isset($configs->$field_ordering_type_name) && $configs->$field_ordering_type_name
							&& isset($configs->$fieldgroup_id_name) && $configs->$fieldgroup_id_name == $fieldgroup_id
						)
						{
							$item      = $component;
							$item_type = 'integration.' . $type;
						}
					}

					if (!$item)
					{
						JUComment::import('helper', 'field');
						$field_group = JUCommentFieldHelper::getFieldGroupById($fieldgroup_id);
						if ($field_group->field_ordering_type == 1)
						{
							$item      = $fieldgroup_id;
							$item_type = 'fieldgroup';
						}
					}
				}
			}
			else
			{
				if ($params->get($field_ordering_type_name, 0))
				{
					if ($cat_id)
					{
						$configs = JUComment::getConfigs($component, $section, $cat_id);
						if ($configs && isset($configs->$field_ordering_type_name) && $configs->$field_ordering_type_name
							&& (!isset($configs->$fieldgroup_id_name) || (isset($configs->$fieldgroup_id_name) && $configs->$fieldgroup_id_name <= 1))
						)
						{
							$item = $component;
							$item .= $section ? '.' . $section : '';
							$item .= '.' . $cat_id;
							$item_type = 'integration.' . $type;
						}
					}

					if (!$item && $section)
					{
						$configs = JUComment::getConfigs($component, $section);
						if ($configs && isset($configs->$field_ordering_type_name) && $configs->$field_ordering_type_name
							&& (!isset($configs->$fieldgroup_id_name) || (isset($configs->$fieldgroup_id_name) && $configs->$fieldgroup_id_name <= 1))
						)
						{
							$item      = $component . '.' . $cat_id;
							$item_type = 'integration.' . $type;
						}
					}

					if (!$item)
					{
						$configs = JUComment::getConfigs($component);
						if ($configs && isset($configs->$field_ordering_type_name) && $configs->$field_ordering_type_name
							&& (!isset($configs->$fieldgroup_id_name) || (isset($configs->$fieldgroup_id_name) && $configs->$fieldgroup_id_name <= 1))
						)
						{
							$item      = $component;
							$item_type = 'integration.' . $type;
						}
					}
				}
			}

			self::$cache[$getFieldStoreId]              = array();
			self::$cache[$getFieldStoreId]['item']      = $item;
			self::$cache[$getFieldStoreId]['item_type'] = $item_type;
		}

		$item      = self::$cache[$getFieldStoreId]['item'];
		$item_type = self::$cache[$getFieldStoreId]['item_type'];

		
		$fieldsStoreId = md5(__METHOD__ . "::fieldsObj::$item::$item_type::" . serialize($includedOnlyFields) . serialize($ignoredFields) . "::" . serialize($additionFields));
		if (!isset(self::$cache[$fieldsStoreId]))
		{
			$db          = JFactory::getDbo();
			$date        = JFactory::getDate();
			$user        = JFactory::getUser();
			$accessLevel = implode(',', $user->getAuthorisedViewLevels());

			$query = $db->getQuery(true);
			$query->select("field.*, plg.folder");
			$query->from("#__jucomment_fields AS field");
			$query->join("", "#__jucomment_plugins AS plg ON field.plugin_id = plg.id");
			if ($item)
			{
				$query->select("fordering.ordering");
				$query->join("LEFT", "#__jucomment_fields_ordering AS fordering ON (fordering.field_id = field.id AND fordering.item = " . $db->quote($item) . " AND fordering.type = '$item_type')");
				$query->order("fordering.ordering");
			}
			$query->join("", "#__jucomment_fields_groups AS fg ON (fg.id = field.group_id)");
			$query->where('fg.access IN (' . $accessLevel . ')');

			
			$nullDate = $db->quote($db->getNullDate());
			$nowDate  = $db->quote($date->toSql());
			$query->where('field.published = 1');
			$query->where('field.publish_up <= ' . $nowDate);
			$query->where('(field.publish_down = ' . $nullDate . ' OR field.publish_down > ' . $nowDate . ')');

			
			$query->where('field.access IN (' . $accessLevel . ')');

			
			if ($fieldgroup_id > 1)
			{
				$query->where("(field.group_id = 1 OR field.group_id = " . (int) $fieldgroup_id . ")");
			}
			
			else
			{
				$query->where("field.group_id = 1");
			}

			
			$additionFieldsStr = "";
			if ($additionFields)
			{
				$additionFieldIds = $additionFieldNames = array();
				foreach ($additionFields AS $additionField)
				{
					if ($additionField && is_numeric($additionField))
					{
						$additionFieldIds[$additionField] = $additionField;
					}
					elseif ($additionField)
					{
						$additionFieldNames[$additionField] = $additionField;
					}
				}

				if ($additionFieldIds)
				{
					$additionFieldsStr .= " OR field.id IN (" . implode(",", $additionFieldIds) . ")";
				}

				if ($additionFieldNames)
				{
					$additionFieldsStr .= " OR field.field_name IN ('" . implode("','", $additionFieldNames) . "')";
				}
			}

			
			$app         = JFactory::getApplication();
			$languageTag = JFactory::getLanguage()->getTag();
			if ($app->getLanguageFilter())
			{
				$query->where("(field.language IN (" . $db->quote($languageTag) . "," . $db->quote('*') . "," . $db->quote('') . ")" . $additionFieldsStr . ")");
			}

			
			if ($ignoredFields)
			{
				$ignoreFieldIds = $ignoreFieldNames = array();
				foreach ($ignoredFields AS $ignoredField)
				{
					if ($ignoredField && is_numeric($ignoredField))
					{
						$ignoreFieldIds[$ignoredField] = $ignoredField;
					}
					elseif ($ignoredField)
					{
						$ignoreFieldNames[$ignoredField] = $ignoredField;
					}
				}

				if ($ignoreFieldIds)
				{
					$query->where("field.id NOT IN (" . implode(",", $ignoreFieldIds) . ")");
				}

				if ($ignoreFieldNames)
				{
					$query->where("field.field_name NOT IN ('" . implode("','", $ignoreFieldNames) . "')");
				}
			}

			
			if ($includedOnlyFields)
			{
				$includedFieldIds = $includedFieldNames = array();
				foreach ($includedOnlyFields AS $includedField)
				{
					if ($includedField && is_numeric($includedField))
					{
						$includedFieldIds[$includedField] = $includedField;
					}
					elseif ($includedField)
					{
						$includedFieldNames[$includedField] = $includedField;
					}
				}

				if ($includedFieldIds)
				{
					$query->where("field.id IN (" . implode(",", $includedFieldIds) . ")");
				}

				if ($includedFieldNames)
				{
					$query->where("field.field_name IN ('" . implode("','", $includedFieldNames) . "')");
				}
			}

			$query->group('field.id');
			
			$query->order("fg.ordering, field.ordering");
			$db->setQuery($query);
			$fields = $db->loadObjectList();

			
			$newFields = array();
			foreach ($fields AS $key => $field)
			{
				
				if (isset($field->ordering) && is_null($field->ordering))
				{
					$newFields[] = $field;
					unset($fields[$key]);
				}
			}
			
			if (!empty($newFields))
			{
				$fields = array_merge($fields, $newFields);
			}

			self::$cache[$fieldsStoreId] = $fields;
		}

		$fields = self::$cache[$fieldsStoreId];

		
		if (!$fields)
		{
			return false;
		}

		$fieldObjectList = array();
		if (count($fields))
		{
			foreach ($fields as $_field)
			{
				$field = clone $_field;

				
				if ($field->field_name != "")
				{
					$newKey = $field->field_name;
				}
				else
				{
					$newKey = $field->id;
				}

				$fieldObjectList[$newKey] = self::getField($field, $comment);

				unset($field);
			}
		}

		return $fieldObjectList;
	}

	
	public static function mergeFieldOptions($global_display_params, $document_display_params)
	{
		$fields = new stdClass();

		if (isset($global_display_params->fields))
		{
			$fields = $global_display_params->fields;

			
			foreach ($fields AS $fieldKey => $fieldOptions)
			{
				foreach ($fieldOptions AS $fieldOptionKey => $fieldOptionValue)
				{
					
					if (isset($document_display_params->fields->$fieldKey->$fieldOptionKey) && $document_display_params->fields->$fieldKey->$fieldOptionKey != '-2')
					{
						$fields->$fieldKey->$fieldOptionKey = $document_display_params->fields->$fieldKey->$fieldOptionKey;
					}
				}
			}
		}

		$app              = JFactory::getApplication();
		$activeMenuParams = new JRegistry;

		if ($menu = $app->getMenu()->getActive())
		{
			$activeMenuParams->loadString($menu->params);
		}

		$activeMenuObj = $activeMenuParams->toObject();

		if (isset($activeMenuObj->comment->fields))
		{
			
			foreach ($activeMenuObj->comment->fields as $fieldKey => $fieldOptions)
			{
				foreach ($fieldOptions as $fieldOptionKey => $fieldOptionValue)
				{
					
					if ($fieldOptionValue !== null && $fieldOptionValue !== '')
					{
						$fields->$fieldKey->$fieldOptionKey = $fieldOptionValue;
					}
				}
			}
		}

		
		$global_display_params->fields = $fields;

		$registry = new JRegistry($global_display_params);

		return $registry;
	}

	
	public static function getFrontEndOrdering($fieldGroupId = 1)
	{
		$db       = JFactory::getDbo();
		$nullDate = $db->quote($db->getNullDate());
		$nowDate  = $db->quote(JFactory::getDate()->toSql());
		$query    = $db->getQuery(true);
		
		
		$query->select('field.caption, field.id, field.field_name,field.group_id');
		$query->from('#__jucomment_fields AS field');
		$query->join('', '#__jucomment_plugins AS plg ON plg.id = field.plugin_id');
		$query->join('', '#__jucomment_fields_groups AS field_group ON field_group.id = field.group_id');
		$query->where('field.frontend_ordering = 1');
		$query->where('field.published = 1');
		$query->where('field.publish_up <= ' . $nowDate);
		$query->where('(field.publish_down = ' . $nullDate . ' OR field.publish_down >= ' . $nowDate . ')');
		$query->where('field_group.published = 1');
		if ($fieldGroupId > 1)
		{
			$query->where('field_group.id IN (1, ' . (int) $fieldGroupId . ')');
		}
		else
		{
			$query->where('field_group.id = 1');
		}
		$query->group('field.id');
		$query->order('field.group_id ASC, field.ordering ASC');
		$db->setQuery($query);
		$fields            = $db->loadObjectList();
		$fieldOrdering     = array();
		$fieldOrdering[""] = JText::_('COM_JUCOMMENT_DEFAULT');
		if (count($fields) > 0)
		{
			foreach ($fields AS $field)
			{
				
				if ($field->field_name != "")
				{
					$fieldOrdering[$field->field_name] = JText::_($field->caption);
				}
				else
				{
					$fieldOrdering[$field->id] = JText::_($field->caption);
				}
			}
		}

		return $fieldOrdering;
	}

	
	public static function getFrontEndDirection()
	{
		$orderDirArray         = array();
		$orderDirArray['ASC']  = JText::_('COM_JUCOMMENT_ASC');
		$orderDirArray['DESC'] = JText::_('COM_JUCOMMENT_DESC');

		return $orderDirArray;
	}

	
	public static function deleteFieldValues($commentId)
	{
		
		$commentObj = JUComment::getComment($commentId);
		if ($commentObj)
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select("field.*, plg.folder");
			$query->from("#__jucomment_fields AS field");
			$query->join("", "#__jucomment_plugins AS plg ON field.plugin_id = plg.id");
			$query->join("", "#__jucomment_fields_groups AS fg ON fg.id = field.group_id");
			if ($commentObj->fieldgroup_id > 1)
			{
				$query->where("fg.id IN (1, " . $commentObj->fieldgroup_id . ")");
			}
			else
			{
				$query->where("fg.id = 1");
			}

			$db->setQuery($query);
			$fields = $db->loadObjectList();
			foreach ($fields AS $field)
			{
				
				$fieldClass = JUCommentFieldHelper::getField($field, $commentId);
				$fieldClass->onDelete();
			}
		}
	}
}