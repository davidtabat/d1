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
class MDN_Cdiscount_Helper_ListingSynchronization extends Mage_Core_helper_Abstract {

    /**
     * Download listiung from cdiscount and ensure that references are stored in MP Data
     *
     * @return string
     */
    public function synchronize($countryId, $page)
    {
        Mage::helper('Cdiscount')->magentoLog('BEGIN sync listing for page #'.$page);

        $debug = array('existing' => array(), 'updated' => array(), 'error' => array(), 'total' => 0);
        $start = time();

        $country = Mage::getModel('MarketPlace/Countries')->load($countryId);
        $account = Mage::getModel('MarketPlace/Accounts')->load($country->getmpac_account_id());
        Mage::register('mp_country', $country);

        $refs = $this->getOffers($page);
        if (count($refs) == 0)
            return false;

        $debug['total'] = count($refs);
        Mage::helper('Cdiscount')->magentoLog('RESPONSE sync listing for page #'.$page);

        //process offers
        $productModel = Mage::getModel('catalog/product');
        foreach($refs as $sku => $cdiscountId)
        {
            Mage::helper('Cdiscount')->magentoLog('START sync listing for page #'.$page.' sku = '.$sku);

            try
            {
                //check that product exists
                if($account->getParam('seller_product_reference') == 'sku')
                    $productId = $productModel->getIdBySku($sku);
                else
                    $productId = $sku;
                if (!$productId)
                {
                    $debug['error'][] = 'Sku '.$sku.' does not exist in magento';
                    continue;
                }

                //check if product exists
                $test = Mage::getModel('catalog/product')->load($productId);
                if (!$test->getId())
                    throw new Exception('Product id #'.$productId.' does not exist in magento');

                //check if association already done
                $mpData = mage::getResourceModel('MarketPlace/Data_collection')
                    ->addFieldToFilter('mp_product_id', $productId)
                    ->addFieldToFilter('mp_marketplace_id', $countryId)
                    ->getFirstItem();
                if ($mpData->getmp_reference() == $cdiscountId)
                {
                    $debug['existing'][] = $sku;
                    continue;
                }

                //associate
                Mage::getModel('MarketPlace/Data')->updateStatus($productId, Mage::registry('mp_country')->getId(), MDN_MarketPlace_Helper_ProductCreation::kStatusCreated, $cdiscountId);
                $debug['updated'][] = $sku.' => '.$cdiscountId;
            }
            catch(Exception $ex)
            {
                $debug['error'][] = $sku.' : '.$ex->getMessage();
            }

            Mage::helper('Cdiscount')->magentoLog('DONE sync listing for page #'.$page.' sku = '.$sku);
        }

        $debugString = 'Page #'.$page.', Total: '.$debug['total'].', existing: '.count($debug['existing']).', associated: '.count($debug['updated']).', error: '.count($debug['error']);
        if (count($debug['error']) > 0)
        {
            $debugString .= '<br>'.implode(',', $debug['error']);
        }

        Mage::helper('Cdiscount')->magentoLog('LOG sync listing for page #'.$page);

        mage::getModel('MarketPlace/Logs')->addLog(
                        Mage::helper('Cdiscount')->getMarketPlaceName(),
                        0,
                        $debugString,
                        MDN_MarketPlace_Model_Logs::kScopeMisc,
                        array('fileName' => NULL),
                        (time() - $start)
                    );

        Mage::helper('Cdiscount')->magentoLog('END sync listing for page #'.$page);

        return $debugString;
    }

    /**
     * Load offers from cdiscount web service
     * @return array
     */
    protected function getOffers($page)
    {
        $refs = array();

        $res = Mage::Helper('Cdiscount/Services')->getOfferListPaginated($page);
        $xml = $res['content'];
        $xmlDoc = new DomDocument();
        $xmlDoc->loadXML($xml);

        //get offers
        $offers = $xmlDoc->getElementsByTagName('Offer');
        for($i = 0; $i < $offers->length; $i++)
        {
            $offer = $offers->item($i);
            $refs[$offer->getElementsByTagName('SellerProductId')->item(0)->nodeValue] = $offer->getElementsByTagName('ProductId')->item(0)->nodeValue;
        }

        return $refs;
    }

}