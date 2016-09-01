<?php
/**
* @version      4.0.1 20.12.2012
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

    defined('_JEXEC') or die('Restricted access');
    error_reporting(error_reporting() & ~E_NOTICE);    
    if (!file_exists(JPATH_SITE.'/components/com_jshopping/jshopping.php')){
        JError::raiseError(500,"Please install component \"joomshopping\"");
    } 

    require_once (dirname(__FILE__).'/helper.php');

    require_once (JPATH_SITE.'/components/com_jshopping/lib/factory.php'); 
    require_once (JPATH_SITE.'/components/com_jshopping/lib/jtableauto.php');
    require_once (JPATH_SITE.'/components/com_jshopping/tables/config.php'); 
    require_once (JPATH_SITE.'/components/com_jshopping/lib/functions.php');
    require_once (JPATH_SITE.'/components/com_jshopping/lib/multilangfield.php');
    
    JSFactory::loadCssFiles();
  
    $lang = JFactory::getLanguage();
    if(file_exists(JPATH_SITE.'/components/com_jshopping/lang/' . $lang->getTag() . '.php')) 
        require_once (JPATH_SITE.'/components/com_jshopping/lang/'.  $lang->getTag() . '.php'); 
    else 
        require_once (JPATH_SITE.'/components/com_jshopping/lang/en-GB.php'); 
    
    JTable::addIncludePath(JPATH_SITE.'/components/com_jshopping/tables'); 
    
    $field_sort = $params->get('sort', 'id');
    $ordering = $params->get('ordering', 'asc');
    $show_image = $params->get('show_image',0);
        
    $category_id = JRequest::getInt('category_id');
    $category = JTable::getInstance('category', 'jshop');        
    $category->load($category_id);
    $categories_id = $category->getTreeParentCategories();
    $categories_arr = jShopCategoriesHelper::getCatsArray($field_sort, $ordering, $category_id, $categories_id);
    
    $jshopConfig = JSFactory::getConfig();

    require(JModuleHelper::getLayoutPath('mod_jshopping_categories'));
?>