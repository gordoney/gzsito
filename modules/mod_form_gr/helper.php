<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_articles_popular
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

abstract class modFormGrHelper
{
	static public function getFields($params) {
		$fields = array();
		foreach (json_decode($params->get('field')) as $nameFields=>$valuefields) {
			foreach ($valuefields as $key=>$field) {
				$fields[$key][$nameFields] = $field;
			}
		}

		return $fields;
	}
	
	static public function checkRequire($fields) {
		foreach ($fields as $field) {
			if ($field['required'] == 1) {
				return true;
			}
		}
		return false;
	}	
}

