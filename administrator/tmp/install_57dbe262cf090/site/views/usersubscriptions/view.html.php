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

class JUCommentViewUsersubscriptions extends JViewLegacy
{
	public function display($tpl = null)
	{
		JUComment::import('helper', 'permission');
		$this->is_own_dashboard = JUCommentPermissionHelper::isOwnDashboard();
		if (JUCommentPermissionHelper::canViewDashboard() == false)
		{
			JError::raiseWarning(500, JText::_('COM_JUCOMMENT_YOU_ARE_NOT_AUTHORIZED_TO_ACCESS_THIS_PAGE'));

			return false;
		}

		$this->items = $this->get('Items');

		
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		$this->model = $this->getModel();

		$this->state     = $this->get('State');
		$this->pagination = $this->get('Pagination');

		$this->_setDocument();
		$this->_prepareDocument();

		$template = JUComment::getTemplate();
		$template->set( 'view', $this );

		echo $template->fetch( 'usersubscriptions/default.php' );

		
	}

	
	public function getSortFields()
	{
		return array(
			's.component' => JText::_('COM_JUCOMMENT_FIELD_COMPONENT'),
			's.cid' => JText::_('COM_JUCOMMENT_FIELD_CID'),
			's.created' => JText::_('COM_JUCOMMENT_FIELD_CREATED')
		);
	}

	
	public function getSortDirection()
	{
		return array(
			'ASC'  => JText::_('COM_JUCOMMENT_ASC'),
			'DESC' => JText::_('COM_JUCOMMENT_DESC')
		);
	}

	protected function _setDocument()
	{
		JUComment::getHelper( 'Document' )->load('view.usersubscriptions', 'css');
	}

	protected function _prepareDocument()
	{
		JUComment::import('helper', 'breadcrumb');
		JUCommentBreadcrumbHelper::breadcrumbWithDashboard($this->getName());

		$document = JFactory::getDocument();
		$document->setTitle(JText::_('COM_JUCOMMENT_SEO_TITLE_USER_SUBSCRIPTIONS'));

		if ($this->pagination->limitstart > 0)
		{
			$document->setTitle($document->getTitle() . ' - ' . JText::sprintf('COM_JUCOMMENT_SEO_PAGE_X_PER_Y', $this->pagination->pagesCurrent, $this->pagination->pagesTotal));
		}
	}
}
