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

$document->addScript(JURI::base().'modules/'.$module->module.'/script.js');

if ($params->get('captcha_on')) {
	$document->addScript("https://www.google.com/recaptcha/api.js");
}	

$fields = modFormGrHelper::getFields($params);

$existRequire = modFormGrHelper::checkRequire($fields);

$quantityFields = count($fields);

$qtyFieldsInFirstBlock = round(count($fields)/2);

$json = array();
$json['id'] = $module->id;
$json['fields'] = $fields;
$json['mailHead'] = $params->get('mail_head');
$json['recipient'] = $params->get('recipient');
$json['quantityFields'] = $quantityFields;
$json['captchaSecretKey'] = $params->get('captcha_secret_key');
$json['captchaOn'] = $params->get('captcha_on');
$json = json_encode($json);

?>

<?php require JModuleHelper::getLayoutPath('mod_form_gr', $params->get('layout', 'default')); ?>