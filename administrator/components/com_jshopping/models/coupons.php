<?php
/**
* @version      4.14.0 20.05.2016
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/

defined('_JEXEC') or die();

class JshoppingModelCoupons extends JshoppingModelBaseadmin{
    
    protected $nameTable = 'coupon';
    protected $tableFieldPublish = 'coupon_publish';

    function getAllCoupons($limitstart, $limit, $order = null, $orderDir = null) {
        $db = JFactory::getDBO(); 
        $queryorder = 'ORDER BY C.used, C.coupon_id desc';
        if ($order && $orderDir){
            $queryorder = "ORDER BY ".$order." ".$orderDir;
        }
        $query = "SELECT C.*, U.f_name, U.l_name  FROM `#__jshopping_coupons` as C left join #__jshopping_users as U on C.for_user_id=U.user_id ".$queryorder;
        extract(js_add_trigger(get_defined_vars(), "before"));
        $db->setQuery($query, $limitstart, $limit);
        return $db->loadObjectList();
    }
    
    function getCountCoupons(){
        $db = JFactory::getDBO(); 
        $query = "SELECT count(*) FROM `#__jshopping_coupons`";
        extract(js_add_trigger(get_defined_vars(), "before"));
        $db->setQuery($query);
        return $db->loadResult();   
    }
    
    public function getPrepareDataSave($input){
        $post = $input->post->getArray();
        $post['coupon_code'] = $input->getVar("coupon_code");
        $post['coupon_publish'] = $input->getInt("coupon_publish", 0);
        $post['finished_after_used'] = $input->getInt("finished_after_used", 0);
        $post['coupon_value'] = saveAsPrice($post['coupon_value']);
        return $post;
    }
    
    public function save(array $post){
        $coupon = JSFactory::getTable('coupon', 'jshop');        
        $dispatcher = JDispatcher::getInstance();        
        $dispatcher->trigger('onBeforeSaveCoupon', array(&$post));
        if (!$post['coupon_code']){
            $this->setError(_JSHOP_ERROR_COUPON_CODE);
            return 0;
        }
        if ($post['coupon_value']<0 || ($post['coupon_value']>100 && $post['coupon_type']==0)){
            $this->setError(_JSHOP_ERROR_COUPON_VALUE);
            return 0;
        }        
        $coupon->bind($post);
        if ($coupon->getExistCode()){
            $this->setError(_JSHOP_ERROR_COUPON_EXIST);
            return 0;
        }
        if (!$coupon->store()) {
            $this->setError(_JSHOP_ERROR_SAVE_DATABASE);
            return 0;
        }
        $dispatcher->trigger('onAfterSaveCoupon', array(&$coupon));
        return $coupon;
    }
    
    public function deleteList(array $cid, $msg = 1){
        $db = JFactory::getDBO();
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforeRemoveCoupon', array(&$cid));
        foreach($cid as $id){
            $query = "DELETE FROM `#__jshopping_coupons` WHERE `coupon_id` = ".(int)$id;
            $db->setQuery($query);
            $db->query();
        }
        if ($msg){
            $app = JFactory::getApplication();
            $app->enqueueMessage(_JSHOP_COUPON_DELETED, 'message');
        }
        $dispatcher->trigger('onAfterRemoveCoupon', array(&$cid));
    }
    
    public function publish(array $cid, $flag){
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onBeforePublishCoupon', array(&$cid, &$flag));
        parent::publish($cid, $flag);
        $dispatcher->trigger('onAfterPublishCoupon', array(&$cid, &$flag));
    }

}