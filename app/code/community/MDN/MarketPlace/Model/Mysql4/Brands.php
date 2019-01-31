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
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Nicolas MUGNIER
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_MarketPlace
 */

class MDN_MarketPlace_Model_Mysql4_Brands extends Mage_Core_Model_Mysql4_Abstract {

    /**
     * Construct 
     */
    public function _construct(){

        $this->_init('MarketPlace/Brands', 'mpb_id');

    }

}
