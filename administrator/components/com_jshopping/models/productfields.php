<?php
/**
* @version      4.14.0 10.05.2016
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined('_JEXEC') or die();

class JshoppingModelProductFields extends JshoppingModelBaseadmin{
    
    protected $nameTable = 'productField';
	
	function getList($groupordering = 0, $order = null, $orderDir = null, $filter=array()){
        $db = JFactory::getDBO();
        $lang = JSFactory::getLang();
        $ordering = "F.ordering";
        if ($order && $orderDir){
            $ordering = $order." ".$orderDir;
        }
        if ($groupordering){
            $ordering = "G.ordering, ".$ordering;
        }        
        $where = '';
		$_where = array();		
		if ($filter['group']){
            $_where[] = " F.group = '".$db->escape($filter['group'])."' ";    
        }		
		if ($filter['text_search']){
            $text_search = $filter['text_search'];
            $word = addcslashes($db->escape($text_search), "_%");
            $_where[]=  "(LOWER(F.`".$lang->get('name')."`) LIKE '%" . $word . "%' OR LOWER(F.`".$lang->get('description')."`) LIKE '%" . $word . "%' OR F.id LIKE '%" . $word . "%')";            
        }		
		if (count($_where)>0){
			$where = " WHERE ".implode(" AND ",$_where);
		}
        $query = "SELECT F.id, F.`".$lang->get("name")."` as name, F.`".$lang->get("description")."` as description, F.allcats, F.type, F.cats, F.ordering, F.`group`, G.`".$lang->get("name")."` as groupname, multilist FROM `#__jshopping_products_extra_fields` as F left join `#__jshopping_products_extra_field_groups` as G on G.id=F.group ".$where." order by ".$ordering;
        extract(js_add_trigger(get_defined_vars(), "before"));
        $db->setQuery($query);
        return $db->loadObjectList();
    }
    
    function save(array $post){
        $id = (int)$post["id"];
        $productfield = JSFactory::getTable('productField', 'jshop');        
        if ($post['type']==-1){
            $post['type'] = 0;
            $post['multilist'] = 1;
        }else{
            $post['multilist'] = 0;
        }
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeSaveProductField', array(&$post));
        $productfield->bind($post);        
        $categorys = $post['category_id'];
        if (!is_array($categorys)){
            $categorys = array();
        }        
        $productfield->setCategorys($categorys);
        if (!$id){
            $productfield->ordering = null;
            $productfield->ordering = $productfield->getNextOrder();            
        }
        if (!$productfield->store()){
            $this->setError(_JSHOP_ERROR_SAVE_DATABASE);            
            return 0; 
        }
        if (!$id){            
            $productfield->addNewFieldProducts();
        }
        $dispatcher->trigger('onAfterSaveProductField', array(&$productfield));        
        return $productfield;
    }
    
    public function deleteList(array $cid, $msg = 1){
        $db = JFactory::getDBO();
        $app = JFactory::getApplication();
        $res = array();
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeRemoveProductField', array(&$cid));
        foreach($cid as $value){
            $query = "DELETE FROM `#__jshopping_products_extra_fields` WHERE `id` = '".$db->escape($value)."'";
            $db->setQuery($query);
            if ($db->query()){
                if ($msg){
                    $app->enqueueMessage(_JSHOP_ITEM_DELETED, 'message');
                }
                $res[$value] = true;
            }else{
                $res[$value] = false;
            }
            
            $query = "DELETE FROM `#__jshopping_products_extra_field_values` WHERE `field_id` = '".$db->escape($value)."'";
            $db->setQuery($query);
            $db->query();
            
            $query = "ALTER TABLE `#__jshopping_products` DROP `extra_field_".(int)$value."`";
            $db->setQuery($query);
            $db->query();
        }
        $dispatcher->trigger('onAfterRemoveProductField', array(&$cid));
        return $res;
    }

}