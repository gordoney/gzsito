<?php
/**
 * @package     Joomla.Tutorials
 * @subpackage  Module
 * @copyright   (C) 2015 gordoney
 * @license     License GNU General Public License version 2 or later; see LICENSE.txt
 */
  
// No direct access.

if ($_POST['captcha']) {

	$response=file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$_POST['captchaSecretKey']."&response=".$_POST['g-recaptcha-response']."&remoteip=".$_SERVER['REMOTE_ADDR']);

}	
if ($response.success||(!$_POST['captcha'])) { 	

	define('_JEXEC', 1);

	define('JPATH_BASE', dirname(__FILE__) . '/../..' );

	define('DS', DIRECTORY_SEPARATOR);
	require_once(JPATH_BASE.DS.'includes'.DS.'defines.php');
	require_once(JPATH_BASE.DS.'includes'.DS.'framework.php');

	JFactory::getApplication('site')->initialise();
		
	$mailer = JFactory::getMailer();
		
	$config = JFactory::getConfig();
	$sender = $config->get('mailfrom');
	$mailer->setSender($sender);
	$mailer->addRecipient($_POST['recipient']);
		
	for ($i=0; $i < $_POST['quantityFields']; $i++) {
		$body  .=  $_POST['namefield'.$i].': '.$_POST['field'.$i];	
		$body  .=   "\r\n";
	}

	if(is_uploaded_file($_FILES["file"]["tmp_name"])) {
		move_uploaded_file($_FILES["file"]["tmp_name"], $_SERVER['DOCUMENT_ROOT'].'/images/mail/'.$_FILES["file"]["name"]);
		$mailer->addAttachment($_SERVER['DOCUMENT_ROOT'].'/images/mail/'.$_FILES["file"]["name"]);	
	}
	
	$mailer->setSubject($_POST['mailSubject']);
	$mailer->setBody($body);
				
	if ($mailer->Send()){
		echo $status= 'success';
	}		

}


