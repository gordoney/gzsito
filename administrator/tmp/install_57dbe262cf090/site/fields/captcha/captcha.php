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

class JUCommentFieldCaptcha extends JUCommentFieldBase
{

	public function __construct($field = null, $comment = null)
	{
		parent::__construct($field, $comment);
		$this->required = true;
	}

	
	public function getCaption($forceShow = false)
	{
		$app = JFactory::getApplication();
		if ($app->isAdmin())
		{
			return "";
		}
		else
		{
			return parent::getCaption($forceShow);
		}
	}

	public function loadDefaultAssets($loadJS = true, $loadCSS = true)
	{
		static $loaded = array();
		if ($this->folder && !isset($loaded[$this->folder]))
		{
			if ($loadJS)
			{
				$document = JFactory::getDocument();
				$script   = '
					if(typeof onTriggerForm === "undefined"){
						var	onTriggerForm = [];
					}

					onTriggerForm["' . $this->getId() . '"] = function(form, type, result){
						if(result && result.type == "success" ){
							form.find(".jucomment-captcha-reload").trigger("click");
						}
					}
				';
				$document->addScriptDeclaration($script);
			}

			$loaded[$this->folder] = true;
		}
	}

	public function getInput($fieldValue = null)
	{
		if (!$this->isPublished())
		{
			return "";
		}

		$app = JFactory::getApplication();
		if ($app->isAdmin())
		{
			return "";
		}

		$this->loadDefaultAssets();

		$options                  = array();
		$options['response_name'] = $this->getName() . "[captcha_response]";
		$options['id_name']       = $this->getName() . "[captcha_id]";
		$options['id']            = $this->getId();
		$options['class']         = $this->isRequired() ? 'required' : '';
		$options['validate_data'] = $this->getValidateData();


		$html = '<div class="jucomment-form-captcha">';
		$html .= JUComment::getCaptcha()->getHTML($options);
		$html .= '</div>';

		$this->registerTriggerForm();

		return $html;
	}

	public function getBackendOutput()
	{
		return "";
	}

	public function getSearchInput($defaultValue = "")
	{
		return "";
	}

	protected function getValue()
	{
		return null;
	}

	public function getPlaceholderValue(&$email = null)
	{
		return false;
	}

	
	public function canSubmit($userID = null)
	{
		$app = JFactory::getApplication();
		if ($app->isAdmin())
		{
			return false;
		}

		$user = JFactory::getUser();

		if ($user->authorise('core.admin', 'com_jucomment'))
		{
			return false;
		}

		return parent::canSubmit($userID);
	}

	
	public function canEdit($userID = null)
	{
		$app = JFactory::getApplication();
		if ($app->isAdmin())
		{
			return false;
		}

		$user = JFactory::getUser();

		if ($user->authorise('core.admin', 'com_jucomment'))
		{
			return false;
		}

		return parent::canEdit($userID);
	}

	public function PHPValidate($values)
	{
		$app = JFactory::getApplication();
		if ($app->isAdmin())
		{
			return true;
		}

		
		if (($values === "" || $values === null) && !$this->isRequired())
		{
			return true;
		}

		$data                     = array();
		$data['captcha_id']       = $values['captcha_id'];
		$data['captcha_response'] = $values['captcha_response'];

		if (!JUComment::getCaptcha()->verify($data))
		{
			
			$message = (string) $this->params->get('invalid_message');

			if ($message)
			{
				return JText::sprintf($message, $this->getCaption(true));
			}
			else
			{
				return JText::sprintf('COM_JUCOMMENT_FIELD_VALUE_IS_INVALID', $this->getCaption(true));
			}
		}

		return true;
	}

	
	public function storeValue($value)
	{
		return true;
	}

	public function isPublished()
	{
		$storeId = md5(__METHOD__ . "::" . $this->id);
		if (!isset(self::$cache[$storeId]))
		{
			
			$app = JFactory::getApplication();
			if ($app->isAdmin())
			{
				self::$cache[$storeId] = false;

				return self::$cache[$storeId];
			}

			self::$cache[$storeId] = parent::isPublished();

			return self::$cache[$storeId];
		}

		return self::$cache[$storeId];
	}


	public function canView($options = array())
	{
		return false;
	}

	public function registerTriggerForm()
	{
		$document = JFactory::getDocument();

		$script = '
			if(typeof juCommentFomTrigger === "undefined"){
				var	juCommentFomTrigger = [];
			}

			juCommentFomTrigger["' . $this->getId() . '"] = function(form, type, result){
				if(type == "reset" || (type == "submit" && result.type == "success")){
					jQuery("#' . $this->getId() . '").val("");
					jQuery("#' . $this->getId() . '").closest(".jucomment-form-captcha").find(".jucomment-captcha-reload").trigger("click");
				}
			}
		';

		$document->addScriptDeclaration($script);
	}
}

?>