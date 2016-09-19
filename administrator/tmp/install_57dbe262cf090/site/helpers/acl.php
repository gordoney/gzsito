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

class JUCommentACLHelper
{
	
	public static function allow( $action, $comment = '', $component = '', $section = '', $cid= '' )
	{
		if(!is_object($comment)){
			$comment = JUCommentHelper::getCommentById($comment);
		}

		if(!empty($comment) && (empty($component) || empty($cid))){
			$component = $comment->component;
			$section = $comment->section;
			$cid = $comment->cid;
		}

		
		if(empty($component) || empty($cid)){
			$component = JUComment::getCurrent('component');
			$section = JUComment::getCurrent('section');
			$cid = JUComment::getCurrent('cid');
		}

		if( empty($component) || empty($cid) )
		{
			return false;
		}

		$JUCMApplication	= JUComment::loadApplication( $component , $section)->load($cid);
		$profile		= JUComment::getProfile();
		$cat_id = $JUCMApplication->getCategoryId();

		switch( $action )
		{
			case 'comment_edit':
				if( $profile->allow('comment.edit') ||
					($JUCMApplication && ($profile->id == $JUCMApplication->getAuthorId()) && $profile->allow( 'author.comment.edit', $comment, $section, $cat_id) ) ||
					($comment && ($profile->id == $comment->user_id) && $profile->allow( 'comment.edit.own', $component, $section, $cat_id ) )
				)
				{
					return true;
				}
				break;

			case 'comment_edit_state':
				if( $profile->allow('comment.edit.state') ||
					($JUCMApplication && ($profile->id == $JUCMApplication->getAuthorId()) && $profile->allow( 'author.comment.edit.state', $component, $section, $cat_id) ) ||
					($comment && $profile->id == $comment->user_id && $profile->allow( 'comment.edit.state.own', $component, $section, $cat_id))
				)
				{
					return true;
				}
				break;

			case 'comment_delete':
				if( $profile->allow( 'comment.delete' ) ||
					($JUCMApplication && ($profile->id == $JUCMApplication->getAuthorId()) && $profile->allow( 'author.comment.delete', $component, $section, $cat_id ) ) ||
					($comment && $profile->id == $comment->user_id && $profile->allow( 'comment.delete.own', $component, $section, $cat_id ))
				)
				{
					return true;
				}
				break;

			case 'comment_auto_approve':
				if( $profile->allow( 'comment.auto_approval', $component, $section, $cat_id ))
				{
					return true;
				}
				break;

			case 'comment_approve':
				
				return false;
				break;

			case 'comment_create':
				if( $profile->allow( 'comment.create', $component, $section, $cat_id ) )
				{
					return true;
				}
				break;

			case 'comment_create_many_times':
				if( $profile->allow( 'comment.create_many_times', $component, $section, $cat_id ) )
				{
					return true;
				}
				break;

			case 'comment_reply':
				if( $profile->allow( 'comment.reply', $component, $section, $cat_id ) )
				{
					return true;
				}
				break;

			case 'comment_vote':
				if( $profile->allow( 'comment.vote', $component, $section, $cat_id ) )
				{
					return true;
				}
				break;

			case 'comment_report':
				if( $profile->allow( 'comment.report', $component, $section, $cat_id ) )
				{
					return true;
				}
				break;

			case 'comment_read':
				if( $profile->allow( 'comment.read', $component, $section, $cat_id ) )
				{
					return true;
				}
				break;

			case 'comment_subscribe':
				if( $profile->allow( 'comment.subscribe', $component, $section, $cat_id ) )
				{
					return true;
				}
				break;
		}

		return false;
	}
}
