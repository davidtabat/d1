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
class MDN_MarketPlace_Model_Mysql4_Logs_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * Construct 
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('MarketPlace/Logs');
    }

    /**
     * Add attribute to sort
     * 
     * @param type $attribute
     * @param string $dir
     * @return \MDN_MarketPlace_Model_Mysql4_Logs_Collection 
     */
    public function addAttributeToSort($attribute, $dir='asc')
    {
        if (!is_string($attribute)) {
            return $this;
        }
        $this->setOrder($attribute, $dir);
        return $this;
    }

}
