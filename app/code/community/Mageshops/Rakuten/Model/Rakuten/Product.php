<?php
/**
 * @category  	Mageshops
 * @package    	Mageshops_Rakuten
 * @license    	http://license.mageshops.com/  Unlimited Commercial License
 * @copyright 	mageSHOPS.com 2014
 * @author 	    Taras Kapushchak with THANKS to mageSHOPS.com <info@mageshops.com>
 */

/**
 * Rakuten Product model class
 */
class Mageshops_Rakuten_Model_Rakuten_Product extends Mageshops_Rakuten_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('rakuten/rakuten_product');
    }

    protected function _beforeSave()
    {
        // Support for magento 1.5
//        $this->setData('updated_at', Varien_Date::now());
        $this->setData('updated_at', date('Y-m-d H:i:s'));

        return parent::_beforeSave();
    }

    public function getType()
    {
        return $this->getHasVariants() == 0 ? 'simple' : 'configurable';
    }

    public function getProductVariantSkus()
    {
        $sku = array();

        $variants = Mage::getModel('rakuten/rakuten_product_variant')->getCollection()
            ->addFieldToSelect('variant_sku')
            ->addFieldToFilter('rakuten_product_id', array('eq' => $this->getRakutenId()));

        foreach ($variants as $variant) {
            $sku[] = (string) $variant->getVariantSku();
        }

        return $sku;
    }

    public function getProductVariants()
    {
        if ($variantsLabel = $this->getVariantsLabel()) {
            $variantsLabels = explode(',', $variantsLabel);
            foreach ($variantsLabels as $variantsLabel) {
                $variantsLabel[] = (string) $variantsLabel;
            }
            return $variantsLabel;
        }
        return array();
    }
}
