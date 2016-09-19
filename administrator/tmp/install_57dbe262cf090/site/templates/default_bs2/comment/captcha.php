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

defined('_JEXEC') or die('Restricted access');

$response_name = isset($options['response_name']) ? $options['response_name'] : 'captcha_response';
$id_name = isset($options['id_name']) ? $options['id_name'] : 'captcha_id';
$input_id = isset($options['id']) ? 'id="'.$options['id'].'"' : '';
$input_class = isset($options['class']) ? $options['class'] : '';
$validate_data = isset($options['validate_data']) ? $options['validate_data'] : '';
?>

<div class="captcha-box">
	<div class="clearfix">
		<img class="captcha-image" src="<?php echo $url;?>" alt="Captcha" />
        <div class="input-append">
			<input type="text" <?php echo $input_id; ?> name="<?php echo $response_name;?>" class="captcha-response <?php echo $input_class; ?> span2" <?php echo $validate_data; ?> />
			<span title="<?php echo JText::_('COM_JUCOMMENT_RELOAD_CAPTCHA'); ?>" class="btn jucomment-captcha-reload"><i class="fa fa-refresh"></i></span>
		</div>
		<input type="hidden" name="<?php echo $id_name;?>" class="captcha-id" value="<?php echo $this->escape($id);?>" />
	</div>
</div>