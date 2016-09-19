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


class JUCommentComponentsHelper
{
	
	public function getAvailableComponents()
	{
		static $components = array();

		if( empty( $components ) )
		{
			
			$folders	= JFolder::folders( JPATH_ROOT . DIRECTORY_SEPARATOR . 'components', 'com_', false, false, array( '.svn', 'CVS', '.DS_Store', '__MACOSX', 'com_jucommen' ) );

			foreach( $folders as $folder )
			{
				if( JFile::exists( JPATH_ROOT . DIRECTORY_SEPARATOR . 'components'  . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . 'jucommen_plugin.php' ) )
				{
					$components[$folder]	= $folder;
				}
			}

			
			foreach( $folders as $folder )
			{
				if( JFile::exists( JUCOMMENT_ROOT . DIRECTORY_SEPARATOR . 'jucomment_plugins' . DIRECTORY_SEPARATOR . $folder . '.php' ) )
				{
					$components[$folder]	= $folder;
				}
			}


			
			$components = array_unique($components);

			
			foreach( $components as $key => $component )
			{
				if( !JComponentHelper::isEnabled( $component ) )
				{
					unset( $components[$key] );
				}
			}
		}

		return $components;
	}

	
	public static function isInstalled( $optionName )
	{
		self::_clean( $optionName );
		$componentName = substr($optionName, 4);

		if( $componentName && ( JFile::exists( JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.$optionName.DIRECTORY_SEPARATOR.'admin.'.$componentName.'.php') || JFile::exists( JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.$optionName.DIRECTORY_SEPARATOR.$componentName.'.php' ) ) )
		{
			return true;
		}
	}

	
	public static function isEnabled( $componentName )
	{
		self::_clean( $componentName );

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select( 'enabled' )
			->from( '#__extensions' )
			->where( 'type = "component"')
			->where( 'element = "'.$componentName.'"');

		$db->setQuery($query);

		return $db->loadResult();
	}

	private static function _clean( &$componentName )
	{
		$componentName	= preg_replace('/[^A-Z0-9_\.-]/i', '', $componentName);
	}

	public function getSupportedComponents()
	{
		static $supported = array();

		if( empty( $supported ) )
		{
			$files = JFolder::files( JUCOMMENT_PLUGINS, 'com_', false, false, array( '.svn', 'CVS', '.DS_Store', '__MACOSX', 'com_sample.php', 'com_sampletemplate' ) );

			foreach( $files as $file )
			{
				
				$tmp = explode( '.', $file );

				$supported[] = array_shift( $tmp );
			}
		}

		return $supported;
	}
}
