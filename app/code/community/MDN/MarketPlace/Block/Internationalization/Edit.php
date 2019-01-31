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
 * @package MDN_MarketPlace
 * @version 2.1
 * @todo : only used by Pixmania extension, will be deprecated, use accoutn configuration instead
 */

class MDN_MarketPlace_Block_Internationalization_Edit extends Mage_Adminhtml_Block_Widget_Form {

    /* @var Mage_Core_Model_Store */
    private $_store = null;

    /**
     * Get internationalisation form
     * 
     * @return string $html
     */
    public function getInternationalizationForm(){

        try{

            $name = 'associated_data';
            $value = Mage::getModel('MarketPlace/Internationalization')->getAssociationValue($this->getStore()->getstore_id(), $this->getMarketPlace()->getMarketPlaceName());
            $html = $this->getMarketPlace()->getInternationalizationForm($name, $value);

        }catch(Exception $e){
            $html = '<span style="color:red;">'.$e->getMessage().'</span>';
        }

        return $html;

    }

    /**
     * get marketplace
     * 
     * @return Mage_Core_Helper_Abstract 
     */
    public function getMarketPlace(){
        $marketplace_id = $this->getRequest()->getParam('marketplace_id');
        return Mage::helper('MarketPlace')->getHelperByName($marketplace_id);
    }

    /**
     * get store
     * 
     * @return Mage_Core_Model_Store 
     */
    public function getStore(){

        if($this->_store == null){
            $store_id = $this->getRequest()->getParam('store_id');
            $this->_store = Mage::getModel('core/store')->load($store_id);
        }
        
        return $this->_store;
    }

}
