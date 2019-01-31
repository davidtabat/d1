<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2013 Boost My Shop (http://www.boostmyshop.com)
 * @author : Nicolas MUGNIER
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_MarketPlace
 * @version 2.1
 */
class MDN_MarketPlace_Model_Configuration extends Mage_Core_Model_Abstract {
    
    /**
     * Construtor
     */
    public function _construct() {
        parent::_construct();
        $this->_init('MarketPlace/Configuration');
    }
    
    /**
     * Get configuration
     * 
     * @param string $mp
     * @return \Varien_Object 
     */
    public function getConfiguration($mp){                
        
        $configuration = Mage::getModel('MarketPlace/Configuration')->load($mp, 'mpc_marketplace_id');
        
        if($configuration->getmpc_id()){
            $params = unserialize($configuration->getmpc_params());
            foreach($params as $k => $v){
                $method = 'set'.$k;
                $configuration->$method($v);
            }
        }else{
            $configuration = new Varien_Object();
            $configuration->setmpc_marketplace_id($mp);
        }
        
        return $configuration;
        
    }
    
    /**
     * Before save
     * 
     * @return type 
     */
    protected function _beforeSave(){
        
        $this->setmpc_params(serialize($this->getmpc_params()));
        
        return parent::_beforeSave();
        
    }
    
    /**
     * Get general config object
     * 
     * @return \Varien_Object 
     */
    public function getGeneralConfigObject(){
        
        $fields = array(
            'mp_barcode_attribute',
            'mp_manufacturer_attribute',
            'mp_brand_attribute',
            'mp_max_category_depth',
            'mp_root_category',
            'mp_generate_invoice',
            'mp_order_status',
            'mp_default_payment_method',
            'mp_bug_report',
            'mp_debug_mode',
            'mp_stack_trace',
            'mp_max_log'
        );
        
        $object = new Varien_Object();
        
        foreach($fields as $field){
            
            $method = 'set'.$field;
            $object->$method(Mage::getStoreConfig($field));
            
        }
        
        //echo '<pre>';var_dump($object);die('</pre>');
        
        return $object;
        
    }
    
}
