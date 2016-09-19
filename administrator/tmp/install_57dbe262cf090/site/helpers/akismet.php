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

class JUCommentAkismetHelper
{
	private $akismet	= null;

	private function init( $url = '' )
	{
		$params		= JUComment::getParams();

		if( !$params->get( 'antispam_akismet_key' ) )
		{
			return false;
		}

		if( is_null( $this->akismet ) )
		{
			require_once( JUCOMMENT_CLASSES . DIRECTORY_SEPARATOR . 'akismet.php' );

			$url			= !empty( $url ) ? $url : JUri::root();
			$this->akismet	= new Akismet( $url , $params->get( 'antispam_akismet_key' ) );
		}

		return $this;
	}

	public function isSpam( $data )
	{
		if( !$this->akismet )
		{
			if( !$this->init() )
			{
				return false;
			}
		}

		$this->akismet->setComment( $data );

		
		
		if( $this->akismet->errorsExist() )
		{
			return false;
		}

		return $this->akismet->isSpam();
	}
}
