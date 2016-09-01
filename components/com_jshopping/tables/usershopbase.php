<?php
/**
* @version      4.11.0 18.12.2014
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die('');

abstract class jshopUserShopBase extends JTable{

    function __construct(&$_db){
        parent::__construct('#__jshopping_users', 'user_id', $_db);
        JPluginHelper::importPlugin('jshoppingcheckout');
		JDispatcher::getInstance()->trigger('onConstruct'.ucfirst(get_class($this)), array(&$this));
    }
	
	function check(){
		$args = func_get_args();
		if (isset($args[0])){
			$type = $args[0];
		}else{
			$type = '';
		}
		return $this->checkData($type, 1);
	}
    
	function checkData($type, $check_exist_email){
		jimport('joomla.mail.helper');
		$jshopConfig = JSFactory::getConfig();
		
        $types = explode(".", $type);
        $type = $types[0];
        if (isset($types[1])){
            $type2 = $types[1];
        }else{
            $type2 = '';
        }
        
		$config_fields = $jshopConfig->getListFieldsRegisterType($type);
		
        JDispatcher::getInstance()->trigger('onBeforeCheck'.ucfirst(get_class($this)), array(&$this, &$type, &$config_fields, &$type2));

        if ($config_fields['title']['require']){
            if (!intval($this->title)) {
                $this->_error = addslashes(_JSHOP_REGWARN_TITLE);
                return false;
            }
        }
                		
        if ($config_fields['f_name']['require']){
		    if(trim($this->f_name) == '') {
			    $this->_error = addslashes(_JSHOP_REGWARN_NAME);
			    return false;
		    }
        }

        if ($config_fields['l_name']['require']){
		    if(trim($this->l_name) == '') {
			    $this->_error = addslashes(_JSHOP_REGWARN_LNAME);
			    return false;
		    }
        }
		
		if ($config_fields['m_name']['require']){
            if(trim($this->m_name) == '') {
                $this->_error = addslashes(_JSHOP_REGWARN_MNAME);
                return false;
            }
        }
        
        if ($config_fields['firma_name']['require']){
            if(trim($this->firma_name) == '') {
                $this->_error = addslashes(_JSHOP_REGWARN_FIRMA_NAME);
                return false;
            }
        }
        
        if ($config_fields['client_type']['require']){
            if(trim($this->client_type) == 0) {
                $this->_error = addslashes(_JSHOP_REGWARN_CLIENT_TYPE);
                return false;
            }
        }

        if ($this->client_type==2 || !$config_fields['client_type']['display']){
            if ($config_fields['firma_code']['require']){
                if(trim($this->firma_code) == '') {
                    $this->_error = addslashes(_JSHOP_REGWARN_FIRMA_CODE);
                    return false;
                }
            }
            
            if ($config_fields['tax_number']['require']){
                if(trim($this->tax_number) == '') {
                    $this->_error = addslashes(_JSHOP_REGWARN_TAX_NUMBER);
                    return false;
                }
            }
        }        

		if ($config_fields['email']['require'] || $this->email!=''){
		    if ((trim($this->email=="")) || !JMailHelper::isEmailAddress($this->email)){
			    $this->_error = addslashes(_JSHOP_REGWARN_MAIL);
			    return false;
		    }
        }

        if ($config_fields['birthday']['require']){
            if(trim($this->birthday) == '') {
                $this->_error = addslashes(_JSHOP_REGWARN_BIRTHDAY);
                return false;
            }
        }		

		if ($type == "register"){
            if ($config_fields['u_name']['require']){
				if(trim($this->u_name) == '') {
					$this->_error = addslashes(_JSHOP_REGWARN_UNAME);
					return false;
				}
			}
			if ($this->u_name!=''){
				if (preg_match("#[<>\"'%;()&]#i", $this->u_name) || strlen(utf8_decode($this->u_name )) < 2) {
					$this->_error = sprintf(addslashes(_JSHOP_VALID_AZ09),addslashes(_JSHOP_USERNAME),2);
					return false;
				}
				// check for existing username
				$query = "SELECT id FROM #__users WHERE username = '".$this->_db->escape($this->u_name)."' AND id != ".(int)$this->user_id;
                JDispatcher::getInstance()->trigger('onBeforeCheckUserNameExistJshopUserShop', array(&$this, &$type, &$config_fields, &$type2, &$query));
				$this->_db->setQuery($query);
				$xid = intval($this->_db->loadResult());
				if($xid && $xid != intval($this->user_id)){
					$this->_error = addslashes(_JSHOP_REGWARN_INUSE);
					return false;
				}
			}
            if ($config_fields['password']['require'] && trim($this->password)==''){
                $this->_error = _JSHOP_REGWARN_PASSWORD;
                return false;
            }

            if (($this->password || $this->password2) && $config_fields['password_2']['display'] && $this->password!=$this->password2){
                $this->_error = _JSHOP_REGWARN_PASSWORD_NOT_MATCH;
                return false;
            }
		}
        
        if ($type=='editaccount'){
            if ($config_fields['password']['require']){
                if(trim($this->password) == '') {
                    $this->_error = addslashes(_JSHOP_REGWARN_PASSWORD);
                    return false;
                }
            }
            if (($this->password || $this->password2) && $config_fields['password_2']['display'] && $this->password!=$this->password2){
                $this->_error = _JSHOP_REGWARN_PASSWORD_NOT_MATCH;
                return false;
            }
        }
        
        if ($type2 == "edituser"){
			if ($config_fields['u_name']['require']){
				if(trim($this->u_name) == '') {
					$this->_error = addslashes(_JSHOP_REGWARN_UNAME);
					return false;
				}
            }
			if ($this->u_name!=''){
				if (preg_match("#[<>\"'%;()&]#i", $this->u_name) || strlen(utf8_decode($this->u_name )) < 2){
					$this->_error = sprintf(addslashes(_JSHOP_VALID_AZ09),addslashes(_JSHOP_USERNAME),2);
					return false;
				}
				// check for existing username
				$query = "SELECT id FROM #__users WHERE username = '".$this->_db->escape($this->u_name)."' AND id != ".(int)$this->user_id;
                JDispatcher::getInstance()->trigger('onBeforeCheckUserNameExistJshopUserShop', array(&$this, &$type, &$config_fields, &$type2, &$query));
				$this->_db->setQuery($query);
				$xid = intval($this->_db->loadResult());
				if($xid && $xid != intval($this->user_id)){
					$this->_error = addslashes(_JSHOP_REGWARN_INUSE);
					return false;
				}
            }
            if ($this->password && $this->password!=$this->password2){
                $this->_error = _JSHOP_REGWARN_PASSWORD_NOT_MATCH;
                return false;
            }
        }
        
		if ($this->email!='' && $check_exist_email){
			// check for existing email
			$query = "SELECT id FROM #__users WHERE email='".$this->_db->escape($this->email)."' AND id != ".(int)$this->user_id;
            JDispatcher::getInstance()->trigger('onBeforeCheckUserEmailExistJshopUserShop', array(&$this, &$type, &$config_fields, &$type2, &$query));
			$this->_db->setQuery($query);
			$xid = intval($this->_db->loadResult());
			if($xid && $xid != intval($this->id)){
				$this->_error = addslashes(_JSHOP_REGWARN_EMAIL_INUSE);
				return false;
			}
		}
		
        if ($config_fields['home']['require']){
            if(trim($this->home) == '') {
                $this->_error = addslashes(_JSHOP_REGWARN_HOME);
                return false;
            }
        }
        
        if ($config_fields['apartment']['require']){
            if(trim($this->apartment) == '') {
                $this->_error = addslashes(_JSHOP_REGWARN_APARTMENT);
                return false;
            }
        }
        
        if ($config_fields['street']['require']){
		    if(trim($this->street) == '') {
			    $this->_error = addslashes(_JSHOP_REGWARN_STREET);
			    return false;
		    }
        }
        
        if ($config_fields['street_nr']['require']){
            if(trim($this->street_nr) == '') {
                $this->_error = addslashes(_JSHOP_REGWARN_STREET);
                return false;
            }
        }
		
        if ($config_fields['zip']['require']){
		    if (trim($this->zip) == ""){
		        $this->_error = addslashes( _JSHOP_REGWARN_ZIP );
		        return false;
		    }
        }
        
        if ($config_fields['city']['require']){
		    if (trim($this->city) == ''){
		        $this->_error = addslashes( _JSHOP_REGWARN_CITY );
		        return false;
		    }
        }		
        
        if ($config_fields['state']['require']){
            if (trim($this->state) == ''){
                $this->_error = addslashes( _JSHOP_REGWARN_STATE ); //region
                return false;
            }
        }
        
        if ($config_fields['country']['require']){
		    if(!intval($this->country)) {
			    $this->_error = addslashes(_JSHOP_REGWARN_COUNTRY);
			    return false;
		    }
        }		
        	
        if ($config_fields['phone']['require']){	
		    if(trim($this->phone) == '') {
		        $this->_error = addslashes(_JSHOP_REGWARN_PHONE);
		        return false;
		    }
        }
        
        if ($config_fields['mobil_phone']['require']){    
            if(trim($this->mobil_phone) == '') {
                $this->_error = addslashes(_JSHOP_REGWARN_MOBIL_PHONE);
                return false;
            }
        }
        
        if ($config_fields['fax']['require']){    
            if(trim($this->fax) == '') {
                $this->_error = addslashes(_JSHOP_REGWARN_FAX);
                return false;
            }
        }
        
        if ($config_fields['ext_field_1']['require']){
            if(trim($this->ext_field_1) == '') {
                $this->_error = addslashes(_JSHOP_REGWARN_EXT_FIELD_1);
                return false;
            }
        }
        
        if ($config_fields['ext_field_2']['require']){
            if(trim($this->ext_field_2) == '') {
                $this->_error = addslashes(_JSHOP_REGWARN_EXT_FIELD_2);
                return false;
            }
        }
        
        if ($config_fields['ext_field_3']['require']){
            if(trim($this->ext_field_3) == '') {
                $this->_error = addslashes(_JSHOP_REGWARN_EXT_FIELD_3);
                return false;
            }
        }
        
		if ($type == "address" || $type == "editaccount") {
			if ($this->delivery_adress) {
                            
                if ($config_fields['d_title']['require']){
                    if(!intval($this->d_title)) {
                        $this->_error = addslashes(_JSHOP_REGWARN_TITLE_DELIVERY);
                        return false;
                    }
                }
                
                if ($config_fields['d_f_name']['require']){
				    if(trim($this->d_f_name) == '') {
					    $this->_error = addslashes(_JSHOP_REGWARN_NAME_DELIVERY);
					    return false;
				    }
                }

                if ($config_fields['d_l_name']['require']){
				    if(trim($this->d_l_name) == '') {
					    $this->_error = addslashes(_JSHOP_REGWARN_LNAME_DELIVERY);
					    return false;
				    }
                }
				
				if ($config_fields['d_m_name']['require']){
                    if(trim($this->d_m_name) == '') {
                        $this->_error = addslashes(_JSHOP_REGWARN_MNAME_DELIVERY);
                        return false;
                    }
                }
                
                if ($config_fields['d_firma_name']['require']){
                    if(trim($this->d_firma_name) == '') {
                        $this->_error = addslashes(_JSHOP_REGWARN_FIRMA_NAME_DELIVERY);
                        return false;
                    }
                }
                
                if (isset($config_fields['d_firma_code']) && $config_fields['d_firma_code']['require']){
                    if(trim($this->d_firma_code) == '') {
                        $this->_error = addslashes(_JSHOP_REGWARN_FIRMA_CODE_DELIVERY);
                        return false;
                    }
                }
                
                if (isset($config_fields['d_tax_number']) && $config_fields['d_tax_number']['require']){
                    if(trim($this->d_tax_number) == '') {
                        $this->_error = addslashes(_JSHOP_REGWARN_TAX_NUMBER_DELIVERY);
                        return false;
                    }
                }                

                if ($config_fields['d_email']['require']){
                    if ( (trim($this->d_email) == "") || ! JMailHelper::isEmailAddress($this->d_email)) {
                        $this->_error = addslashes(_JSHOP_REGWARN_MAIL_DELIVERY);
                        return false;
                    }
                }
				
				if ($config_fields['d_birthday']['require']){
                    if(trim($this->d_birthday) == '') {
                        $this->_error = addslashes(_JSHOP_REGWARN_BIRTHDAY_DELIVERY);
                        return false;
                    }
                }
                
                if ($config_fields['d_home']['require']){
                    if(trim($this->d_home) == '') {
                        $this->_error = addslashes(_JSHOP_REGWARN_HOME_DELIVERY);
                        return false;
                    }
                }
                
                if ($config_fields['d_apartment']['require']){
                    if(trim($this->d_apartment) == '') {
                        $this->_error = addslashes(_JSHOP_REGWARN_APARTMENT_DELIVERY);
                        return false;
                    }
                }
                
                if ($config_fields['d_street']['require']){
				    if(trim($this->d_street) == '') {
					    $this->_error = addslashes(_JSHOP_REGWARN_STREET_DELIVERY);
					    return false;
				    }
                }
                
                if ($config_fields['d_street_nr']['require']){
                    if(trim($this->d_street_nr) == '') {
                        $this->_error = addslashes(_JSHOP_REGWARN_STREET_DELIVERY);
                        return false;
                    }
                }
				
                if ($config_fields['d_zip']['require']){
                    if (trim($this->d_zip) == ""){
				        $this->_error = addslashes( _JSHOP_REGWARN_ZIP_DELIVERY );
				        return false;
				    }
                }
                
                if ($config_fields['d_city']['require']){
                    if (trim($this->d_city) == ''){
                        $this->_error = addslashes( _JSHOP_REGWARN_CITY_DELIVERY );
                        return false;
                    }
                }

                if ($config_fields['d_state']['require']){
				    if (trim($this->d_state) == ''){
				        $this->_error = addslashes( _JSHOP_REGWARN_STATE_DELIVERY );
				        return false;
				    }
                }
                
                if ($config_fields['d_country']['require']){
				    if(!intval($this->d_country)) {
                        $this->_error = addslashes(_JSHOP_REGWARN_COUNTRY_DELIVERY);
                        return false;
                    }
                }                                
				
                if ($config_fields['d_phone']['require']){
				    if(trim($this->d_phone) == '') {
				        $this->_error = addslashes(_JSHOP_REGWARN_PHONE_DELIVERY);
				        return false;
				    }
                }
                
                if ($config_fields['d_mobil_phone']['require']){    
                    if(trim($this->d_mobil_phone) == '') {
                        $this->_error = addslashes(_JSHOP_REGWARN_MOBIL_PHONE_DELIVERY);
                        return false;
                    }
                }
                
                if ($config_fields['d_fax']['require']){    
                    if (trim($this->d_fax) == '') {
                        $this->_error = addslashes(_JSHOP_REGWARN_FAX_DELIVERY);
                        return false;
                    }
                }
                
                if ($config_fields['d_ext_field_1']['require']){
                    if(trim($this->d_ext_field_1) == '') {
                        $this->_error = addslashes(_JSHOP_REGWARN_EXT_FIELD_1_DELIVERY);
                        return false;
                    }
                }
                
                if ($config_fields['d_ext_field_2']['require']){
                    if(trim($this->d_ext_field_2) == '') {
                        $this->_error = addslashes(_JSHOP_REGWARN_EXT_FIELD_2_DELIVERY);
                        return false;
                    }
                }
                
                if ($config_fields['d_ext_field_3']['require']){
                    if(trim($this->d_ext_field_3) == '') {
                        $this->_error = addslashes(_JSHOP_REGWARN_EXT_FIELD_3_DELIVERY);
                        return false;
                    }
                }
                                
			}
		}        

		return true;
	}
    
    function saveTypePayment($id){
        $this->payment_id = $id;
        $this->store();
        return 1;
    }
    
    function saveTypeShipping($id){
        $this->shipping_id = $id;
        $this->store();
        return 1;
    }
    
    function getError($i = null, $toString = true){
        return $this->_error;
    }
    
    function setError($error){
        $this->_error = $error;
    }
    
	function loadDataFromEdit(){
		$this->prepareBirthdayFormat();
		$this->updateCountryToDefault();
		return $this;
	}
	
    function updateCountryToDefault(){
        $jshopConfig = JSFactory::getConfig();
        if (!$this->country) $this->country = $jshopConfig->default_country;
        if (!$this->d_country) $this->d_country = $jshopConfig->default_country;
    }

    function prepareBirthdayFormat(){
        $jshopConfig = JSFactory::getConfig();        
        $this->birthday = getDisplayDate($this->birthday, $jshopConfig->field_birthday_format);
        $this->d_birthday = getDisplayDate($this->d_birthday, $jshopConfig->field_birthday_format);
    }
	
}