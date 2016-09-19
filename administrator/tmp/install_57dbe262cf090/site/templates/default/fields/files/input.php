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
?>
<!-- New video template -->
<script id="newfile-template-<?php echo $this->getId(); ?>" type="text/x-handlebars-template">
	<li id="{{file.id}}" class="pending">
		<span class="file-remove-btn fa fa-trash" data-icon-undo="fa-undo" data-icon-trash="fa-trash" title="<?php echo JText::_('COM_JUCOMMENT_REMOVE'); ?>"></span>
		<div class="file-title-name">
			<input type="text" class="file-name" name="<?php echo $this->getName() ?>[{{key}}][name]" value="{{file.name}}" placeholder="<?php echo JText::_('COM_JUCOMMENT_FILE_NAME'); ?>" />
			<input type="text" class="file-title" name="<?php echo $this->getName() ?>[{{key}}][title]" value="" placeholder="<?php echo JText::_('COM_JUCOMMENT_TITLE'); ?>"/>
		</div>
		<div class="file-info-wrap">
			<div class="file-info file-size-wrap"><label class="info-label"><?php echo JText::_('COM_JUCOMMENT_SIZE'); ?>:</label><span class="info-value">{{file.sizeFormatted}}</span></div>
			<div class="file-info file-type-wrap"><label class="info-label"><?php echo JText::_('COM_JUCOMMENT_TYPE'); ?>:</label><span class="info-value">{{file.type}}</span></div>
			<!--<div class="file-info file-counter-wrap"><label
					class="info-label"><?php /*echo JText::_('COM_JUCOMMENT_DOWNLOADS'); */?>:</label>
				<span class="info-value"><input type="text"
				                                name="<?php /*echo $this->getName() */?>[{{key}}][downloads]" value="0" /></span>
			</div>-->
			<div class="file-info file-state-wrap"><label class="info-label"><?php echo JText::_('COM_JUCOMMENT_STATE'); ?>:</label><span class="info-value"><?php echo JText::_('COM_JUCOMMENT_PENDING'); ?></span></div>
		</div>

			<input type="hidden" class="file-id" name="<?php echo $this->getName() ?>[{{key}}][id]" value="{{file.id}}" />
			<input type="hidden" class="file-size" value="{{file.size}}" name="<?php echo $this->getName(); ?>[{{key}}][size]" />
			<input type="hidden" class="file-type" value="{{file.type}}" name="<?php echo $this->getName(); ?>[{{key}}][type]" />
			<input type="hidden" class="file-target" value="" name="<?php echo $this->getName(); ?>[{{key}}][target]" />
			<input type="hidden" class="file-remove" value="0" name="<?php echo $this->getName(); ?>[{{key}}][remove]" />
			<input type="hidden" class="file-isnew" value="1" name="<?php echo $this->getName(); ?>[{{key}}][is_new]" />
		</li>
	</script>

<?php
$html = "<div id=\"" . $this->getId() . "_wrap\" " . $this->getAttribute(null, null, "input") . " class=\"field-files-input\">";
$html .= "<ul class=\"file-list\">";
if ($files)
{
	foreach ($files AS $key => $file)
	{
		if (isset($file['target']) && !$file['target'])
		{
			continue;
		}

		$isNewFile = isset($file['is_new']) ? true : false;

		$class = array();
		if ($isNewFile)
		{
			$state   = JText::_('COM_JUCOMMENT_UPLOADED');
			$class[] = 'uploaded';
		}
		else
		{
			$state   = JText::_('COM_JUCOMMENT_COMPLETED');
			$class[] = 'completed';
		}

		$iconRemove = 'fa-trash';
		if (isset($file['remove']) && $file['remove'] == 1)
		{
			$iconRemove = 'fa-undo';
			$class[]    = 'remove';
		}

		$class = implode(' ', $class);

		$html .= '<li class="' . $class . '">';
		$html .= '<span class="file-remove-btn fa ' . $iconRemove . '" data-icon-undo="fa-undo" data-icon-trash="fa-trash" title="' . JText::_('COM_JUCOMMENT_REMOVE') . '"></span>';
		$html .= '<div class="file-title-name">';
		$html .= '<input type="text" class="file-name" name="' . $this->getName() . '[' . $key . '][name]" value="' . $file['name'] . '" placeholder="' . JText::_('COM_JUCOMMENT_FILE_NAME') . '" />';
		$html .= '<input type="text" class="file-title" name="' . $this->getName() . '[' . $key . '][title]" value="' . $file['title'] . '" placeholder="' . JText::_('COM_JUCOMMENT_TITLE') . '" />';
		$html .= '</div>';
		$html .= '<div class="file-info-wrap">';
		$html .= '<div class="file-info file-size-wrap"><label class="info-label">' . JText::_('COM_JUCOMMENT_SIZE') . ':</label><span class="info-value">' . $this->formatBytes($file['size']) . '</span></div>';
		$html .= '<div class="file-info file-type-wrap"><label class="info-label">' . JText::_('COM_JUCOMMENT_TYPE') . ':</label><span class="info-value">' . $file['type'] . '</span></div>';
		$html .= '<div class="file-info file-counter-wrap"><label class="info-label">' . JText::_('COM_JUCOMMENT_DOWNLOADS') . ':</label>';
		$html .= '<span class="info-value"><input type="text" name="' . $this->getName() . '[' . $key . '][downloads]" value="' . $file['downloads'] . '" /></span>';
		$html .= '</div>';
		$html .= '<div class="file-info file-state-wrap"><label class="info-label">' . JText::_('COM_JUCOMMENT_STATE') . ':</label><span class="info-value">' . $state . '</span></div>';
		$html .= '</div>';
		if($isNewFile){
			$html .= '<input type="hidden" class="file-name" name="'.$this->getName().'['.$key.'][name]" value="'.$file['name'].'" />';
			$html .= '<input type="hidden" class="file-size" name="'.$this->getName().'['.$key.'][size]" value="'.$file['size'].'" />';
			$html .= '<input type="hidden" class="file-type" name="'.$this->getName().'['.$key.'][type]" value="'.$file['type'].'" />';
			$html .= '<input type="hidden" class="file-target" name="'.$this->getName().'['.$key.'][target]" value="'.$file['target'].'" />';
			$html .= '<input type="hidden" class="file-isnew" name="'.$this->getName().'['.$key.'][is_new]" value="1" />';
		}
		$html .= '<input type="hidden" class="file-remove" name="' . $this->getName() . '[' . $key . '][remove]" value="' . (isset($file['remove']) ? $file['remove'] : 0) . '" />';
		$html .= '<input type="hidden" class="file-id" name="' . $this->getName() . '[' . $key . '][id]" value="' . $file['id'] . '" />';
		$html .= "</li>";
	}
}
$html .= "</ul>";

$html .= "<div class=\"file-warning-area-wrap hide\">";
$html .= "<span class=\"toggle-file-warning fa fa-expand\" data-icon-expand=\"fa-expand\" data-icon-contract=\"fa-compress\"></span>";
$html .= "<div class=\"file-warning-area\">";
$html .= "</div>";
$html .= "</div>";

$html .= "<div class=\"file-upload-container\">";
$html .= "<span class=\"pickfiles btn btn-default btn-sm\"><i class=\"fa fa-plus\"></i> " . JText::_('COM_JUCOMMENT_SELECT_FILE') . "</span>";
if(!$this->params->get('auto_upload', 0)) {
    $html .= " <span class=\"uploadfiles btn btn-default btn-sm disabled\"><i class=\"fa fa-upload\"></i> " . JText::_('COM_JUCOMMENT_UPLOAD') . "</span>";
}
$html .= "</div>";

$html .= "<input type=\"hidden\" " . $this->getValidateData() . " id=\"" . $this->getId() . "\" name=\"" . $this->getId() . "\" value=\"0\" />";

$html .= "</div>";

echo $html;

?>