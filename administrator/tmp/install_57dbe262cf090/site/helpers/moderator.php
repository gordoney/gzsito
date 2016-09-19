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

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

class JUCommentModeratorHelper
{
	
	protected static $cache = array();

	
	public static function isModerator()
	{
		$storeId = md5(__METHOD__);
		if (!isset(self::$cache[$storeId]))
		{
			$user = JFactory::getUser();

			
			if ($user->authorise('core.admin', 'com_jucomment'))
			{
				self::$cache[$storeId] = true;

				return self::$cache[$storeId];
			}

			
			if ($user->get('guest'))
			{
				self::$cache[$storeId] = false;

				return self::$cache[$storeId];
			}

			
			$return = self::getModerator();
			if($return){
				self::$cache[$storeId] = true;
			}else{
				self::$cache[$storeId] = false;
			}
		}

		return self::$cache[$storeId];
	}

	
	public static function getModerator($component = '')
	{
		$user = JFactory::getUser();

		if(!$user->id){
			return false;
		}

		if(!$component){
			$component = JUComment::getCurrent('component');
		}

		$storeId = md5(__METHOD__ . "::$component::".$user->id);

		if (!isset(self::$cache[$storeId]))
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('m.*');
			$query->from('#__jucomment_moderators AS m');
			$query->join('', '#__jucomment_moderators_xref AS mxref ON mxref.mod_id = m.id');
			$query->where('m.published = 1');

			$nullDate = $db->quote($db->getNullDate());
			$nowDate  = $db->quote(JFactory::getDate()->toSql());

			$query->where('(m.publish_up = ' . $nullDate . ' OR m.publish_up <= ' . $nowDate . ')');
			$query->where('(m.publish_down = ' . $nullDate . ' OR m.publish_down >= ' . $nowDate . ')');

			$query->where('m.user_id = ' . $user->id);
			$query->where('mxref.component = "*" OR mxref.component = "'.$component.'"');
			$db->setQuery($query);

			$modObj = $db->loadObject();

			if(!$modObj){
				$modObj = false;
			}

			self::$cache[$storeId] = $modObj;
		}

		return self::$cache[$storeId];
	}

	
	public static function allow( $action, $comment = null, $component = ''){

		$user = JFactory::getUser();

		if($user->authorise('core.admin', 'com_jucomment')){
			return true;
		}

		if(is_object($comment)){
			$component = $comment->component;
		}

		
		if(empty($component)){
			$component = JUComment::getCurrent('component');
		}

		$moderator = self::getModerator($component);
		if(!$moderator || !isset($moderator->$action)){
			return false;
		}

		return $moderator->$action;
	}
}