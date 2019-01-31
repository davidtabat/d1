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

class MDN_MarketPlace_Block_Internationalization_Grid extends Mage_Adminhtml_Block_Widget_Form {

    /**
     * get marketplaces
     * 
     * @return array $helpers
     */
    public function getMarketPlaces(){

        $helpers = array();

        $marketplaces = Mage::helper('MarketPlace')->getHelpers();
        foreach($marketplaces as $mp){
            $helper = Mage::helper($mp);
            if($helper->allowInternationalization() === true)
                    $helpers[] = $helper;
        }

        return $helpers;
    }

    /**
     * get stores
     * 
     * @return array $retour 
     */
    public function getStores(){

        $retour = array();
        $stores = mage::getModel('Core/Store')
                    ->getCollection();

        foreach($stores as $store){
            $retour[] = $store;
        }

        return $retour;
    }

    /**
     * ge trow text
     * 
     * @param Mage_Core_Model_Store $store
     * @param MDN_MarketPlace_Helper_Abstract $mp
     * @return string $retour 
     */
    public function getRowText($store, $mp){

        $retour = '-';

        $value = $this->formatValue(Mage::getModel('MarketPlace/Internationalization')->getAssociationValue($store->getstore_id(), $mp->getMarketPlaceName()), $mp);

        if($value != ''){
            $retour = '<span style="color:green">Associated : '.$value.' (<a href="'.$this->getUrl('MarketPlace/Internationalization/Edit', array('marketplace_id' => $mp->getMarketPlaceName(), 'store_id' => $store->getstore_id())).'">Edit</a>)</span>';
        }
        else
            $retour = '<span style="color:red">Not associated (<a href="'.$this->getUrl('MarketPlace/Internationalization/Edit', array('marketplace_id' => $mp->getMarketPlaceName(), 'store_id' => $store->getstore_id())).'">Edit</a>)</span>';

        return $retour;

    }

    /**
     * Format value
     * 
     * @param string $value
     * @param MDN_MarketPlace_Helper_Abstract $mp
     * @return string $retour
     */
    protected function formatValue($value, $mp){

        $retour = '';

        if($value != ''){
            $helper = Mage::Helper(ucfirst($mp->getMarketPlaceName()).'/Internationalization');

            $tmp = explode(",",$value);

            foreach($tmp as $k => $v){

                $retour .= $helper->getCountryByCode(trim($v)).' ';

            }
        }


        return $retour;

    }

}
