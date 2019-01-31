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

class MDN_Cdiscount_Debug_TrackingsController extends Mage_Adminhtml_Controller_Action {

    /**
     * Send tracking CRON action
     */
    public function sendTrackingsCronAction(){
        try{

            $countryId = $this->getRequest()->getParam('countryId');
            $country = Mage::getModel('MarketPlace/Countries')->load($countryId);
            Mage::register('mp_country', $country);
            
            Mage::helper('MarketPlace/Main')->sendTracking(Mage::helper('Cdiscount')->getMarketplaceName());
            mage::getSingleton('adminhtml/session')->addSuccess('Tracking successfully send.');
            $this->_redirectReferer();

        }catch(Exception $e){

            Mage::getSingleton('Adminhtml/session')->addError($e->getMessage().' : '.$e->getTraceAsString());
            $this->_redirectReferer();

        }
    }

}
