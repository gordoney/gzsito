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

class JUCommentDocumentHelper
{
	static $loaded;

	
	public static function loadHeaders()
	{
		if( !self::$loaded )
		{
			$document	= JFactory::getDocument();
			$params = JUComment::getParams();

			if( $document->getType() != 'html' )
			{
				return true;
			}

			
			
			self::addTemplateCss( 'common.css' );
			

			if( $params->get( 'layout_inherit_default_css', 1 ) )
			{
				$document->addStylesheet( JUri::root(true) . '/components/com_jucomment/templates/default/assets/css/style.css' );
			}

			
			
			if( $document->direction == 'rtl' || JFactory::getApplication()->input->get( 'forcertl' ) == 1 )
			{
				$document->addStylesheet( JUri::root(true) . '/components/com_jucomment/templates/default/assets/css/style-rtl.css' );
			}

			
			$document->addStyleSheet(JUri::root(true) . '/components/com_jucomment/assets/css/font-awesome.min.css');

			JHtml::_('jquery.framework');
			$script = '
						var jucomment = {};
						jucomment.component = "'.JUComment::getCurrent('component').'";
						jucomment.section = "'.JUComment::getCurrent('section').'";
						jucomment.cat_id = "'.JUComment::getCurrent('cat_id').'";
						jucomment.cid = "'.JUComment::getCurrent('cid').'";
						jucomment.token = "'.JSession::getFormToken().'";';
			$document->addScriptDeclaration($script);
			$document->addScript( JUri::root(true) . '/components/com_jucomment/assets/js/jucomment.js' );
			$document->addScript( JUri::root(true) . '/components/com_jucomment/assets/js/handlebars.min.js' );
			$document->addScript(JUri::root(true).'/components/com_jucomment/assets/jqueryvalidation/jquery.validate.min.js');
			$document->addScript(JUri::root(true).'/components/com_jucomment/assets/jqueryvalidation/additional-methods.min.js');

			$document->addStyleSheet(JUri::root(true) . "/components/com_jucomment/assets/fancybox/css/jquery.fancybox.css");
			$document->addStyleSheet(JUri::root(true) . "/components/com_jucomment/assets/fancybox/css/jquery.fancybox-thumbs.css");
			$document->addStyleSheet(JUri::root(true) . "/components/com_jucomment/assets/fancybox/css/jquery.fancybox-buttons.css");

			$document->addScript(JUri::root(true) . "/components/com_jucomment/assets/fancybox/js/jquery.mousewheel-3.0.6.pack.js");
			$document->addScript(JUri::root(true) . "/components/com_jucomment/assets/fancybox/js/jquery.fancybox.pack.js");
			$document->addScript(JUri::root(true) . "/components/com_jucomment/assets/fancybox/js/jquery.fancybox-thumbs.js");
			$document->addScript(JUri::root(true) . "/components/com_jucomment/assets/fancybox/js/jquery.fancybox-buttons.js");
			$document->addScript(JUri::root(true) . "/components/com_jucomment/assets/fancybox/js/jquery.fancybox-media.js");

			self::load('style', 'css', 'themes');

			
			

			
			
			

			self::$loaded		= true;
		}
		return self::$loaded;
	}

	
	public static function load( $list, $type='js', $location='themes' )
	{
		$mainframe	= JFactory::getApplication();
		$document	= JFactory::getDocument();
		$params		= JUComment::getParams();
		$JUCMApplication		= JUComment::loadApplication();

		
		

		$files		= explode( ',', $list );
		$dir		= JUri::root(true) . '/components/com_jucomment/assets';
		$pathdir	= JUCOMMENT_ASSETS;
		$template = JUComment::getTemplate();
		$version	= str_ireplace( '.' , '' , JUComment::getJoomlaVersion() );

		if ( $location != 'assets' )
		{
			$dir	= JUri::root(true) . '/components/com_jucomment/templates/' . $template->getName().'/assets';
			$pathdir = JUCOMMENT_TEMPLATES . '/' . $template->getName().'/assets';
		}

		foreach( $files as $file )
		{
			if ( $type == 'js' )
			{
				$file .= '.js?' . $version;
			}
			elseif ( $type == 'css' )
			{
				$file .= '.css';
			}

			$path = '';
			if( $location == 'themes' )
			{
				$checkOverride	= JPATH_ROOT . '/templates/' .  $mainframe->getTemplate() . '/html/com_jucomment/assets/' . $type . '/' . $file;
				$checkComponent	= $JUCMApplication->getComponentTemplatePath() . '/assets/' . $type . '/' . $file;
				$checkSelected	= JUCOMMENT_TEMPLATES . '/' . $template->getName() . '/assets/' . $type . '/' . $file;
				$checkDefault	= JUCOMMENT_TEMPLATES . '/default/assets/' . $type . '/' . $file;

				$overridePath	= JUri::root(true) . '/templates/' . $mainframe->getTemplate() . '/html/com_jucomment/assets/' . $type . '/' . $file;
				$componentPath	= $JUCMApplication->getComponentTemplateURI() . '/assets/' . $type . '/' . $file;
				$selectedPath	= $dir . '/' . $type . '/' . $file;
				$defaultPath	= JUri::root(true) . '/components/com_jucomment/templates/default/assets/' . $type . '/' . $file;

				
				if( JFile::exists( $checkOverride ) )
				{
					$path = $overridePath;
					$pathdir = $checkOverride;
				}
				
				elseif( JFile::exists( $checkSelected ) )
				{
					$path = $selectedPath;
					$pathdir = $checkSelected;
				}
				
				else
				{
					$path = $defaultPath;
					$pathdir = $checkDefault;
				}
			}
			else
			{
				$path = $dir . '/' . $type . '/' . $file;
				$pathdir = $pathdir . '/' . $type . '/' . $file;
			}

			if ( $type == 'js' )
			{
				$document->addScript( $path );
			}
			elseif ( $type == 'css' )
			{
				if( JFile::exists($pathdir) )
				{
					$document->addStylesheet( $path );
				}
			}
		}
	}

	
	public static function addTemplateCss( $fileName )
	{
		$document		= JFactory::getDocument();
		$document->addStyleSheet( rtrim(JUri::root(), '/') . '/components/com_jucomment/assets/css/' . $fileName );

		$mainframe		= JFactory::getApplication();
		$templatePath	= JPATH_ROOT . '/templates/' . $mainframe->getTemplate() . '/html/com_jucomment/assets/css/' . $fileName;

		if( JFile::exists($templatePath) )
		{
			$document->addStyleSheet( rtrim(JUri::root(), '/') . '/templates/' . $mainframe->getTemplate() . '/html/com_jucomment/assets/css/' . $fileName );

			return true;
		}

		return false;
	}
}
