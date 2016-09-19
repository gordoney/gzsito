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

class JUCommentViewComment extends JViewLegacy
{
	public function display($tpl = null)
	{
		
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode('<br />', $errors));

			return false;
		}

		$this->_prepareDocument();

		
		$this->_setDocument();

		$app = JFactory::getApplication();
		$layout = $app->input->get('layout','');

		$template = JUComment::getTemplate();
		$template->set( 'view', $this );

		$commentId = $app->input->getInt('id', 0);
		$template->set( 'row', JUComment::getComment($commentId));

		if($layout == 'preview'){
			JUComment::getHelper( 'Document' )->load('common', 'css', 'assets');
			echo $template->fetch( 'comment/preview.php' );
		}else{
			$template->set( 'task', 'comment.save');
			$template->set( 'token', true);
			$return = $app->getUserState('return', '');
			if($return){
				$template->set( 'return', $return);
			}else{
				$template->set( 'return', base64_encode('index.php?option=com_jucomment&view=modcomments'));
			}
			echo $template->fetch( 'comment/edit.php' );
		}

		
	}

	protected function _prepareDocument()
	{
		
		
	}

	protected function _setDocument()
	{
        $document = JFactory::getDocument();
        $document->addScript(JUri::root(true).'/components/com_jucomment/assets/jqueryvalidation/jquery.validate.min.js');
    }

} 