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
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Nicolas MUGNIER
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class MDN_MarketPlace_Helper_CheckResponse extends Mage_Core_Helper_Abstract {

    /* @var array */
    protected $_toSkip = array();
    /* @var int */
    protected $_limit = 10;
    /* @var array */
    protected $_errors = array();
    /* @var int */
    protected $_cpt = 0;
    /* @var int */
    protected $_cptError = 0;
    /* @var boolean */
    protected $_updateStatus = false;
    /* @var array */
    protected $_idsInError = array();
    /* @var array */
    protected $_idsOk = array();

    /**
     * get mp 
     */
    abstract function getMp();

    /**
     * check export 
     */
    abstract function checkExport($feedId = null, $submit = false);

    /**
     * Get collection
     * 
     * @param int $feedId
     * @return MDN_MarketPlace_Model_Feed_Mysql4_Feed_Collection $collection 
     */
    public function getCollection($feedId = null) {

        $collection = Mage::getModel('MarketPlace/Feed')->getCollection()
                ->addFieldToFilter('mp_marketplace_id', $this->getMp())
                ->addFieldToFilter('mp_type', array('in' => array(MDN_MarketPlace_Helper_Feed::kFeedTypeUpdateStockPrice, MDN_MarketPlace_Helper_Feed::kFeedTypeDeleteProduct)))
                ->addFieldToFilter('mp_country', Mage::registry('mp_country')->getId());

        if ($feedId !== null)
            $collection->addFieldToFilter('mp_feed_id', $feedId);
        else {
            $collection->addFieldToFilter('mp_status', array('in', array(MDN_MarketPlace_Helper_Feed::kFeedInProgress, MDN_MarketPlace_Helper_Feed::kFeedSubmitted)));
            $collection->setOrder('mp_feed_id', 'ASC');
            $collection->getSelect()->limit($this->_limit);
        }

        //echo $collection->getSelect();die();  

        return $collection;
    }

    /**
     * Notify errors
     * 
     * @return string 
     */
    public function notifyErrors() {

        $message = "";

        if (count($this->_errors) > 0) {

            $message .= Mage::Helper('MarketPlace')->__('Some error occured during price and stock update').' : '."\n";

            foreach ($this->_errors as $tab) {

                $message .= "- ";

                foreach ($tab as $k => $v) {

                    $message .= $k . " : " . $v . " ";
                }

                $message .= "\n";
            }

            $to = Mage::getModel('MarketPlace/Configuration')->getGeneralConfigObject()->getmp_bug_report();
            $subject = Mage::Helper('MarketPlace')->__("Error in update process (%s)", $this->getMp());

            mail($to, $subject, utf8_decode($message));
        }

        if ($this->_updateStatus === true) {

            $this->updateMarketPlaceData();
        }

        return $message;
    }

    /**
     * Update product update status
     * 
     * @return int 
     */
    public function updateMarketPlaceData() {

        if(count($this->_idsInError) > 0){
            foreach ($this->_idsInError as $id) {

                Mage::getModel('MarketPlace/Data')->addMessage($id, $this->_errors[$id]['log'], strtolower($this->getMp()));
                Mage::getModel('MarketPlace/Data')->setLastUpdateStatus($id, Mage::registry('mp_country')->getId(), MDN_MarketPlace_Model_Data::kUpdateStatusError);
            }
        }

        if (count($this->_idsOk) > 0){
            foreach($this->_idsOk as $id)
                Mage::getModel('MarketPlace/Data')->setLastUpdateStatus($id, Mage::registry('mp_country')->getId(), MDN_MarketPlace_Model_Data::kUpdateStatusOk);
        }
        
        return 0;
    }

}
