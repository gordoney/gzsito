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

jimport('joomla.utilities.date');

class JUCommentDateHelper
{
	
	public static function dateWithOffSet($str='')
	{
		$userTZ = JUCommentDateHelper::getOffSet();
		$date	= JFactory::getDate($str);

		$user		= JFactory::getUser();
		$jConfig	= JFactory::getConfig();

		

		if($user->id != 0)
		{
			$userTZ	= $user->getParam('timezone');
		}

		if(empty($userTZ))
		{
			$userTZ	= $jConfig->get('offset');
		}

		$tmp = new DateTimeZone( $userTZ );
		$date->setTimeZone( $tmp );

		return $date;
	}

	public static function getDate($str='')
	{
		return JUCommentDateHelper::dateWithOffSet($str);
	}

	public static function geRawUnixTimeOld($str='')
	{
		$tzoffset 	= JUCommentDateHelper::getOffSet();
		$date 		= JFactory::getDate( $str );

		$newdate = mktime( ($date->toFormat('%H')  - $tzoffset),
							$date->toFormat('%M'),
							$date->toFormat('%S'),
							$date->toFormat('%m'),
							$date->toFormat('%d'),
							$date->toFormat('%Y'));
		return $newdate;
	}

	public static function getOffSet($numberOnly = false)
	{
		jimport('joomla.form.formfield');

		$user		= JFactory::getUser();
		$jConfig	= JFactory::getConfig();

		

		if($user->id != 0)
		{
			$userTZ	= $user->getParam('timezone');
		}

		if(empty($userTZ))
		{
			$userTZ	= $jConfig->get('offset');
		}

		if( $numberOnly )
		{
			$newTZ  	= new DateTimeZone($userTZ);
			$dateTime   = new DateTime( "now" , $newTZ );

			$offset		= $newTZ->getOffset( $dateTime ) / 60 / 60;
			return $offset;
		}
		else
		{
			
			return $userTZ;
		}
	}

	public static function enableDateTimePicker()
	{
		$document	= JFactory::getDocument();

		
		$html = '
		<script type="text/javascript">
		
		var sJan			= "'.JText::_('JAN').'";
		var sFeb			= "'.JText::_('FEB').'";
		var sMar			= "'.JText::_('MAR').'";
		var sApr			= "'.JText::_('APR').'";
		var sMay			= "'.JText::_('MAY').'";
		var sJun			= "'.JText::_('JUN').'";
		var sJul			= "'.JText::_('JUL').'";
		var sAug			= "'.JText::_('AUG').'";
		var sSep			= "'.JText::_('SEP').'";
		var sOct			= "'.JText::_('OCT').'";
		var sNov			= "'.JText::_('NOV').'";
		var sDec			= "'.JText::_('DEC').'";
		var sAm				= "'.JText::_('AM').'";
		var sPm				= "'.JText::_('PM').'";
		var btnOK			= "'.JText::_('COM_JUCOMMENT_SAVE_BUTTON').'";
		var btnReset		= "'.JText::_('COM_JUCOMMENT_RESET').'";
		var btnCancel		= "'.JText::_('COM_JUCOMMENT_CANCEL').'";
		var sNever			= "'.JText::_('COM_JUCOMMENT_NEVER').'";
		</script>';

		$document->addCustomTag( $html );
	}

	public static function getLapsedTime( $time )
	{
		$now	= JFactory::getDate();
		$end	= JFactory::getDate( $time );
		$time	= $now->toUnix() - $end->toUnix();

		$tokens = array (
							31536000 	=> 'COM_JUCOMMENT_X_YEAR',
							2592000 	=> 'COM_JUCOMMENT_X_MONTH',
							604800 		=> 'COM_JUCOMMENT_X_WEEK',
							86400 		=> 'COM_JUCOMMENT_X_DAY',
							3600 		=> 'COM_JUCOMMENT_X_HOUR',
							60 			=> 'COM_JUCOMMENT_X_MINUTE',
							1 			=> 'COM_JUCOMMENT_X_SECOND'
						);

		foreach( $tokens as $unit => $key )
		{
			if ($time < $unit)
			{
				continue;
			}

			$units	= floor( $time / $unit );

			$string = $units > 1 ?  $key . 'S' : $key;
			$string = $string . '_AGO';

			$text   = JText::sprintf(strtoupper($string), $units);
			return $text;
		}

		return JText::_('COM_JUCOMMENT_ONE_SECOND_AGO');
	}

	public static function getDifference( $end, $format = '' )
	{
		if( $end == '' )
		{
			return 0;
		}

		$now	= JFactory::getDate();
		$time	= $now->toUnix() - $end;

		if( $format )
		{
			$time = self::toFormat( $end, $format );
		}

		return $time;
	}

	public static function toFormat( $jdate = null, $format = '%Y-%m-%d %H:%M:%S' )
	{
		if( $jdate instanceof JDate )
		{
			return $jdate->toFormat( $format );
		}

		return self::getDate( $jdate )->toFormat( $format );
	}

	public static function strftimeToDate( $format )
	{
		$strftimeMap = array(
			
			'%a' => 'D', 
			'%A' => 'l', 
			'%d' => 'd', 
			'%e' => 'j', 
			'%j' => 'z', 
			'%u' => 'N', 
			'%w' => 'w', 

			
			'%U' => 'W', 
			'%V' => 'W', 
			'%W' => 'W', 

			
			'%b' => 'M', 
			'%B' => 'F', 
			'%h' => 'M', 
			'%m' => 'm', 

			
			'%C' => '', 
			'%g' => 'y', 
			'%G' => 'o', 
			'%y' => 'y', 
			'%Y' => 'Y', 

			
			'%H' => 'H', 
			'%I' => 'h', 
			'%l' => 'g', 
			'%M' => 'i', 
			'%p' => 'A', 
			'%P' => 'a', 
			'%r' => 'h:i:s A', 
			'%R' => 'H:i', 
			'%S' => 's', 
			'%T' => 'H:i:s', 
			'%X' => 'H:i:s', 
			'%z' => 'O', 
			'%Z' => 'T', 

			
			'%c' => 'Y-m-d H:i:s', 
			'%D' => 'm/d/y', 
			'%F' => 'Y-m-d', 
			'%s' => '', 
			'%x' => 'Y-m-d', 

			
			'%n' => '\n', 
			'%t' => '\t', 
			'%%' => '%'  
		);

		$dateMap = array(
			
			'd', 
			'D', 
			'j', 
			'l', 
			'N', 
			'S', 
			'w', 
			'z', 

			
			'W', 

			
			'F', 
			'm', 
			'M', 
			'n', 
			't', 

			
			'L', 
			'o', 
			'Y', 
			'y', 

			
			'a', 
			'A', 
			'B', 
			'g', 
			'G', 
			'h', 
			'H', 
			'i', 
			's', 
			'u', 

			
			'e', 
			'I', 
			'O', 
			'P', 
			'T', 
			'Z', 

			
			'c', 
			'r', 
			'U'  
		);

		foreach( $strftimeMap as $key => $value )
		{
			$format = str_replace( $key, $value, $format );
		}

		return $format;
	}
}
