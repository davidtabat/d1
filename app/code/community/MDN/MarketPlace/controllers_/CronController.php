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

set_time_limit(3600);

class MDN_MarketPlace_CronController extends Mage_Adminhtml_Controller_Action {

    /**
     * Run all marketplce cron jobs
     */
    public function runAction(){
        
        try{

            $model = Mage::getModel('MarketPlace/Observer');
            $model->getOrders();
            $model->updateStocks();
            $model->checkProductCreation();

            Mage::getSingleton('adminhtml/session')->addSuccess('Job done');

        }catch(Exception $e){

            Mage::getSingleton('adminhtml/session')->addError($e->getMessage().' : '.$e->getTraceAsString());

        }

        $this->_redirectReferer();

    }

}
