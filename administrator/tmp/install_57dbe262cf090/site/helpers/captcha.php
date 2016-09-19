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

class JUCommentCaptchaHelper extends JObject
{
	static $instance = null;

	
	public function getInstance()
	{
		if (is_null(self::$instance))
		{
			$params	= JUComment::getParams();

			if( $params->get( 'antispam_captcha_enable', 1 ) == 1 )
			{
				$file = JUCOMMENT_CLASSES . '/captcha.php';

				if (!JFile::exists($file))
				{
					self::$instance = false;

					return false;
				}

				require_once($file);

				self::$instance = new JUCommentCaptcha();
			}
		}

		return self::$instance;
	}

	
	public function getHTML($options = array())
	{
		return self::$instance->getHTML($options);
	}

	
	public function verify( $data, $params = array() )
	{
		return self::$instance->verify( $data );
	}

	
	public function getError( $i = null, $toString = true )
	{
		return self::$instance->getError();
	}

	
	public function getReloadSyntax()
	{
		return self::$instance->getReloadSyntax();
	}

	public static function show(){
		$app = JFactory::getApplication();
		$id			= $app->input->get( 'captcha_id' , '' );
		$captchaTable	= JUComment::getTable( 'Captcha');

		if( ob_get_length() !== false )
		{
			while (@ ob_end_clean());
			if( function_exists( 'ob_clean' ) )
			{
				@ob_clean();
			}
		}

		
		$captchaTable->clear();

		
		$captchaTable->load( $id );

		if( !$captchaTable->id )
		{
			return false;
		}

		require_once JUCOMMENT_CLASSES.'/securimage/securimage.php';

		$params  = JUComment::getParams();
		$options = array();
		$options['image_width'] = $params->get('captcha_width', '155');
		$options['image_height'] = $params->get('captcha_height', '50');
		$options['font_ratio'] = null;
		
		$options['image_bg_color'] = new Securimage_Color($params->get('captcha_bg_color', '#ffffff'));
		$options['text_color'] = new Securimage_Color($params->get('captcha_color', '#050505'));
		$options['line_color'] = new Securimage_Color($params->get('captcha_line_color', '#707070'));
		$options['noise_color'] = new Securimage_Color($params->get('captcha_noise_color', '#707070'));
		$options['use_transparent_text'] = true;
		$options['text_transparency_percentage'] = 20;
		$options['code_length'] = $params->get('captcha_length', '6');
		$options['case_sensitive'] = true;
		$options['charset'] = 'ABCDEFGHKLMNPRSTUVWYZabcdefghklmnprstuvwyz23456789';
		$options['perturbation'] = $params->get('captcha_perturbation', '5') / 10;
		$options['num_lines'] = $params->get('captcha_num_lines', '3');
		$options['noise_level'] = $params->get('captcha_noise_level', '2');
		$options['image_signature'] = '';
		$options['signature_color'] = new Securimage_Color('#707070');
		$options['signature_font'] = null;
		
		$options['ttf_file'] = 'components/com_jucomment/classes/securimage/fonts/' . $params->get('captcha_font', 'AHGBold.ttf');
		$options['use_wordlist']                 = false;
		$options['wordlist_file']                = null;
		$options['background_directory']         = null;
		$options['use_database'] = false;
		$options['no_session'] = false;
		$options['no_exit'] = true;

		$secureImage = new Securimage($options);
		$secureImage->show();
		$captchaTable->response = $secureImage->getCode(false, true);
		
		$captchaTable->store();

		exit;
		
	}
}
