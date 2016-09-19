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

class JUCommentParamsHelper extends JRegistry
{
	public $component;
	public $section;
	public $cat_id;

	public static $cache;

	public function __construct($data = null)
	{
		parent::__construct();

		$this->component = '';
		$this->section   = '';
		$this->cat_id    = '';

		$this->getGlobalConfig();
		$component = isset($data[0]) ? $data[0] : '';
		$section   = isset($data[1]) ? $data[1] : '';
		$cat_id    = isset($data[2]) ? $data[2] : '';

		if ($component || $section || $cat_id)
		{
			$this->loadParams($component, $section, $cat_id);
		}
	}

	public function getGlobalConfig()
	{
		$storeId = md5(__METHOD__);
		if (!isset(self::$cache[$storeId]))
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('global_configs');
			$query->from('#__jucomment_integrations');
			$query->where('component = ""');
			$query->where('section = 0');
			$query->where('cat_id = 0');
			$db->setQuery($query);
			$global_configs = $db->loadResult();

			self::$cache[$storeId] = $global_configs;
		}

		$this->loadString(self::$cache[$storeId]);
	}

	public function loadParams($component = '', $section = '', $cat_id = '')
	{
		$db = JFactory::getDbo();

		if ($component && $component != $this->component)
		{
			$query = $db->getQuery(true);
			$query->select('global_configs');
			$query->select('component_configs');
			$query->from('#__jucomment_integrations');
			$query->where('component = ' . $db->quote($component));
			$query->where('section = 0');
			$query->where('cat_id = 0');
			$db->setQuery($query);
			$component_config = $db->loadObject();
			if ($component_config)
			{
				if ($this->component)
				{
					$this->resetParams();
				}

				$this->component = $component;

				$this->loadString($component_config->global_configs);
				$this->loadString($component_config->component_configs);
			}
		}

		if ($component && $section && $this->section != $section)
		{
			$query = $db->getQuery(true);
			$query->select('global_configs');
			$query->select('component_configs');
			$query->select('section_configs');
			$query->from('#__jucomment_integrations');
			$query->where('component = ' . $db->quote($component));
			$query->where('section = ' . $db->quote($section));
			$query->where('cat_id = 0');
			$db->setQuery($query);
			$section_config = $db->loadObject();
			if ($section_config)
			{
				$this->section = $section;

				$this->loadString($section_config->global_configs);
				$this->loadString($section_config->component_configs);
				$this->loadString($section_config->section_configs);
			}
		}

		$cat_id = (int) $cat_id;
		if ($this->component && $cat_id && $this->cat_id != $cat_id)
		{
			$query = $db->getQuery(true);
			$query->select('global_configs');
			$query->select('component_configs');
			$query->select('section_configs');
			$query->select('cat_configs');
			$query->from('#__jucomment_integrations');
			$query->where('component = ' . $db->quote($component));
			$query->where('section = ' . $db->quote($section));
			$query->where('cat_id = ' . $cat_id);
			$db->setQuery($query);
			$cat_config = $db->loadObject();
			if ($cat_config)
			{
				$this->cat_id = $cat_id;

				$this->loadString($cat_config->global_configs);
				$this->loadString($cat_config->component_configs);
				$this->loadString($cat_config->section_configs);
				$this->loadString($cat_config->cat_configs);
			}
		}
	}

	public function resetParams()
	{
		$this->__construct();
	}
}
