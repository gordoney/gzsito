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

JUComment::trigger('onBeforeJUCommentBox', array('component' => $component, 'cid' => $cid, 'system' => &$system, 'comments' => &$comments));
$this->set('captcha_namespace_value', md5(time()));
?>

<div id="section-jucomment" class="jucm-comments clearfix template-<?php echo $this->getName(); ?>">
	<?php
	if ($componentHelper->getCommentAnchorId())
	{ ?>
		<a id="<?php echo $componentHelper->getCommentAnchorId(); ?>"></a>
	<?php
	} ?>

	<?php
	if (!$system->permission->allow('read') && !$system->permission->allow('create'))
	{
		?>
			<div class="jucomment-not-allowed">
				<?php echo JText::_('COM_JUCOMMENT_COMMENT_FORM_NOT_ALLOWED'); ?>
			</div>
		<?php
		if ($system->my->guest && $system->params->get('enable_login_form', 0))
		{
			//echo $this->fetch( 'comment/form/login.php' );
		} ?>
	<?php
	}
	else
	{
		//form comment editor
		echo $this->fetch('comment/form.php');
		if($commentCount)
		{
			echo $this->fetch('comment/list.php');
		}
	}
	?>
</div><!--/section-jucomment-->