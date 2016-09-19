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

jimport('joomla.application.component.controllerform');

class JUCommentControllerComment extends JControllerForm
{
	
	public function getModel($name = '', $prefix = '', $config = array('ignore_request' => true))
	{
		return JUComment::getModel('Comment', true);
	}

	
	public function save($key = null, $urlVar = null)
	{
		
		JSession::checkToken('get') or JSession::checkToken('post') or jexit(JText::_('JINVALID_TOKEN'));

		$model      = $this->getModel();
		$table      = $model->getTable();
		$data       = $this->input->post->get('jform', array(), 'array');
		$fieldsData = $this->input->post->get('fields', array(), 'array');
		$checkin    = property_exists($table, 'checked_out');
		$key = 'id';

		$fieldId  = JUCommentFieldHelper::getField('id');

		if(isset($fieldsData[$fieldId->id]))
		{
			$recordId = $data[$key] = $fieldsData[$fieldId->id];
		}
		else
		{
			$recordId = $data[$key] = 0;
		}

		$fieldComponent = JUCommentFieldHelper::getField('component');
		$fieldSection   = JUCommentFieldHelper::getField('section');
		$fieldCid       = JUCommentFieldHelper::getField('cid');

		$component = $fieldsData[$fieldComponent->id];
		$section   = $fieldsData[$fieldSection->id];
		$cid       = $fieldsData[$fieldCid->id];

		if (!$component || !$cid)
		{
			$this->setError(JText::_('Invalid component or cid'));

			return false;
		}

		JUComment::setCurrent('component', $component);
		JUComment::setCurrent('section', $section);
		JUComment::setCurrent('cid', $cid);

		
		if (!$this->allowSave($data, $key))
		{
			return false;
		}

		$fieldgroupObj = JUCommentFieldHelper::getField('fieldgroup_id');
		$fieldgroupId  = ($fieldgroupObj && isset($fieldsData[$fieldgroupObj->id])) ? $fieldsData[$fieldgroupObj->id] : 1;
		$fields        = $model->getFields($fieldgroupId, $data[$key]);
		
		$validFieldsData = $model->validateFields($fields, $fieldsData, $data[$key]);
		$validData              = array();
		$validData[$key]        = $recordId;
		$fieldParentId          = $fields['parent_id'];
		$validData['parent_id'] = isset($fieldsData[$fieldParentId->id]) ? $fieldsData[$fieldParentId->id] : 1;
		
		if ($validFieldsData === false)
		{
			
			$errors  = $model->getErrors();
			$message = array();
			
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof Exception)
				{
					$message[] = $errors[$i]->getMessage();
				}
				else
				{
					$message[] = $errors[$i];
				}
			}

			$this->setError(implode("<br/>", $message));

			return false;
		}

		if ($data[$key] == 0)
		{
			$validData['approved']  = 0;
			$validData['published'] = 1;

			$params     = JUComment::getParams();
			$permission = JUComment::getHelper('permission');
			if ($validData[$key] == 0)
			{
				if ($permission->allow('auto_approve'))
				{
					$validData['approved'] = 1;
				}

				$profile = JUComment::getProfile();
				if ($validData['approved'] == 0 && $profile->id > 0)
				{
					JUComment::import('helper', 'comment');

					$auto_approval_comment = $params->get('auto_approval_comment_threshold', 0);
					if ($auto_approval_comment > 0)
					{
						$approvalComments = JUCommentCommentHelper::getTotalApprovedCommentsOfUser($profile->id, 'comment');
						if ($approvalComments >= $auto_approval_comment)
						{
							$validData['approved'] = 1;
						}
					}
				}
			}
		}

		$data = array();
		
		$data['data']   = $validData;
		$data['fields'] = $fields;
		$data['fieldsData'] = $validFieldsData;

		$commentId = $model->save($data);

		if (!$commentId)
		{
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));

			return false;
		}

		
		if ($checkin && $model->checkin($validData[$key]) === false)
		{
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()));

			return false;
		}

		$app = JFactory::getApplication();

		$return = $app->input->get('return', '', 'string');
		if ($return)
		{
			$return = base64_decode($return);
			$lang   = JFactory::getLanguage();
			$this->setMessage(
				JText::_(
					($lang->hasKey($this->text_prefix . ($recordId == 0 && $app->isSite() ? '_SUBMIT' : '') . '_SAVE_SUCCESS')
						? $this->text_prefix
						: 'JLIB_APPLICATION') . ($recordId == 0 && $app->isSite() ? '_SUBMIT' : '') . '_SAVE_SUCCESS'
				)
			);

			$this->setRedirect(
				JRoute::_($return, false)
			);

			return false;
		}

		$table->load($commentId);

		return $table;
	}

	protected function allowAdd($data = array())
	{
		$fieldsData = $this->input->post->get('fields', array(), 'array');

		$fieldCid = JUCommentFieldHelper::getField('cid');
		$cid      = ($fieldCid && isset($fieldsData[$fieldCid->id])) ? $fieldsData[$fieldCid->id] : '0';
		if (!$cid)
		{
			$this->setError(JText::_('Empty cid'));

			return false;
		}

		$permission = JUComment::getHelper('permission');

		$fieldParentId = JUCommentFieldHelper::getField('parentId');
		$parentId      = ($fieldParentId && isset($fieldsData[$fieldParentId->id])) ? $fieldsData[$fieldParentId->id] : '1';
		if ($parentId > 1)
		{
			if (!$permission->allow('reply'))
			{
				$this->setError(JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));

				return false;
			}
		}
		else
		{
			if (!$permission->allow('create'))
			{
				$this->setError(JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));

				return false;
			}
		}

		
		$profile = JUComment::getProfile();
		if ($profile->id > 0)
		{
			$user = $profile->id;
		}
		else
		{
			$fieldEmailObj = JUCommentFieldHelper::getField('guest_email');
			$user          = ($fieldEmailObj && isset($fieldsData[$fieldEmailObj->id])) ? $fieldsData[$fieldEmailObj->id] : '';
		}

		$params = JUComment::getParams();
		JUComment::import('helper', 'comment');
		if (JUCommentCommentHelper::getTotalCommentsOnArticleOfUser($cid, $user) && !$permission->allow('create_many_times'))
		{
			$this->setError(JText::_('COM_JUCOMMENT_YOU_CAN_COMMENT_ONCE_IN_THIS_ARTICLE'));

			return false;
		}

		$comment_interval = $params->get('comment_interval', 60);
		if ($comment_interval > 0)
		{
			$comment_latest = strtotime(JUCommentCommentHelper::getLatestCommentTime($user));
			$waiting        = ($comment_latest + $comment_interval) - time();
			if ($waiting > 0)
			{
				$this->setError($this->getErrorForIntervalComment($waiting));

				return false;
			}
		}

		$comment_interval_same_article = $params->get('comment_interval_in_same_article', 60);
		if ($comment_interval_same_article > 0)
		{
			$comment_latest_same_article = strtotime(JUCommentCommentHelper::getLatestCommentTime($user, $cid));
			$waiting                     = ($comment_latest_same_article + $comment_interval_same_article) - time();
			if ($waiting > 0)
			{
				$this->setError($this->getErrorForIntervalComment($waiting));

				return false;
			}
		}

		return true;
	}

	protected function getErrorForIntervalComment($waiting)
	{
		$date    = new JDate($waiting);
		$waiting = $date->format('d H i s');

		$timeArr = explode(' ', $waiting);

		$time_str = '';
		if (($timeArr[0] - 1) > 0)
		{
			$time_str .= " " . JText::plural('COM_JUCOMMENT_TIME_N_DAY', $timeArr[0] - 1);
		}

		if ($timeArr[1] > 0)
		{
			$time_str .= " " . JText::plural('COM_JUCOMMENT_TIME_N_HOUR', $timeArr[1]);
		}

		if ($timeArr[2] > 0)
		{
			$time_str .= " " . JText::plural('COM_JUCOMMENT_TIME_N_MINUTE', $timeArr[2]);
		}

		if ($timeArr[3] > 0)
		{
			$time_str .= " " . JText::plural('COM_JUCOMMENT_TIME_N_SECOND', $timeArr[3]);
		}

		$error = JText::sprintf('COM_JUCOMMENT_YOU_HAVE_TO_WAIT_TIME_BEFORE_SUBMIT_NEW_COMMENT', $time_str);

		return $error;
	}

	protected function allowEdit($data = array(), $key = 'id')
	{
		$permission = JUComment::getHelper('permission');
		$comment    = JUComment::getComment($data[$key]);
		if (!$permission->allow('edit', $comment))
		{
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));

			return false;
		}

		return true;
	}

	public function addComment()
	{
		
		JSession::checkToken('get') or jexit(JText::_('JINVALID_TOKEN'));
		$table  = $this->save();
		$ajax   = JUComment::getHelper('ajax');
		$result = array();
		if (!$table)
		{
			$result['message'] = $this->getError();
			$ajax->fail($result);
			$ajax->send();
		}

		if (!$table->approved)
		{
			$result['message'] = JText::_('COM_JUCOMMENT_COMMENT_SUBMITTED_AND_PENDING_APPROVAL');
			$ajax->success($result);
			$ajax->send();
		}
		else
		{
			$app = JFactory::getApplication();
			$app->input->set('component', $table->component);
			$app->input->set('section', $table->section);
			$app->input->set('cid', $table->cid);
			$app->input->set('comment_id', $table->id);
			$this->gotoItem();
		}
	}

	public function replyComment()
	{
		
		JSession::checkToken('get') or jexit(JText::_('JINVALID_TOKEN'));

		$table  = $this->save();
		$ajax   = JUComment::getHelper('ajax');
		$result = array();
		if ($table === false)
		{
			$result['message'] = $this->getError();
			$ajax->fail($result);
			$ajax->send();
		}

		if ($table->approved == 1)
		{
			JUComment::setCurrent('component', $table->component);
			JUComment::setCurrent('section', $table->section);
			JUComment::setCurrent('cid', $table->cid);

			$template = JUComment::getTemplate();

			$template->set('comments', array($table));
			$template->set('parent', $table->parent_id);

			$result['message']    = JText::_('COM_JUCOMMENT_REPLY_COMMENT_SUCCESSFULLY');
			$result['html']       = $template->fetch('comment/items.php');
			$result['comment_id'] = $table->id;
			$result['parent']     = $table->parent_id;
		}
		else
		{
			$result['message'] = JText::_('COM_JUCOMMENT_REPLY_COMMENT_SUCCESSFULLY_AND_PENDING_APPROVAL');
		}

		$ajax->success($result);
		$ajax->send();
	}

	
	public function editComment()
	{
		
		JSession::checkToken('get') or jexit(JText::_('JINVALID_TOKEN'));

		$table  = $this->save();
		$ajax   = JUComment::getHelper('ajax');
		$result = array();

		if (!$table)
		{
			$result['message'] = $this->getError();
			$ajax->fail($result);
			$ajax->send();
		}

		if ($table->approved)
		{
			JUComment::setCurrent('component', $table->component);
			JUComment::setCurrent('section', $table->section);
			JUComment::setCurrent('cid', $table->cid);

			$template = JUComment::getTemplate();
			$template->set('row', $table);
			$template->set('ajax', 1);

			$result['message']    = JText::_('JLIB_APPLICATION_SUBMIT_SAVE_SUCCESS');
			$result['comment_id'] = $table->id;
			$result['html']       = $template->fetch('comment/item.php');
		}
		else
		{
			$result['message'] = JText::_('COM_JUCOMMENT_COMMENT_SUBMITTED_AND_PENDING_APPROVAL');
		}

		$ajax->success($result);
		$ajax->send();
	}

	
	public function delete()
	{
		
		JSession::checkToken('get') or die(JText::_('JINVALID_TOKEN'));

		$ajax = JUComment::getHelper('ajax');
		
		$comment_id = JFactory::getApplication()->input->get('comment_id', 0);

		$result = array();
		if (!$comment_id)
		{
			$result['message'] = JText::_($this->text_prefix . '_NO_ITEM_SELECTED');
			$ajax->fail($result);
			$ajax->send();
		}
		else
		{
			
			$model = $this->getModel();

			
			jimport('joomla.utilities.arrayhelper');
			$cid = array($comment_id);
			JArrayHelper::toInteger($cid);

			
			if ($model->delete($cid))
			{
				$this->getCommentList();
			}
			else
			{
				$result['message'] = $model->getError();
				$ajax->fail($result);
				$ajax->send();
			}
		}
	}

	public function report()
	{
		
		JSession::checkToken('get') or jexit(JText::_('JINVALID_TOKEN'));

		$controllerReport = JUComment::getController('Report');
		$controllerReport->save();
	}

	public function subscribe()
	{
		
		JSession::checkToken('get') or die(JText::_('JINVALID_TOKEN'));

		$app  = JFactory::getApplication();
		$type = $app->input->get('type');
		if ($type == 'subscribe')
		{
			$controllerSubscription = JUComment::getController('Subscription');
			$controllerSubscription->save();
		}
		elseif ($type == 'unsubscribe')
		{
			$controllerSubscription = JUComment::getController('Subscriptions');
			$controllerSubscription->unSubscribe();
		}
	}

	public function getCommentList()
	{
		
		JSession::checkToken('get') or die(JText::_('JINVALID_TOKEN'));

		$ajax        = JUComment::getHelper('ajax');
		$app         = JFactory::getApplication();
		$component   = $app->input->get('component');
		$section     = $app->input->get('section');
		$cid         = $app->input->get('cid');
		$sort        = $app->input->get('sort');
		$direction   = $app->input->get('direction');
		$contentLink = $app->input->get('contentLink');
		$options     = array();
		$result      = array();
		if ($sort)
		{
			$options['sort'] = $sort;
		}
		if ($direction)
		{
			$options['direction'] = $direction;
		}

		$application = JUComment::loadApplication($component, $section)->load($cid);

		JUComment::setCurrent('component', $component);
		JUComment::setCurrent('section', $section);
		JUComment::setCurrent('cid', $cid);

		$commentsModel = JUComment::getModel('comments');
		$permission    = JUComment::getHelper('permission');

		if ($application === false)
		{
			$application = JUComment::getErrorApplication($component, $cid);
		}

		if (!$permission->allow('read'))
		{
			$result['message'] = JText::_('COM_JUCOMMENT_YOU_ARE_NOT_AUTHORIZED_TO_READ_COMMENT');
			$ajax->fail($result);
			$ajax->send();
		}

		
		if (isset($options['published']) && $options['published'] != '1' && !$permission->allow('edit_state'))
		{
			$result['message'] = JText::_('COM_JUCOMMENT_YOU_ARE_NOT_AUTHORIZED_TO_READ_UNPUBLISHED_COMMENT');
			$ajax->fail($result);
			$ajax->send();
		}

		$params = JUComment::getParams();

		$options['max_level'] = 2;
		$options['filter_language']  = $params->get('filter_comment_language', 0);
		$commentCount         = $commentsModel->getCount($component, $section, $cid, $options);

		$topCommentOptions['max_level'] = 2;
		$topCommentOptions['filter_language']  = $params->get('filter_comment_language', 0);
		$topCommentOptions['parent_id'] = 1;
		$topCommentCount                = $commentsModel->getCount($component, $section, $cid, $topCommentOptions);

		$page        = $app->input->getInt('page', 1);
		$total_pages = ceil($topCommentCount / $params->get('comment_pagination', 10));
		if ($page > $total_pages)
		{
			$page = $total_pages;
		}

		if ($page < 1)
		{
			$page = 1;
		}

		$options['limit']      = $params->get('comment_pagination', 10);
		$options['limitstart'] = ($page - 1) * $params->get('comment_pagination', 10);
		$comments              = $commentsModel->getComments($component, $section, $cid, $options);

		$template = JUComment::getTemplate();

		$template->set('options', $options);
		$template->set('comments', $comments);
		$template->set('commentCount', $commentCount);
		$template->set('componentHelper', $application);
		$template->set('contentLink', $contentLink);
		$template->set('subscription', JUComment::getSubscription($component, $section, $cid));

		$template->set('component', $component);
		$template->set('section', $section);
		$template->set('cid', $cid);
		$template->set('fieldgroup_id', $params->get('comment_fieldgroup_id', 0));

		
		$template->set('page', $page);
		$template->set('total_pages', $total_pages);

		$result['html'] = $template->fetch('comment/list.php');
		$result['message'] = JText::_('COM_JUCOMMENT_SUCCESSFUL');
		$ajax->success($result);
		$ajax->send();
	}

	
	public function gotoItem()
	{
		
		JSession::checkToken('get') or die(JText::_('JINVALID_TOKEN'));

		$ajax        = JUComment::getHelper('ajax');
		$app         = JFactory::getApplication();
		$component   = $app->input->get('component');
		$section     = $app->input->get('section');
		$cid         = $app->input->get('cid');
		$sort        = $app->input->get('sort');
		$direction   = $app->input->get('direction');
		$contentLink = $app->input->get('contentLink', '', 'string');
		$comment_id  = $app->input->get('comment_id');
		$options     = array();
		$result      = array();
		if ($sort)
		{
			$options['sort'] = $sort;
		}
		if ($direction)
		{
			$options['direction'] = $direction;
		}

		$JUCMApplication = JUComment::loadApplication($component, $section);
		$JUCMApplication = $JUCMApplication->load($cid);

		JUComment::setCurrent('component', $component);
		JUComment::setCurrent('section', $section);
		JUComment::setCurrent('cid', $cid);

		$commentsModel = JUComment::getModel('comments');
		$permission    = JUComment::getHelper('permission');

		if ($JUCMApplication === false)
		{
			$JUCMApplication = JUComment::getErrorApplication($component, $cid);
		}

		if (!$permission->allow('read'))
		{
			$result['message'] = JText::_('COM_JUCOMMENT_YOU_ARE_NOT_AUTHORIZED_TO_READ_COMMENT');
			$ajax->fail($result);
			$ajax->send();
		}

		
		if (isset($options['published']) && $options['published'] != '1' && !$permission->allow('edit_state'))
		{
			$result['message'] = JText::_('COM_JUCOMMENT_YOU_ARE_NOT_AUTHORIZED_TO_READ_UNPUBLISHED_COMMENT');
			$ajax->fail($result);
			$ajax->send();
		}

		$params = JUComment::getParams();

		$index = 0;
		$page  = 1;

		$comments = $commentsModel->getComments($component, $section, $cid, $options);
		foreach ($comments as $comment)
		{
			if ($comment->parent_id == 1)
			{
				$index++;
			}
			if ($comment->id == $comment_id)
			{
				break;
			}
		}

		if ($index)
		{
			$page = ceil($index / $params->get('comment_pagination', 10));
		}

		$options['limit']      = $params->get('comment_pagination', 10);
		$options['limitstart'] = ($page - 1) * $params->get('comment_pagination', 10);
		$options['max_level']  = 2;
		$options['filter_language']   = $params->get('filter_comment_language', 0);
		$commentCount          = $commentsModel->getCount($component, $section, $cid, $options);

		$topCommentOptions['max_level'] = 2;
		$topCommentOptions['filter_language']  = $params->get('filter_comment_language', 0);
		$topCommentOptions['parent_id'] = 1;
		$topCommentCount                = $commentsModel->getCount($component, $section, $cid, $topCommentOptions);

		$comments    = $commentsModel->getComments($component, $section, $cid, $options);
		$total_pages = ceil($topCommentCount / $params->get('comment_pagination', 10));

		$template = JUComment::getTemplate();
		$template->set('options', $options);
		$template->set('comments', $comments);
		$template->set('commentCount', $commentCount);
		$template->set('componentHelper', $JUCMApplication);
		$template->set('contentLink', $contentLink);
		$template->set('subscription', JUComment::getSubscription($component, $section, $cid));

		$template->set('component', $component);
		$template->set('section', $section);
		$template->set('cid', $cid);
		$template->set('fieldgroup_id', $params->get('comment_fieldgroup_id', 0));


		
		$template->set('page', $page);
		$template->set('total_pages', $total_pages);

		$result['html']       = $template->fetch('comment/list.php');
		$result['comment_id'] = $comment_id;
		$result['message']    = 'Successful';

		$ajax->success($result);
		$ajax->send();
	}

	public function getForm()
	{
		
		JSession::checkToken('get') or jexit(JText::_('JINVALID_TOKEN'));

		$app      = JFactory::getApplication();
		$formtype = $app->input->get('formtype', '');
		$width    = $app->input->get('width', '');
		switch ($formtype)
		{
			case 'edit':
				$comment_id = $app->input->get('comment_id');
				$comment    = JUCommentHelper::getCommentById($comment_id);
				JUComment::setCurrent('component', $comment->component);
				JUComment::setCurrent('section', $comment->section);
				JUComment::setCurrent('cid', $comment->cid);
				$template = JUComment::getTemplate();
				$template->set('width', $width);
				$template->set('row', $comment);
				
				JUComment::getHelper('Document')->loadHeaders();

				echo $template->fetch('comment/form_edit.php');
				break;

			case 'reply':
				$comment_id = $app->input->get('comment_id');
				$comment    = JUCommentHelper::getCommentById($comment_id);
				JUComment::setCurrent('component', $comment->component);
				JUComment::setCurrent('section', $comment->section);
				JUComment::setCurrent('cid', $comment->cid);
				$template = JUComment::getTemplate();
				$template->set('row', $comment);
				$template->set('width', $width);
				$params = JUComment::getParams();
				$template->set('fieldgroup_id', $params->get('reply_fieldgroup_id', 0));
				
				JUComment::getHelper('Document')->loadHeaders();

				echo $template->fetch('comment/form_reply.php');
				break;

			case 'report':
				$comment_id = $app->input->get('comment_id');
				$comment    = JUCommentHelper::getCommentById($comment_id);
				JUComment::setCurrent('component', $comment->component);
				JUComment::setCurrent('section', $comment->section);
				JUComment::setCurrent('cid', $comment->cid);
				$template = JUComment::getTemplate();
				$template->set('row', $comment);
				$template->set('width', $width);
				
				JUComment::getHelper('Document')->loadHeaders();

				echo $template->fetch('comment/form_report.php');
				break;
		}
	}

	public function quoteComment()
	{
		
		JSession::checkToken('get') or jexit(JText::_('JINVALID_TOKEN'));

		$ajax       = JUComment::getHelper('ajax');
		$comment_id = JFactory::getApplication()->input->getInt('comment_id', 0);
		if (!$comment_id)
		{
			$result['message'] = JText::_('COM_JUCOMMENT_INVALID_COMMENT');
			$ajax->fail($result);
			$ajax->send();
		}

		$comment = JUCommentHelper::getCommentById($comment_id);
		if (!$comment)
		{
			if (!$comment_id)
			{
				$result['message'] = JText::_('COM_JUCOMMENT_CAN_NOT_LOAD_COMMENT');
				$ajax->fail($result);
				$ajax->send();
			}
		}

		$name           = ($comment->user_id > 0) ? JFactory::getUser($comment->user_id)->name : $comment->guest_name;
		$result['html'] = '[quote="' . $name . '"]' . $comment->comment . '[/quote]';
		$ajax->success($result);
		$ajax->send();
	}

	public function vote()
	{
		JSession::checkToken('get') or die(JText::_('JINVALID_TOKEN'));

		$ajax       = JUComment::getHelper('ajax');
		$app        = JFactory::getApplication();
		$comment_id = $app->input->get('comment_id');
		$action     = $app->input->get('action', 'add', 'string');
		$type       = $app->input->get('type', 'vote-up', 'string');
		$result     = array();

		if (!$comment_id)
		{
			$result['message'] = JText::_('COM_JUCOMMENT_INVALID_COMMENT');
			$ajax->fail($result);
			$ajax->send();
		}

		$comment = JUCommentHelper::getCommentById($comment_id);
		if (!$comment)
		{
			if (!$comment_id)
			{
				$result['message'] = JText::_('COM_JUCOMMENT_CAN_NOT_LOAD_COMMENT');
				$ajax->fail($result);
				$ajax->send();
			}
		}

		$permission = JUComment::getHelper('permission');

		$comment = JUCommentHelper::getCommentById($comment_id);
		if (!$permission->allow('vote', $comment))
		{
			if (!$comment_id)
			{
				$result['message'] = JText::_('COM_JUCOMMENT_YOU_ARE_NOT_AUTHORIZED_TO_VOTE_COMMENT');
				$ajax->fail($result);
				$ajax->send();
			}
		}

		$commentsModel = JUComment::getModel('comments');
		if ($commentsModel->vote($type, $comment_id, $action) === false)
		{
			$result['message'] = $commentsModel->getError();
			$ajax->fail($result);
			$ajax->send();
		}
		else
		{
			$comment                = JUCommentHelper::getCommentById($comment_id, true);
			$vote_counter           = $comment->helpful_votes - ($comment->total_votes - $comment->helpful_votes);
			$vote_counter           = $vote_counter > 0 ? "+" . $vote_counter : $vote_counter;
			$result['message']      = JText::_('COM_JUCOMMENT_VOTE_COMMENT_SUCCESSFULLY');
			$result['vote_counter'] = $vote_counter;
			$ajax->success($result);
			$ajax->send();
		}
	}

	public function unpublish()
	{
		JSession::checkToken('get') or die(JText::_('JINVALID_TOKEN'));

		$ajax       = JUComment::getHelper('ajax');
		$app        = JFactory::getApplication();
		$comment_id = $app->input->get('comment_id');

		$result = array();
		if (!$comment_id)
		{
			$result['message'] = JText::_($this->text_prefix . '_NO_ITEM_SELECTED');
			$ajax->fail($result);
			$ajax->send();
		}

		$model       = $this->getModel();
		$comment_ids = array($comment_id);
		if ($model->publish($comment_ids, 0))
		{
			$this->getCommentList();
		}
		else
		{
			$result['message'] = JText::_("COM_JUCOMMENT_FAIL_TO_UNPUBLISH_COMMENT");
			$ajax->fail($result);
			$ajax->send();
		}
	}

	public function captcha()
	{
		$commentHelper = JUComment::getHelper('Captcha');
		$commentHelper->show();
	}

	public function reloadCaptcha()
	{
		
		JSession::checkToken('get') or jexit(JText::_('JINVALID_TOKEN'));

		$ajax          = JUComment::getHelper('Ajax');
		$commentHelper = JUComment::getCaptcha();
		$data          = $commentHelper->getReloadSyntax();
		if ($data)
		{
			$ajax->success($data);
		}
		else
		{
			$ajax->fail(JText::_('COM_JUCOMMENT_FAIL_TO_RELOAD_CAPTCHA'));
		}

		$ajax->send();
	}

	
	public function cancel($key = null)
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$app     = JFactory::getApplication();
		$model   = $this->getModel();
		$table   = $model->getTable();
		$checkin = property_exists($table, 'checked_out');
		$context = "$this->option.edit.$this->context";

		if (empty($key))
		{
			$key = $table->getKeyName();
		}

		$recordId = $app->input->getInt($key);
		$return   = $app->input->get('return', '', 'string');
		$return   = base64_decode($return);
		
		if ($recordId)
		{
			if ($checkin)
			{
				if ($model->checkin($recordId) === false)
				{
					
					$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()));
					$this->setMessage($this->getError(), 'error');
					$this->setRedirect(
						JRoute::_(
							'index.php?option=' . $this->option . '&view=' . $this->view_item
							. $this->getRedirectToItemAppend($recordId, $key), false
						)
					);

					return false;
				}
			}
		}

		
		$this->releaseEditId($context, $recordId);
		$app->setUserState($context . '.data', null);

		$this->setRedirect(
			JRoute::_($return, false)
		);

		return true;
	}

	public function edit($key = null, $urlVar = null)
	{
		$app            = JFactory::getApplication();
		$listviewlayout = $app->input->get('return', '');
		$app->setUserState('return', $listviewlayout);

		return parent::edit($key, $urlVar);
	}
}
