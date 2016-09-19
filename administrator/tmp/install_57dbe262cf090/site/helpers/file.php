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

class JUCommentFileHelper
{
	
	public static function canUpload(&$file, &$error = array(), $legal_extensions, $max_size = 0, $check_mime = false, $allowed_mime = '', $ignored_extensions = '', $image_extensions = 'bmp,gif,jpg,jpeg,png')
	{
		
		if (empty($file['name']))
		{
			isset($error['WARN_SOURCE']) ? $error['WARN_SOURCE']++ : $error['WARN_SOURCE'] = 1;

			return false;
		}

		jimport('joomla.filesystem.file');

		
		

		
		$executable = array(
			'php', 'js', 'exe', 'phtml', 'java', 'perl', 'py', 'asp', 'dll', 'go', 'ade', 'adp', 'bat', 'chm', 'cmd', 'com', 'cpl', 'hta', 'ins', 'isp',
			'jse', 'lib', 'mde', 'msc', 'msp', 'mst', 'pif', 'scr', 'sct', 'shb', 'sys', 'vb', 'vbe', 'vbs', 'vxd', 'wsc', 'wsf', 'wsh'
		);

		$legal_extensions   = array_map('trim', explode(",", strtolower(str_replace("\n", ",", $legal_extensions))));
		$ignored_extensions = array_map('trim', explode(",", strtolower(str_replace("\n", ",", $ignored_extensions))));

		$format = strtolower(JFile::getExt($file['name']));
		
		if ($format == '' || $format == false || (!in_array($format, $legal_extensions)) || in_array($format, $executable))
		{
			isset($error['WARN_FILETYPE']) ? $error['WARN_FILETYPE']++ : $error['WARN_FILETYPE'] = 1;

			return false;
		}

		
		if ($max_size > 0 && (int) $file['size'] > $max_size)
		{
			isset($error['WARN_FILETOOLARGE']) ? $error['WARN_FILETOOLARGE']++ : $error['WARN_FILETOOLARGE'] = 1;

			return false;
		}

		
		if ($check_mime)
		{
			$image_extensions = array_map('trim', explode(",", strtolower(str_replace("\n", ",", $image_extensions))));

			
			if (in_array($format, $image_extensions))
			{
				
				
				if (!empty($file['tmp_name']))
				{
					if (($imginfo = getimagesize($file['tmp_name'])) === false)
					{
						isset($error['WARN_INVALID_IMG']) ? $error['WARN_INVALID_IMG']++ : $error['WARN_INVALID_IMG'] = 1;

						return false;
					}
				}
				else
				{
					isset($error['WARN_FILETOOLARGE']) ? $error['WARN_FILETOOLARGE']++ : $error['WARN_FILETOOLARGE'] = 1;

					return false;
				}

				$file['mime_type'] = $imginfo['mime'];
			}
			
			elseif (!in_array($format, $ignored_extensions))
			{
				
				$allowed_mime = array_map('trim', explode(",", strtolower(str_replace("\n", ",", $allowed_mime))));

				if (function_exists('finfo_open'))
				{
					
					$finfo = finfo_open(FILEINFO_MIME);
					$type  = finfo_file($finfo, $file['tmp_name']);

					if (strlen($type) && !in_array($type, $allowed_mime))
					{
						isset($error['WARN_INVALID_MIME']) ? $error['WARN_INVALID_MIME']++ : $error['WARN_INVALID_MIME'] = 1;

						return false;
					}
					$file['mime_type'] = $type;
					finfo_close($finfo);
				}
				elseif (function_exists('mime_content_type'))
				{
					
					$type = mime_content_type($file['tmp_name']);

					if (strlen($type) && !in_array($type, $allowed_mime))
					{
						isset($error['WARN_INVALID_MIME']) ? $error['WARN_INVALID_MIME']++ : $error['WARN_INVALID_MIME'] = 1;

						return false;
					}
					$file['mime_type'] = $type;
				}
				
			}

		}

		$xss_check = file_get_contents($file['tmp_name'], false, null, -1, 256);

		$html_tags = array(
			'abbr', 'acronym', 'address', 'applet', 'area', 'audioscope', 'base', 'basefont', 'bdo', 'bgsound', 'big', 'blackface', 'blink',
			'blockquote', 'body', 'bq', 'br', 'button', 'caption', 'center', 'cite', 'code', 'col', 'colgroup', 'comment', 'custom', 'dd', 'del',
			'dfn', 'dir', 'div', 'dl', 'dt', 'em', 'embed', 'fieldset', 'fn', 'font', 'form', 'frame', 'frameset', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
			'head', 'hr', 'html', 'iframe', 'ilayer', 'img', 'input', 'ins', 'isindex', 'keygen', 'kbd', 'label', 'layer', 'legend', 'li', 'limittext',
			'link', 'listing', 'map', 'marquee', 'menu', 'meta', 'multicol', 'nobr', 'noembed', 'noframes', 'noscript', 'nosmartquotes', 'object',
			'ol', 'optgroup', 'option', 'param', 'plaintext', 'pre', 'rt', 'ruby', 's', 'samp', 'script', 'select', 'server', 'shadow', 'sidebar',
			'small', 'spacer', 'span', 'strike', 'strong', 'style', 'sub', 'sup', 'table', 'tbody', 'td', 'textarea', 'tfoot', 'th', 'thead', 'title',
			'tr', 'tt', 'ul', 'var', 'wbr', 'xml', 'xmp', '!DOCTYPE', '!--'
		);

		
		foreach ($html_tags AS $tag)
		{
			
			if (stristr($xss_check, '<' . $tag . ' ') || stristr($xss_check, '<' . $tag . '>'))
			{
				isset($error['WARN_IEXSS']) ? $error['WARN_IEXSS']++ : $error['WARN_IEXSS'] = 1;

				return false;
			}
		}

		

		return true;
	}

	
	public static function downloadFile($file, $fileName, $transport = 'php', $speed = 50, $resume = true, $downloadMultiParts = true, $mimeType = false)
	{
		
		if (ini_get('zlib.output_compression'))
		{
			@ini_set('zlib.output_compression', 'Off');
		}

		
		if (function_exists('apache_setenv'))
		{
			apache_setenv('no-gzip', '1');
		}

		
		

		
		
		
		$agent = isset($_SERVER['HTTP_USER_AGENT']) ? trim($_SERVER['HTTP_USER_AGENT']) : null;
		if ($agent && preg_match('#(?:MSIE |Internet Explorer/)(?:[0-9.]+)#', $agent)
			&& (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
		)
		{
			header('Pragma: ');
			header('Cache-Control: ');
		}
		else
		{
			header('Pragma: no-store,no-cache');
			header('Cache-Control: no-cache, no-store, must-revalidate, max-age=-1');
			header('Cache-Control: post-check=0, pre-check=0', false);
		}
		header('Expires: Mon, 14 Jul 1789 12:30:00 GMT');
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

		
		if (get_resource_type($file) == "stream")
		{
			$transport = 'php';
		}
		
		elseif (!JFile::exists($file))
		{
			return JText::sprintf("COM_JUCOMMENT_FILE_NOT_FOUND_X", $fileName);
		}

		
		if ($transport != 'php')
		{
			
			header('Content-Description: File Transfer');
			header('Date: ' . @gmdate("D, j M m Y H:i:s ") . 'GMT');
			
			if ($resume)
			{
				header('Accept-Ranges: bytes');
			}
			
			elseif (isset($_SERVER['HTTP_RANGE']))
			{
				exit;
			}

			if (!$downloadMultiParts)
			{
				
				header('Accept-Ranges: none');
			}

			header('Content-Type: application/force-download');
			
			
			
			
			header('Content-Disposition: attachment; filename="' . $fileName . '"');
		}

		switch ($transport)
		{
			
			case 'apache':
				
				$modules = apache_get_modules();
				if (in_array('mod_xsendfile', $modules))
				{
					header('X-Sendfile: ' . $file);
				}
				break;

			
			case 'ngix':
				$path = preg_replace('/' . preg_quote(JPATH_ROOT, '/') . '/', '', $file, 1);
				header('X-Accel-Redirect: ' . $path);
				break;

			
			case 'lighttpd':
				header('X-LIGHTTPD-send-file: ' . $file); 
				header('X-Sendfile: ' . $file); 
				break;

			
			case 'php':
			default:
				JUComment::import('class', 'download');

				JUComment::obCleanData();

				$download = new JUCommentDownload($file);
				$download->rename($fileName);
				if ($mimeType)
				{
					$download->mime($mimeType);
				}
				if ($resume)
				{
					$download->resume();
				}
				$download->speed($speed);
				$download->start();

				if ($download->error)
				{
					return $download->error;
				}

				unset($download);
				break;
		}

		return true;
	}

	
	public static function fileNameFilter($fileName)
	{
		
		$fileNameFilterPath = JPATH_ADMINISTRATOR . "/components/com_jucomment/helper/filenamefilter.php";
		if (JFile::exists($fileNameFilterPath))
		{
			require_once $fileNameFilterPath;
			if (class_exists("JUFileNameFilter"))
			{
				
				if (function_exists("fileNameFilter"))
				{
					$fileName = call_user_func("fileNameFilter", $fileName);
				}
			}
		}

		$fileInfo = pathinfo($fileName);
		$fileName = str_replace("-", "_", JFilterOutput::stringURLSafe($fileInfo['filename']));

		$fileName = JFile::makeSafe($fileName);

		
		if (!$fileName)
		{
			$fileName = JFactory::getDate()->format('Y_m_d_H_i_s');
		}

		return isset($fileInfo['extension']) ? $fileName . "." . $fileInfo['extension'] : $fileName;
	}
}
