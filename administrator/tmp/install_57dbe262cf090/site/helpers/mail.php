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

class JUCommentMailHelper
{
	
	protected static $cache = array();

	
	public static $sendMailError;

	
	public static $sendMailReportMessage;

	
	protected static function textVersion($html, $fullConvert = true)
	{
		$html = self::absoluteURL($html);

		if ($fullConvert)
		{
			$html = preg_replace('# +#', ' ', $html);
			$html = str_replace(array("\n", "\r", "\t"), '', $html);
		}

		$removepictureslinks    = "#< *a[^>]*> *< *img[^>]*> *< *\/ *a *>#isU";
		$removeScript           = "#< *script(?:(?!< */ *script *>).)*< */ *script *>#isU";
		$removeStyle            = "#< *style(?:(?!< */ *style *>).)*< */ *style *>#isU";
		$removeStrikeTags       = '#< *strike(?:(?!< */ *strike *>).)*< */ *strike *>#iU';
		$replaceByTwoReturnChar = '#< *(h1|h2)[^>]*>#Ui';
		$replaceByStars         = '#< *li[^>]*>#Ui';
		$replaceByReturnChar1   = '#< */ *(li|td|dt|tr|div|p)[^>]*> *< *(li|td|dt|tr|div|p)[^>]*>#Ui';
		$replaceByReturnChar    = '#< */? *(br|p|h1|h2|legend|h3|li|ul|dd|dt|h4|h5|h6|tr|td|div)[^>]*>#Ui';
		$replaceLinks           = '/< *a[^>]*href *= *"([^#][^"]*)"[^>]*>(.+)< *\/ *a *>/Uis';

		$text = preg_replace(array($removepictureslinks, $removeScript, $removeStyle, $removeStrikeTags, $replaceByTwoReturnChar, $replaceByStars, $replaceByReturnChar1, $replaceByReturnChar, $replaceLinks), array('', '', '', '', "\n\n", "\n* ", "\n", "\n", '${2} ( ${1} )'), $html);

		$text = preg_replace('#(&lt;|&\#60;)([^ \n\r\t])#i', '&lt; ${2}', $text);

		$text = str_replace(array(" ", "&nbsp;"), ' ', strip_tags($text));

		$text = trim(@html_entity_decode($text, ENT_QUOTES, 'UTF-8'));

		if ($fullConvert)
		{
			$text = preg_replace('# +#', ' ', $text);
			$text = preg_replace('#\n *\n\s+#', "\n\n", $text);
		}

		return $text;
	}

	protected static function absoluteURL($text)
	{
		$mailling_live = rtrim(JUri::root(), '/') . '/';

		$urls = parse_url($mailling_live);

		if (!empty($urls['path']))
		{
			$mainurl = substr($mailling_live, 0, strrpos($mailling_live, $urls['path'])) . '/';
		}
		else
		{
			$mainurl = $mailling_live;
		}

		$text = str_replace(array('href="../undefined/', 'href="../../undefined/', 'href="../../../undefined//', 'href="undefined/'), array('href="' . $mainurl, 'href="' . $mainurl, 'href="' . $mainurl, 'href="' . $mailling_live), $text);
		$text = preg_replace('#href="(/?administrator)?/({|%7B)#Uis', 'href="$2', $text);

		$replace   = array();
		$replaceBy = array();
		if ($mainurl !== $mailling_live)
		{
			$replace[]   = '#(href|src|action|background)[ ]*=[ ]*\"(?!(\{|%7B|\#|[a-z]{3,7}:|/))(?:\.\./)#i';
			$replaceBy[] = '$1="' . substr($mailling_live, 0, strrpos(rtrim($mailling_live, '/'), '/') + 1);
		}
		$replace[]   = '#(href|src|action|background)[ ]*=[ ]*\"(?!(\{|%7B|\#|[a-z]{3,7}:|/))(?:\.\./|\./)?#i';
		$replaceBy[] = '$1="' . $mailling_live;
		$replace[]   = '#(href|src|action|background)[ ]*=[ ]*\"(?!(\{|%7B|\#|[a-z]{3,7}:))/#i';
		$replaceBy[] = '$1="' . $mainurl;

		$replace[]   = '#((background-image|background)[ ]*:[ ]*url\(\'?"?(?!([a-z]{3,7}:|/|\'|"))(?:\.\./|\./)?)#i';
		$replaceBy[] = '$1' . $mailling_live;

		return preg_replace($replace, $replaceBy, $text);
	}

	protected static function changeEmailCharset($data, $input, $output)
	{
		$input  = strtoupper(trim($input));
		$output = strtoupper(trim($output));

		if ($input == $output)
		{
			return $data;
		}

		if ($input == 'UTF-8' && $output == 'ISO-8859-1')
		{
			$data = str_replace(array('€', '„', '“'), array('EUR', '"', '"'), $data);
		}

		if (function_exists('iconv'))
		{
			set_error_handler('acymailing_error_handler_encoding');
			$encodedData = iconv($input, $output . "//IGNORE", $data);
			restore_error_handler();
			if (!empty($encodedData) && !acymailing_error_handler_encoding('result'))
			{
				return $encodedData;
			}
		}

		if (function_exists('mb_convert_encoding'))
		{
			return mb_convert_encoding($data, $output, $input);
		}

		if ($input == 'UTF-8' && $output == 'ISO-8859-1')
		{
			return utf8_decode($data);
		}

		if ($input == 'ISO-8859-1' && $output == 'UTF-8')
		{
			return utf8_encode($data);
		}

		return $data;
	}

	
	protected static function getEmailByEvent($event, $item_id = null)
	{
		if (!$event)
		{
			return null;
		}

		$event = strtolower($event);

		if ($item_id)
		{
			$comment = JUComment::getComment($item_id);
		}

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('e.*');
		$query->from('#__jucomment_emails AS e');
		$query->join('', '#__jucomment_emails_xref AS exref ON (e.id = exref.email_id)');
		if ($comment)
		{
			$query->where('exref.component = ' . $db->quote($comment->component) . ' OR exref.component = "*"');
		}
		else
		{
			$query->where('exref.component = "*"');
		}

		$app = JFactory::getApplication();
		if ($app->isSite())
		{
			$query->where('e.trigger_in IN (1,3)');
		}
		else
		{
			$query->where('e.trigger_in IN (2,3)');
		}
		$query->where('e.published = 1');

		$nullDate = $db->quote($db->getNullDate());
		$nowDate  = $db->quote(JFactory::getDate()->toSql());

		$query->where('e.publish_up <= ' . $nowDate);
		$query->where('(e.publish_down = ' . $nullDate . ' OR e.publish_down >= ' . $nowDate . ')');

		$query->where("(e.event = " . $db->quote($event) .
			" OR e.event LIKE '" . $db->escape($event, true) . "|%'" .
			" OR e.event LIKE '%|" . $db->escape($event, true) . "|%'" .
			" OR e.event LIKE '%|" . $db->escape($event, true) . "' )");
		$query->group('e.id');
		$db->setQuery($query);
		$emails = $db->loadObjectList();

		return $emails;
	}

	
	protected static function getModeratorEmail($moderator_permissions)
	{
		if (!$moderator_permissions)
		{
			return false;
		}

		
		
		$db       = JFactory::getDbo();
		$nullDate = $db->getNullDate();
		$nowDate  = JFactory::getDate()->toSql();
		$query    = $db->getQuery(true);
		$query->select('u.email');
		$query->from('#__jucomment_moderators as m');
		$query->join('', '#__jucomment_moderators_xref as mxref ON (mxref.mod_id = m.id)');
		$query->join('', '#__users AS u ON u.id = m.user_id');
		$permissions = array();
		foreach ($moderator_permissions as $permission)
		{
			$permissions[] = "m.$permission = 1";
		}
		$query->where("(" . implode(" OR ", $permissions) . ")");
		$query->where('m.published = 1');
		$query->where('m.publish_up <= ' . $db->quote($nowDate));
		$query->where('(m.publish_down = ' . $db->quote($nullDate) . ' OR m.publish_down > ' . $db->quote($nowDate) . ')');
		$query->group('m.email');

		$db->setQuery($query);
		$moderatorEmails = $db->loadColumn();

		if (empty($moderatorEmails))
		{
			return '';
		}

		$moderatorEmails = array_unique($moderatorEmails);

		return implode(',', $moderatorEmails);
	}

	
	public static function getEmailTagsByEvent($event)
	{
		if (!$event)
		{
			return false;
		}
		$events = (array) $event;
		$tags   = array();

		foreach ($events as $event)
		{
			list($type, $action) = explode(".", $event);

			$tags[] = '{site_name}';
			$tags[] = '{admin_email}';
			$tags[] = '{admin_name}';
			$tags[] = '{ip_address}';
			$tags[] = '{browser}';
			$tags[] = '{platform}';
			$tags[] = '{user_email}';
			$tags[] = '{user_name}';
			$tags[] = '{user_username}';
			$tags[] = '{moderator_emails}';
			$tags[] = '{article_id}';
			$tags[] = '{article_title}';
			$tags[] = '{article_link}';
			$tags[] = '{action}';
			$tags[] = '{event}';
			
			switch ($type)
			{
				case 'comment' :
				case 'reply' :
					switch ($action)
					{
						case 'report':
							$tags[] = '{report_admin_link}';
							$tags[] = '{report_subject}';
							$tags[] = '{report_content}';
							break;

						default:
							$tags[] = '{comment_id}';
							$tags[] = '{comment_title}';
							$tags[] = '{comment_link}';
							$tags[] = '{comment_admin_link}';
							$tags[] = '{comment_name}';
							$tags[] = '{comment_email}';
							$tags[] = '{comment_state}';

							$tags[] = '{subscriber_email}';
							$tags[] = '{subscriber_name}';
							$tags[] = '{date:format}';

							$tags[] = '{field_title:field-id}';
							$tags[] = '{field_value:field-id}';
							break;
					}
					break;

				case 'article' :
					switch ($action)
					{
						case 'subscribe' :
							$tags[] = '{subscribe_admin_link}';
							$tags[] = '{subscribe_confirm_link}';
							$tags[] = '{unsubscribe_link}';
							break;
					}
					break;
			}
		}

		return array_unique($tags);
	}

	
	public static function getMailq($start = 0, $limit = 0)
	{
		$db    = JFactory::getDbo();
		$date  = JFactory::getDate();
		$query = $db->getQuery(true);
		$query->select('mailq.*');
		$query->from('#__jucomment_mailqs AS mailq')
			->join('INNER', '#__jucomment_emails as mail on mail.id = mailq.email_id')
			->where('mailq.send_date <= ' . $date->toUnix())
			->order('mail.priority ASC, mailq.last_attempt ASC, mailq.created ASC');

		if ($start || $limit)
		{
			$db->setQuery($query, $start, $limit);
		}
		else
		{
			$db->setQuery($query);
		}

		return $db->loadObjectList();
	}

	
	protected static function deleteMailq($queueIds)
	{
		if (empty($queueIds))
		{
			return true;
		}

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->delete('#__jucomment_mailqs')
			->where('id IN (' . implode(",", $queueIds) . ')');

		$db->setQuery($query);
		if (!$db->execute())
		{
			return false;
		}

		return true;
	}

	
	protected static function updateMailq($queueIds)
	{
		if (empty($queueIds))
		{
			return true;
		}

		$delay = 3600;

		$db    = JFactory::getDbo();
		$date  = JFactory::getDate();
		$set   = array(
			'attempts = attempts + 1',
			'send_date = send_date + ' . $delay,
			'last_attempt = ' . $db->quote($date->toSql())
		);
		$query = $db->getQuery(true);
		$query->update('#__jucomment_mailqs')
			->set($set)
			->where('id IN (' . implode(",", $queueIds) . ')');

		$db->setQuery($query);
		if (!$db->execute())
		{
			return false;
		}

		return true;
	}

	protected static function addMailq($email, $data = array())
	{
		$date       = JFactory::getDate();
		$user       = JFactory::getUser();
		$mailqTable = JUComment::getTable('Mailq');
		$recipients = $email->recipients ? explode(",", $email->recipients) : array();

		$itemId = isset($data['item_id']) ? $data['item_id'] : 0;
		if (in_array('{subscriber_emails}', $recipients))
		{
			$index = array_search('{subscriber_emails}', $recipients);
			
			unset($recipients[$index]);

			$subscribers = self::getSubscriberObjects($data);
			if ($subscribers)
			{
				$lang = JFactory::getLanguage();
				foreach ($subscribers as $subscriber)
				{
					
					if ($email->language && $email->language != '*')
					{
						
						if ($subscriber->user_id)
						{
							$_user         = JFactory::getUser($subscriber->user_id);
							$_userLanguage = $_user->getParam('language', $lang->getTag());
							
							if ($_userLanguage != $email->language)
							{
								continue;
							}
							
							
						}
						elseif ($subscriber->language && $subscriber->language != $lang->getTag())
						{
							continue;
						}
					}
					$mailq                    = array();
					$mailq['id']              = 0;
					$mailq['email_id']        = $email->id;
					$mailq['item_id']         = $itemId;
					$mailq['send_date']       = $date->toUnix();
					$mailq['created']         = $date->toSql();
					$_data                    = $data;
					$_data['user_id']         = $user->id;
					$_data['subscriber_data'] = get_object_vars($subscriber);
					$_data['recipients']      = $subscriber->email;
					$registry                 = new JRegistry($_data);
					$mailq['data']            = $registry->toString('JSON');

					$mailqTable->reset();
					$mailqTable->bind($mailq);
					if ($mailqTable->check())
					{
						if ($mailqTable->store())
						{
							if (in_array($subscriber->email, $recipients))
							{
								$index = array_search($subscriber->email, $recipients);
								unset($recipients[$index]);
							}
						}
					}
				}
			}
		}

		if ($recipients)
		{
			$mailq              = array();
			$mailq['id']        = 0;
			$mailq['email_id']  = $email->id;
			$mailq['item_id']   = $itemId;
			$mailq['send_date'] = $date->toUnix();
			$mailq['created']   = $date->toSql();
			$data['user_id']    = $user->id;
			$data['recipients'] = $recipients;
			$registry           = new JRegistry($data);
			$mailq['data']      = $registry->toString('JSON');

			$mailqTable->reset();
			$mailqTable->bind($mailq);
			if ($mailqTable->check())
			{
				$mailqTable->store();
			}
		}
	}

	protected static function getEmailById($emailId)
	{
		$storeId = md5(__METHOD__ . "::" . $emailId);
		if (!isset(self::$cache[$storeId]))
		{
			$db    = JFactory::getDbo();
			$query = "SELECT * FROM #__jucomment_emails WHERE id=" . intval($emailId);
			$db->setQuery($query);
			$email                        = $db->loadObject();
			$email->event                 = $email->event ? explode("|", $email->event) : array();
			$email->moderator_permissions = $email->moderator_permissions ? explode("|", $email->moderator_permissions) : array();
			self::$cache[$storeId]        = $email;
		}

		return clone self::$cache[$storeId];
	}

	
	public static function replaceEmailTags(&$email, $data = array())
	{
		
		$event = isset($data['event']) ? $data['event'] : '';
		if (!$email || !$event)
		{
			return false;
		}

		
		$app             = JFactory::getApplication();
		$emailTags       = self::getEmailTagsByEvent($event);
		$commentObj      = null; 
		$JUCMApplication = null;
		

		
		$itemId = (isset($data['item_id']) && $data['item_id']) ? $data['item_id'] : 0;
		if ($itemId)
		{
			$commentObj = JUComment::getComment($itemId);
			if ($commentObj)
			{
				$JUCMApplication = JUComment::loadApplication($commentObj->component, $commentObj->section);
				$JUCMApplication = $JUCMApplication->load($commentObj->cid);
			}
		}

		

		$allowFields = array("from", "from_name", "recipients", "cc", "bcc", "reply_to", "reply_to_name", "subject", "body_html", "body_text");

		
		foreach ($email as $key => $value)
		{
			if (!in_array($key, $allowFields))
			{
				continue;
			}

			
			if (isset($data['recipients']) && $data['recipients'] && $key == 'recipients')
			{
				$email->$key = $data['recipients'];
			}

			$hasPlaceHolder = 0;
			$replaceValues  = array();
			$replaceTags    = array();

			if (is_string($value))
			{
				$hasPlaceHolder = preg_match_all('/{.*?}/', $value, $matches);
			}

			
			if ($hasPlaceHolder)
			{
				$realTags = $matches[0];
				foreach ($realTags as $tag)
				{
					
					$replaceBy = false;

					
					if (in_array($tag, $emailTags))
					{
						switch ($tag)
						{
							
							case '{site_name}' :
								$replaceBy = $app->get('sitename');
								break;

							
							case '{admin_email}' :
								$replaceBy = $app->get('mailfrom');
								break;

							
							case '{admin_name}' :
								$replaceBy = $app->get('fromname');
								break;

							case '{ip_address}':
								$replaceBy = JUCommentFrontHelper::getIpAddress();
								break;

							case '{browser}':
								JUComment::import('class', 'browser');
								$browser    = new Browser();
								$_browser   = array();
								$_browser[] = $browser->getBrowser();
								$_browser[] = $browser->getVersion();

								$replaceBy = implode(" ", $_browser);
								break;

							case '{platform}':
								JUComment::import('class', 'browser');
								$browser    = new Browser();
								$user_agent = $browser->getUserAgent();

								$replaceBy = JUCommentLogHelper::getPlatform($user_agent);
								break;

							
							case '{user_email}' :
								if (isset($data['user_email']))
								{
									$replaceBy = $data['user_email'];
								}
								elseif (isset($data['user_id']))
								{
									if ($data['user_id'])
									{
										$user = JFactory::getUser($data['user_id']);
										if ($user)
										{
											$replaceBy = $user->email;
										}
									}
								}
								else
								{
									$user      = JFactory::getUser();
									$replaceBy = $user->email;
								}

								break;

							
							case '{user_name}' :
								if (isset($data['user_name']))
								{
									$replaceBy = $data['user_name'];
								}
								elseif (isset($data['user_id']))
								{
									if ($data['user_id'])
									{
										$user = JFactory::getUser($data['user_id']);
										if ($user)
										{
											$replaceBy = $user->name;
										}
									}
								}
								else
								{
									$user      = JFactory::getUser();
									$replaceBy = $user->name;
								}

								if (!$replaceBy)
								{
									$replaceBy = JText::_('COM_JUCOMMENT_GUEST');
								}

								break;

							
							case '{user_username}' :
								if (isset($data['user_username']))
								{
									$replaceBy = $data['user_username'];
								}
								elseif (isset($data['user_id']))
								{
									if ($data['user_id'])
									{
										$user = JFactory::getUser($data['user_id']);
										if ($user)
										{
											$replaceBy = $user->username;
										}
									}
								}
								else
								{
									$user      = JFactory::getUser();
									$replaceBy = $user->username;
								}

								if (!$replaceBy)
								{
									$replaceBy = JText::_('COM_JUCOMMENT_GUEST');
								}

								break;

							case '{moderator_emails}' :
								$replaceBy = self::getModeratorEmail($email->moderator_permissions);
								break;

							case '{action}' :
								list($type, $action) = explode(".", $event);
								$replaceBy = $action;
								break;

							case '{event}' :
								$events    = self::getEvents();
								$replaceBy = $events[$event];
								break;

							case '{article_id}':
								$replaceBy = $JUCMApplication->getContentId();
								break;

							case '{article_title}':
								$replaceBy = $JUCMApplication->getContentTitle();
								break;

							case '{article_link}':
								$replaceBy = $JUCMApplication->getContentPermalink();
								break;

							case '{category_id}':
								$replaceBy = $JUCMApplication->getCategoryId();
								break;

							case '{category_title}':
								$catid      = $JUCMApplication->getCategoryId();
								$categories = $JUCMApplication->getCategories();
								foreach ($categories as $category)
								{
									if ($category->id == $catid)
									{
										$replaceBy = $category->title;
										break;
									}
								}
								break;

							
							case '{unsubscribe_link}':
								if ($data && isset($data['id']) && $data['id'])
								{
									$secret  = JFactory::getConfig()->get('secret');
									$ip      = $data['ip_address'];
									$created = $data['created'];
									$type    = $data['type'];
									$code    = md5($ip . $created . $type . $secret);

									$link = "index.php?option=com_jucomment&task=subscribe.remove&sub_id=" . $data['id'] . "&code=" . $code;

									$replaceBy = JUCommentRouterHelper::emailLinkRouter($link, false, -1);
								}
								break;

							
							case '{subscribe_confirm_link}':
								if ($data && isset($data['id']) && $data['id'])
								{
									$db      = JFactory::getDbo();
									$secret  = JFactory::getConfig()->get('secret');
									$ip      = $db->quote($data['ip_address']);
									$created = $db->quote($data['created']);
									$code    = md5($ip . $created . $secret);

									$link = "index.php?option=com_jucomment&task=subscribe.activate&code=" . $code . '&id=' . $data['id'];
									$link .= '&comment_id=' . $data['item_id'];

									JUComment::import('helper', 'router');
									$replaceBy = JUCommentRouterHelper::emailLinkRouter($link, false, -1);
								}
								break;

							case '{subscribe_admin_link}':
								if ($data && isset($data['id']) && $data['id'])
								{
									$filter_search = 'filter[search]=id:' . $data['id'];
									$replaceBy = JUri::root() . 'administrator/index.php?option=com_jucomment&view=subscriptions&' . $filter_search;
								}
								break;

							case '{subscriber_email}':
								if ($data && isset($data['subscriber_data']))
								{
									if ($data['subscriber_data']['user_id'])
									{
										$user      = JFactory::getUser($data['subscriber_data']['user_id']);
										$replaceBy = $user->email;
									}
									elseif ($data['subscriber_data']['email'])
									{
										$replaceBy = $data['subscriber_data']['email'];
									}
								}

								break;

							case '{subscriber_name}':
								if ($data && isset($data['subscriber_data']))
								{
									if ($data['subscriber_data']['user_id'])
									{
										$user      = JFactory::getUser($data['subscriber_data']['user_id']);
										$replaceBy = $user->name;
									}
									elseif ($data['subscriber_data']['name'])
									{
										$replaceBy = $data['subscriber_data']['name'];
									}
								}
								break;

							
							case '{comment_id}' :
								if ($commentObj)
								{
									$replaceBy = $commentObj->id;
								}
								break;

							
							case '{comment_title}' :
								if ($commentObj)
								{
									$replaceBy = $commentObj->title;
								}
								break;

							case '{comment_link}' :

								if ($commentObj)
								{
									
									$replaceBy = $JUCMApplication->getContentPermalink() . '#comment-item-' . $commentObj->id;
								}
								break;

							case '{comment_admin_link}' :
								if ($commentObj)
								{
									$filter_search = 'filter[search]=id:' . $commentObj->id;
									$replaceBy = JUri::root() . 'administrator/index.php?option=com_jucomment&view=subscriptions&' . $filter_search;
								}
								break;

							
							case '{comment_email}':
								if ($commentObj)
								{
									if ($commentObj->user_id && $commentObj->user_id)
									{
										$user      = JFactory::getUser($commentObj->user_id);
										$replaceBy = $user->email;
									}
									else
									{
										if ($commentObj->guest_email)
										{
											$replaceBy = $commentObj->guest_email;
										}
									}
								}
								elseif ($data)
								{
									if (isset($data['user_id']) && $data['user_id'])
									{
										$user      = JFactory::getUser($data['user_id']);
										$replaceBy = $user->email;
									}
									elseif (isset($data['guest_email']) && $data['guest_email'])
									{
										
										$replaceBy = JUCommentFrontHelperCategory::getMainCategoryId($data['guest_email']);
									}
								}
								break;

							
							case '{comment_name}':
								if ($commentObj)
								{
									if ($commentObj->user_id && $commentObj->user_id)
									{
										$user      = JFactory::getUser($commentObj->user_id);
										$replaceBy = $user->name;
									}
									else
									{
										if ($commentObj->guest_name)
										{

											$replaceBy = $commentObj->guest_name;
										}
									}
								}
								elseif ($data)
								{
									if (isset($data['user_id']) && $data['user_id'])
									{
										$user      = JFactory::getUser($data['user_id']);
										$replaceBy = $user->name;
									}
									elseif (isset($data['guest_name']) && $data['guest_name'])
									{
										
										$replaceBy = JUCommentFrontHelperCategory::getMainCategoryId($data['guest_name']);
									}
								}

								break;

							case '{comment_state}':
								if ($commentObj)
								{
									if ($commentObj->published)
									{
										$replaceBy = JText::_('COM_JUCOMMENT_PUBLISHED');
									}
									else
									{
										$replaceBy = JText::_('COM_JUCOMMENT_UNPUBLISHED');
									}
								}
								break;

							case '{report_admin_link}':
								if ($data && isset($data['report']))
								{
									$filter_search = 'filter[search]=id:' . $data['id'];
									$replaceBy = JUri::root() . 'administrator/index.php?option=com_jucomment&view=reports&' . $filter_search;
								}
								break;

							case '{report_content}':
								if ($data && isset($data['report']))
								{
									$replaceBy = $data['report'];
								}
								break;

							case '{report_subject}':
								if ($data && isset($data['subject']))
								{
									$replaceBy = $data['subject'];
								}
								break;

							
							case '{from_email}':
								if ($data)
								{
									
									if (isset($data['from_email']))
									{
										$replaceBy = $data['from_email'];
									}
									
									elseif (isset($data['guest_email']))
									{
										$replaceBy = $data['guest_email'];
									}
								}
								break;

							case '{from_name}':
								if ($data)
								{
									
									if (isset($data['from_name']))
									{
										$replaceBy = $data['from_name'];
									}
									
									elseif (isset($data['guest_name']))
									{
										$replaceBy = $data['guest_name'];
									}
								}
								break;

							case '{contact_message}':
								if ($data && isset($data['message']))
								{
									$replaceBy = $data['message'];
								}

								break;

							case '{to_email}':
								if ($data)
								{
									if (isset($data['to_email']))
									{
										$replaceBy = $data['to_email'];
									}
									elseif (isset($data['email']))
									{
										$replaceBy = $data['email'];
									}
								}
								break;

							case '{to_name}':
								if ($data)
								{
									if (isset($data['to_name']))
									{
										$replaceBy = $data['to_name'];
									}
									elseif (isset($data['name']))
									{
										$replaceBy = $data['name'];
									}
								}
								break;
						}
					}
					else
					{
						
						if (preg_match('/{(date:)(.*?)}/', $tag, $subMatches) > 0)
						{
							if (empty($subMatches[2]))
							{
								$subMatches[2] = 'Y-m-d H:i:s';
							}

							$replaceBy = date($subMatches[2]);
						}
						
						elseif (preg_match('/{(field_)(value|title):(.*?)}/', $tag, $subMatches) > 0 && !empty($commentObj))
						{
							if (!empty($subMatches[3]))
							{
								$field = JUCommentFieldHelper::getField($subMatches[3], $commentObj);
								if ($field)
								{
									if ($subMatches[2] == 'value')
									{
										$replaceBy = $field->getPlaceholderValue($email);
									}
									else
									{
										$replaceBy = $field->getCaption(true);
									}
								}
							}
						}
					}

					
					if ($replaceBy !== false)
					{
						$replaceTags[]   = $tag;
						$replaceValues[] = (string) $replaceBy;
					}
				}

				if ($replaceValues)
				{
					$email->$key = str_replace($replaceTags, $replaceValues, $value);
				}
			}
		}
	}


	public static function prepareSend(&$email)
	{
		$email->recipients    = $email->recipients ? array_filter(array_map('trim', array_unique(explode(',', $email->recipients)))) : array();
		$email->cc            = $email->cc ? array_filter(array_map('trim', array_unique(explode(',', $email->cc)))) : array();
		$email->bcc           = $email->bcc ? array_filter(array_map('trim', array_unique(explode(',', $email->bcc)))) : array();
		$email->reply_to      = $email->reply_to ? array_filter(array_map('trim', array_unique(explode(',', $email->reply_to)))) : array();
		$email->reply_to_name = $email->reply_to_name ? array_filter(array_map('trim', array_unique(explode(',', $email->reply_to_name)))) : array();

		$params = JUComment::getParams();

		if ($email->attachments)
		{
			$registry           = new JRegistry($email->attachments);
			$email->attachments = $registry->toArray();

			if ($email->attachments)
			{
				$app = JFactory::getApplication();
				JUComment::import('helper', 'router');

				foreach ($email->attachments AS $i => $attachment)
				{
					$code                              = md5($email->id . $attachment . $app->get('secret'));
					$email->attachments[$i]            = new stdClass();
					$email->attachments[$i]->file_name = $attachment;
					$email->attachments[$i]->url       = JUCommentRouterHelper::emailLinkRouter('index.php?option=com_jucomment&task=email.downloadattachment&mail_id=' . $email->id . '&file=' . $attachment . '&code=' . $code, false, -1);
					$email->attachments[$i]->file_path = JPATH_SITE . "/media/com_jucomment/email_attachments/" . $email->id . '/' . $attachment;
				}

				if ($params->get('email_embedded_files', 0))
				{
					$attachStringHTML = '<br/><fieldset><legend>' . JText::_('COM_JUCOMMENT_ATTACHMENTS') . '</legend><table><tbody>';
					$attachStringText = "\n" . "\n" . '------- ' . JText::_('COM_JUCOMMENT_ATTACHMENTS') . ' -------';
					foreach ($email->attachments as $attachment)
					{
						$attachStringHTML .= '<tr><td><a href="' . $attachment->url . '" target="_blank">' . $attachment->file_name . '</a></td></tr>';
						$attachStringText .= "\n" . '-- ' . $attachment->file_name . ' ( ' . $attachment->url . ' )';
					}
					$attachStringHTML .= '</tbody></table></fieldset>';

					$email->attachments = null;
					$email->body_html .= $attachStringHTML;
					$email->body_text .= $attachStringText;
				}
			}
		}

		$email->body_text = self::textVersion($email->body_text, false);
		$email->body_text = str_replace(" ", ' ', $email->body_text);

		if (function_exists('mb_convert_encoding'))
		{
			$email->body_html = mb_convert_encoding($email->body_html, 'HTML-ENTITIES', 'UTF-8');
			$email->body_html = str_replace(array('&amp;', '&sigmaf;'), array('&', 'ς'), $email->body_html);
		}
		$email->body_html = self::absoluteURL($email->body_html);
		$email->body_html = str_replace(" ", ' ', $email->body_html);


		if ($email->mode == 0)
		{
			$email->body = $email->body_text;
		}
		else
		{
			$email->body = $email->body_html;
		}

		$email->subject = str_replace(array('’', '“', '”', '–'), array("'", '"', '"', '-'), $email->subject);

		$charset = $params->get('email_charset', 'UTF-8');
		if ($charset != 'UTF-8')
		{
			$email->body    = self::changeEmailCharset($email->body, 'UTF-8', $charset);
			$email->subject = self::changeEmailCharset($email->subject, 'UTF-8', $charset);
		}

		
	}

	
	protected static function check($email)
	{
		
		
		self::$sendMailError         = 0;
		self::$sendMailReportMessage = '';

		if (!$email)
		{
			self::$sendMailError         = 2;
			self::$sendMailReportMessage = JText::_('COM_JUCOMMENT_CAN_NOT_LOAD_EMAIL');

			return false;
		}

		if ((count($email->recipients) + count($email->cc) + count($email->bcc)) < 1)
		{
			self::$sendMailError         = 4;
			self::$sendMailReportMessage = JText::_('COM_JUCOMMENT_ERROR_RECIPIENTS_EMPTY');

			return false;
		}

		
		$emailPattern = '/^[\w\.-]+@[\w\.-]+\.[\w\.-]+$/';

		if (!preg_match($emailPattern, $email->from))
		{
			self::$sendMailError         = 5;
			self::$sendMailReportMessage = JText::_('COM_JUCOMMENT_ERROR_INVALID_FROM_EMAIL');

			return false;
		}

		$recipients = array_filter(array_unique(array_merge($email->recipients, $email->cc, $email->bcc)));

		foreach ($recipients AS $recipient)
		{
			if (!preg_match($emailPattern, $recipient))
			{
				self::$sendMailError         = 5;
				self::$sendMailReportMessage = JText::_('COM_JUCOMMENT_ERROR_INVALID_RECIPIENTS_EMAIL');

				return false;
			}
		}

		if (isset($email->reply_to) && $email->reply_to)
		{
			$replyToArr = explode(',', $email->reply_to);
			$replyToArr = array_unique($replyToArr);

			foreach ($replyToArr AS $reply)
			{
				if (!preg_match($emailPattern, $reply))
				{
					self::$sendMailError         = 5;
					self::$sendMailReportMessage = JText::_('COM_JUCOMMENT_ERROR_INVALID_REPLY_EMAIL');

					return false;
				}
			}
		}

		
		if (empty($email->body))
		{
			self::$sendMailError         = 6;
			self::$sendMailReportMessage = JText::_('COM_JUCOMMENT_ERROR_EMAIL_BODY_EMPTY');

			return false;
		}

		return true;
	}

	
	protected static function send($email)
	{
		
		$mail = JFactory::getMailer();

		
		$mail->setSender(array($email->from, $email->from_name));

		
		if ($email->attachments)
		{
			foreach ($email->attachments AS $attachment)
			{
				$mail->addAttachment($attachment->file_path, $attachment->file_name);
			}
		}

		
		$mail->setSubject($email->subject);
		$mail->addRecipient($email->recipients);
		if ($email->cc)
		{
			$mail->addCC($email->cc);
		}

		if ($email->bcc)
		{
			$mail->addBCC($email->bcc);
		}

		if ($email->reply_to)
		{
			if ($email->reply_to_name && count($email->reply_to) == count($email->reply_to_name))
			{
				$mail->addReplyTo($email->reply_to, $email->reply_to_name);
			}
			else
			{
				$mail->addReplyTo($email->reply_to);
			}
		}

		if ($email->mode == 1)
		{
			$mail->IsHTML(true);
		}
		$mail->setBody($email->body);

		
		ob_start();
		$result  = $mail->send();
		$warning = ob_get_clean();

		
		if ($result)
		{
			self::$sendMailReportMessage = JText::sprintf('COM_JUCOMMENT_SEND_EMAIL_SUCCESSFUL', $email->subject, implode(",", $email->recipients));
			if ($warning)
			{
				self::$sendMailReportMessage .= " | " . $warning;
			}

			if ($mail->ErrorInfo)
			{
				self::$sendMailReportMessage .= ' | ' . $mail->ErrorInfo;
			}
		}
		else
		{
			$result                      = false;
			self::$sendMailReportMessage = JText::sprintf('COM_JUCOMMENT_SEND_EMAIL_ERROR', $email->subject, implode(",", $email->recipients));
			if ($warning)
			{
				self::$sendMailReportMessage .= " | " . $warning;
			}

			if ($mail->ErrorInfo)
			{
				self::$sendMailReportMessage .= ' | ' . $mail->ErrorInfo;
			}

			self::$sendMailError = 1;
		}

		return $result;
	}

	
	public static function sendEmail($email, $data = array())
	{
		self::replaceEmailTags($email, $data);
		self::prepareSend($email);

		
		return self::send($email);
	}

	protected static function sendEmailDirectly($email, $data = array())
	{
		$recipients = $email->recipients ? explode(',', $email->recipients) : array();
		if (in_array('{subscriber_emails}', $recipients))
		{
			$index = array_search('{subscriber_emails}', $recipients);

			
			unset($recipients[$index]);
			list($type, $action) = explode('.', $email->event, 2);
			$subscribers = self::getSubscriberObjects($type);

			if (!empty($subscribers))
			{
				$lang = JFactory::getLanguage();
				foreach ($subscribers as $subscriber)
				{
					
					if ($email->language && $email->language != '*')
					{
						
						if ($subscriber->user_id)
						{
							$_user         = JFactory::getUser($subscriber->user_id);
							$_userLanguage = $_user->getParam('language', $lang->getTag());
							
							if ($_userLanguage != $email->language)
							{
								continue;
							}
						}
						
						
						elseif ($subscriber->language && $subscriber->language != $lang->getTag())
						{
							continue;
						}
					}

					$emailTmp                 = clone $email;
					$_data                    = $data;
					$_data['subscriber_data'] = get_object_vars($subscriber);
					$_data['recipients']      = $subscriber->email;
					if (self::sendEmail($emailTmp, $_data))
					{
						if (in_array($subscriber->email, $recipients))
						{
							$index = array_search($subscriber->email, $recipients);
							unset($recipients[$index]);
						}
					}
				}
			}
		}

		if ($recipients)
		{
			$data['recipients'] = implode(',', $recipients);
			self::sendEmail($email, $data);
		}

		return;
	}

	
	public static function sendEmailByEvent($event, $itemId = null, $data = array())
	{
		
		$emails          = self::getEmailByEvent($event, $itemId);
		$params          = JUComment::getParams();
		$enableMailq     = $params->get('enable_mailq', 1);
		$defaultMailq    = $params->get('use_mailq_default', 1);
		$data            = (array) $data;
		$data['item_id'] = $itemId;
		$data['event']   = $event;

		if (!is_array($emails) || empty($emails))
		{
			return true;
		}

		foreach ($emails as $email)
		{
			
			if ($enableMailq && ($email->use_mailq == 1 || ($email->use_mailq == -2 && $defaultMailq)))
			{
				self::addMailq($email, $data);
			}
			else
			{
				self::sendEmailDirectly($email, $data);
			}
		}

		return true;
	}

	
	public static function sendMailq($limit = null, $report = false)
	{
		$params = JUComment::getParams();
		if (!$limit)
		{
			$limit = $params->get('total_mailqs_sent_each_time', 5);
		}

		
		$queueElements = self::getMailq(0, $limit);
		if (empty($queueElements))
		{
			if ($report)
			{
				echo '<div id="messages_success"><h4>' . JText::_('COM_JUCOMMENT_SEND_EMAIL_SUCCESSFULLY') . '</h4></div>';
			}

			return true;
		}

		@ini_set('max_execution_time', 600);

		
		@ini_set('pcre.backtrack_limit', 1000000);

		@ini_set('default_socket_timeout', 10);

		
		@ignore_user_abort(true);

		$timelimit = ini_get('max_execution_time');

		if (!empty($timelimit))
		{
			$stoptime = time() + $timelimit - 4;
		}

		$start         = 0;
		$mod_security2 = 0;

		if ($report)
		{
			$app = JFactory::getApplication();
			
			$obend = 0;
			
			$pause = 5;
			//
			$finish = false;
			
			$total = $app->input->getInt('total', 0);
			
			$start = $app->input->getInt('start', 0);

			if (function_exists('apache_get_modules'))
			{
				$modules       = apache_get_modules();
				$mod_security2 = in_array('mod_security2', $modules);
			}

			if (!headers_sent())
			{
				while (ob_get_level() > 0 && $obend++ < 3)
				{
					
					@ob_end_flush();
				}
			}

			$disp = '<html><head><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />';
			$disp .= '<title>' . JText::_('COM_JUCOMMENT_SEND_PROCESS') . '</title>';
			$disp .= '<style>body{font-size:12px;font-family: Arial,Helvetica,sans-serif;}</style></head><body>';
			$disp .= "<div style='position:fixed; top:3px;left:3px;background-color : white;border : 1px solid grey; padding : 3px;font-size:14px'>";
			$disp .= "<span id='divpauseinfo' style='padding:10px;margin:5px;font-size:16px;font-weight:bold;display:none;background-color:black;color:white;'> </span>";
			$disp .= JText::_('COM_JUCOMMENT_SEND_PROCESS') . ': <span id="counter" >' . $start . '</span> / ' . $total;
			$disp .= '</div>';
			$disp .= "<div id='divinfo' style='display:none; position:fixed; bottom:3px;left:3px;background-color : white; border : 1px solid grey; padding : 3px;'> </div>";
			$disp .= '<br /><br />';
			$url = JUri::base() . 'index.php?option=com_jucomment&task=mailqs.send&tmpl=component&total=' . $total . "&start=";
			$disp .= '<script type="text/javascript" language="javascript">';
			$disp .= 'var mycounter = document.getElementById("counter");';
			$disp .= 'var divinfo = document.getElementById("divinfo");
						var divpauseinfo = document.getElementById("divpauseinfo");
						function setInfo(message){ divinfo.style.display = \'block\';divinfo.innerHTML=message; }
						function setPauseInfo(nbpause){ divpauseinfo.style.display = \'\';divpauseinfo.innerHTML=nbpause;}
						function setCounter(val){ mycounter.innerHTML=val;}
						var scriptpause = ' . intval($pause) . ';
						function handlePause(){
							setPauseInfo(scriptpause);
							if(scriptpause > 0){
								scriptpause = scriptpause - 1;
								setTimeout(\'handlePause()\',1000);
							}else{
								document.location.href=\'' . $url . '\'+mycounter.innerHTML;
							}
						}
						</script>';
			echo $disp;

			
			if (!$mod_security2)
			{
				@flush();
			}
		}

		$queueUpdate = array();
		$queueDelete = array();

		
		$currentEmail = $start;

		$nbprocess = 0;

		$successSend = 0;

		$consecutiveError = 0;

		$maxAttempt = $params->get('mailq_max_attempts', 5);

		$deleteMaxtryEmail = $params->get('delete_error_mailq', 0);

		if (count($queueElements) < $limit)
		{
			$finish = true;
		}

		
		foreach ($queueElements as $element)
		{
			$currentEmail++;
			$nbprocess++;

			if ($report)
			{
				echo '<script type="text/javascript" language="javascript">setCounter(' . $currentEmail . ')</script>';

				
				
				if (function_exists('ob_flush'))
				{
					@ob_flush();
				}

				if (!$mod_security2)
				{
					@flush();
				}
			}

			
			$email = self::getEmailById($element->email_id);

			$result        = false;
			$queueDeleteOk = true;
			$otherMessage  = '';

			if ($email)
			{
				$exData = array();
				if ($element->data)
				{
					$registry = new JRegistry($element->data);
					$exData   = $registry->toArray();
				}

				$result = self::sendEmail($email, $exData);
			}

			if ($result)
			{
				$consecutiveError = 0;
				$successSend++;

				
				$queueDeleteOk = self::deleteMailq(array($element->id));
			}
			else
			{
				$newtry = false;
				
				if (self::$sendMailError == 1)
				{
					if (empty($maxAttempt) || $element->attempts < $maxAttempt)
					{
						$newtry       = true;
						$otherMessage = JText::sprintf('COM_JUCOMMENT_QUEUE_NEXT_TRY', 60);
					}

					$consecutiveError++;
					if ($consecutiveError == 2)
					{
						
						sleep(1);
					}
				}

				if (!$newtry)
				{
					$queueDelete[] = $element->id;
					if (self::$sendMailError == 1 && $deleteMaxtryEmail)
					{
						
						$queueDeleteOk = self::deleteMailq($queueDelete);
						$queueDelete   = array();
						$otherMessage  = JText::sprintf('COM_JUCOMMENT_FAIL_TO_SEND_EMAIL_TO_X', $email->recipients);
					}
				}
				else
				{
					
					$queueUpdate[] = $element->id;
				}
			}

			$messageOnScreen = '[' . $element->id . '] ' . self::$sendMailReportMessage;

			if (!empty($otherMessage))
			{
				$messageOnScreen .= ' => ' . $otherMessage;
			}

			self::_display($messageOnScreen, $result, $currentEmail, $report, $mod_security2);

			if (!$queueDeleteOk)
			{
				$finish = true;
				break;
			}

			
			if (!empty($stoptime) && $stoptime < time())
			{

				self::_display(JText::_('COM_JUCOMMENT_SEND_REFRESH_TIMEOUT'), '', '', $report, $mod_security2);

				if ($nbprocess < count($queueElements))
				{
					$finish = false;
				}
				break;
			}

			if ($consecutiveError > 3 && $successSend > 3)
			{
				self::_display(JText::_('COM_JUCOMMENT_SEND_REFRESH_CONNECTION'), '', '', $report, $mod_security2);
				break;
			}

			if ($consecutiveError > 5 || connection_aborted())
			{
				$finish = true;
				break;
			}
		}

		
		self::updateMailq($queueUpdate);

		if (!empty($total) AND $currentEmail >= $total)
		{
			$finish = true;
		}

		if ($consecutiveError > 5)
		{
			self::_handleError($start, $report, $successSend, $mod_security2);

			return false;
		}

		if ($report)
		{
			if (!$finish)
			{
				echo '<script type="text/javascript" language="javascript">handlePause();</script>';
			}

			echo "</body></html>";
			while ($obend-- > 0)
			{
				ob_start();
			}
		}

		return true;
	}

	
	protected static function getSubscriberObjects($data)
	{
		if (!$data['component'] || !$data['cid'])
		{
			return '';
		}

		$db = JFactory::getDbo();

		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__jucomment_subscriptions');
		$query->where('item_id = ' . (int) $data['cid']);
		$query->where('component = ' . $db->quote($data['component']));
		$query->where('section = ' . $db->quote($data['section']));
		$query->where('cid = ' . $db->quote($data['cid']));
		$query->where('published = 1');

		$db->setQuery($query);
		$subscribers = $db->loadObjectList();

		return $subscribers;
	}

	private static function _display($message, $status = '', $num = '', $report, $mod_security2)
	{
		if ($report)
		{
			$message = nl2br($message);
			if (!empty($num))
			{
				$color = $status ? 'green' : 'red';
				echo '<br/>' . $num . ' : <font color="' . $color . '">' . $message . '</font>';
			}
			else
			{
				echo '<script type="text/javascript" language="javascript">setInfo(\'' . addslashes($message) . '\')</script>';
			}

			if (function_exists('ob_flush'))
			{
				@ob_flush();
			}
			if (!$mod_security2)
			{
				@flush();
			}
		}
	}

	private static function _handleError($start, $report, $successSend, $mod_security2)
	{
		$message = JText::_('COM_JUCOMMENT_EMAIL_SENDING_STOPPED');
		$message .= '<br/>';
		$message .= JText::_('COM_JUCOMMENT_SEND_KEPT_ALL');
		$message .= '<br/>';
		if ($report)
		{
			if (empty($successSend) AND empty($start))
			{
				$message .= JText::_('COM_JUCOMMENT_SEND_CHECK_ONE');
				$message .= '<br/>';
				$message .= JText::_('COM_JUCOMMENT_SEND_ADVISE_LIMITATION');
			}
			else
			{
				$message .= JText::_('COM_JUCOMMENT_SEND_REFUSE');
				$message .= '<br/>';
			}
		}

		self::_display($message, '', '', $report, $mod_security2);
	}

	public static function getEvents()
	{
		return array(
			"comment.create"    => JText::_('COM_JUCOMMENT_EVENT_CREATE_COMMENT'),
			"comment.approve"   => JText::_('COM_JUCOMMENT_EVENT_APPROVE_COMMENT'),
			"comment.reject"    => JText::_('COM_JUCOMMENT_EVENT_REJECT_COMMENT'),
			"comment.editstate" => JText::_('COM_JUCOMMENT_EVENT_EDIT_STATE_COMMENT'),
			"comment.edit"      => JText::_('COM_JUCOMMENT_EVENT_EDIT_COMMENT'),
			"comment.delete"    => JText::_('COM_JUCOMMENT_EVENT_DELETE_COMMENT'),
			"comment.report"    => JText::_('COM_JUCOMMENT_EVENT_REPORT_COMMENT'),

			"reply.create"    => JText::_('COM_JUCOMMENT_EVENT_CREATE_REPLY'),
			"reply.approve"   => JText::_('COM_JUCOMMENT_EVENT_APPROVE_REPLY'),
			"reply.reject"    => JText::_('COM_JUCOMMENT_EVENT_REJECT_REPLY'),
			"reply.editstate" => JText::_('COM_JUCOMMENT_EVENT_EDIT_STATE_REPLY'),
			"reply.edit"      => JText::_('COM_JUCOMMENT_EVENT_EDIT_REPLY'),
			"reply.delete"    => JText::_('COM_JUCOMMENT_EVENT_DELETE_REPLY'),
			"reply.report"    => JText::_('COM_JUCOMMENT_EVENT_REPORT_REPLY'),

			"article.subscribe" => JText::_('COM_JUCOMMENT_EVENT_SUBSCRIBE_ARTICLE')
		);
	}
}
