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

jimport('joomla.utilities.utility');

class JUCommentLogHelper
{
	
	protected static $cache = array();

	
	public static function getPlatform($user_agent)
	{
		$os_platform = "";

		$os_array = array(
			'/windows nt 10/i'      => 'Windows 10',
			'/windows nt 6.3/i'     => 'Windows 8.1',
			'/windows nt 6.2/i'     => 'Windows 8',
			'/windows nt 6.1/i'     => 'Windows 7',
			'/windows nt 6.0/i'     => 'Windows Vista',
			'/windows nt 5.2/i'     => 'Windows Server 2003/XP x64',
			'/windows nt 5.1/i'     => 'Windows XP',
			'/windows xp/i'         => 'Windows XP',
			'/windows nt 5.0/i'     => 'Windows 2000',
			'/windows me/i'         => 'Windows ME',
			'/win98/i'              => 'Windows 98',
			'/win95/i'              => 'Windows 95',
			'/win16/i'              => 'Windows 3.11',
			'/macintosh|mac os x/i' => 'Mac OS X',
			'/mac_powerpc/i'        => 'Mac OS 9',
			'/linux/i'              => 'Linux',
			'/ubuntu/i'             => 'Ubuntu',
			'/iphone/i'             => 'iPhone',
			'/ipod/i'               => 'iPod',
			'/ipad/i'               => 'iPad',
			'/android/i'            => 'Android',
			'/blackberry/i'         => 'BlackBerry',
			'/webos/i'              => 'Mobile'
		);

		foreach ($os_array AS $regex => $value)
		{
			if (preg_match($regex, $user_agent))
			{
				$os_platform = $value;
			}
		}

		return $os_platform;
	}

	
	public static function loggable($event)
	{
		$params               = JUComment::getParams();
		$logged_events        = $params->get('logged_events', array());
		$log_events_for_guest = $params->get('log_events_for_guest', 0);
		$log_events_in = $params->get('log_events_in', 1);

        if(strpos($event, 'migration.') !== false)
        {
            return true;
        }

		$app = JFactory::getApplication();
		if($log_events_in == 1 && !$app->isSite())
		{
			return false;
		}
		elseif($log_events_in == 2 && !$app->isAdmin())
		{
			return false;
		}

		if (!$log_events_for_guest)
		{
			$user = JFactory::getUser();
			if ($user->guest)
			{
				return false;
			}
		}

		if (!in_array($event, $logged_events))
		{
			return false;
		}

		return true;
	}

	public static function addLog($event, $comment_id, $data = array())
	{
		if(!$event || !$comment_id || !self::loggable($event)){
			return false;
		}

		JUComment::import('class', 'browser');

		$data = (array) $data;
		$data['event'] = $event;
		$data['comment_id'] = $comment_id;
		if(!isset($data['comment_title'])){
			$comment = JUCommentHelper::getCommentById($comment_id);
			$data['comment_title'] = $comment->title;
		}

		
		JUComment::import('help', 'log');
		if (empty($data) || !isset($data['event']) || !$data['comment_id'])
		{
			return false;
		}
		
		if (!in_array("user_id", array_keys($data)))
		{
			$user            = JFactory::getUser();
			$data["user_id"] = $user->id;
		}

		$browser    = new Browser();
		$user_agent = $browser->getUserAgent();
		
		$platform = self::getPlatform($user_agent);
		if (!$platform)
		{
			$platform = $browser->getPlatform();
		}

		$data["platform"] = $platform;

		$_browser   = array();
		$_browser[] = $browser->getBrowser();
		$_browser[] = $browser->getVersion();

		$data["browser"] = implode(" ", $_browser);

		$data["user_agent"] = $user_agent;
		$data["ip_address"] = JUComment::getIpAddress();
		$data["date"]       = JFactory::getDate()->toSql();

		$logTable = JUComment::getTable('Log');

		$logTable->bind($data);

		return $logTable->store($data);
	}
}