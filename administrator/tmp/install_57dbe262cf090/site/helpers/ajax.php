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

class JUCommentAjaxHelper
{
	
	public function __call($method, $args)
	{
		$data = is_array($args[0]) ? $args[0] : $args;
		$this->command = array(
			'type' => $method,
			'data' => $data
		);

	}

	public function fail($data){
		$this->command = array(
			'type' => 'fail',
			'data' => $data
		);
	}

	public function success($data){
		$this->command = array(
			'type' => 'success',
			'data' => $data
		);
	}

	public function send()
	{
		
		
		
		
		
		
		
		

		header('Content-type: text/x-json; UTF-8');

		$callback = JRequest::getVar('callback');

		if (isset($callback))
		{
			echo $callback . '(' . json_encode( $this->command ) . ');';
		} else {
			echo json_encode( $this->command );
		}

		exit;
	}
}
