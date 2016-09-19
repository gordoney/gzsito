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

require_once( dirname(dirname( __FILE__ )) . DIRECTORY_SEPARATOR . 'abstract.php' );

class JUCommentComcontent extends JUCommentExtension
{
	public $_item;
	public $_map = array(
		'id'			=> 'id',
		'title'			=> 'title',
		'hits'			=> 'hits',
		'created_by'	=> 'created_by',
		'catid'			=> 'catid',
		'introtext'     => 'introtext',
		'fulltext'      => 'fulltext',
		'text'          => 'text'
	);

	public function __construct( $component, $section = null )
	{
		$this->addFile( JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_content' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR .'route.php' );

		parent::__construct( $component, $section);
	}

	
	public function load( $cid )
	{
		static $instances = array();

		if( !isset( $instances[$cid] ) )
		{
			$db		= JFactory::getDbo();
			$query	= 'SELECT a.id, a.title, a.alias, a.catid, a.created_by, a.created_by_alias, a.hits,' 
				. ' c.title AS category_title, c.alias AS category_alias,'
				. ' u.name AS author,'
				. ' parent.id AS parent_id, parent.alias AS parent_alias'
				. ' FROM ' . $db->quoteName( '#__content') . ' AS a'
				. ' LEFT JOIN ' . $db->quoteName( '#__categories' ) . ' AS c ON c.id = a.catid'
				. ' LEFT JOIN ' . $db->quoteName( '#__users') . ' AS u ON u.id = a.created_by'
				. ' LEFT JOIN ' . $db->quoteName( '#__categories') . ' AS parent ON parent.id = c.parent_id'
				. ' WHERE a.id = ' . $db->quote( (int) $cid );

			$db->setQuery( $query );
			if( !$result = $db->loadObject() )
			{
				return $this->onLoadArticleError( $cid );
			}

			$instances[$cid] = $result;
		}

		$this->_item = $instances[$cid];

		return $this;
	}

	public function getContentIds( $categories = '' )
	{
		$db		= JUComment::getDbo();
		$query = '';

		if( empty( $categories ) )
		{
			$query = 'SELECT `id` FROM ' . $db->nameQuote( '#__content' ) . ' ORDER BY `id`';
		}
		else
		{
			if( is_array( $categories ) )
			{
				$categories = implode( ',', $categories );
			}

			$query = 'SELECT `id` FROM ' . $db->nameQuote( '#__content' ) . ' WHERE `catid` IN (' . $categories . ') ORDER BY `id`';
		}

		$db->setQuery( $query );
		return $db->loadResultArray();
	}

	public function getCategories()
	{
		$db		= JFactory::getDbo();
		$query	= 'SELECT a.id, a.title, a.level, a.parent_id'
			. ' FROM `#__categories` AS a'
			. ' WHERE a.extension = ' . $db->quote( 'com_content' )
			. ' AND a.parent_id > 0'
			. ' ORDER BY a.lft';

		$db->setQuery( $query );
		$categories = $db->loadObjectList();

		foreach( $categories as &$row )
		{
			$repeat = ( $row->level - 1 >= 0 ) ? $row->level - 1 : 0;
			$row->treename = str_repeat( '.&#160;&#160;&#160;', $repeat ) . ( $row->level - 1 > 0 ? '|_&#160;' : '' ) . $row->title;
		}

		return $categories;
	}

	public function isListingView()
	{
		$views = array('featured', 'category', 'categories', 'archive', 'frontpage' );

		return in_array(JRequest::getCmd('view'), $views);
	}

	public function isEntryView()
	{
		return JRequest::getCmd('view') == 'article';
	}

	public function onExecute( &$article, $html, $view, $options = array() )
	{
		if( $view == 'listing' )
		{
			$config = JUComment::getParams( 'com_content' );

			if( !$config->get( 'frontpage_readmore_use_joomla', 0 ) )
			{
				$article->readmore = false;
			}
			else
			{
				if( $config->get( 'frontpage_readmore', 1 ) == 2 )
				{
					$article->readmore = true;
				}

				if( $config->get( 'frontpage_readmore', 1 ) == 0 )
				{
					$article->readmore = false;
				}
			}

			return $html;
		}

		if( $view == 'entry' )
		{
			return $html;
		}
	}

	
	public function getEventTrigger()
	{
		return 'onContentAfterDisplay';
	}

	
	public function getContext()
	{
		
		if( $this->isEntryView() )
		{
			return 'com_content.article';
		}

		
		
		
		if( $this->isListingView() )
		{
			return array( 'com_content.article', 'com_content.category', 'com_content.featured' );
		}

		return false;
	}

	public function getAuthorName()
	{
		return $this->_item->created_by_alias ? $this->_item->created_by_alias : $this->_item->author;
	}

	public function getContentPermalink()
	{
		$slug = $this->_item->alias ? ($this->_item->id.':'.$this->_item->alias) : $this->_item->id;
		$catslug = $this->_item->category_alias ? ($this->_item->catid.':'.$this->_item->category_alias) : $this->_item->catid;
		$parent_slug = $this->_item->category_alias ? ($this->_item->parent_id.':'.$this->_item->parent_alias) : $this->_item->parent_id;

		$link = ContentHelperRoute::getArticleRoute($slug, $catslug);

		$link = $this->prepareLink( $link );

		return $link;
	}

	
	public function onBeforeLoad( $eventTrigger, $context, &$article, &$params, &$page, &$options )
	{
		if( $this->isEntryView() )
		{
			$config = JUComment::getParams( 'com_content' );

			if( $config->get( 'pagebreak_load' ) == 'all' || JRequest::getInt( 'showall', 0 ) == 1 )
			{
				return true;
			}

			$regex = '#<hr(.*)class="system-pagebreak"(.*)\/>#iU';

			$matches = array();
			$count = 0;

			preg_match_all($regex, $article->introtext, $matches, PREG_SET_ORDER);
			$count += count( $matches );

			preg_match_all($regex, $article->fulltext, $matches, PREG_SET_ORDER);
			$count += count( $matches );

			preg_match_all($regex, $article->text, $matches, PREG_SET_ORDER);
			$count += count( $matches );

			if( $count === 0 )
			{
				return true;
			}
			else
			{
				if( $config->get( 'pagebreak_load' ) == 'first' && $page == 0 )
				{
					return true;
				}

				if( $config->get( 'pagebreak_load' ) == 'last' && $count == $page )
				{
					return true;
				}

				return false;
			}
		}
		else
		{
			return true;
		}
	}

	
	public function onContentAfterDisplay( $context, &$article, &$params, $page = 0 )
	{
		return 'COM_JUCOMMENT_FORM_COMMENT';
	}
}
