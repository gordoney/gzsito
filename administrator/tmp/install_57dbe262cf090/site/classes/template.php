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

JUComment::import( 'helper', 'date' );

JUComment::import( 'helper', 'string' );
JUComment::import( 'helper', 'comment' );


class JUCommentTemplate
{
	
	public $vars			= null;
	private  $_system		= null;
	protected $_json		= null;

	
	protected $_template		= null;
	protected $_direction	= null;
	protected $_templateInfo	= array();

	public $params = null;

	
	public function __construct($style = null)
	{
		$template = null;
		if($style){
			$style = JUComment::getStyle($style);
			if($style){
				$template = JUCommentHelper::getPlugin($style->template_id);
			}
		}

		if(!$template){
			$params = JUComment::getParams(false, false, false);
			$style = JUComment::getStyle($params->get('default_style', 1));
			if($style){
				$template = JUCommentHelper::getPlugin($style->template_id);
			}
		}
		if(!$template){
			return false;
		}

		$templateParams = new JRegistry();
		if($style && $style->params){
			$templateParams->loadString($style->params);
		}

		$this->_template = $template->folder;
		$this->params = new JRegistry($templateParams->get($template->folder));

		if(!defined('JUCOMMENT_CLI'))
		{
			$this->init();
		}
	}

	public function init()
	{
		$obj			= new stdClass();
		$obj->params	= JUComment::getParams();
		$obj->my		= JUComment::getProfile();
		$obj->acl		= JUComment::getHelper( 'acl' );
		$obj->moderator	= JUComment::getHelper( 'moderator' );
		$obj->permission= JUComment::getHelper( 'permission' );
		
		$obj->session	= JFactory::getSession();

		$this->_system	= $obj;
	}

	public function getDirection()
	{
		if ($this->_direction === null)
		{
			$document	= JFactory::getDocument();
			$this->_direction	= $document->getDirection();
		}

		return $this->_direction;
	}

	public function getNouns( $text , $count , $includeCount = false )
	{
		return JUCommentStringHelper::getNoun( $text , $count , $includeCount );
	}

	public function chopString( $string , $length )
	{
		return JString::substr( $string , 0 , $length );
	}

	public function formatDate( $format , $dateString )
	{
		$date	= JUCommentDateHelper::dateWithOffSet($dateString);
		return $date->toFormat( $format );
	}

	
	public function set($name, $value)
	{
		$this->vars[$name] = $value;
	}

	public function getName()
	{
		return $this->_template;
	}

	
	public function fetch( $file )
	{
		static $tpl = array();

		if (empty($tpl[$file]))
		{
			$tpl[$file] = $this->resolve( $file );
		}

		$system = $this->_system;

		if( isset( $this->vars ) )
		{
			extract($this->vars);
		}

		ob_start();

		if( !JFile::exists( $tpl[$file] ) )
		{
			echo JText::sprintf( 'Invalid template file %1s' , $tpl[$file] );
		}
		else
		{
			include($tpl[$file]);
		}

		$html	= ob_get_contents();
		ob_end_clean();

		return $html;
	}

	public function resolve( $file )
	{
		if(defined('JUCOMMENT_CLI'))
		{
			$defaultPath	= JUCOMMENT_TEMPLATES . '/' . JUCOMMENT_THEME_BASE . '/' . $file;

			return $defaultPath;
		}

		$mainframe		= JFactory::getApplication();

		$params			= JUComment::getParams();

		

		$overridePath	= JPATH_ROOT . '/templates/' . $mainframe->getTemplate() . '/html/com_jucomment/' . $file;
		$componentPath	= JUComment::loadApplication()->getComponentTemplatePath() . '/' . $file;
		$selectedPath	= JUCOMMENT_TEMPLATES . '/' . $this->_template . '/' . $file;
		$defaultPath	= JUCOMMENT_TEMPLATES . '/' . JUCOMMENT_THEME_BASE . '/' . $file;

		
		if( $params->get( 'layout_template_override', true ) && JFile::exists( $overridePath ) )
		{
			$path	= $overridePath;
		}
		
		elseif( $params->get( 'layout_component_override', true ) && JFile::exists( $componentPath ) )
		{
			$path	= $componentPath;
		}
		
		elseif( JFile::exists( $selectedPath ) )
		{
			$path	= $selectedPath;
		}
		
		else
		{
			$path	= $defaultPath;
		}

		return $path;
	}

	public function json_encode( $value )
	{
		if ($this->_json === null)
		{
			include_once( JUCOMMENT_CLASSES . '/json.php' );
			$this->_json	= new Services_JSON();
		}

		return $this->_json->encode( $value );
	}

	public function json_decode( $value )
	{
		if ($this->_json === null)
		{
			include_once( JUCOMMENT_CLASSES . '/json.php' );
			$this->_json	= new Services_JSON();
		}

		return $this->_json->decode( $value );
	}

	public function escape( $val )
	{
		return JUCommentStringHelper::escape( $val );
	}
}
