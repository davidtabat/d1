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
class MDN_MarketPlace_TestController extends Mage_Adminhtml_Controller_Action {

    /**
     * Test it ! 
     */
    /* public function testAction(){

      $helper = Mage::Helper('MarketPlace/Main');
      echo 'import orders<br/>';
      $helper->cronImportOrders();
      echo 'update stocks<br/>';
      $helper->cronUpdateStocksAndPrices();
      echo 'send tracking<br/>';
      $helper->cronSendTracking();
      echo 'check product creation<br/>';
      $helper->cronCheckProductCreation();

      } */

    public function cronUpdateAction() {

        $helper = Mage::Helper('MarketPlace/Main');
        echo 'update stocks<br/>';
        $helper->cronUpdateStocksAndPrices();
    }

    public function cronImportOrdersAction() {

        $helper = Mage::Helper('MarketPlace/Main');
        echo 'import orders<br/>';
        $helper->cronImportOrders();
    }

    public function cronSendTrackingAction() {

        $helper = Mage::Helper('MarketPlace/Main');
        echo 'send tracking<br/>';
        $helper->cronSendTracking();
    }

    public function cronCheckProductCreationAction() {

        $helper = Mage::Helper('MarketPlace/Main');
        echo 'check product creation<br/>';
        $helper->cronCheckProductCreation();
    }
    
    public function cronAutoSubmitAction(){
        
        $helper = Mage::Helper('MarketPlace/Main');
        echo 'auto submit<br/>';
        $helper->cronAutoSubmit();
        
    }

    protected function _isAllowed() {
        return true;
    }

}
