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

class MDN_MarketPlace_Helper_Picture extends Mage_Core_Helper_Abstract {

    const kSmall = 'small';
    const kMain = 'main';

    /**
     * Get url
     * 
     * @param Mage_Catalog_Model_Product $product
     * @param type $type
     * @return string $url 
     */
    public function getUrl($product, $type = null){

        $url = "";

        $product = $this->getParent($product);

        // image (manually build image path to avoid magento < 1.3 issue with catalog/image helper)
        $imageUrl = Mage::getBaseUrl('media') . 'catalog/product' . $product->getimage();
        $smallImageUrl = Mage::getBaseUrl('media') . 'catalog/product' . $product->getsmall_image();

        if($type === null){

            if (!preg_match('#no_selection#i', $imageUrl)) {
                $url = $imageUrl;
            } elseif (!preg_match('#no_selection#i', $smallImageUrl)) {
                $url = $smallImageUrl;
            } else {
                $url = "";
            }
            
        }else{

            switch($type){
                case self::kMain:
                    $url =  $imageUrl;
                    break;
                case self::kSmall:
                    $url = $smallImageUrl;
                    break;
                default:
                    $url = "";
                    break;
            }

        }

        return $url;

    }

    /**
     * Get parent
     * 
     * @param Mage_Catalog_Model_Product $product
     * @return Mage_Catalog_Model_Product 
     */
    protected function getParent($product){

        $retour = $product;

        $parentIdArray = Mage::Helper('Pixmania/ConfigurablesProducts')->getproductParentId($product);

        if(count($parentIdArray) > 0){
            $product = Mage::getModel('catalog/product')->load($parentIdArray[0]);
        }

        return $product;

    }

}
