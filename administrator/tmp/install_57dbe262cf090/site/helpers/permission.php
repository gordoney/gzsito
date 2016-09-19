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


class JUCommentPermissionHelper
{

	
	protected static $cache = array();

	
	public static function isOwnDashboard()
	{
		$app  = JFactory::getApplication();
		$view = $app->input->getString('view', '');
		if ($view == 'modpermission')
		{
			return true;
		}
		$userId = $app->input->getInt('id', 0);
		$user   = JFactory::getUser();
		
		$isOwnDashboard = true;
		
		if ($userId > 0 && $userId != $user->id)
		{
			$isOwnDashboard = false;
		}

		

		return $isOwnDashboard;
	}

	public static function allow( $type, $comment = '', $component = '', $section = '', $cid= '' )
	{
		$moderator = JUComment::getHelper('moderator');
		$acl = JUComment::getHelper('acl');
		$profile = JUComment::getProfile();
		switch( $type )
		{
			case 'edit':
				$app = JFactory::getApplication();
				if($app->isSite()){
					$params = JUComment::getParams();
					$edit_comment_time = $params->get('allow_edit_comment_within', 600);
					$comment_time = JFactory::getDate()->toUnix() - JFactory::getDate($comment->created)->toUnix();
					if($edit_comment_time && $comment_time <= $edit_comment_time){
						if($profile->id > 0){
							if($profile->id == $comment->user_id){
								return true;
							}
						}else{
							$ip_address = JUComment::getIpAddress();
							if($comment->ip_address == $ip_address){
								return true;
							}
						}
					}
				}

				if($moderator->allow('comment_edit', $comment, $component)){
					return true;
				}

				if($acl->allow('comment_edit', $comment, $component, $section, $cid)){
					return true;
				}
				break;

			case 'edit_state':
				if($moderator->allow('comment_edit_state', $comment, $component)){
					return true;
				}

				if($acl->allow('comment_edit_state', $comment, $component, $section, $cid)){
					return true;
				}
				break;

			case 'delete':
				if($moderator->allow('comment_delete', $comment, $component)){
					return true;
				}

				if($acl->allow('comment_delete', $comment, $component, $section, $cid)){
					return true;
				}
				break;

			case 'auto_approve':
				if($acl->allow('comment_auto_approve', $comment, $component, $section, $cid)){
					return true;
				}
				break;

			case 'approve':
				if($moderator->allow('comment_approve', $comment, $component)){
					return true;
				}

				if($acl->allow('comment_approve', $comment, $component, $section, $cid)){
					return true;
				}
				break;

			case 'create':
				if(!self::checkBlackListUserId()){
					return false;
				}

				if(!self::checkBlackListUserIP()){
					return false;
				}

				if($acl->allow('comment_create', $comment, $component, $section, $cid)){
					return true;
				}
				break;

			case 'create_many_times':
				if(!self::checkBlackListUserId()){
					return false;
				}

				if(!self::checkBlackListUserIP()){
					return false;
				}

				if($acl->allow('comment_create_many_times', $comment, $component, $section, $cid)){
					return true;
				}
				break;

			case 'reply':
				if(!self::checkBlackListUserId()){
					return false;
				}

				if(!self::checkBlackListUserIP()){
					return false;
				}

				if($acl->allow('comment_reply', $comment, $component, $section, $cid)){
					return true;
				}
				break;

			case 'vote':
				if($acl->allow('comment_vote', $comment, $component, $section, $cid)){
					return true;
				}
				break;

			case 'report':
				if($acl->allow('comment_report', $comment, $component, $section, $cid)){
					return true;
				}
				break;

			case 'read':
				if($acl->allow('comment_read', $comment, $component, $section, $cid)){
					return true;
				}
				break;

			case 'subscribe':
				if($acl->allow('comment_subscribe', $comment, $component, $section, $cid)){
					return true;
				}
				break;
		}

		return false;
	}

	public static function canViewDashboard()
	{
		$params                = JUComment::getParams();
		$public_user_dashboard = $params->get("public_user_dashboard", 0);
		$user                  = JFactory::getUser();

		if ($public_user_dashboard)
		{
			$app    = JFactory::getApplication();
			$userId = $app->input->getInt('id', 0);
			
			if ($user->id == 0 && $userId == 0)
			{
				return false;
			}

			return true;
		}
		else
		{
			
			if ($user->id == 0)
			{
				return false;
			}
			
			else
			{
				$isOwnDashboard = self::isOwnDashboard();

				return $isOwnDashboard;
			}
		}
	}

	
	public static function checkBlackListUserId()
	{
		$app = JFactory::getApplication();
		if($app->isAdmin()){
			return true;
		}

		$params      = JUComment::getParams();
		$userIdBlackList = $params->get('userid_blacklist', '');
		if ($userIdBlackList !== '')
		{
			$user           = JFactory::getUser();
			$userIdBlackListArr = array_map('trim', explode(",", strtolower(str_replace("\n", ",", $userIdBlackList))));
			if(in_array($user->id, $userIdBlackListArr))
			{
				return false;
			}
		}

		return true;
	}

	
	public static function checkBlackListUserIP()
	{
		$app = JFactory::getApplication();
		if($app->isAdmin()){
			return true;
		}

		JUComment::import('class', 'ipblocklist');
		$params    = JUComment::getParams();
		$app       = JFactory::getApplication();
		$is_passed = true;

		if ($app->isSite() && $params->get('block_ip', 0))
		{
			$ip_address  = JUComment::getIpAddress();
			$ipWhiteList = $params->get('ip_whitelist', '');
			$ipBlackList = $params->get('ip_blacklist', '');

			$checkIp   = new IpBlockList($ipWhiteList, $ipBlackList);
			$is_passed = $checkIp->ipPass($ip_address);
		}

		return $is_passed;
	}
}