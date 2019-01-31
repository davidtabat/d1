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
 * @version 2.0.1
 */
class MDN_Cdiscount_Model_TokenHistory extends Mage_Core_Model_Abstract {
    
    /**
     * Construct 
     */
    public function _construct() {
        parent::_construct();
        $this->_init('Cdiscount/TokenHistory');
    }
    
    /**
     * Add token history
     *  
     * @param string $token
     * @param string $url
     */
    public function add($token, $url){
        
        $this->setcth_token($token)
                ->setcth_url($url)
                ->setcth_date(date('Y-m-d H:i:s', Mage::getSingleton('core/date')->timestamp()))
                ->save();
        
    }
    
}
