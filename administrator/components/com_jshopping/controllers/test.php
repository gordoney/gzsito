<?php
/**
* @version      4.14.0 24.07.2013
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined('_JEXEC') or die();

class JshoppingControllerTest extends JshoppingControllerBaseadmin{
    
    protected $nameModel = 'attribut';
    protected $urlEditParamId = 'attr_id';
    
    function __construct($config = array()){
        parent::__construct($config);
        checkAccessController("attributes");
        addSubmenu("other");
    }

    function display($cachable = false, $urlparams = false){
        $mainframe = JFactory::getApplication();
        $context = "jshoping.list.admin.attributes";
        $filter_order = $mainframe->getUserStateFromRequest($context.'filter_order', 'filter_order', "A.attr_ordering", 'cmd');
        $filter_order_Dir = $mainframe->getUserStateFromRequest($context.'filter_order_Dir', 'filter_order_Dir', "asc", 'cmd');
		
		include JPATH_COMPONENT_SITE."/addons/addon_core.php";
		AddonCore::getView();
    }
}