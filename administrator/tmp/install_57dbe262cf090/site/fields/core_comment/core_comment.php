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

class JUCommentFieldCore_comment extends JUCommentFieldTextarea
{
	protected $field_name = 'comment';
	protected $filter = 'raw';

	public function getTextArea($value, $class = '')
	{
		$this->setVariable('value', $value);
		$this->setVariable('class', $class);

		return $this->fetch('textarea.php', __CLASS__);
	}

	public function getPreview()
	{
		$this->getInput(null);
	}

	public function getInput($fieldValue = null)
	{
		parent::getInput($fieldValue);

		return $this->fetch('input.php', __CLASS__);
	}


	protected function getEditorHtml($selectedEditor, $value)
	{
		$this->settupWysibbEditor('.comment-editor');

		return $this->getTextArea($value, 'comment-editor');
	}

	public function getOutput($options = array())
	{
		if (!$this->isPublished())
		{
			return "";
		}

		return $description = '<div class="comment-content" itemprop="reviewBody">' . $this->value . '</div>';
	}

	
	protected function settupWysibbEditor($jQuerySelector = '.wysibb', $returnJS = false, $wbbOpt = '')
	{
		static $loaded = array();
		if (isset($loaded[$this->id]) && $loaded[$this->id])
		{
			return true;
		}

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

		$wbbOpt = "
			jucmWbbOpt.buttons = '$buttons';
			jucmWbbOpt.lang = 'en';
			jucmWbbOpt.minCommentChar = " . (int) $params->get('min_comment_characters', 20) . ";
			jucmWbbOpt.maxCommentChar = " . (int) $params->get('max_comment_characters', 1000) . ";
		";

		$script = "
		jQuery(document).ready(function($){
			" . $wbbOpt . "
			$('$jQuerySelector').wysibb(jucmWbbOpt);
		});";

		if ($returnJS == true)
		{
			$document = JFactory::getDocument();
			$document->addStyleSheet(JUri::root(true) . "/components/com_jucomment/assets/wysibb/theme/default/wbbtheme.css");

			return '<script type="text/javascript">' . $script . '</script>';
		}
		else
		{
			JHtml::_('jquery.framework');
			$document = JFactory::getDocument();
			$document->addStyleSheet(JUri::root(true) . "/components/com_jucomment/assets/wysibb/theme/default/wbbtheme.css");
			$document->addScript(JUri::root(true) . "/components/com_jucomment/assets/wysibb/jquery.wysibb.js");
			$document->addScript(JUri::root(true) . "/components/com_jucomment/assets/wysibb/preset/phpbb3.js");
			$document->addScript(JUri::root(true) . "/components/com_jucomment/assets/wysibb/override.jquery.wysibb.js");
			$document->addScriptDeclaration($script);

			$script = '
					if(typeof onTriggerForm === "undefined"){
						var	onTriggerForm = [];
					}

					onTriggerForm["' . $this->getId() . '"] = function(form, type, result){
						if(type == "reset" || (result && result.type == "success")){
							form.find(".' . $this->getId() . '_wysibb").clean();
						}
					}
				';
			$document->addScriptDeclaration($script);
		}
		

		$loaded[$this->id] = true;
	}
}

?>