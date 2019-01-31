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
 * @package MDN_Cdiscount
 * @version 2.0
 */

class MDN_Cdiscount_Debug_CategoriesController extends Mage_Adminhtml_Controller_Action {

    /**
     * ge tall category tree  
     */
    public function getAllAllowedCategoryTreeAction(){

        $countryId = $this->getRequest()->getParam('countryId');
        $country = Mage::getModle('MarketPlace/Countries')->load($countryId);
        Mage::register('mp_country', $country);
        
        $helper = Mage::Helper('Cdiscount/Auth');
        $helper->setAuth(
                array(
                    'login' => 'AllData',
                    'pass' => 'pa$$word'
                ));

        $helper->getToken();

    }


    protected function _isAllowed() {
        return true;
    }

}
