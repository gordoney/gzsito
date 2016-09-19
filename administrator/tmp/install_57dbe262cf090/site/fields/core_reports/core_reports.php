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

class JUCommentFieldCore_reports extends JUCommentFieldText
{
	protected $field_name = 'reports';
	protected $fieldvalue_column = "reports";

	protected function getValue()
	{
		$app = JFactory::getApplication();
		
		if ($app->isSite() && isset($this->comment->total_reports) && !is_null($this->comment->total_reports))
		{
			$value = $this->comment->total_reports;

		}
		else
		{
			$db    = JFactory::getDbo();
			$query = "SELECT count(*) FROM #__jucomment_reports WHERE (item_id = " . $this->comment_id . " AND type = 'comment')";
			$db->setQuery($query);
			$result = $db->loadResult();
			$value  = $result;
		}

		return $value;
	}

	
	public function storeValue($value)
	{
		return true;
	}

	public function getPredefinedValuesHtml()
	{
		return '<span class="readonly">' . JText::_('COM_JUCOMMENT_NOT_SET') . '</span>';
	}

	public function getBackendOutput()
	{
		$value = $this->value;

		return '<span class="reports"><a href="index.php?option=com_jucomment&view=reports&comment_id=' . $this->comment_id . '" title="' . JText::_('COM_JUCOMMENT_VIEW_REPORTS') . '">' . JText::plural('COM_JUCOMMENT_N_REPORTS', $value) . '</a></span>';
	}

	public function onSimpleSearch(&$query, &$where, $search, $forceModifyQuery = false)
	{
		if ($search !== "")
		{
			$query->where("(SELECT COUNT(*) FROM #__jucomment_reports AS r WHERE r.item_id = comment.id AND r.type='comment') = " . (int) $search);
		}
	}

	public function onSearch(&$query, &$where, $search, $forceModifyQuery = false)
	{
		if ($this->params->get("is_numeric", 0) && is_array($search) && !empty($search))
		{
			if ($search['from'] !== "" && $search['to'] !== "")
			{
				$from = (int) $search['from'];
				$to   = (int) $search['to'];
				if ($from > $to)
				{
					$this->swap($from, $to);
				}

				$where[] = "(SELECT COUNT(*) FROM #__jucomment_reports AS r WHERE r.item_id = comment.id AND r.type='comment') BETWEEN $from AND $to";
			}
			elseif ($search['from'] !== "")
			{
				$from = (int) $search['from'];

				$where[] = "(SELECT COUNT(*) FROM #__jucomment_reports AS r WHERE r.item_id = comment.id AND r.type='comment') >= $from";
			}
			elseif ($search['to'] !== "")
			{
				$to = (int) $search['to'];

				$where[] = "(SELECT COUNT(*) FROM #__jucomment_reports AS r WHERE r.item_id = comment.id AND r.type='comment') <= $to";
			}
		}
		else
		{
			$this->onSimpleSearch($query, $where, $search, $forceModifyQuery);
		}
	}

	public function orderingPriority(&$query = null)
	{
		$this->appendQuery($query, 'select', '(SELECT COUNT(*) FROM #__jucomment_reports AS r WHERE r.comment_id = comment.id) AS reports');

		return array('ordering' => 'reports', 'direction' => $this->priority_direction);
	}
}

?>