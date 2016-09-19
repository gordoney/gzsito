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

defined('JPATH_PLATFORM') or die;


jimport('joomla.application.component.modellist');



JUComment::import('class', 'jucmpagination');

class JUCMModelList extends JModelList
{
	
	public function getPagination()
	{
		
		$store = $this->getStoreId('getPagination');

		
		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		
		jimport('joomla.html.pagination');
		$limit = (int) $this->getState('list.limit') - (int) $this->getState('list.links');
		$page  = new JUCMPagination($this->getTotal(), $this->getStart(), $limit);

		
		$this->cache[$store] = $page;

		return $this->cache[$store];
	}

	
	protected function _getListCount($query)
	{
		
		if ($query instanceof JDatabaseQuery
			&& $query->type == 'select'
			&& ($query->group !== null || $query->having !== null)
		)
		{
			$query = clone $query;
			$query->clear('select')->clear('order')->select('1');
		}

		return parent::_getListCount($query);
	}
}
