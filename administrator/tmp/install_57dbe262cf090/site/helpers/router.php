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

jimport( 'joomla.filter.filteroutput');
jimport( 'joomla.application.router');

class JUCommentRouterHelper extends JRouter
{
	public static function _($url, $xhtml = true, $ssl = null , $search = false )
	{
		return JRoute::_($url, $xhtml, $ssl);
	}

	public function getFeedUrl($component = 'all', $cid = 'all', $userid = '')
	{
		$link = 'index.php?option=com_jucommento&view=rss&format=feed';

		if($component != 'all')
		{
			$link .= '&component=' . $component;
		}

		if($cid != 'all')
		{
			$link .= '&cid=' . $cid;
		}

		if($userid != '')
		{
			$link .= '&userid=' . $userid;
		}

		return self::_($link);
	}

	
	public static function emailLinkRouter($url, $xhtml = true, $ssl = null)
	{
		
		$app    = JFactory::getApplication('site');
		$router = $app->getRouter();

		
		if (!$router)
		{
			return null;
		}

		if ((strpos($url, '&') !== 0) && (strpos($url, 'index.php') !== 0))
		{
			return $url;
		}

		
		$uri = $router->build($url);

		$url = $uri->toString(array('path', 'query', 'fragment'));

		
		$url = preg_replace('/\s/u', '%20', $url);

		
		if ((int) $ssl)
		{
			$uri = JUri::getInstance();

			
			static $prefix;
			if (!$prefix)
			{
				$prefix = $uri->toString(array('host', 'port'));
			}

			
			$scheme = ((int) $ssl === 1) ? 'https' : 'http';

			
			if (!preg_match('#^/#', $url))
			{
				$url = '/' . $url;
			}

			
			$url = $scheme . '://' . $prefix . $url;
		}

		if ($xhtml)
		{
			$url = htmlspecialchars($url);
		}

		
		$url = str_replace('/administrator', '', $url);

		return $url;
	}
}
