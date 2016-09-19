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

class JUCommentCaptcha extends JObject
{
	public function getHTML($options = array())
	{
		$captcha = JUComment::getTable('Captcha');
		$captcha->created	= JFactory::getDate()->toSql();
		$captcha->store();

		$template		= JUComment::getTemplate();
		$template->set( 'id' , $captcha->id );
		$template->set( 'url', $this->getCaptchaUrl( $captcha->id ) );
		$template->set( 'options' , $options );

		return $template->fetch( 'comment/captcha.php' );
	}

	public function verify( $data, $params = array() )
	{
		if (!array_key_exists('captcha_response', $data) || !array_key_exists('captcha_id', $data) )
		{
			return false;
		}

		$captcha = JUComment::getTable('Captcha');
		if( !$captcha->load( $data['captcha_id'] ) || !$captcha->response || !$captcha->verify($data['captcha_response']) )
		{
			$this->setError( JText::_('COM_JUCOMMENT_CAPTCHA_INVALID_RESPONSE') );
			return false;
		}

		
		$captcha->delete();
		return true;
	}

	public function getReloadSyntax()
	{
		if ($currentId = JRequest::getInt( 'captcha_id' ))
		{
			$ref = JUComment::getTable('Captcha');
			if($ref->load( $currentId )){
				$ref->delete();
			}
		}

		
		$captcha = JUComment::getTable('Captcha');
		$captcha->id = 0;
		$captcha->created	= JFactory::getDate()->toSql();
		$captcha->store();

		$url	= $this->getCaptchaUrl( $captcha->id );

		$reloadData = array(
			'image'	=> $url,
			'captcha_id'	=> $captcha->id
		);

		return $reloadData;
	}

	public function getCaptchaUrl( $id )
	{
		$base = 'index.php?option=com_jucomment&task=comment.captcha&captcha_id=' . $id . '&tmpl=component';

		$url = JUri::root() . $base;

		return $url;
	}
}
