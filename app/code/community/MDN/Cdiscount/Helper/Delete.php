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
 * @package MDN_Cdiscount
 * @version 2
 */
class MDN_Cdiscount_Helper_Delete extends Mage_Core_Helper_Abstract {
    
    /**
     * Delete products on Amazon
     * 
     * @param array $ids     
     * @throws Exception 
     */
    public function process($ids){
        
        throw new Exception('Not available for this marketplace');             
        
    }
    
}
