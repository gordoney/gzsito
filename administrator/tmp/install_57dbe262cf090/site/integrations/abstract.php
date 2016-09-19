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


abstract class JUCommentExtension
{
	const APIVERSION = '1.0';

	protected static $cache = array();

	
	public $state = true;

	
	public $component = null;

	
	public $section = null;

	
	public $_item = null;

	

	public $_map = array(
		
		'id'			=> 'id',

		
		'title'			=> 'title',

		
		'hits'			=> 'hits',

		
		'created_by'	=> 'created_by',

		
		'catid'			=> 'catid',

		
		'permalink'		=> 'permalink',

		'introtext'     => 'introtext',
		'fulltext'      => 'fulltext',
		'text'          => 'text'
	);

	

	
	abstract public function load( $cid );

	
	abstract public function getContentIds( $categories = '' );

	
	abstract public function getCategories();

	
	abstract public function isListingView();

	
	abstract public function isEntryView();

	
	abstract public function onExecute( &$article, $html, $view, $options = array() );

	


	

	
	public function __construct( $component, $section = null)
	{
		$this->component	= $component;
		$this->section	= $section;
	}

	public function addFile( $file )
	{
		if( $this->state )
		{
			if( JFile::exists( $file ) )
			{
				require_once( $file );
			}
			else
			{
				$this->state = false;
			}
		}
	}

	
	public function getAPIVersion()
	{
		return self::APIVERSION;
	}

	
	public function getComponentName()
	{
		$storeId = md5(__METHOD__."::".$this->component);
		if(!isset(self::$cache[$storeId])){
			if($this->component)
			{
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->select('title')
					->from('#__jucomment_plugins')
					->where('folder = ' . $db->quote($this->component));
				$db->setQuery($query);
				$componentName = $db->loadResult();
				if (!$componentName)
				{
					$componentName = JText::_('COM_JUCOMMENT_' . strtoupper($this->component));
				}
			}else{
				$componentName = '';
			}

			self::$cache[$storeId] = $componentName;
		}

		return self::$cache[$storeId];
	}

	
	public function getComponentIcon()
	{
		$base = 'administrator/components/com_jucomment/assets/images/components/';
		$file = $this->component . '.png';

		if( !JFile::exists( JPATH_ROOT . '/' . $base . $file ) )
		{
			return JUri::root() . $base . 'error.png';
		}

		return JUri::root() . $base . $file;
	}

	
	public function getComponentTemplatePath()
	{
		return JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . $this->component . DIRECTORY_SEPARATOR . 'jucomment';
	}

	
	public function getComponentTemplateURI()
	{
		return JUri::root() . 'components/' . $this->component . '/jucomment';
	}

	public function getSections(){
		return array();
	}

	public function getSection(){
		return null;
	}

	
	public function prepareLink( $link )
	{
		$link = JRoute::_( $link );

		
		$relpath = JUri::root( true );
		if( $relpath != '' && strpos( $link, $relpath ) === 0 )
		{
			$link = substr( $link, strlen( $relpath ) );
		}

		
		if( strpos( $link, '/administrator/' ) === 0 )
		{
			$link = substr( $link, 14 );
		}

		$link = rtrim( JUri::root(), '/' ) . '/' . ltrim( $link, '/' );

		return $link;
	}

	
	public function getEventTrigger()
	{
		return true;
	}

	
	public function getContext()
	{
		return true;
	}

	
	public function getContentId()
	{
		return $this->_item->{$this->_map['id']};
	}

	
	public function getContentTitle()
	{
		return $this->_item->{$this->_map['title']};
	}

	
	public function getContentHits()
	{
		return $this->_item->{$this->_map['hits']};
	}

	
	public function getContentPermalink()
	{
		return $this->_item->{$this->_map['permalink']};
	}

	
	public function getCategoryId()
	{
		if(isset($this->_item->{$this->_map['catid']})){
			return $this->_item->{$this->_map['catid']};
		}
		return '';
	}

	
	public function getAuthorId()
	{
		return $this->_item->{$this->_map['created_by']};
	}

	
	public function getAuthorName()
	{
		return JFactory::getUser( $this->getAuthorId() )->name;
	}

	
	public function getAuthorAvatar()
	{
		return '';
	}

	
	public function getCommentAnchorId()
	{
		return '';
	}

	
	public function onBeforeLoad( $eventTrigger, $context, &$article, &$params, &$page, &$options )
	{
		return true;
	}

	
	public function onParameterDisabled( $eventTrigger, $context, &$article, &$params, &$page, &$options )
	{
		return false;
	}

	
	public function onAfterLoad( $eventTrigger, $context, &$article, &$params, &$page, &$options )
	{
		return true;
	}

	
	public function onRollBack( $eventTrigger, $context, &$article, &$params, &$page, &$options )
	{
		return true;
	}

	
	public function onArticleDeleted( $article )
	{
		$cid = $article;

		if( is_object( $article ) )
		{
			$cid = $article->{$this->_map['id']};
		}

		require_once( JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_jucomment' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'helper.php' );

		$model = JUComment::getModel( 'comments' );

		$result = $model->deleteArticleComments( $this->component, $cid );

		return $result;
	}

	
	public function onLoadArticleError( $cid )
	{
		static $componentInstances	= array();
		static $cidInstances		= array();

		if( empty( $componentInstances[$this->component] ) )
		{
			require_once( JUCOMMENT_ROOT . '/integrations/error.php' );
			$componentInstances[$this->component] = new JUCommentError( $this->component );
		}

		if( empty( $cidInstances[$this->component][$cid] ) )
		{
			$cidInstances[$this->component][$cid] = $componentInstances[$this->component]->load( $cid );
		}

		return $cidInstances[$this->component][$cid];
	}

	

	

	
	public function onBeforeJUCommentBar( $commentCount ) {}


	
	public function onBeforeJUCommentBox( $system, $comments ) {}

	
	public function onBeforeSaveComment( $comment )
	{
		return true;
	}

	
	public function onAfterSaveComment( $comment ) {}

	
	public function onBeforeProcessComment( $comment ) {}

	
	public function onAfterProcessComment( $comment ) {}

	
	public function onBeforeSendNotification( $recipient )
	{
		return true;
	}

	
	public function onBeforeDeleteComment( $comment )
	{
		return true;
	}

	
	public function onAfterDeleteComment( $comment ) {}

	
	public function onBeforePublishComment( $comment )
	{
		return true;
	}

	
	public function onAfterPublishComment( $comment ) {}

	
	public function onBeforeUnpublishComment( $comment )
	{
		return true;
	}

	
	public function onAfterUnpublishComment( $comment ) {}

	
}
