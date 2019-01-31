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
 */
abstract class MDN_MarketPlace_Helper_Matching extends Mage_Core_Helper_Abstract {

    const kSKU = 'sku';
    const kEAN = 'ean';
    const kNAME = 'name';
    const kPRICE = 'price';
    const kSTOCK = 'stock';
    const kID = 'id';
    const kPackageWeight = 'packageWeight';
    const kShippingWeight = 'shippingWeight';

    /**
     * must be implemented
     */
    abstract function Match($products);

    /**
     * Get required keys
     *
     * @return array
     */
    protected function _getRequiredKeys(){

        return array(
            self::kID => self::kID,
            self::kSKU => self::kSKU,
            self::kEAN => self::kEAN,
            self::kPRICE => self::kPRICE,
            self::kSTOCK => self::kSTOCK,
            self::kNAME => self::kNAME
        );

    }

    /**
     * Check that $products contain all required informations
     *
     * @param array $products
     * @return boolean
     */
    protected function _checkProductTab($products){

        $retour = true;

        $required_keys = $this->_getRequiredKeys();

        foreach($required_keys as $k => $v){

            if(!array_key_exists($k, $products)){
                $retour = false;
                break;
            }

        }

        return $retour;

    }

    /**
     * Get filename
     *
     * @return string
     */
    protected function _getFilename(){
        return 'matching.txt';
    }

    /**
     * Save feed for matching EAN requests
     *
     * @param string $mp
     * @param string $content
     * @param string $response
     * @param int $id
     * @param string $status
     * @return int 0
     */
    protected function _addFeed($mp, $content, $response, $id, $status){

        // add marketplace feed entry
        $feed = Mage::getModel('MarketPlace/Feed')
                    ->setmp_content($content)
                    ->setmp_response($response->getBody())
                    ->setmp_marketplace_id(Mage::Helper(ucfirst($mp))->getMarketPlaceName())
                    ->setmp_date(date('Y-m-d H:i:s'), Mage::getModel('core/date')->timestamp())
                    ->setmp_type(MDN_MarketPlace_Helper_Feed::kFeedTypeMatchingEAN)
                    ->setmp_feed_id($id)
                    ->setmp_status($status)
                    ->save();

        return 0;

    }

    /**
     * Build matching array
     *
     * @param array $products
     * @return array $retour
     */
    public function buildMatchingArray($products) {

        $retour = array();

        foreach ($products as $item) {

            $barcode = Mage::Helper('MarketPlace/Barcode')->getBarcodeForProduct($item);
            
            $retour[$barcode] = array(
                self::kID => $item->getentity_id(),
                self::kSKU => $item->getsku(),
                self::kNAME => $item->getname(),
                self::kEAN => $barcode,
                self::kPRICE => $this->_formatPrice(Mage::Helper('MarketPlace/Product')->getPriceToExport($item)),
                self::kSTOCK => 0,
                self::kShippingWeight => $item->getweight(),
                self::kPackageWeight => $item->getweight()
            );

        }

        return $retour;
    }

    /**
     * Format price
     *
     * @param float $price
     * @return float $price
     */
    protected function _formatPrice($price){
        return $price;
    }


}
