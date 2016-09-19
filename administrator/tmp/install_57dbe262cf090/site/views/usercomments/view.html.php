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

class JUCommentViewUsercomments extends JViewLegacy
{
	public function display($tpl = null)
	{
		JUComment::import('helper', 'permission');
		if (JUCommentPermissionHelper::canViewDashboard() == false)
		{
			JError::raiseWarning(500, JText::_('COM_JUCOMMENT_YOU_ARE_NOT_AUTHORIZED_TO_ACCESS_THIS_PAGE'));

			return false;
		}

		
		$this->model  = $this->getModel();
		$this->state  = $this->get('State');
		$this->params = JUComment::getParams();

		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->total      = $this->get('Total');

		$this->user = JFactory::getUser();

		
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		$this->_prepareDocument();
		$this->_setDocument();

		$template = JUComment::getTemplate();
		$template->set( 'view', $this );

		$app = JFactory::getApplication();
		$layout = $app->input->get('layout', 'default');

		echo $template->fetch( 'usercomments/'.$layout.'.php' );

		
	}

	
	public function getSortFields()
	{
		return array(
			'cm.title'         => JText::_('COM_JUCOMMENT_FIELD_TITLE'),
			'cm.created'       => JText::_('COM_JUCOMMENT_FIELD_CREATED'),
			'cm.helpful_votes' => JText::_('COM_JUCOMMENT_FIELD_HELPFUL_VOTES'),
			'cm.total_votes'   => JText::_('COM_JUCOMMENT_FIELD_TOTAL_VOTES'),
			'cm.lft'            => JText::_('COM_JUCOMMENT_FIELD_ORDERING')
		);
	}

	
	public function getSortDirection()
	{
		return array(
			'ASC'  => JText::_('COM_JUCOMMENT_ASC'),
			'DESC' => JText::_('COM_JUCOMMENT_DESC')
		);
	}

	protected function _prepareDocument()
	{
		JUComment::import('helper', 'breadcrumb');
		JUCommentBreadcrumbHelper::breadcrumbWithDashboard($this->getName());

		$document = JFactory::getDocument();
		$document->setTitle(JText::_('COM_JUCOMMENT_SEO_TITLE_USER_COMMENTS'));

		if ($this->pagination->limitstart > 0)
		{
			$document->setTitle($document->getTitle() . ' - ' . JText::sprintf('COM_JUCOMMENT_SEO_PAGE_X_PER_Y', $this->pagination->pagesCurrent, $this->pagination->pagesTotal));
		}
	}

	protected function _setDocument()
	{
		JHtml::_('jquery.framework');
		JUComment::getHelper( 'Document' )->load('view.usercomments.default', 'css');
		JUComment::getHelper( 'Document' )->load('view.usercomments.full', 'css');
		JUComment::getHelper( 'Document' )->load('usercomments', 'js', 'assets');

		$document = JFactory::getDocument();
		$document->addStyleSheet(JUri::root(true) . "/components/com_jucomment/assets/fancybox/css/jquery.fancybox.css");
		$document->addStyleSheet(JUri::root(true) . "/components/com_jucomment/assets/fancybox/css/jquery.fancybox-thumbs.css");
		$document->addStyleSheet(JUri::root(true) . "/components/com_jucomment/assets/fancybox/css/jquery.fancybox-buttons.css");

		$document->addScript(JUri::root(true) . "/components/com_jucomment/assets/fancybox/js/jquery.mousewheel-3.0.6.pack.js");
		$document->addScript(JUri::root(true) . "/components/com_jucomment/assets/fancybox/js/jquery.fancybox.pack.js");
		$document->addScript(JUri::root(true) . "/components/com_jucomment/assets/fancybox/js/jquery.fancybox-thumbs.js");
		$document->addScript(JUri::root(true) . "/components/com_jucomment/assets/fancybox/js/jquery.fancybox-buttons.js");
		$document->addScript(JUri::root(true) . "/components/com_jucomment/assets/fancybox/js/jquery.fancybox-media.js");
	}
} 
