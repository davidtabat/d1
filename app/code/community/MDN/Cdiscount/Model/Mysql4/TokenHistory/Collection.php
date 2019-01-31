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
class MDN_Cdiscount_Model_Mysql4_TokenHistory_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
    
    /**
     * Construct 
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('Cdiscount/TokenHistory');
    }
    
}
