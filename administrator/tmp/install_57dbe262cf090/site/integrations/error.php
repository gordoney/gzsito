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

require_once JUCOMMENT_ROOT . '/integrations/abstract.php';

class JUCommentError extends JUCommentExtension
{
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

	public function __construct( $component )
	{
		parent::__construct( $component );
	}

	public function load( $cid )
	{
		static $instances = array();

		if( empty( $instances[$cid] ) )
		{
			$instance = new stdClass();

			$instance->id = $cid;
			$instance->title = JText::_( 'COM_JUCOMMENT_UNABLE_TO_LOAD_ARTICLE_DETAILS' ) . ' (' . $cid . ')';
			$instance->permalink = 'javascript:void(0);';
			$instance->hits = 0;
			$instance->created_by = 0;
			$instance->cat_id = 0;
			$instance->introtext = '';
			$instance->fulltext = '';
			$instance->text = '';

			$instances[$cid] = $instance;
		}

		$this->_item = $instances[$cid];

		return $this;
	}

	public function getSection(){
		return '';
	}

	public function getContentIds( $categories = '' )
	{
		return array();
	}

	public function getCategories()
	{
		return array();
	}

	public function isListingView()
	{
		return false;
	}

	public function isEntryView()
	{
		return false;
	}

	public function onExecute( &$article, $html, $view, $options = array() )
	{
		return false;
	}

	public function getComponentTemplatePath()
	{
		return '';
	}

	public function getComponentName()
	{
		if( !empty( $this->component ) )
		{
			return $this->component;
		}

		return JText::_( 'COM_JUCOMMENT_NO_COMPONENT_NAME_ASSIGNED' );
	}
}
