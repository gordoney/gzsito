<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_falang
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
JHtml::_('stylesheet', 'mod_falang/template.css', array(), true);

//add alternate tag
$doc = JFactory::getDocument();
$default_lang = JComponentHelper::getParams('com_languages')->get('site', 'en-GB');
$current_lang = JFactory::getLanguage()->getTag();

$sef = JFactory::getApplication()->getCfg('sef');

$remove_default_prefix = 0;
$filter_plugin = JPluginHelper::getPlugin('system', 'languagefilter');
if (!empty($filter_plugin)) {
    $filter_plugin_params = new JRegistry($filter_plugin->params);
    $remove_default_prefix = $filter_plugin_params->get('remove_default_prefix','0');
}

//add an alterante by language
// hack to fix the fact that $language->link already contains the rootpath of the joomla site
// ex falang3/en for http//localhost/falang3

$uri_base = substr(JURI::base(false), 0,strlen(JURI::base(false))-strlen(JURI::base(true)));
foreach($list as $language) {
    if ($sef == '1') {
        $link = $uri_base . substr($language->link, 1);
        if (($language->lang_code == $default_lang) && $remove_default_prefix == '1') {
            $link = preg_replace('|/' . $language->sef . '/|', '/', $link, 1);
            //remove last slash for default language
            $link = rtrim($link, "/");
            $doc->addCustomTag('<link rel="alternate" href="' . $link . '" hreflang="' . $language->sef . '" />');
        } else {
            $doc->addCustomTag('<link rel="alternate" href="' . $link . '" hreflang="' . $language->sef . '" />');
        }

    } else {
        $doc->addCustomTag('<link rel="alternate" href="' . $language->link . '" hreflang="' . $language->sef . '" />');
    }
}
?>

<!-- Support of language domain from yireo  -->
<?php
$yireo_plugin = JPluginHelper::getPlugin('system', 'languagedomains');
if (!empty($yireo_plugin)) {
    foreach($list as $language):
        if (empty($language->link) || in_array($language->link, array('/', 'index.php'))) $language->link = '/?lang='.$language->sef;
    endforeach;
}
?>


<div class="mod-languages<?php echo $moduleclass_sfx ?> <?php echo ($params->get('dropdown', 1) && $params->get('advanced_dropdown', 1)) ? ' advanced-dropdown' : '';?>">
<?php if ($headerText) : ?>
	<div class="pretext"><p><?php echo $headerText; ?></p></div>
<?php endif; ?>

<?php if ($params->get('dropdown',1)) : ?>
    <?php require JModuleHelper::getLayoutPath('mod_falang', $params->get('layout', 'default') . '_dropdown'); ?>
<?php else : ?>
    <?php require JModuleHelper::getLayoutPath('mod_falang', $params->get('layout', 'default') . '_list'); ?>
<?php endif; ?>

<?php if ($footerText) : ?>
	<div class="posttext"><p><?php echo $footerText; ?></p></div>
<?php endif; ?>
</div>
