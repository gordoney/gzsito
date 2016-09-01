<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_articles_popular
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

abstract class modSliderGrHelper
{
	static public function getSlides($params) {
		$slides = array();
		foreach (json_decode($params->get('slider')) as $nameFields=>$valuefields) {
			foreach ($valuefields as $key=>$field) {
				$slides[$key][$nameFields] = $field;
			}
		}

		return $slides;
	}
}

