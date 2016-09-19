<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_articles_popular
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

abstract class modMapGrHelper
{
	static public function getData($params, $repeatBlock) {
		$data = array();
        if ($params->get($repeatBlock)) {
            foreach (json_decode($params->get($repeatBlock)) as $nameFields=>$fields) {
                foreach ($fields as $key=>$field) {
                    $data[$key][$nameFields] = $field;
                }
            }        
        }

		return $data;
	}
}

