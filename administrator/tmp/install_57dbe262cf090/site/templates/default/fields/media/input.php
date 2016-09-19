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
<!-- New media template -->
<script id="newfile-template-<?php echo $this->getId(); ?>" type="text/x-handlebars-template">
	<li id="{{file.id}}" class="pending">
		<span class="file-remove-btn fa fa-trash" data-icon-undo="fa-undo" data-icon-trash="fa-trash" title="<?php echo JText::_('COM_JUCOMMENT_REMOVE'); ?>"></span>
		<span class="file-publish-btn fa fa-eye" data-icon-publish="fa-eye" data-icon-unpublish="fa-eye-slash" title="<?php echo JText::_('COM_JUCOMMENT_PUBLISH'); ?>"></span>
		<div class="file-image">
			<img src="" alt="<?php echo JText::_('COM_JUCOMMENT_VIDEO_THUMBNAIL'); ?>"/>
			<div class="image-action">
				<span class="image-browser fa fa-plus" title="<?php echo JText::_('COM_JUCOMMENT_UPLOAD'); ?>"></span>
				<span class="image-remove fa fa-trash" data-icon-undo="fa-undo" data-icon-trash="fa-trash" title="<?php echo JText::_('COM_JUCOMMENT_REMOVE'); ?>"></span>
			</div>
		</div>
		<div class="file-data">
			<input type="text" class="file-title" name="<?php echo $this->getName() ?>[{{key}}][title]" value="{{file.name}}" placeholder="<?php echo JText::_('COM_JUCOMMENT_TITLE'); ?>"/>
			<div class="file-info-wrap">
				<div class="file-info file-size-wrap"><label class="info-label"><?php echo JText::_('COM_JUCOMMENT_SIZE'); ?>:</label><span class="info-value">{{file.sizeFormatted}}</span></div>
				<div class="file-info file-type-wrap"><label class="info-label"><?php echo JText::_('COM_JUCOMMENT_TYPE'); ?>:</label><span class="info-value">{{file.type}}</span></div>
				<div class="file-info file-state-wrap"><label class="info-label"><?php echo JText::_('COM_JUCOMMENT_STATE'); ?>:</label><span class="info-value"><?php echo JText::_('COM_JUCOMMENT_PENDING'); ?></span></div>
			</div>
			<input type="hidden" class="file-id" name="<?php echo $this->getName() ?>[{{key}}][id]" value="{{file.id}}" />
			<input type="hidden" class="file-name" name="<?php echo $this->getName() ?>[{{key}}][name]" value="{{file.name}}" />
			<input type="hidden" class="file-size" value="{{file.size}}" name="<?php echo $this->getName(); ?>[{{key}}][size]" />
			<input type="hidden" class="file-type" value="{{file.type}}" name="<?php echo $this->getName(); ?>[{{key}}][type]" />
			<input type="hidden" class="file-target" value="" name="<?php echo $this->getName(); ?>[{{key}}][target]" />
			<input type="hidden" class="file-image-newname" value="" name="<?php echo $this->getName(); ?>[{{key}}][imagenewname]" />
			<input type="hidden" class="file-image-target" value="" name="<?php echo $this->getName(); ?>[{{key}}][imagetarget]" />
			<input type="hidden" class="file-image-remove" value="0" name="<?php echo $this->getName(); ?>[{{key}}][imageremove]" />
			<input type="hidden" class="file-published" value="1" name="<?php echo $this->getName(); ?>[{{key}}][published]" />
			<input type="hidden" class="file-remove" value="0" name="<?php echo $this->getName(); ?>[{{key}}][remove]" />
			<input type="hidden" class="file-isnew" value="1" name="<?php echo $this->getName(); ?>[{{key}}][is_new]" />
		</li>
	</script>
<?php
$html = "<div id=\"" . $this->getId() . "_wrap\" " . $this->getAttribute(null, null, "input") . " class=\"field-media-input\">";
$html .= "<ul class=\"file-list\">";
if ($files)
{
	$imageUrl = JUri::root(true) . "/media/com_jucomment/field_attachments/media/" . $this->id . "_" . $this->comment_id . "/";
	$imageTmp = JUri::root(true) . "/media/com_jucomment/tmp_img/";
	foreach ($files AS $key => $file)
	{
		if (isset($file['target']) && !$file['target'])
		{
			continue;
		}

		$isNewFile = isset($file['is_new']) ? true : false;

		$class = array();
		// Fail to save listing
		if ($isNewFile)
		{
			$state   = JText::_('COM_JUCOMMENT_UPLOADED');
			$class[] = 'uploaded';
		}
		// Save success
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

		$iconPublished = 'fa-eye';
		if ($file['published'] == 0)
		{
			$iconPublished = 'fa-eye-slash';
			$class[]       = 'unpublish';
		}

		$iconImageRemove = 'fa-trash';
		if (isset($file['imageremove']) && $file['imageremove'] == 1)
		{
			$iconImageRemove = 'fa-undo';
			$class[]         = 'imageremove';
		}

		$class = implode(' ', $class);

		$html .= '<li id="' . $file['id'] . '" class="' . $class . '">';
		$html .= '<span class="file-remove-btn fa ' . $iconRemove . '" data-icon-undo="fa-undo" data-icon-trash="fa-trash" title="' . JText::_('COM_JUCOMMENT_REMOVE') . '"></span>';
		$html .= '<span class="file-publish-btn fa ' . $iconPublished . '" data-icon-publish="fa-eye" data-icon-unpublish="fa-eye-slash" title="' . JText::_('COM_JUCOMMENT_PUBLISH') . '"></span>';
		$html .= '<div class="file-image">';
		if (isset($file['imagetarget']) && $file['imagetarget'])
		{
			$html .= '<img src="' . $imageTmp . $file['imagetarget'] . '" alt="' . JText::_('COM_JUCOMMENT_VIDEO_THUMBNAIL') . '" />';
		}
		else
		{
			$imageSrc = $file['imagename'] ? $imageUrl . $file['imagename'] : '';
			$html .= '<img src="' . $imageSrc . '" alt="' . JText::_('COM_JUCOMMENT_VIDEO_THUMBNAIL') . '" />';
		}
		$html .= '<div class="image-action">';
		$html .= '<span class="image-browser fa fa-plus" title="' . JText::_('COM_JUCOMMENT_UPLOAD') . '"></span>';
		$html .= '<span class="image-remove fa ' . $iconImageRemove . '" data-icon-undo="fa-undo" data-icon-trash="fa-trash" title="' . JText::_('COM_JUCOMMENT_REMOVE') . '"></span>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '<div class="file-data">';
		$html .= '<input type="text" class="file-title" name="' . $this->getName() . '[' . $key . '][title]" value="' . $file['title'] . '" placeholder="' . JText::_('COM_JUCOMMENT_TITLE') . '" />';
		$html .= '<div class="file-info-wrap">';
		$html .= '<div class="file-info file-size-wrap"><label class="info-label">' . JText::_('COM_JUCOMMENT_SIZE') . ':</label><span class="info-value">' . $this->formatBytes($file['size']) . '</span></div>';
		$html .= '<div class="file-info file-type-wrap"><label class="info-label">' . JText::_('COM_JUCOMMENT_TYPE') . ':</label><span class="info-value">' . $file['type'] . '</span></div>';
		$html .= '<div class="file-info file-state-wrap"><label class="info-label">' . JText::_('COM_JUCOMMENT_STATE') . ':</label><span class="info-value">' . $state . '</span></div>';
		$html .= '</div>';
		$html .= '</div>';
		if ($isNewFile)
		{
			$html .= '<input type="hidden" class="file-name" name="' . $this->getName() . '[' . $key . '][name]" value="' . $file['name'] . '" />';
			$html .= '<input type="hidden" class="file-size" name="' . $this->getName() . '[' . $key . '][size]" value="' . $file['size'] . '" />';
			$html .= '<input type="hidden" class="file-type" name="' . $this->getName() . '[' . $key . '][type]" value="' . $file['type'] . '" />';
			$html .= '<input type="hidden" class="file-target" name="' . $this->getName() . '[' . $key . '][target]" value="' . $file['target'] . '" />';
			$html .= '<input type="hidden" class="file-isnew" name="'.$this->getName().'['.$key.'][is_new]" value="1" />';
		}
		$html .= '<input type="hidden" class="file-published" name="' . $this->getName() . '[' . $key . '][published]" value="' . $file['published'] . '" />';
		$html .= '<input type="hidden" class="file-image-newname" name="' . $this->getName() . '[' . $key . '][imagenewname]" value="' . (isset($file['imagenewname']) ? $file['imagenewname'] : '') . '" />';
		$html .= '<input type="hidden" class="file-image-target" name="' . $this->getName() . '[' . $key . '][imagetarget]" value="' . (isset($file['imagetarget']) ? $file['imagetarget'] : '') . '" />';
		$html .= '<input type="hidden" class="file-image-remove" name="' . $this->getName() . '[' . $key . '][imageremove]" value="' . (isset($file['imageremove']) ? $file['imageremove'] : 0) . '" />';
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
$html .= "<span class=\"pickimagefiles btn btn-default btn-sm hide\"><i class=\"fa fa-plus\"></i> " . JText::_('COM_JUCOMMENT_SELECT_FILE') . "</span>";
$html .= "</div>";

$html .= "<input type=\"hidden\" " . $this->getValidateData() . " id=\"" . $this->getId() . "\" name=\"" . $this->getId() . "\" value=\"0\" />";

$html .= "</div>";

echo $html;

?>