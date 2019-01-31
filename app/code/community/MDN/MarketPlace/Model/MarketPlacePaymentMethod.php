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
 * @copyright  Copyright (c) 2012 Boost My Shop (http://www.boostmyshop.com)
 * @author : Nicolas MUGNIER
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_MarketPlace
 * @version 2.1
 */
class MDN_MarketPlace_Model_MarketPlacePaymentMethod extends Mage_Payment_Model_Method_Abstract {

    /* @var string */
    protected $_code  = 'MarketPlacePaymentMethod';
    /* @var string */
    protected $_formBlockType = 'MarketPlace/MarketPlacePaymentMethod_form';
    /* @var string */
    protected $_infoBlockType = 'MarketPlace/MarketPlacePaymentMethod_info';

    /**
     * get information
     * 
     * @return string 
     */
    public function getInformation() {
        return $this->getConfigData('information');
    }

    /**
     * get address
     * 
     * @return string 
     */
    public function getAddress() {
        return $this->getConfigData('address');
    }
}
