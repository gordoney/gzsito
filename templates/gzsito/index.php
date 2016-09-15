<?php 
/**
 * @package		Joomla.Site
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die; 

/* объявляем кастомные скрипты */
$document = JFactory::getDocument();
$document->addScript('http://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js');
$document->addScript($this->baseurl.'/templates/'.$this->template.'/js/bootstrap.min.js');
$document->addScript($this->baseurl.'/templates/'.$this->template.'/js/custom.js');

/* объявляем кастомные стили */
$document->addStyleSheet('https://fonts.googleapis.com/css?family=Open+Sans:400,700,300&subset=cyrillic');
$document->addStyleSheet($this->baseurl.'/templates/'.$this->template.'/css/reset.css');
$document->addStyleSheet($this->baseurl.'/templates/'.$this->template.'/css/bootstrap.min.css');
//$document->addStyleSheet($this->baseurl.'/templates/'.$this->template.'/css/style.css');
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>" >
<head>
	<jdoc:include type="head" />
	<link rel="stylesheet/less" type="text/css" href="/templates/gzsito/css/style.less" />
    <script src="//cdnjs.cloudflare.com/ajax/libs/less.js/2.6.1/less.min.js"></script>

	<link rel="icon" type="image/vnd.microsoft.icon" href="/templates/<?php echo $this->template; ?>/favicon.ico">
	<meta name="viewport" content="width=1280px">
</head>
<?php /* Получаем класс страницы */
$app = JFactory::getApplication();
$menu = $app->getMenu();
$activeMenu = $menu->getActive();
$pageClass = $activeMenu->params['pageclass_sfx']; ?>
<body class="<?php echo $activeMenu->params['pageclass_sfx']; ?>">
	
	<div id="header" class="header">
		<div class="container clearfix">
			<div class="header-block header-left">
				<jdoc:include type="modules" name="header-left" style="none" />
			</div>
			<div class="header-block header-center">
				<div class="header-center-top clearfix">
					<jdoc:include type="modules" name="header-center-top" style="none" />
				</div>
				<div class="header-center-bottom">
					<jdoc:include type="modules" name="header-center-bottom" style="none" />
				</div>
			</div>
			<div class="header-block header-right">
				<jdoc:include type="modules" name="header-right" style="none" />
			</div>			
		</div>
	</div>

	<?php if ($this->countModules('top-menu')) { ?>
		<div id="top-menu" class="top-menu">
			<div class="container">
				<jdoc:include type="modules" name="top-menu" style="xhtml" />
			</div>
		</div>
	<?php } ?>

	<?php if ($this->countModules('before-content-without-container')) { ?>
		<div id="before-content-without-container">
			<jdoc:include type="modules" name="before-content-without-container" style="xhtml" />
		</div>	
	<?php } ?>


		<?php if ($this->countModules('before-content')) { ?>
			<div id="before-content">
				<div class="container">
					<jdoc:include type="modules" name="before-content" style="xhtml" />
				</div>
			</div>
		<?php } ?>

		<?php if ($this->countModules('content')) { ?>
			<div id="main-content" class="main-content">
				<div class="container">
					<jdoc:include type="modules" name="content" style="xhtml" />
				</div>
			</div>
		<?php } else { ?>
			<div id="main-content" class="main-content">
				<div class="container clearfix">
					<div class="left-block-content">
						<jdoc:include type="modules" name="left-block-content" style="xhtml" />
					</div>
					<div class="right-block-content">
						<!--<jdoc:include type="message" />-->
						<jdoc:include type="component" />
					</div>
				</div>
			</div>
		<?php } ?>

		<?php if ($this->countModules('after-content-style-none')) { ?>
			<div id="after-content-style-none" class="after-content-style-none">
				<div class="container clearfix">
					<div class="name"><?php echo JText :: _('MOD_MAIN_NEWS_NAME'); ?></div>
					<div class="all-news"><jdoc:include type="modules" name="after-content-all" style="none" /></div>
					<jdoc:include type="modules" name="after-content-style-none" style="none" />
				</div>
			</div>
		<?php } ?>

		<?php if ($this->countModules('after-content')) { ?>
			<div id="after-content">
				<div class="container">
					<jdoc:include type="modules" name="after-content" style="xhtml" />
				</div>
			</div>
		<?php } ?>


	<?php if ($this->countModules('after-content-without-container')) { ?>
		<div id="after-content-without-container">
			<jdoc:include type="modules" name="after-content-without-container" style="xhtml" />
		</div>		
	<?php } ?>

    <div id="footer" class="footer">
        <div class="container clearfix">
			<jdoc:include type="modules" name="footer" style="none" />
			<div class="medialine custom">
				<?php if(JURI::current() !== JURI::base()){?>
					<span>Разработка сайта - </span><a target="_blank" rel="nofollow" href="http://www.medialine.by/">Media Line</a>
				<?php } else { ?>
					<span>Разработка сайта - </span><a target="_blank" href="http://www.medialine.by/">Media Line</a>
				<?php } ?>
			</div>
		</div>	
	</div>	

</body>
</html>