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
class MDN_MarketPlace_Model_System_Config_Source_ProductTitleTypes extends Mage_Core_Model_Abstract {

    /**
     * Get all options
     * 
     * @return array 
     */
    public function getAllOptions() {
        if (!$this->_options) {

            $options[] = array(
                'value' => '',
                'label' => ''
            );
            
            foreach(Mage::Helper('MarketPlace/ProductCreation')->getProductTitleTypes() as $k => $v){
                
                $options[] = array(
                    'value' => $k,
                    'label' => $v
                );
                
            }           

            $this->_options = $options;
        }
        return $this->_options;
    }

    /**
     * To option array
     * 
     * @return array 
     */
    public function toOptionArray() {
        return $this->getAllOptions();
    }

}
