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

class JUCommentBreadcrumbHelper
{
	
	protected static $cache = array();

	
	public static function findMenuItem($view = '')
	{
		if ($view)
		{
			$app       = JFactory::getApplication();
			$menus     = $app->getMenu('site');
			$component = JComponentHelper::getComponent('com_jucomment');
			$items     = $menus->getItems('component_id', $component->id);
			foreach ($items AS $item)
			{
				if ($view == $item->query['view'])
				{
					return $item;
				}
			}
		}

		return false;
	}

	
	public static function breadcrumbWithDashboard($view = null)
	{
		
		$app     = JFactory::getApplication();
		$pathway = $app->getPathway();
		$user    = JFactory::getUser();

		
		$pathwayArray    = array();

		$dashboardItem = self::findMenuItem('dashboard');
		$itemId        = '';
		if ($dashboardItem)
		{
			$name   = $dashboardItem->title;
			$itemId = '&Itemid=' . $dashboardItem->id;
		}
		else
		{
			$menuActive = $app->getMenu()->getActive();
			$name       = JText::_('COM_JUCOMMENT_USER_DASHBOARD');
			if (isset($menuActive->id))
			{
				$itemId = '&Itemid=' . $menuActive->id;
			}
		}

		$pathwayUserDashBoard       = new StdClass;
		$pathwayUserDashBoard->name = $name;
		$pathwayUserDashBoard->link = JRoute::_('index.php?option=com_jucomment&view=dashboard&id=' . $user->id . $itemId);;
		$pathwayArray[] = $pathwayUserDashBoard;

		JUComment::import('helper', 'permission');
		$isOwnDashboard = JUCommentPermissionHelper::isOwnDashboard();

		if (!$isOwnDashboard)
		{
			$userId                      = JUCommentFrontHelper::getDashboardUserId();
			$pathwayOtherDashBoard       = new StdClass;
			$pathwayOtherDashBoard->name = JFactory::getUser($userId);
			$pathwayOtherDashBoard->link = JRoute::_('index.php?option=com_jucomment&view=dashboard&id=' . $userId . $itemId);;
		}

		
		if ($view)
		{
			$viewItemId = '';

			$viewName = ucfirst($view);
			if (isset($menuActive->id))
			{
				$viewItemId = $menuActive->id;
			}

			if($view == 'modpermission'){
				$pathwayView       = new stdClass;
				$pathwayView->name = 'Modpermissions';
				$pathwayView->link = JRoute::_('index.php?option=com_jucomment&view=modpermissions' . $viewItemId);
				$pathwayArray[]    = $pathwayView;
			}

			$pathwayView       = new stdClass;
			$pathwayView->name = $viewName;
			$pathwayView->link = JRoute::_('index.php?option=com_jucomment&view=' . $view . $viewItemId);
			$pathwayArray[]    = $pathwayView;
		}

		$pathway->setPathway($pathwayArray);
	}
}