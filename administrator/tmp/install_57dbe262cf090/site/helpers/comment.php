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

// No direct access to this file
defined('_JEXEC') or die('Restricted access');


class JUCommentCommentHelper
{
	
	protected static $cache = array();

	
	public static function getRootComment()
	{
		$storeId = md5(__METHOD__);
		if (!isset(self::$cache[$storeId]))
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from('#__jucomment_comments');
			$query->where('parent_id = 0');
			$query->where('level = 0');
			$db->setQuery($query);
			self::$cache[$storeId] = $db->loadObject();
		}

		return self::$cache[$storeId];
	}

	
	public static function getCommentObject($commentId, $select = 'cm.*', $resetCache = false)
	{
		if (!$commentId)
		{
			return null;
		}

		
		if (strpos(",", $select) !== false)
		{
			$selectColumnArr = explode(",", $select);
			sort($selectColumnArr);
			$select = implode(",", $selectColumnArr);
		}

		$storeID = md5(__METHOD__ . "::" . $commentId . "::" . $select);
		if (!isset(self::$cache[$storeID]) || $resetCache)
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select($select);
			$query->from('#__jucomment_comments AS cm');
			$query->where('cm.id = ' . $commentId);
			$db->setQuery($query);
			self::$cache[$storeID] = $db->loadObject();
		}

		return self::$cache[$storeID];
	}

	
	public static function parseCommentText($str)
	{
		$str = JUCommentCommentHelper::BBCode2Html($str);
		$str = JUCommentCommentHelper::autoLinkVideo($str);
		$str = JUCommentCommentHelper::autoLinkUrl($str);
		$str = JUCommentCommentHelper::replaceForbiddenWords($str);

		$params = JUComment::getParams();
		if ($params->get("parse_plugin", 0))
		{
			$str = JHtml::_('content.prepare', $str);
		}

		return $str;
	}

	protected static function autoLinkUrl($str){

		$params                       = JUComment::getParams();
		$auto_link_url_in_comment     = $params->get('auto_link_url_in_comment', 0);
		$trim_long_url_in_comment     = $params->get('trim_long_url_in_comment', 0);
		$front_portion_url_in_comment = $params->get('front_portion_url_in_comment', 0);
		$back_portion_url_in_comment  = $params->get('back_portion_url_in_comment', 0);

		if ($auto_link_url_in_comment)
		{
			if ($params->get('nofollow_link_in_comment', 1))
			{
				$noFollow = 'rel="nofollow"';
			}
			else
			{
				$noFollow = '';
			}

			$regex = "#\shttp(?:s)?:\/\/(?:www\.)?[\.0-9a-z]{1,255}(\.[a-z]{2,4}){1,2}([\/\?][^\s]{1,}){0,}[\/]?#i";
			preg_match_all($regex, $str, $matches);

			$matches = array_unique($matches[0]);

			if (count($matches) > 0)
			{
				JUComment::import('helper', 'string');
				foreach ($matches AS $url)
				{
					$url        = trim($url);
					$shortenUrl = urldecode($url);
					
					if ($trim_long_url_in_comment > 0 && strlen($shortenUrl) > $trim_long_url_in_comment)
					{
						if ($front_portion_url_in_comment > 0 || $back_portion_url_in_comment > 0)
						{
							$frontStr   = $front_portion_url_in_comment > 0 ? substr($shortenUrl, 0, $front_portion_url_in_comment) : "";
							$backStr    = $back_portion_url_in_comment > 0 ? substr($shortenUrl, (int) (0 - $back_portion_url_in_comment)) : "";
							$shortenUrl = $frontStr . '...' . $backStr;
						}

						$shortenUrl = '<a ' . $noFollow . ' href="' . $url . '">' . $shortenUrl . '</a> ';
						$str        = str_replace(trim($url), $shortenUrl, $str);
						$str        = JUCommentStringHelper::replaceIgnore(trim($url), $shortenUrl, $str);
					}
					
					else
					{
						$str = JUCommentStringHelper::replaceIgnore($url, '<a ' . $noFollow . ' href="' . $url . '">' . trim($shortenUrl) . '</a> ', $str);
					}
				}
			}
		}

		return $str;
	}

	
	protected static function replaceForbiddenWords($str){
		$params = JUComment::getParams();
		$forbidden_words = array_map('trim', explode(",", strtolower(str_replace("\n", ",", $params->get('forbidden_words', '')))));
		if (trim($params->get('forbidden_words', '')) && count($forbidden_words))
		{
			$forbidden_words_replaced_by = $params->get('forbidden_words_replaced_by', '***');
			foreach ($forbidden_words as $value)
			{
				if($value)
				{
					$str = preg_replace('#' . $value . '#ism', $forbidden_words_replaced_by, $str);
				}
			}
		}

		return $str;
	}

	
	protected static function autoLinkVideo($text)
	{
		$params                        = JUComment::getParams();
		$auto_embed_youtube_in_comment = $params->get('auto_embed_youtube_in_comment', 0);
		$auto_embed_vimeo_in_comment   = $params->get('auto_embed_vimeo_in_comment', 0);
		$video_width_in_comment        = $params->get('video_width_in_comment', 360);
		$video_height_in_comment       = $params->get('video_height_in_comment', 240);

		
		if ($auto_embed_youtube_in_comment)
		{
			$regexYoutube = "#(?:http(?:s)?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:v|vi|user)\/))([^\?&\"'<>\/\s]+)(?:$|\/|\?|\&)?#i";
			preg_match_all($regexYoutube, $text, $matchesYoutube);
			if(count($matchesYoutube[0])){
				foreach ($matchesYoutube[0] as $key => $match)
				{
					$youtube_html = self::parseVideo($match, $video_width_in_comment, $video_height_in_comment);
					$text         = str_replace($matchesYoutube[0][$key], $youtube_html . '<br/>', $text);
				}
			}
		}

		
		if ($auto_embed_vimeo_in_comment)
		{
			$regexVimeo = "#(?:http(?:s)?:\/\/)?(?:www\.)?vimeo.com\/(\d+)(?:$|\/|\?)?#";
			preg_match_all($regexVimeo, $text, $matchesVimeo);
			if(count($matchesVimeo[0])){
				$arrIdVimeo = array_unique($matchesVimeo[0]);
				foreach ($arrIdVimeo as $key => $match)
				{
					$vimeo_html = self::parseVideo($match, $video_width_in_comment, $video_height_in_comment);
					$text       = str_replace($matchesVimeo[0][$key], $vimeo_html, $text);
				}
			}
		}

		return $text;
	}

	
	public static function parseVideo($url, $video_width_in_comment = 360, $video_height_in_comment = 240)
	{
		$document = JFactory::getDocument();

		
		$ytRegex = "#(?:http(?:s)?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&\"'<>\/\s]+)(?:$|\/|\?|\&)?#i";
		preg_match($ytRegex, $url, $ytMatches);

		if (isset($ytMatches[1]))
		{
			$videoId = $ytMatches[1];
			if ($videoId)
			{
				$src          = "http://www.youtube.com/embed/" . $videoId . "?hd=1&wmode=opaque&controls=1&showinfo=0;rel=0";
				$youtube_html = '<iframe width="' . $video_width_in_comment . '" height="' . $video_height_in_comment . '" src="' . $src . '" frameborder="0" allowfullscreen ></iframe>';
			}
			else
			{
				$youtube_html = '';
			}

			$document->addScript("https://www.youtube.com/iframe_api");

			return $youtube_html;
		}

		
		$vmRegex = "#(?:http(?:s)?:\/\/)?(?:www\.)?(?:player\.)?vimeo.com(?:\/video)?\/(\d+)(?:$|\/|\?)?#";
		preg_match($vmRegex, $url, $vmMatches);
		if (isset($vmMatches[1]))
		{
			$videoId = $vmMatches[1];
			if ($videoId)
			{
				$src        = "http://player.vimeo.com/video/" . $videoId . "?title=0&byline=0&portrait=0;api=1";
				$vimeo_html = '<iframe width="' . $video_width_in_comment . '" height="' . $video_height_in_comment . '" src="' . $src . '" frameborder="0" allowfullscreen ></iframe>';
			}
			else
			{
				$vimeo_html = '';
			}
			$document->addScript("http://a.vimeocdn.com/js/froogaloop2.min.js");

			return $vimeo_html;
		}

		return false;
	}

	
	public static function getTotalCommentsOnArticleOfUser($cid, $user)
	{
		if (!$user)
		{
			return 0;
		}

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('COUNT(*)');
		$query->from('#__jucomment_comments');
		$query->where('cid = ' . $cid);
		if(is_numeric($user)){
			$query->where('user_id = ' . $user);
		}else{
			$ip = JUComment::getIpAddress();
			$query->where('(guest_email = ' . $db->quote($user).' OR ip_address = '.$db->quote($ip).')');
		}

		$query->where('level = 1');
		$db->setQuery($query);
		$result = $db->loadResult();

		return $result;
	}

	
	public static function getTotalApprovedCommentsOfUser($userId, $type = 'comment')
	{
		if (!$userId)
		{
			return 0;
		}
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('COUNT(*)');
		$query->from('#__jucomment_comments');
		$query->where('user_id = ' . $userId);
		$query->where('approved = 1');
		if($type == 'comment'){
			$query->where('level = 1');
		}else{
			$query->where('level > 1');
		}
		$db->setQuery($query);
		$result = $db->loadResult();

		return $result;
	}

	
	static public function process( $row)
	{
		if( isset( $row->processed ) && $row->processed )
		{
			return $row;
		}

		$userId = JFactory::getUser()->id;
		$params = JUComment::getParams();

		JUComment::import( 'helper', 'date' );

		
		
		

		
		if( !empty($row->url) )
		{
			
			$row->url = ( 0 === strpos( $row->url, 'http' ) ) ? $row->url : 'http://' . $row->url;
		}

		
		$application	= JUComment::loadApplication( $row->component, $row->section )->load( $row->cid );

		if( $application === false )
		{
			$application = JUComment::getErrorApplication( $row->component, $row->cid );
		}

		$row->cat_id = $application->getCategoryId();

		
		$row->componenttitle = $application->getComponentName();

		
		$row->contenttitle = $application->getContentTitle();
		
		$row->pagelink = $application->getContentPermalink();
		$row->permalink = $row->pagelink . '#comment-item-' . $row->id;
		$row->checkout_link = 'index.php?option=com_jucomment&task=modcomments.checkin&cid=' . $row->id . '&' . JSession::getFormToken() . '=1' . '&return=' . base64_encode($row->permalink);
		
		if( $row->parent_id != 0 )
		{
			$row->parentlink = $row->pagelink . '#comment-item-' . $row->parent_id;
		}

		
		$row->shortlink = $row->permalink;

		
		
		
		$row->extension = $application;

		
		$actionsModel = JUComment::getModel( 'actions' );

		
		$row->comment = self::parseCommentText( $row->comment );

		
		$row->author = JUComment::getProfile( $row->user_id );

		$row->email = $row->author->id ? $row->author->email : $row->guest_email;

		if( $row->user_id != 0 )
		{
			switch( $params->get( 'name_type', 'default' ) )
			{
				case 'username':
					
					$row->name = $row->author->getUsername();
					break;
				case 'name':
					$row->name = $row->author->getName();
					break;
				case 'default':
				default:
					
					if( empty( $row->name ) )
					{
						$row->name = $row->author->getName();
					}
					break;
			}
		}
		else
		{
			if( empty( $row->guest_name ) )
			{
				$row->name = JText::_( 'COM_JUCOMMENT_GUEST' );
			}
			else
			{
				$row->name = JText::_( 'COM_JUCOMMENT_GUEST' ) . ' - '. $row->guest_name;
			}
		}

		
		$row->voted = $actionsModel->voted( $row->id, $userId );

		
		$row->reported = $actionsModel->reported( $row->id, $userId );

		$row->processed = 1;

		return $row;
	}

	public static function BBCode2Html($text)
	{
		$text = trim($text);

		
		if (!function_exists('escape'))
		{
			function escape($s)
			{
				global $text;
				$text = strip_tags($text);
				$code = $s[1];
				$code = htmlspecialchars($code);
				$code = str_replace("[", "&#91;", $code);
				$code = str_replace("]", "&#93;", $code);

				return '<pre><code>' . $code . '</code></pre>';
			}
		}
		$text = preg_replace_callback('/\[code\](.*?)\[\/code\]/ms', "escape", $text);

		

		$in = array(
			'3:)', ':)', ':(',  ':P',':D',
			'&gt;:o', ':o', ';)', ':-/', ':v',':\'(',
			'^_^', '8-)', '&lt;3', '-_-', 'o.O',':3','(y)'

		);
		$smileFolder = JUri::root(true) . '/components/com_jucomment/assets/wysibb/theme/default/img/smiles/';
		
		$out  = array(
			'<img  src="' . $smileFolder . 'devil.png" />',
			'<img  src="' . $smileFolder . 'smile.png" />',
			'<img  src="' . $smileFolder . 'frown.png" />',
			'<img  src="' . $smileFolder . 'tongue.png" />',
			'<img  src="' . $smileFolder . 'grin.png" />',
			'<img  src="' . $smileFolder . 'angry.png" />',
			'<img  src="' . $smileFolder . 'gasp.png" />',
			'<img  src="' . $smileFolder . 'wink.png" />',
			'<img  src="' . $smileFolder . 'unsure.png" />',
			'<img  src="' . $smileFolder . 'pacman.png" />',
			'<img  src="' . $smileFolder . 'cry.png" />',
			'<img  src="' . $smileFolder . 'kiki.png" />',
			'<img  src="' . $smileFolder . 'glasses.png" />',
			'<img  src="' . $smileFolder . 'heart.png" />',
			'<img  src="' . $smileFolder . 'squinting.png" />',
			'<img  src="' . $smileFolder . 'confused.png" />',
			'<img  src="' . $smileFolder . 'colonthree.png" />',
			'<img  src="' . $smileFolder . 'like.png" />',
		);
		$text = str_replace($in, $out, $text);

		
		$in = array(
			'/\[b\](.*?)\[\/b\]/ms',
			'/\[i\](.*?)\[\/i\]/ms',
			'/\[u\](.*?)\[\/u\]/ms',
			'/\[align=(.*?)\](.*?)\[\/align\]/ms',
			'/\[img\](.*?)\[\/img\]/ms',
			'/\[email\](.*?)\[\/email\]/ms',
			'/\[url\="?(.*?)"?\](.*?)\[\/url\]/ms',
			'/\[size\="?(.*?)"?\](.*?)\[\/size\]/ms',
			'/\[color\="?(.*?)"?\](.*?)\[\/color\]/ms',
			'/\[background\="?(.*?)"?\](.*?)\[\/background\]/ms',
			'/\[list\=(.*?)\](.*?)\[\/list\]/ms',
			'/\[list\](.*?)\[\/list\]/ms',
			'/\[\*\]([^\[\]\*\<]*)/ms',
			'/\[left\](.*?)\[\/left\]/ms',
			'/\[right\](.*?)\[\/right\]/ms',
			'/\[center\](.*?)\[\/center\]/ms'
		);
		
		$out  = array('<strong>\1</strong>',
			'<em>\1</em>',
			'<u>\1</u>',
			'<p style="\1">\2</p>',
			'<img src="\1" alt="\1" />',
			'<a href="mailto:\1">\1</a>',
			'<a href="\1" rel="nofollow">\2</a>',
			'<span style="font-size:\1%">\2</span>',
			'<span style="color:\1">\2</span>',
			'<span style="background-color:\1">\2</span>',
			'<ol start="\1">\2</ol>',
			'<ul>\1</ul>',
			'<li>\1</li>',
			'<p style="text-align:left">\1</p>',
			'<p style="text-align:right">\1</p>',
			'<p style="text-align:center">\1</p>'
		);
		$text = preg_replace($in, $out, $text);

		$quoteFind    = array(
			
			'/\[quote\]/is',
			'/\[\/quote\]/is',
			'/\[quote\s*=\s*"?(.*?)"?\s*\]/is'
		);
		$quoteReplace = array(
			'<blockquote>',
			'</blockquote>',
			'<span class="author">$1 said:</span><blockquote>'
		);
		
		$count = 0;
		do
		{
			$text = preg_replace($quoteFind, $quoteReplace, $text, -1, $count);
		} while ($count > 0);

		
		$videoPatt = '/\[video\](.*?)\[\/video\]/ms';
		preg_match($videoPatt, $text, $matches);

		if($matches && $matches[1]){
			$vimeo_html = self::parseVideo($matches[1]);
			$text       = str_replace($matches[0], $vimeo_html, $text);
		}
		

		
		$text = str_replace("\r", "", $text);
		
		
		$text = preg_replace("/(\n){2,}/", "</p><p>", $text);

		$text = nl2br($text);

		
		
		if (!function_exists('removeBr'))
		{
			function removeBr($s)
			{
				return str_replace("<br />", "", $s[0]);
			}
		}
		$text = preg_replace_callback('/<pre>(.*?)<\/pre>/ms', "removeBr", $text);
		$text = preg_replace('/<p><pre>(.*?)<\/pre><\/p>/ms', "<pre>\\1</pre>", $text);

		$text = preg_replace_callback('/<ul>(.*?)<\/ul>/ms', "removeBr", $text);
		$text = preg_replace('/<p><ul>(.*?)<\/ul><\/p>/ms', "<ul>\\1</ul>", $text);

		return $text;
	}

	
	public static function getLatestCommentTime($user, $cid = 0){
		$db        = JFactory::getDbo();
		$query     = $db->getQuery(true);
		$query->select('created')
			->from('#__jucomment_comments')
			->order('id DESC');
		if($cid > 0){
			$query->where('cid = ' . (int)$cid);
		}

		if (is_numeric($user))
		{
			$query->where('user_id =' . $user);
		}
		else
		{
			$ipAddress = JUComment::getIpAddress();
			$query->where('(guest_email = '.$db->quote($user).' OR ip_address = ' . $db->quote($ipAddress) . ')');
		}

		$db->setQuery($query,0,1);
		$result  = $db->loadResult();
		return $result;
	}

	
	public static function checkNameOfGuest($guest_name)
	{
		
		$params         = JUComment::getParams();
		$forbiddenNames = array_map('trim', explode(",", strtolower(str_replace("\n", ",", $params->get('forbidden_names', '')))));
		$forbiddenNames = array_filter($forbiddenNames);
		if ($forbiddenNames && $guest_name)
		{
			foreach ($forbiddenNames as $value)
			{
				if($value != '')
				{
					$pattern = '/' . $value . '/i';
					if (preg_match($pattern, $guest_name))
					{
						return false;
					}
				}
			}
		}

		return true;
	}

	
	public static function checkEmailOfGuest($guest_email)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select("COUNT(*)");
		$query->from('#__users');
		$query->where('email = ' . $db->quote($guest_email));
		$db->setQuery($query);
		$total = $db->loadResult();
		if ($total)
		{
			return false;
		}else{
			return true;
		}
	}
}