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

abstract class JUCommentProfileVendor
{
	protected $profile	= null;
	protected $link		= null;
	protected $avatar	= null;
	protected $paths	= array();
	protected $jucommentprofile = 0;

	public $state 		= null;

	public function __construct( $profile )
	{
		$this->profile	= $profile;

		settype($this->paths, 'array');

		if( !empty($this->paths) )
		{
			foreach ($this->paths as $path)
			{
				if( !JFile::exists($path) )
				{
					$this->state 	= false;

					return false;
				}

				require_once($path);
			}
		}
		$this->state 	= true;

		$this->jucommentprofile = JUComment::getParams()->get( 'use_jucomment_profile' );
	}

	public function addFile( $path )
	{
		$path = trim($path);
		array_unshift($this->paths, $path);
	}

	public function getAvatar() {}
	public function getLink() {}
}

class JUCommentProfileDefault extends JUCommentProfileGravatar
{
}

class JUCommentProfileGravatar extends JUCommentProfileVendor
{
	public function getAvatar( $email = null )
	{
		if( empty( $email ) )
		{
			$email = $this->profile->email;
		}

		$image = '';

		$params = JUComment::getParams();

		$emailKey = md5( strtolower( trim ( $email ) ) );

		if( isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1)
			|| isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' )
		{
			$image = 'https://secure.gravatar.com';
		}
		else
		{
			$image = 'http://www.gravatar.com';
		}

		$image .= '/avatar/' . $emailKey . '?s=100&amp;d=' . $params->get( 'gravatar_default_avatar', 'mm' );
		return $image;
	}

	public function getLink( $email = null )
	{
		if( empty( $email ) )
		{
			$email = $this->profile->email;
		}

		if( $this->jucommentprofile )
		{
			return JRoute::_('index.php?option=com_jucomment&view=profile&id=' . $this->profile->id);
		}

		$link = '';

		if( isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1)
			|| isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' )
		{
			$link = 'https://secure.gravatar.com/' . md5($email);
		}
		else
		{
			$link = 'http://www.gravatar.com/' . md5($email);
		}
		return $link;
	}
}
