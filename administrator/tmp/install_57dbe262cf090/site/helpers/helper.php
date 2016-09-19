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


defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.utilities.utility');

class JUCommentFrontHelper {
	
	
	protected static $cache = array();

	public static $package = 'paid';

	public static $component = '';

	public static $section = '';

	public static $cat_id = '';

	public static $cid = '';

	public static $jucmapplication;

	private static $messages = array();
	
	
	public static function getWysibbEditor($jQuerySelector = '.wysibb', $returnJS = false)
	{
		$params                      = JUComment::getParams();
		$wysibbButtons['bold,']      = $params->get('bb_bold_tag', 'Bold');
		$wysibbButtons['italic,']    = $params->get('bb_italic_tag', 'Italic');
		$wysibbButtons['underline,'] = $params->get('bb_underline_tag', 'Underline');

		$wysibbButtons['img,']   = $params->get('bb_img_tag', 'Picture');
		$wysibbButtons['link,']  = $params->get('bb_link_tag', 'Link');
		$wysibbButtons['video,'] = $params->get('bb_video_tag', 'Video');

		$wysibbButtons['smilebox,']  = $params->get('bb_smilebox_tag', 'Smilebox');
		$wysibbButtons['fontcolor,'] = $params->get('bb_color_tag', 'Colors');
		$wysibbButtons['fontsize,']  = $params->get('bb_fontsize_tag', 'Fontsize');

		$wysibbButtons['justifyleft,']   = $params->get('bb_align_left', 'alignleft');
		$wysibbButtons['justifycenter,'] = $params->get('bb_align_center', 'aligncenter');
		$wysibbButtons['justifyright,']  = $params->get('bb_align_right', 'alignright');

		$wysibbButtons['bullist,'] = $params->get('bb_bulleted_list', 'Bulleted-list');
		$wysibbButtons['numlist,'] = $params->get('bb_numeric_list', 'Numeric-list');
		$wysibbButtons['quote,']   = $params->get('bb_quote_tag', 'Quotes');

		$buttons = '';
		$i       = 0;
		foreach ($wysibbButtons as $key => $value)
		{
			if ($i % 3 == 0)
			{
				$buttons .= "|,";
			}
			
			if ($value)
			{
				$buttons .= $key;
			}

			$i++;
		}
		$script = " jQuery(document).ready(function($){
						var jucmWbbOpt = {
							buttons: '$buttons',
							lang: 'en'
						};

						$('$jQuerySelector').wysibb(jucmWbbOpt);
					}); ";

		if ($returnJS == true)
		{
			return '<script type="text/javascript">' . $script . '</script>';
		}
		else
		{
			$document = JFactory::getDocument();
			$document->addStyleSheet(JUri::root(true) . "/components/com_jucomment/assets/js/wysibb/theme/default/wbbtheme.css");
			$document->addScript(JUri::root(true) . "/components/com_jucomment/assets/js/wysibb/jquery.wysibb.js");
			$document->addScript(JUri::root(true) . "/components/com_jucomment/assets/js/wysibb/preset/phpbb3.js");
			$document->addScriptDeclaration($script);
		}
		
	}
	
	public static function loadjQuery($forceLoad = false)
	{
		$document = JFactory::getDocument();
		if ($document->getType() != 'html')
		{
			return true;
		}

		JHtml::_('jquery.framework');
	}

	public static function loadjQueryUI($forceLoad = false)
	{
		
		$params = JUComment::getParams();
		
		if ($params->get('load_jquery_ui', 2) == 0 && !$forceLoad)
		{
			return false;
		}

		$loadjQueryUI = true;
		
		if ($params->get('load_jquery_ui', 2) == 2)
		{
			$loadjQueryUI = true;
			$document     = JFactory::getDocument();
			$header       = $document->getHeadData();
			$scripts      = $header['scripts'];
			if (count($scripts))
			{
				$pattern = '/([\/\\a-zA-Z0-9_:\.-]*)jquery[.-]ui([0-9\.-]|core|custom|min|pack)*?.js(.*?)/i';
				foreach ($scripts AS $script => $opts)
				{
					if (preg_match($pattern, $script))
					{
						$loadjQueryUI = false;
						break;
					}
				}
			}
		}

		
		if ($loadjQueryUI || $forceLoad)
		{
			self::loadjQuery();
			$document = JFactory::getDocument();
			$document->addScript(JUri::root(true) . '/components/com_jucomment/assets/js/jquery-ui.min.js');
			$document->addStyleSheet(JUri::root(true) . '/components/com_jucomment/assets/css/jquery-ui.min.css');
		}
	}

	
	public static function loadBootstrap($version = 2, $type = 2)
	{
		$document = JFactory::getDocument();

		
		if ($document->getType() != 'html')
		{
			return true;
		}

		$app = JFactory::getApplication();

		
		if ($type == 0)
		{
			return false;
		}

		
		$loadBootstrap = true;
		if ($type == 2 || $app->isAdmin())
		{
			$header  = $document->getHeadData();
			$scripts = $header['scripts'];
			if (count($scripts))
			{
				$pattern = '/([\/\\a-zA-Z0-9_:\.-]*)bootstrap.([0-9\.-]|core|custom|min|pack)*?.js(.*?)/i';
				foreach ($scripts AS $script => $opts)
				{
					if (preg_match($pattern, $script))
					{
						$loadBootstrap = false;
						break;
					}
				}
			}
		}

		
		if ($loadBootstrap)
		{
			JUCommentFrontHelper::loadjQuery();

			if ($version == 2)
			{
				JHtml::_('bootstrap.framework');
				if ($app->isSite())
				{
					JHtml::_('bootstrap.loadCss');
				}
			}
			elseif ($version == 3)
			{
				$document->addScript(JUri::root(true) . '/components/com_jucomment/assets/bootstrap3/js/bootstrap.min.js');
				$document->addStyleSheet(JUri::root(true) . '/components/com_jucomment/assets/bootstrap3/css/bootstrap.min.css');
				$document->addStyleSheet(JUri::root(true) . '/components/com_jucomment/assets/bootstrap3/css/bootstrap-theme.min.css');

				$document->addScriptDeclaration('
					jQuery(document).ready(function($){
						$(\'.hasTooltip\').tooltip({\'html\': true, trigger: \'hover\'}).bind(\'hidden\', function () {
					        $(this).show();
					    });
					});
				');
			}
		}

		
		if ($app->isAdmin())
		{
			$document->addScript(JUri::root(true) . '/administrator/components/com_jucomment/assets/js/bootstrap-hover-dropdown.js');
		}
	}

	
	public static function getIpAddress()
	{
		if (getenv('HTTP_CLIENT_IP'))
		{
			$ipaddress = getenv('HTTP_CLIENT_IP');
		}
		else
		{
			if (getenv('HTTP_X_FORWARDED_FOR'))
			{
				$ipaddress = getenv('HTTP_X_FORWARDED_FOR');
			}
			else
			{
				if (getenv('HTTP_X_FORWARDED'))
				{
					$ipaddress = getenv('HTTP_X_FORWARDED');
				}
				else
				{
					if (getenv('HTTP_FORWARDED_FOR'))
					{
						$ipaddress = getenv('HTTP_FORWARDED_FOR');
					}
					else
					{
						if (getenv('HTTP_FORWARDED'))
						{
							$ipaddress = getenv('HTTP_FORWARDED');
						}
						else
						{
							if (getenv('REMOTE_ADDR'))
							{
								$ipaddress = getenv('REMOTE_ADDR');
							}
							else
							{
								$ipaddress = '';
							}
						}
					}
				}
			}
		}

		return $ipaddress;
	}

	
	public static function loadApplication( $component = null, $section = null)
	{
		static $instances = array();

		$component = preg_replace('/[^A-Z0-9_\.-]/i', '', $component);

		$properInstance = true;
		$JUCommentPlugin = true;

		
		$componentName = $component;

		if( empty( $section ) )
		{
			$section = self::getCurrent('section');
		}

		
		if( empty( $componentName ) )
		{
			$componentName = self::getCurrent('component');

			
			if( empty( $componentName ) )
			{
				$componentName = 'error';
				$section = '';
			}
		}

		
		
		
		
		
		

		
		if( empty($instances[$componentName.$section]) )
		{
			
			require_once( JUCOMMENT_ROOT.  '/integrations/abstract.php' );

			
			if($section != ''){
				$integrationFile = JUCOMMENT_ROOT. '/integrations/' . $componentName . '/' .$componentName.'_'.$section.'.php';
			}else{
				$integrationFile = JUCOMMENT_ROOT . '/integrations/' . $componentName . '/' .$componentName.'.php';
			}

			if ( !JFile::exists($integrationFile) )
			{
				$JUCommentPlugin = false;
			}

			
			if( $JUCommentPlugin )
			{
				require_once( $integrationFile );

				
				$className = 'JUComment' . ucfirst( strtolower( preg_replace( '/[^A-Z0-9]/i', '', $componentName ) ) );
				if($section){
					$className .= ucfirst( strtolower( preg_replace( '/[^A-Z0-9]/i', '', $section ) ) );
				}

				if( class_exists( $className ) )
				{
					$classObject = new $className( $component , $section);

					
					if( !($classObject instanceof JUCommentExtension) || !$classObject->state )
					{
						$properInstance = false;
					}
					else
					{
						$instances[$componentName.$section] = $classObject;
					}
				}
				else
				{
					$properInstance = false;
				}
			}
		}

		
		if( !$JUCommentPlugin || !$properInstance || empty( $componentName ) )
		{
			require_once( JUCOMMENT_ROOT . '/integrations/error.php' );
			$classObject = new JUCommentError( $component );

			if( empty( $componentName ) )
			{
				$componentName = 'error';
			}

			$instances[$componentName.$section] = $classObject;
		}elseif(!$section){
			$section = $instances[$componentName.$section]->getSection();
			if($section){
				$instances[$componentName.$section] = self::loadApplication($component, $section);
			}
		}

		return $instances[$componentName.$section];
	}

	
	public static function getParams( $component = null, $section = null, $cid = null, $cat_id = null)
	{
		$component = $component ? $component : ($component === false ? '' : self::getCurrent('component'));
		$section = $section ? $section : ($section === false ? '' : self::getCurrent('section'));
		$cid = $cid ? $cid : ($section === false ? '' : self::getCurrent('cid'));

		if(!$cat_id && $cid){
			$JUCMApplication = self::loadApplication($component, $section);
			$JUCMApplication = $JUCMApplication->load($cid);
			if($JUCMApplication)
			{
				$cat_id = $JUCMApplication->getCategoryId();
			}
		}

		$storeId = md5(__METHOD__."$component::$section::$cat_id");

		if(!isset(self::$cache[$storeId])){
			$data = array();
			$data[] = $component;
			$data[] = $section;
			$data[] = $cat_id;
			self::import('helper', 'params');
			self::$cache[$storeId] = new JUCommentParamsHelper($data);
		}

		return self::$cache[$storeId];
	}

	
	public static function commentify( $component, &$article, $options = array() )
	{
		$eventTrigger	= null;
		$context		= null;
		$params			= array();
		$page			= 0;

		if( array_key_exists('trigger', $options) )
		{
			$eventTrigger = $options[ 'trigger' ];
		}
		if( array_key_exists('context', $options) )
		{
			$context = $options[ 'context' ];
		}
		if( array_key_exists('params', $options) )
		{
			$params = $options[ 'params' ];
		}
		if( array_key_exists('page', $options) )
		{
			$page = $options['page'];
		}

		
		if( is_array( $article ) )
		{
			$article = (object) $article;
		}

		
		if( empty( $component ) )
		{
			return false;
		}

		if(array_key_exists('section', $options)){
			$section = $options['section'];
			$JUCMApplication = JUComment::loadApplication($component, $section);
		}else{
			$JUCMApplication = JUComment::loadApplication($component);
			$section = $JUCMApplication->section;
		}

		

		
		if( !JUComment::verifyContext( $context, $JUCMApplication->getContext() ) )
		{
			return false;
		}

		
		if( !JUComment::verifyEventTrigger( $eventTrigger, $JUCMApplication->getEventTrigger() ) )
		{
			return false;
		}

		
		
		
		if( !$JUCMApplication->onBeforeLoad( $eventTrigger, $context, $article, $params, $page, $options ) )
		{
			return false;
		}

		
		
		if( is_string( $article ) || is_int( $article ) )
		{
			$cid = $article;
		}
		elseif(isset($article->{$JUCMApplication->_map['id']}))
		{
			
			$cid = $article->{$JUCMApplication->_map['id']};
		}

		
		if( empty( $cid ) )
		{
			return false;
		}

		
		self::processParameter( $article, $options );

		
		if( $options['disable'] )
		{
			if( !$JUCMApplication->onParameterDisabled( $eventTrigger, $context, $article, $params, $page, $options ) )
			{
				return false;
			}
		}

		
		if( !$JUCMApplication->load( $cid ) )
		{
			return false;
		}

		
		$catid	= $JUCMApplication->getCategoryId();

		
		self::setCurrent('component', $component);
		self::setCurrent('section', $section);
		self::setCurrent('cat_id', $catid);
		self::setCurrent('cid', $cid);

		
		$juparams = JUComment::getParams();

		
		if( !$juparams->get('enable_jucomment', 1) )
		{
			return false;
		}

		
		if( $juparams->get('disable_jucomment_on_tmpl_component', 1) && JFactory::getApplication()->input->get('tmpl', '') === 'component' )
		{
			return false;
		}

		
		if( array_key_exists('enable', $options ) && !$options['enable'] )
		{
			
			$categories	= $juparams->get( 'selected_categories', array() );

			
			switch( $juparams->get( 'allowed_categories_mode',0) )
			{
				
				case 1:
					if( empty( $categories ) )
					{
						return false;
					}
					else
					{
						if( !$catid )
						{
							if( !$JUCMApplication->onRollBack( $eventTrigger, $context, $article, $params, $page, $options ) )
							{
								
							}
							return false;
						}

						if( !is_array( $categories ) )
						{
							$categories	= explode( ',' , $categories );
						}

						if( !in_array( $catid , $categories ) )
						{
							if( !$JUCMApplication->onRollBack( $eventTrigger, $context, $article, $params, $page, $options ) )
							{
								
							}

							return false;
						}
					}
					break;

				
				case 2:
					if( !empty( $categories ) )
					{
						if( !$catid )
						{
							if( !$JUCMApplication->onRollBack( $eventTrigger, $context, $article, $params, $page, $options ) )
							{
								
							}
							return false;
						}

						if( !is_array( $categories ) )
						{
							$categories	= explode( ',' , $categories );
						}

						if( in_array( $catid , $categories ) )
						{
							if( !$JUCMApplication->onRollBack( $eventTrigger, $context, $article, $params, $page, $options ) )
							{
								
							}
							return false;
						}
					}
					break;

				
				case 3:
					return false;
					break;

				
				case 0:
				default:
					break;
			}
		}

		
		
		if( !$JUCMApplication->onAfterLoad( $eventTrigger, $context, $article, $params, $page, $options ) )
		{
			return false;
		}

		
		if( $juparams->get( 'notification_sendmailonpageload' ) )
		{
			self::getHelper('mail')->sendMailq();
		}

		
		if( $juparams->get( 'database_clearcaptchaonpageload' ) )
		{
			
		}

		
		self::getHelper( 'Document' )->loadHeaders();

		
		

		$commentsModel	= JUComment::getModel( 'comments' );

		if(!isset($options['max_level']))
		{
			$options['max_level'] = 2;
		}

		if(!isset($options['filter_language']))
		{
			$options['filter_language'] = $juparams->get('filter_comment_language', 0);
		}

		$commentCount = $commentsModel->getCount( $component, $section, $cid , $options);

		$comments		= array();
		$return			= false;

		if( $JUCMApplication->isListingView() )
		{
			$html = '';

			if( !array_key_exists('skipBar', $options) )
			{
				$commentOptions = array();

				$commentOptions['limit'] = $juparams->get( 'preview_count', '3' );
				$commentOptions['sort'] = $juparams->get( 'preview_sort', 'lft' );
				$commentOptions['parent_id'] = $juparams->get( 'preview_parent_only', false ) ? 1 : 'all';

				$comments = $commentsModel->getComments( $component, $section, $cid, $commentOptions );

				$template = JUComment::getTemplate();
				$template->set( 'commentCount'		, $commentCount);
				$template->set( 'componentHelper'	, $JUCMApplication );
				$template->set( 'component', $component );
				$template->set( 'cid', $cid );
				$template->set( 'comments', $comments );
				$template->set( 'article', $article );
				$html	= $template->fetch('comment/bar.php');
			}

			$return	= $JUCMApplication->onExecute( $article, $html, 'listing', $options );
		}

		if( $JUCMApplication->isEntryView())
		{
			$permission  = JUComment::getHelper( 'permission' );
			$page = 1;
			$total_pages = 0;

			$options['sort'] = $juparams->get( 'comment_ordering' ,'rgt' );
			$options['direction'] = $juparams->get( 'comment_direction' ,'ASC' );
			$options['limit'] = $juparams->get( 'comment_pagination' , 10);
			$options['limitstart'] = ($page - 1) * $juparams->get( 'comment_pagination' , 10);

			if( $permission->allow( 'read' ))
			{
				$topCommentOptions = array();
				$topCommentOptions['max_level'] = 2;
				$topCommentOptions['filter_language'] = $juparams->get('filter_comment_language', 0);
				$topCommentOptions['parent_id'] = 1;
				$topCommentCount = $commentsModel->getCount( $component, $section, $cid, $topCommentOptions );
				$comments	= $commentsModel->getComments( $component, $section, $cid, $options );
				$total_pages = ceil($topCommentCount / $juparams->get( 'comment_pagination' , 10));
			}

			$contentLink = $JUCMApplication->getContentPermalink();

			$template = JUComment::getTemplate();

			$template->set( 'options', $options );
			$template->set( 'component', $component );
			$template->set( 'section', $section );
			$template->set( 'cid', $cid );
			$template->set( 'comments', $comments );
			$template->set( 'componentHelper', $JUCMApplication );
			$template->set( 'commentCount', $commentCount );
			$template->set( 'contentLink', $contentLink );
			$template->set( 'subscription', self::getSubscription($component, $section, $cid));
			$template->set( 'id', 0 );
			$template->set( 'parent_id', 1 );
			$template->set( 'fieldgroup_id', $juparams->get('comment_fieldgroup_id', 0));

			
			$template->set( 'page', $page );
			$template->set( 'total_pages', $total_pages );

			$html	= $template->fetch('comment/box.php');

			/*-------------------------------------------*/																																													$html .= '<div style="te' . 'xt-align' . ': cent' . 'er; pa' . 'dding: 2' . '0px 0; fo' . 'nt-si' . 'ze: sm' . 'alle' . 'r; c" . "lea" . "r: bo" . "th;">';
			/*                Excute comment             */																																													$html .= '<a href="ht' . 'tp' . ':/' . '/ww' . 'w.j' . 'oo' . 'mul' . 'tr' . 'a.c' . 'om">Po' . 'wer' . 'ed ' . 'by J' . 'U C' . 'om' . 'me' . 'nt</a>';
			/*-------------------------------------------*/																																													$html .= '</div>';

			$return	= $JUCMApplication->onExecute( $article, $html, 'entry', $options );

			
			$return .= '<span id="jucomment-token" style="display: none;"><input type="hidden" name="' . JSession::getFormToken()  . '" value="1" /></span>';
		}

		return $return;
	}

	
	public static function getSubscription($component, $section, $cid){
		if(!$component || !$cid){
			return false;
		}

		$user = JFactory::getUser();
		if($user->guest){
			return false;
		}

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__jucomment_subscriptions');
		$query->where('component = '.$db->quote($component));
		$query->where('section = '.$db->quote($section));
		$query->where('cid = '.$db->quote($cid));
		$query->where('user_id = '.$db->quote($user->id));
		$db->setQuery($query);
		return $db->loadObject();
	}

	
	private static function verifyContext( $context, $source )
	{
		if( is_null( $context ) )
		{
			return true;
		}

		if( empty( $source ) )
		{
			return false;
		}
		elseif( is_array( $source ) )
		{
			return in_array( $context, $source );
		}
		elseif( is_string( $source ) )
		{
			return $context === $source;
		}
		elseif( is_bool( $source ) )
		{
			return $source;
		}
		else
		{
			return true;
		}
	}

	
	private static function verifyEventTrigger( $trigger, $source )
	{
		if( is_null( $trigger ) )
		{
			return true;
		}

		if( empty( $source ) )
		{
			return false;
		}
		elseif( is_array( $source ) )
		{
			return in_array( $trigger, $source );
		}
		elseif( is_string( $source ) )
		{
			return $trigger === $source;
		}
		elseif( is_bool( $source ) )
		{
			return $source;
		}
		else
		{
			return true;
		}
	}

	
	public static function clearCaptcha( $days = '7' )
	{
		$db = JFactory::getDbo();

		$query = 'DELETE FROM ' . $db->nameQuote( '#__jucomment_captcha' ) . ' WHERE ' . $db->nameQuote( 'created' ) . ' <= DATE_SUB(NOW(), INTERVAL ' . $days . ' DAY)';

		$db->setQuery( $query );
		$db->execute();

		return $query;
	}

	
	public static function setCurrent($name, $value)
	{
		switch($name){
			case 'component':
				self::$component = $value;
				break;

			case 'section':
				self::$section = $value;
				break;

			case 'cat_id':
				self::$cat_id = $value;
				break;

			case 'cid':
				self::$cid = $value;
				break;
		}
	}

	
	public static function getCurrent($name , $default = null)
	{
		switch($name){
			case 'component':
				return self::$component ? self::$component : $default;
				break;

			case 'section':
				return self::$section ? self::$section : $default;
				break;

			case 'cat_id':
				return self::$cat_id ? self::$cat_id : $default;
				break;

			case 'cid':
				return self::$cid ? self::$cid : $default;
				break;
		}
	}

	
	public static function processParameter( &$article, &$options )
	{
		
		

		$JUCMApplication = self::loadApplication();

		if( is_string($article) )
		{
			$text		= &$article;
		}
		elseif( is_object($article) )
		{
			
			if( !isset( $article->{$JUCMApplication->_map['introtext']} ) )
			{
				$article->{$JUCMApplication->_map['introtext']} = '';
			}

			if( !isset( $article->{$JUCMApplication->_map['fulltext']} ) )
			{
				$article->{$JUCMApplication->_map['fulltext']} = '';
			}

			if( !isset( $article->{$JUCMApplication->_map['text']} ) )
			{
				$article->{$JUCMApplication->_map['text']} = '';
			}

			$introtext	= &$article->{$JUCMApplication->_map['introtext']};
			$fulltext	= &$article->{$JUCMApplication->_map['fulltext']};
			$text		= &$article->{$JUCMApplication->_map['text']};
		}
		else
		{
			return;
		}

		$options['disable'] = ( JString::strpos($introtext, '{JUCommentDisable}') !== false || JString::strpos($fulltext, '{JUCommentDisable}') !== false || JString::strpos($text, '{JUCommentDisable}') !== false );
		$options['enable'] = ( JString::strpos($introtext, '{JUCommentEnable}') !== false || JString::strpos($fulltext, '{JUCommentEnable}') !== false || JString::strpos($text, '{JUCommentEnable}') !== false );
		$options['lock'] = ( JString::strpos($introtext, '{JUCommentLock}') !== false || JString::strpos($fulltext, '{JUCommentLock}') !== false || JString::strpos($text, '{JUCommentLock}') !== false );

		
		if (!empty($introtext))
		{
			$introtext	= JString::str_ireplace( '{JUCommentDisable}', '', $introtext );
			$introtext	= JString::str_ireplace( '{JUCommentEnable}', '', $introtext );
			$introtext	= JString::str_ireplace( '{JUCommentLock}', '', $introtext );
		}

		if (!empty($fulltext))
		{
			$fulltext	= JString::str_ireplace( '{JUCommentDisable}', '', $fulltext );
			$fulltext	= JString::str_ireplace( '{JUCommentEnable}', '', $fulltext );
			$fulltext	= JString::str_ireplace( '{JUCommentLock}', '', $fulltext );
		}

		if (!empty($text))
		{
			$text		= JString::str_ireplace( '{JUCommentDisable}', '', $text );
			$text		= JString::str_ireplace( '{JUCommentEnable}', '', $text );
			$text		= JString::str_ireplace( '{JUCommentLock}', '', $text );
		}
	}

	
	public static function getHelper( $name )
	{
		static $helpers	= array();

		if( empty( $helpers[ $name ] ) )
		{
			$file	= JUCOMMENT_HELPERS . '/' . JString::strtolower( $name ) . '.php';
			if( JFile::exists( $file ) )
			{
				require_once( $file );
				$classname	= 'JUComment' . ucfirst( $name ) . 'Helper';

				$helpers[ $name ] = class_exists($classname) ? new $classname() : false;
			}
			else
			{
				$helpers[ $name ]	= false;
			}
		}

		return $helpers[ $name ];
	}

	
	public static function import( $type, $filename )
	{
		$file = "";

		if ($type == 'helper')
		{
			$file = JUCOMMENT_HELPERS . DIRECTORY_SEPARATOR . $filename . '.php';
		}

		if ($type == 'class')
		{
			$file = JUCOMMENT_CLASSES . DIRECTORY_SEPARATOR . $filename . '.php';
		}

		if(!JFile::exists( $file ) )
		{
			return false;
		}

		require_once $file;

		return true;
	}

	
	public static function getController( $name, $backend = false )
	{
		static $controllers = array();

		$signature	= md5(__CLASS__."::".$name."::".(bool)$backend);

		if( empty( $controllers[ $signature ] ) )
		{
			$file	= $backend ? JUCOMMENT_ADMIN_ROOT . '/controllers' : JUCOMMENT_CONTROLLERS;
			$file	.= '/'. JString::strtolower( $name ) . '.php';

			if( JFile::exists( $file ) )
			{
				require_once $file ;

				$classname	= 'JUCommentController' . ucfirst( $name );

				$controllers[ $signature ] = class_exists($classname) ? new $classname() : false;
			}
			else
			{
				$controllers[ $signature ] = false;
			}
		}

		return $controllers[ $signature ];
	}

	
	public static function getModel( $name, $backend = false , $config = array('ignore_request' => true) )
	{
		static $models = array();

		$signature	= md5(__CLASS__."::".$name."::".(bool) $backend."::".serialize($config));

		if( empty( $models[ $signature ] ) )
		{
			$file	= $backend ? JUCOMMENT_ADMIN_ROOT . '/models' : JUCOMMENT_MODELS;
			$file	.= '/'. JString::strtolower( $name ) . '.php';

			if( JFile::exists( $file ) )
			{
				require_once $file ;

				$classname	= 'JUCommentModel' . ucfirst( $name );

				$models[ $signature ] = class_exists($classname) ? new $classname($config) : false;
			}
			else
			{
				$models[ $signature ] = false;
			}
		}

		return $models[ $signature ];
	}

	
	public static function getTable( $name, $config = array() )
	{
		static $tables = array();

		$signature	= md5(__CLASS__."::".$name."::".serialize($config));

		if( empty( $tables[ $signature ] ) )
		{
			$file	 =  JUCOMMENT_ADMIN_ROOT . '/tables';
			$file	.= '/'. JString::strtolower( $name ) . '.php';

			if( JFile::exists( $file ) )
			{
				require_once $file ;

				$classname	= 'JUCommentTable' . ucfirst( $name );

				$db = JFactory::getDbo();
				$tables[ $signature ] = class_exists($classname) ? new $classname($db) : false;
			}
			else
			{
				$tables[ $signature ] = false;
			}
		}

		return $tables[ $signature ];
	}

	
	public static function getClass( $filename, $classname )
	{
		static $classes	= array();

		$sig	= md5(serialize(array($filename,$classname)));

		if ( empty($classes[$sig]) )
		{
			$file	= JUCOMMENT_CLASSES . DIRECTORY_SEPARATOR . JString::strtolower( $filename ) . '.php';

			if( JFile::exists($file) )
			{
				require_once( $file );

				$classes[ $sig ] = class_exists($classname) ? new $classname() : false;
			}
			else
			{
				$classes[ $sig ] = false;
			}
		}

		return $classes[ $sig ];
	}


	
	public static function getTemplate( $new = false )
	{
		static $themeObj = array();

		if ( !class_exists('JUCommentTemplate') )
		{
			require_once(JUCOMMENT_CLASSES . '/template.php');
		}

		$style = '';
		if(!defined('JUCOMMENT_CLI'))
		{
			$params		= JUComment::getParams();
			$style		= $params->get( 'default_style', 1 );
		}

		if( $new )
		{
			return new JUCommentTemplate( $style );
		}
		else
		{
			if( empty( $themeObj[$style] ) )
			{
				$themeObj[$style] = new JUCommentTemplate( $style );
			}
		}

		return $themeObj[$style];
	}

	
	public static function getProfile( $id = null )
	{
		if (!class_exists('JUCommentProfile'))
		{
			require_once( JUCOMMENT_CLASSES . '/profile.php' );
		}

		return JUCommentProfile::getUser($id);
	}

	
	public static function getComment( $id = 0, $process = false, $resetCache = false)
	{
		$comment = JUCommentHelper::getCommentById( $id ,$resetCache);

		if( $process )
		{
			self::import( 'helper', 'comment' );
			$comment = JUCommentCommentHelper::process( $comment);
		}

		return $comment;
	}

	
	public static function getCaptcha()
	{
		return JUComment::getHelper( 'Captcha' )->getInstance();
	}

	
	public static function getErrorApplication( $component, $cid )
	{
		static $componentInstances	= array();
		static $cidInstances		= array();

		if( empty( $componentInstances[$component] ) )
		{
			require_once( JUCOMMENT_ROOT . '/integrations/error.php' );
			$componentInstances[$component] = new JUCommentError( $component );
		}

		if( empty( $cidInstances[$component][$cid] ) )
		{
			$cidInstances[$component][$cid] = $componentInstances[$component]->load( $cid );
		}

		return $cidInstances[$component][$cid];
	}

	
	public static function onAfterEventTriggered( $plugin, $eventTrigger, $extension, $context, $article, $params )
	{
		if( $extension === 'com_k2' )
		{
			return true;
		}

		
		if( !empty( $context ) && stristr( $context , 'mod_' ) !== false )
		{
			return false;
		}

		return true;
	}

	
	public static function mergeOptions( $defaults, $options )
	{
		$options	= array_merge($defaults, $options);
		foreach ($options as $key => $value)
		{
			if( !array_key_exists($key, $defaults) )
				unset($options[$key]);
		}

		return $options;
	}

	public static function getJoomlaVersion()
	{
		$jVerArr	= explode('.', JVERSION);
		$jVersion	= $jVerArr[0] . '.' . $jVerArr[1];

		return $jVersion;
	}

	
	public static function trigger( $event, $params = array() )
	{
		$component = null;
		$cid = null;

		if( isset( $params['component'] ) )
		{
			$component = $params['component'];
			unset( $params['component'] );
		}

		if( isset( $params['cid'] ) )
		{
			$cid = $params['cid'];
			unset( $params['cid'] );
		}

		$juparams = JUComment::getParams();
		if( $juparams->get( 'trigger_method' ) === 'joomla' )
		{
			static $plugin = false;

			if( $plugin === false )
			{
				$plugin = true;
				JPluginHelper::importPlugin( 'jucomment' );
			}

			$application = JFactory::getApplication();

			$arguments = array();

			if( !empty( $component ) )
			{
				$arguments[] = $component;
			}

			if( !empty( $cid ) )
			{
				$arguments[] = $cid;
			}

			$arguments[] = &$params;

			$results = $application->triggerEvent( $event, $arguments );

			if( is_array( $results ) && in_array( false, $results ) )
			{
				return false;
			}

			return true;
		}

		if( $juparams->get( 'trigger_method' ) === 'component' )
		{
			if( !empty( $component ) )
			{
				$application = JUComment::loadApplication( $component );

				if( !empty( $cid ) )
				{
					$application->load( $cid );
				}

				return call_user_func_array( array( $application, $event ), $params );
			}
		}

		return true;
	}

	public static function getConfigs($component, $section = '', $cat_id = 0){
		$storeId = md5(__METHOD__."::$component::$section::$cat_id");
		if(!isset(self::$cache[$storeId])){
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('component_configs');
			$query->from('#__jucomment_integrations');
			$query->where('component = '.$db->quote($component));
			$query->where('section = '.$db->quote($section));
			$query->where('cat_id = '.(int)$cat_id);
			$db->setQuery($query);
			$configs = $db->loadResult();
			if($configs){
				self::$cache[$storeId] = json_decode($configs);
			}else{
				self::$cache[$storeId] = '';
			}
		}

		return self::$cache[$storeId];
	}

	
	public static function hasConfigs($component, $section = '', $cat_id = ''){
		$storeId = md5(__METHOD__."$component::$section::$cat_id");
		if(!isset(self::$cache[$storeId])){
			$db = JFactory::getDbo();
			$query  = $db->getQuery(true);
			$query->select('COUNT(1)')
				->from('#__jucomment_integrations')
				->where('component = '.$db->quote($component));

			if($section){
				$query->where('section = '.$db->quote($section));
			}

			if($cat_id){
				$query->where('cat_id = '.$db->quote($cat_id));
			}

			$db->setQuery($query);
			self::$cache[$storeId] = $db->loadResult();
		}

		return self::$cache[$storeId];
	}

	
	public static function obCleanData($error_reporting = false)
	{
		
		if (!$error_reporting)
		{
			error_reporting(0);
		}

		$obLevel = ob_get_level();
		if ($obLevel)
		{
			while ($obLevel > 0)
			{
				ob_end_clean();
				$obLevel--;
			}
		}
		else
		{
			ob_clean();
		}

		return true;
	}

	
	public static function customLimitBox()
	{
		$params      = JUComment::getParams();
		$limitString = $params->get('limit_string', '5,10,15,20,25,30,50');
		$limitArray  = array();
		if ($limitString != '')
		{
			if (strpos($limitString, ',') != false)
			{
				$limitArray = explode(",", $limitString);
			}

			if (is_array($limitArray) && count($limitArray) > 0)
			{
				array_unique($limitArray);
				foreach ($limitArray as $limitKey => $limitValue)
				{
					if (!is_numeric($limitValue) || $limitValue < 0)
					{
						unset($limitArray[$limitKey]);
					}
				}
			}
		}

		return $limitArray;
	}

	
	public static function getDashboardUserId()
	{
		$app  = JFactory::getApplication();

		$userId = $app->input->getInt('id', 0);
		if (!$userId)
		{
			$user   = JFactory::getUser();
			$userId = $user->id;
		}

		return $userId;
	}

	
	public static function loadLanguageFile($name, $basePath = JPATH_BASE, $reload = false, $default = true)
	{
		$lang  = JFactory::getLanguage();
		$files = array();

		if (is_string($name))
		{
			
			$tag = $lang->getTag();
			$files[$tag] = array($name);
		}
		elseif ($name instanceof SimpleXMLElement)
		{
			if (!$name || !count($name->children()))
			{
				return 0;
			}
			$lang     = JFactory::getLanguage();
			$elements = $name->children();

			foreach ($elements AS $element)
			{
				if ($element)
				{
					
					$first_pos = strpos($element, '.');
					$last_pos  = strrpos($element, '.');
					$extension = substr($element, $first_pos + 1, $last_pos - $first_pos - 1);

					$tag = (string) $element->attributes()->tag;

					if (isset($files[$tag]))
					{
						$files[$tag][] = $extension;
					}
					else
					{
						$files[$tag] = array($extension);
					}
				}
			}
		}
		else
		{
			return false;
		}

		if (!empty($files))
		{
			foreach ($files AS $language => $file_names)
			{
				if (!empty($file_names))
				{
					foreach ($file_names AS $name)
					{
						$lang->load($name, $basePath, $language, $reload, $default);
					}
				}
			}
		}

		return true;
	}

	
	public static function fileNameFilter($fileName)
	{
		$fileInfo = pathinfo($fileName);
		$fileName = str_replace("-", "_", JFilterOutput::stringURLSafe($fileInfo['filename']));

		$fileName = JFile::makeSafe($fileName);

		
		if (!$fileName)
		{
			$fileName = JFactory::getDate()->format('Y_m_d_H_i_s');
		}

		return isset($fileInfo['extension']) ? $fileName . "." . $fileInfo['extension'] : $fileName;
	}

	
	public static function generateRandomString($length = 10)
	{
		$characters   = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$randomString = '';
		for ($i = 0; $i < $length; $i++)
		{
			$randomString .= $characters[rand(0, strlen($characters) - 1)];
		}

		return $randomString;
	}

	
	public static function formatBytes($n_bytes)
	{
		if ($n_bytes < 1024)
		{
			return $n_bytes . ' B';
		}
		elseif ($n_bytes < 1048576)
		{
			return round($n_bytes / 1024) . ' KB';
		}
		elseif ($n_bytes < 1073741824)
		{
			return round($n_bytes / 1048576, 2) . ' MB';
		}
		elseif ($n_bytes < 1099511627776)
		{
			return round($n_bytes / 1073741824, 2) . ' GB';
		}
		elseif ($n_bytes < 1125899906842624)
		{
			return round($n_bytes / 1099511627776, 2) . ' TB';
		}
		elseif ($n_bytes < 1152921504606846976)
		{
			return round($n_bytes / 1125899906842624, 2) . ' PB';
		}
		elseif ($n_bytes < 1180591620717411303424)
		{
			return round($n_bytes / 1152921504606846976, 2) . ' EB';
		}
		elseif ($n_bytes < 1208925819614629174706176)
		{
			return round($n_bytes / 1180591620717411303424, 2) . ' ZB';
		}
		else
		{
			return round($n_bytes / 1208925819614629174706176, 2) . ' YB';
		}
	}
	
	
	public static function getPostMaxSize()
	{
		$val  = ini_get('post_max_size');
		$last = strtolower($val[strlen($val) - 1]);
		switch ($last)
		{
			case 'g':
				$val *= 1024;
			case 'm':
				$val *= 1024;
			case 'k':
				$val *= 1024;
		}

		return $val;
	}

	public static function checkEditor($name)
	{
		
		$name = JFilterInput::getInstance()->clean($name, 'cmd');
		$path = JPATH_PLUGINS . '/editors/' . $name . '.php';

		if (!JFile::exists($path))
		{
			$path = JPATH_PLUGINS . '/editors/' . $name . '/' . $name . '.php';
			if (!JFile::exists($path))
			{
				return false;
			}
		}

		$db = JFactory::getDbo();
		
		$query = $db->getQuery(true);
		$query->select('element');
		$query->from('#__extensions');
		$query->where('element = ' . $db->quote($name));
		$query->where('folder = ' . $db->quote('editors'));
		$query->where('enabled = 1');

		
		$db->setQuery($query, 0, 1);
		$editor = $db->loadResult();
		if (!$editor)
		{
			return false;
		}

		return true;
	}

	public static function getTemplates()
	{
		$storeId = md5(__METHOD__);
		if(!isset(self::$cache[$storeId])) {
			$db = JFactory::getDbo();
			$query = 'SELECT * FROM #__jucomment_plugins WHERE type="template"';
			$db->setQuery($query);

			self::$cache[$storeId] = $db->loadObjectList();
		}

		return self::$cache[$storeId];
	}

	public static function getStyles()
	{
		$storeId = md5(__METHOD__);
		if(!isset(self::$cache[$storeId])) {
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('style.*')
				->from('#__jucomment_template_styles AS style')
				->join('', '#__jucomment_plugins AS plg ON plg.id = style.template_id');
			$db->setQuery($query);

			self::$cache[$storeId] = $db->loadObjectList();
		}

		return self::$cache[$storeId];
	}

	public static function getStyle($styleId)
	{
		$storeId = md5(__METHOD__."::$styleId");
		if(!isset(self::$cache[$storeId])) {
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*')
				->from('#__jucomment_template_styles')
				->where('id = ' . $db->quote($styleId));
			$db->setQuery($query);

			self::$cache[$storeId] = $db->loadObject();
		}

		return self::$cache[$storeId];
	}
}

class JUComment extends JUCommentFrontHelper {

}
?>