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

$placeholder = $this->params->get("placeholder", "") ? "placeholder=\"" . htmlspecialchars($this->params->get("placeholder", ""), ENT_COMPAT, 'UTF-8') . "\"" : "";
$width       = $this->params->get('width', 400);
$height      = $this->params->get('height', 300);
$cols        = $this->params->get('cols', 50);
$rows        = $this->params->get('rows', 5);
$html        = '<textarea id="' . $this->getId() . '" name="' . $this->getName() . '" class="' . $class . '"
							style="width: ' . $width . 'px; height: ' . $height . 'px;"
						    cols="' . $cols . '" rows="' . $rows . '" ' . $placeholder . '
						    ' . $this->getValidateData() . '>' . $value . '</textarea>';

echo $html;
?>