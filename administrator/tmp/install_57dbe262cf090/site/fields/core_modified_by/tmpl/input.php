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

$html = '<input id="' . $this->getId() . '_name" ' . $this->getAttribute(null, null, "input") . ' value="' . $user->name . '"/>';
$html .= '<input type="hidden" id="' . $this->getId() . '" name="' . $this->getName() . '" value="' . $user->id . '"/>';

echo $html;

?>