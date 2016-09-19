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



class JUCommentStringHelper
{

	public static function escape( $var )
	{
		return htmlspecialchars( $var, ENT_COMPAT, 'UTF-8' );
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

	
	public static function truncateHtml($text, $length = 320, $ending = '&hellip;', $exact = false, $considerHtml = true)
	{
		if ($considerHtml)
		{
			
			if (JString::strlen(preg_replace('/<.*?>/', '', $text)) <= $length)
			{
				return $text;
			}
			
			preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);
			$open_tags    = array();
			$total_length = 0;
			$truncate     = '';
			foreach ($lines AS $line_matchings)
			{
				
				if (!empty($line_matchings[1]))
				{
					
					if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings[1]))
					{
						
						
					}
					else
					{
						if (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings))
						{
							
							$pos = array_search($tag_matchings[1], $open_tags);
							if ($pos !== false)
							{
								unset($open_tags[$pos]);
							}
							
						}
						else
						{
							if (preg_match('/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings))
							{
								
								array_unshift($open_tags, strtolower($tag_matchings[1]));
							}
						}
					}
					
					$truncate .= $line_matchings[1];
				}
				
				$content_length = JString::strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));
				if ($total_length + $content_length > $length)
				{
					
					$left            = $length - $total_length;
					$entities_length = 0;
					
					if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE))
					{
						
						foreach ($entities[0] AS $entity)
						{
							if ($entity[1] + 1 - $entities_length <= $left)
							{
								$left--;
								$entities_length += JString::strlen($entity[0]);
							}
							else
							{
								
								break;
							}
						}
					}
					$truncate .= JString::substr($line_matchings[2], 0, $left + $entities_length);
					
					break;
				}
				else
				{
					$truncate .= $line_matchings[2];
					$total_length += $content_length;
				}
				
				if ($total_length >= $length)
				{
					break;
				}
			}
		}
		else
		{
			if (JString::strlen($text) <= $length)
			{
				return $text;
			}
			else
			{
				$truncate = JString::substr($text, 0, $length);
			}
		}
		
		if (!$exact && $length > 10)
		{
			$spacepos = JString::strrpos($truncate, ' ');
			if (isset($spacepos))
			{
				$truncate = JString::substr($truncate, 0, $spacepos);
			}
		}
		
		$truncate .= $ending;
		
		if ($considerHtml)
		{
			foreach ($open_tags AS $tag)
			{
				$truncate .= '</' . $tag . '>';
			}
		}

		return $truncate;
	}

	
	public static function replaceIgnore($search, $replace, $subject, $pos = 0)
	{
		if ($pos == 0)
		{
			$pos = strpos($subject, $search);
		}
		else
		{
			$pos = strpos($subject, $search, $pos);
		}

		$canReplace = true;

		
		$ignoredArr = array('href="', 'href=\'', 'src="', 'src=\'', 'alt="', 'alt=\'', 'data-mce-src="', 'data-mce-src=\'');
		foreach ($ignoredArr AS $value)
		{
			$valueLength = strlen($value);
			$subStr      = substr($subject, $pos - $valueLength, $valueLength);
			if ($subStr == $value)
			{
				$canReplace = false;
				break;
			}
		}

		
		if ($pos !== false && $pos < strlen($subject))
		{
			if ($canReplace == true)
			{
				
				$subject = substr_replace($subject, $replace, $pos, strlen($search));
				$pos     = $pos + strlen($replace);
			}
			else
			{
				
				$pos = $pos + strlen($search);
			}

			
			$subject = self::replaceIgnore($search, $replace, $subject, $pos);
		}

		return $subject;
	}
}
