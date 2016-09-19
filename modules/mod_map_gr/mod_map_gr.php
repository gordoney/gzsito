<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_footer
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die; 

require_once dirname(__FILE__).'/helper.php';

$document = JFactory::getDocument();

$document->addScript("https://api-maps.yandex.ru/2.1/?lang=ru_RU");
$document->addScript(JURI::base().'modules/'.$module->module.'/script.js');

$markers = modMapGrHelper::getData($params, 'marker');
$phones = modMapGrHelper::getData($params, 'phone');

$json = array();
$json['id'] = $module->id;
$json['mapx'] = $params->get('mapx');
$json['mapy'] = $params->get('mapy');
$json['mapzoom'] = $params->get('mapzoom');
$json['markers'] = $markers;
$json = json_encode($json);

require JModuleHelper::getLayoutPath('mod_map_gr', $params->get('layout', 'default'));

?>
