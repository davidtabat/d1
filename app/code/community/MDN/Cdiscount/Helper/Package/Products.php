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
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Nicolas MUGNIER
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class MDN_Cdiscount_Helper_Package_Products extends MDN_Cdiscount_Helper_Package_Abstract {

    protected $_type = 'products';

    /**
     * Build XML products file
     */
    protected function _buildXML(){

        $content = '';

        $configObj = Mage::getModel('MarketPlace/Configuration')->getGeneralConfigObject();

        $country = Mage::registry('mp_country');
        $account = Mage::getModel('MarketPlace/Accounts')->load($country->getmpac_account_id());

        $this->log('========================================================');
        $this->log('Start products');
		
        $xml = new DomDocument();

        $productPackageNode = $xml->createElement('ProductPackage');
        $xml->appendChild($productPackageNode);

        $name = $xml->createAttribute('Name');
        $name->appendChild($xml->createTextNode($this->getPackageId()));
        $productPackageNode->appendChild($name);

        $xmlns = $xml->createAttribute('xmlns');
        $xmlns->appendChild($xml->createTextNode('clr-namespace:Cdiscount.Service.ProductIntegration.Pivot;assembly=Cdiscount.Service.ProductIntegration'));
        $productPackageNode->appendChild($xmlns);

        $xmlnsx = $xml->createAttribute('xmlns:x');
        $xmlnsx->appendChild($xml->createTextNode('http://schemas.microsoft.com/winfx/2006/xaml'));
        $productPackageNode->appendChild($xmlnsx);

        $productPackageCollection = $xml->createElement('ProductPackage.Products');

        foreach($this->_getProducts() as $product){

            $productNode = $xml->createElement('Product');

            $shortLabel = $xml->createAttribute('ShortLabel');
            $shortLabel->appendChild($xml->createTextNode($this->_cleanText($product->getname())));
            $productNode->appendChild($shortLabel);

            $sellerProductId = $xml->createAttribute('SellerProductId');
            $sellerProductId->appendChild($xml->createTextNode(($account->getParam('seller_product_reference') == 'sku') ? $product->getsku() : $product->getentity_id()));
            $productNode->appendChild($sellerProductId);

            $productKind = $xml->createAttribute('ProductKind');
            $productKind->appendChild($xml->createTextNode('Standard')); // Standard | Variant
            $productNode->appendChild($productKind);


            $longLabel = $xml->createAttribute('LongLabel');
            $longLabel->appendChild($xml->createTextNode($this->_cleanText($product->getname())));
            $productNode->appendChild($longLabel);

            $description = $xml->createAttribute('Description');
            $description->appendChild($xml->createTextNode($this->_cleanText($product->getDescription())));
            $productNode->appendChild($description);

            $cdiscountCategoryCode = mage::helper('MarketPlace/Categories')->getCategoryDataForProduct($product, mage::helper('Cdiscount')->getMarketPlaceName());
            $cdiscountCategoryPath = Mage::helper('Cdiscount/Category')->getCatName($cdiscountCategoryCode);
            $cdiscountCategoryPath = str_replace(' > ', '/', $cdiscountCategoryPath);

            $categoryCode = $xml->createAttribute('CategoryCode');
            $categoryCode->appendChild($xml->createTextNode($cdiscountCategoryCode));
            $productNode->appendChild($categoryCode);

            $navigation = $xml->createAttribute('Navigation');
            $navigation->appendChild($xml->createTextNode($cdiscountCategoryPath));
            $productNode->appendChild($navigation);

            $cdiscountModelName = Mage::helper('Cdiscount/Model')->getModelNameForCategory($cdiscountCategoryCode);
            $model = $xml->createAttribute('Model');
            $model->appendChild($xml->createTextNode($cdiscountModelName));
            $productNode->appendChild($model);

            $manufacturerAttribute = $configObj->getmp_manufacturer_attribute();
            if($manufacturerAttribute == "")
                throw new Exception('Manufacturer attribute is not defined in system > configuration > marketplace');
            $cdiscountBrand = Mage::getModel('MarketPlace/Brands')->getBrandForManufacturer('cdiscount', $product->getData($manufacturerAttribute));
            if (!$cdiscountBrand)
            {
                Mage::getModel('MarketPlace/Data')->updateStatus( $product->getentity_id(), $country->getId(), MDN_MarketPlace_Helper_ProductCreation::kStatusInError);
                Mage::getModel('MarketPlace/Data')->addMessage( $product->getentity_id(), 'Brand '.$product->getData($manufacturerAttribute).' not associated', $country->getId());
                continue;
            }

            $brandName = $xml->createAttribute('BrandName');
            $brandName->appendChild($xml->createTextNode($cdiscountBrand->getmpb_code())); // TODO : add brand name
            $productNode->appendChild($brandName);

            $productEanListNode = $xml->createElement('Product.EanList');
            $productNode->appendChild($productEanListNode);

            $productEanNode = $xml->createElement('ProductEan');
            $ean = $xml->createAttribute('Ean');
            $ean->appendChild($xml->createTextNode(Mage::Helper('MarketPlace/Barcode')->getBarcodeForProduct($product)));
            $productEanNode->appendChild($ean);
            $productEanListNode->appendChild($productEanNode);

            $picturesNode = $xml->createElement('Product.Pictures');
            $productNode->appendChild($picturesNode);

            // TODO : use picture helper
            // image (manually build image path to avoid magento < 1.3 issue with catalog/image helper)
            $img_url = "";
            $imageUrl = Mage::getBaseUrl('media') . 'catalog/product' . $product->getimage();
            $smallImageUrl = Mage::getBaseUrl('media') . 'catalog/product' . $product->getsmall_image();

            if (!preg_match('#no_selection#i', $imageUrl)) {
                $img_url = $imageUrl;
            } elseif (!preg_match('#no_selection#i', $smallImageUrl)) {
                $img_url = $smallImageUrl;
            }

            if($img_url == ""){
                // TODO : use default url ?
            }

            $productImage = $xml->createElement('ProductImage');
            $url = $xml->createAttribute('Uri');
            $url->appendChild($xml->createTextNode($img_url));
            $productImage->appendChild($url);
            $picturesNode->appendChild($productImage);

            $productPackageCollection->appendChild($productNode);

            $this->_submitted[] = $product->getmp_id();

        }

        $productPackageNode->appendChild($productPackageCollection);

        $content = $xml->saveXML($productPackageNode);

        $this->_save($this->_getPackageTmpPath().'/Content/Products.xml', $content);

		$this->log('Stream saved to '.$this->_getPackageTmpPath().'/Content/Products.xml');
    }

    protected function _cleanText($str){

        $retour = $str;
        $retour = str_replace('&lt;', '<', $retour);
        $retour = str_replace('&gt;', '>', $retour);
        $retour = strip_tags($retour);
        $retour = str_replace('"',"'",$retour);
        $retour = str_replace('`','',$retour);
        $retour = str_replace(array('&eacute;', '&agrave;','&egrave;','&ecirc;','&acirc;','&ocirc;','&icirc;'), array('é','à','è','ê','â','ô','î'), $retour);

        return $retour;

    }

    /**
     * Get default nodes for content types
     *
     * @see parent::_getDefaultNodes
     * @return array
     */
    protected function _getDefaultNodes(){

        return array_merge(
                array(
                    array(
                        'Extension' => 'png',
                        'ContentType' => "image/jpeg"
                    )
                ),
                parent::_getDefaultNodes()
        );

    }

    protected function log($msg)
    {
        Mage::log($msg, null, 'cdiscount_products_export.log');
    }
	
}
