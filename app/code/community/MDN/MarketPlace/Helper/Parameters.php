<?php

/* 
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
 * @deprecated since version 2
 */
class MDN_MarketPlace_Helper_Parameters extends Mage_Core_Helper_Abstract {

    /**
     * Getter
     *
     * @param string $path
     * @return string
     * @deprecated
     */
    public function getParamValue($path){
        throw new Exception('Method deprecated in '.__METHOD__);
        //return mage::getStoreConfig($path);
    }

    /**
     * Setter
     *
     * @param string $path
     * @param string $value
     * @deprecated
     */
    public function setParamValue($path, $value){
        throw new Exception('Method deprecated in '.__METHOD__);
        //$obj = new Mage_Core_Model_Config();
        //$obj->saveConfig($path, $value);
    }

}
