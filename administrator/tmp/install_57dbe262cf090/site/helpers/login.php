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


class JUCommentLoginHelper
{
	public function getRegistrationLink()
	{
		$params = JUComment::getParams();

		$link	= JRoute::_( 'index.php?option=com_users&view=registration' );

		switch( $params->get( 'login_provider' ) )
		{
			case 'cb':
				$link 	= JRoute::_( 'index.php?option=com_comprofiler&task=registers' );
				break;
			break;

			case 'joomla':
				$link	= JRoute::_( 'index.php?option=com_users&view=registration' );
			break;

			case 'jomsocial':
				$link	= JRoute::_( 'index.php?option=com_community&view=register' );
			break;

			case 'easysocial':
				$es = JUComment::getHelper( 'EasySocial' );

				if( $es->exists() )
				{
					$link = FRoute::registration();
				}
			break;
		}

		return $link;
	}

	public function getLoginLink( $returnURL = '' )
	{
		$params 	= JUComment::getParams();

		if( !empty( $returnURL ) )
		{
			$returnURL	= '&return=' . $returnURL;
		}

		$link = JRoute::_('index.php?option=com_users&view=login' . $returnURL );

		switch( $params->get( 'login_provider' ) )
		{
			case 'cb':
				$link 	= JRoute::_( 'index.php?option=com_comprofiler&task=login' . $returnURL);
				break;
			break;

			case 'joomla':
			case 'jomsocial':
			    $link 	= JRoute::_('index.php?option=com_users&view=login' . $returnURL );
			break;

			case 'easysocial':
				$es = JUComment::getHelper( 'EasySocial' );

				if( $es->exists() )
				{
					$link = FRoute::login();
				}
			break;
		}

		return $link;
	}

	public function getResetPasswordLink()
	{
		$params	= JUComment::getParams();

		$link = JRoute::_( 'index.php?option=com_users&view=reset' );

		switch( $params->get( 'login_provider' ) )
		{
			case 'cb':
				$link 		= JRoute::_( 'index.php?option=com_comprofiler&task=lostpassword' );
			break;

			case 'joomla':
			case 'jomsocial':
				$link	= JRoute::_( 'index.php?option=com_users&view=reset' );
			break;

			case 'easysocial':
				$es = JUComment::getHelper( 'EasySocial' );

				if( $es->exists() )
				{
					$link = FRoute::profile( array( 'layout' => 'forgetPassword' ) );
				}
			break;
		}

		return $link;
	}

	public function getRemindUsernameLink()
	{
		$params 	= JUComment::getParams();

		$link = JRoute::_( 'index.php?option=com_users&view=remind' );

		switch( $params->get( 'login_provider' ) )
		{
			case 'easysocial':
				$es = JUComment::getHelper( 'EasySocial' );

				if( $es->exists() )
				{
					$link = FRoute::profile( array( 'layout' => 'forgetPassword' ) );
				}
			break;

			default:
				$link	= JRoute::_( 'index.php?option=com_users&view=remind' );
			break;
		}

		return $link;
	}
}
