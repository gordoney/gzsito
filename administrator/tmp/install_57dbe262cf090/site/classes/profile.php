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

jimport('joomla.user.user');



class JUCommentProfile extends JUser
{
	protected $profileName		= null;
	protected $profileAvatar	= null;
	protected $profileLink		= null;
	protected $profileUsername	= null;

	public function __construct($id = null)
	{
		if (empty($id))
		{
			$this->set( 'name',		JText::_( 'COM_JUCOMMENT_GUEST' ) );
			$this->set( 'username',	JText::_( 'COM_JUCOMMENT_GUEST' ) );
		}
		parent::__construct($id);
	}

	public static function getUser($id = null)
	{
		static $profiles = array();

		$juser		= JFactory::getUser($id);
		$newid		= $juser->id;

		if( empty( $newid ) )
		{
			$newid = 0;
		}

		if( empty( $profiles[$newid] ) )
		{
			$profiles[$newid]	= new JUCommentProfile($newid);
			if ($newid != 0)
			{
				$profiles[$newid]->load($newid);
			}
		}

		return $profiles[$newid];
	}

	
	public function load($id = null)
	{
		$result = parent::load($id);
		return $result;
	}

	public function isAdmin()
	{
		return $this->authorise('core.admin');
	}

	public function getName()
	{
		$params = JUComment::getParams();

		if( $params->get( 'name_type', 'default' ) == 'username' )
		{
			return $this->getUsername();
		}

		if (!$this->profileName)
		{
			$this->profileName	= $this->name;
		}

		return $this->profileName;
	}

	public function getUsername()
	{
		if (!$this->profileUsername)
		{
			$this->profileUsername	= $this->username;
		}

		return $this->profileUsername;
	}

	public function getAvatar( $email = '' )
	{
		static $avatar = array();

		$params = JUComment::getParams();
		$vendorName	= $params->get( 'avatar_integration', 'default' );

		if( $vendorName == 'gravatar' && $email != '' )
		{
			if( !isset( $avatar[$email] ) )
			{
				$avatar[$email] = $this->getVendor()->getAvatar( $email );
			}

			$this->profileAvatar = $avatar[$email];
		}
		else
		{
			if (!$this->profileAvatar)
			{
				$this->profileAvatar	= $this->getVendor()->getAvatar( $email );
			}
		}

		$app = JFactory::getApplication();

		if ( $app->isAdmin() )
		{
			$this->profileAvatar = str_ireplace( '/administrator/', '/', $this->profileAvatar );
		}

		return $this->profileAvatar;
	}

	public function getProfileLink( $email = '' )
	{
		if (!$this->profileLink)
		{
			$this->profileLink	= $this->getVendor()->getLink( $email );
		}

		return $this->profileLink;
	}

	public function getVendor( $name = '' )
	{
		static $vendors	= array();

		$params		= JUComment::getParams();
		$preferred	= $params->get( 'avatar_integration', 'default');
		$vendorName	= $name !== '' ? $name : $preferred;

		if (empty($vendors[$vendorName][$this->id]))
		{
			require_once( JUCOMMENT_CLASSES . '/profileVendors.php' );
			$classname	= 'JUCommentProfile' . ucfirst($vendorName);
			$vendor = null;
			if( class_exists( $classname ) )
			{
				$vendor		= new $classname($this);
				if( !isset($vendor->state) || !$vendor->state )
				{
					$vendor	= $this->getVendor('default');
				}
			}
			else
			{
				$vendor	= $this->getVendor('default');
			}

			$vendors[$vendorName][$this->id]	= $vendor;
		}

		return $vendors[$vendorName][$this->id];
	}

	public function allow( $action = '', $component = '' , $section = '', $cat_id = '')
	{
		if(!$component){
			$component = JUComment::getCurrent('component');
			$section = JUComment::getCurrent('section');
			$cat_id = JUComment::getCurrent('cat_id');
		}

		$assetName = 'com_jucomment';
		if($component && $section && $cat_id){
			if(JUComment::hasConfigs($component, $section, $cat_id)){
				$assetName .= '.'.$component.'.'.$section.'.'.$cat_id;
			}elseif(JUComment::hasConfigs($component, $section)){
				$assetName .= '.'.$component.'.'.$section;
			}elseif(JUComment::hasConfigs($component)){
				$assetName .= '.'.$component;
			}
		}elseif($component && $section){
			if(JUComment::hasConfigs($component, $section)){
				$assetName .= '.'.$component.'.'.$section;
			}elseif(JUComment::hasConfigs($component)){
				$assetName .= '.'.$component;
			}
		}elseif($component && $cat_id){

			if(JUComment::hasConfigs($component, '', $cat_id)){
				$assetName .= '.'.$component.'.'.$cat_id;
			}elseif(JUComment::hasConfigs($component)){
				$assetName .= '.'.$component;
			}
		}elseif($component){
			if(JUComment::hasConfigs($component)){
				$assetName .= '.'.$component;
			}
		}

		return $this->authorise($action, $assetName);
	}
}
