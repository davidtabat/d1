<?php

class MDN_Cdiscount_Helper_Brand extends Mage_Core_helper_Abstract {


    public function synchronize()
    {
        $cdiscountBrand = $this->getListFromCdiscount();
        $existingBrands = $this->getExistingBrands();

        $missing = array_diff($cdiscountBrand, $existingBrands);
        foreach($missing as $item)
        {
            Mage::getModel('MarketPlace/Brands')
                ->setmpb_code($item)
                ->setmpb_label($item)
                ->setmpb_marketplace_id('cdiscount')
                ->save();
        }

        return count($missing);
    }

    public function getListFromCdiscount()
    {
        $result = Mage::helper('Cdiscount/Services')->getBrandList();
        $string = $result['content'];
        $string = str_replace('xmlns=', 'ns=', $string);
        $string = str_replace('a:BrandList', 'BrandList', $string);
        $xml = new SimpleXMLElement($string);

        $list = array();
        $nodes = $xml->xpath('/s:Envelope/s:Body/GetBrandListResponse/GetBrandListResult/BrandList/Brand/BrandName');
        foreach($nodes as $node)
        {
            $list[] = ((string)$node);
        }
        return $list;
    }

    protected function getExistingBrands()
    {
        $list = array();
        $collection =  Mage::getModel('MarketPlace/Brands')->getCollection()->addFieldToFilter('mpb_marketplace_id', 'cdiscount');
        foreach($collection as $item)
        {
            $list[] = $item->getmpb_code();
        }
        return $list;
    }

    public function autoAssociation()
    {
        $attribute = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', Mage::getModel('MarketPlace/Configuration')->getGeneralConfigObject()->getmp_brand_attribute());
        if (!$attribute->getId())
            throw new Exception('Brand attribute is not set');
        $manufacturers = $valuesCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
            ->setAttributeFilter($attribute->getData('attribute_id'))
            ->setStoreFilter(0, false);
        $count = 0;
        foreach($manufacturers as $manufacturer)
        {
            $brand = Mage::getModel('MarketPlace/Brands')->getCollection()->addFieldToFilter('mpb_label', $manufacturer->getValue())->getFirstItem();
            if ($brand->getId())
            {
                if (!$brand->getmpb_manufacturer_id())
                {
                    $brand->setmpb_manufacturer_id($manufacturer->getoption_id())->save();
                    $count++;
                }
            }
        }

        return $count;
    }

}