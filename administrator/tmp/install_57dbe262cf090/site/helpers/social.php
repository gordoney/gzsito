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

class JUCommentSocialHelper
{
	public static function shortenUrl( $link )
	{
		if( !stristr( $link, rtrim( JUri::root() , '/' ) ) )
		{
			$link = rtrim( JUri::root() , '/' ) . '/' . ltrim( $link, '/' );
		}

		if( function_exists( 'curl_init' ) )
		{
			$ch = curl_init();

			curl_setopt($ch,CURLOPT_URL, "https://www.googleapis.com/urlshortener/v1/url");
			curl_setopt($ch,CURLOPT_POST, 1);
			curl_setopt($ch,CURLOPT_POSTFIELDS, json_encode(array("longUrl"=>$link)));
			curl_setopt($ch,CURLOPT_HEADER, 0);
			curl_setopt($ch,CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
			curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);

			$result = curl_exec($ch);

			if( $result )
			{
				curl_close($ch);

				$result = json_decode($result, true);

				if( array_key_exists( 'id', $result ) )
				{
					return $result['id'];
				}
				else
				{
					return $link;
				}
			}
			else
			{
				return $link;
			}
		}
		else
		{
			return $link;
		}
	}
}
