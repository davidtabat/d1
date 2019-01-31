<?php

/**
 * @category    Mageshops
 * @package     Mageshops_Rakuten
 * @license     http://license.mageshops.com/  Unlimited Commercial License
 * @copyright   mageSHOPS.com 2014 - 2015
 * @author      Taras Kapushchak & Viktors Stepucevs & Kristaps Rjabovs with THANKS to mageSHOPS.com <info@mageshops.com>
 */
class Mageshops_Rakuten_Helper_Variant extends Mageshops_Market_Helper_Data
{

    protected $_config = array();

    protected function getConfig()
    {
        if (!$this->_config) {
            $this->_config['sync_bundle'] = Mage::getStoreConfig('nn_market/rakuten_product/sync_bundle');
            $this->_config['sync_grouped'] = Mage::getStoreConfig('nn_market/rakuten_product/sync_grouped');
        }
        return $this;
    }

    protected function isSyncBundle()
    {
        $this->getConfig();
        if ($this->_config['sync_bundle']) {
            return true;
        }
        return false;
    }

    protected function isSyncGrouped()
    {
        $this->getConfig();
        if ($this->_config['sync_grouped']) {
            return true;
        }
        return false;
    }

    public function getAllowedProductTypes($useSimple = true)
    {
        $types = $useSimple ? array('simple', 'configurable') : array('configurable');
        if ($this->isSyncBundle()) {
            $types[] = 'bundle';
        }
        if ($this->isSyncGrouped()) {
            $types[] = 'grouped';
        }
        return $types;
    }

    public function isVariant(Mage_Catalog_Model_Product $product)
    {
        $productTypeId = $product->getTypeId();

        if (($productTypeId == 'configurable') || ($this->isSyncBundle() && $productTypeId == 'bundle') || ($this->isSyncGrouped() && $productTypeId == 'grouped') || ($product->getHasOptions() && $product->getProductOptionsCollection()->getSize() > 0)
        ) {
            return true;
        }

        return false;
    }

    public function isSimple(Mage_Catalog_Model_Product $product)
    {
        if ($product->getTypeId() == 'simple') {
            return true;
        }

        return false;
    }
    
    
    /**
     * Gets product options from tab custom options
     * 
     * @param Mage_Catalog_Model_Product $product
     * @return array
     * @throws Exception
     */
    public function getProductOptions(Mage_Catalog_Model_Product $product)
    {
        $attributeOptions = array();

        if ($product->getTypeId() == 'simple') {

            $options = $product->getProductOptionsCollection();
            
            foreach ($options as $option) {

                if (!in_array($option->getType(), array('drop_down', 'radio'))) {
                    throw new Exception($this->__('Simple product [%s] should have only drop down or radio custom options', $product->getSku()));
                }

                $attrId = $option->getOptionId();

                $attributeOptions[$attrId]['attribute_id'] = $option->getOptionId();
                $attributeOptions[$attrId]['attribute_code'] = $option->getOptionId();
                $attributeOptions[$attrId]['label'] = $option->getTitle();

                foreach ($option->getValues() as $attribute) {
                    $attributeOptions[$attrId]['values'][$attribute->getOptionTypeId()] = array(
                        'label' => $attribute->getTitle(),
                        'is_percent' => $attribute->getPriceType() == 'percent' ? 1 : 0,
                        'pricing_value' => $attribute->getPrice(),
                    );
                }
            }
        } elseif ($this->isSyncBundle() && $product->getTypeId() == 'bundle') {

            $helper = Mage::helper('rakuten');

            $typeInstance = $product->getTypeInstance(true);

            $optionCollection = $typeInstance->getOptionsCollection($product);
            

            $selectionCollection = $typeInstance->getSelectionsCollection(
                $typeInstance->getOptionsIds($product), $product
            );

            $options = $optionCollection->appendSelections($selectionCollection, false, true);

            foreach ($options as $option) {
                if (!in_array($option->getType(), array('select', 'radio'))) {
                    throw new Exception($this->__('Bundle product [%s] should have only drop down or radio custom options', $product->getSku()));
                }

                $attrId = $option->getOptionId();
                $attributeOptions[$attrId]['attribute_id'] = $attrId;
                $attributeOptions[$attrId]['attribute_code'] = $attrId;
                $attributeOptions[$attrId]['label'] = $option->getDefaultTitle();

                foreach ($option->getSelections() as $selection) {
                    if ($selectionId = $selection->getId()) {
                        $name = $helper->prepareName($selection);
                        if (mb_strlen($name) > 50) {
                            throw new Exception($this->__('Product "%s" has too long name to be used as variant (%s).', $selection->getSku(), $name));
                        }
                        $attributeOptions[$attrId]['values'][$selection->getSku()] = array(
                            'label' => $name,
                            'is_percent' => 0,
                            'pricing_value' => $selection->getPrice(),
                            'attr_id' => $attrId,
                            'sku' => $selection->getSku(),
                            'stock' => $selection->getStockItem()->getQty(),
                            'variant_label' => $option->getDefaultTitle(),
                            'available' => $selection->getStatus() == 1 ? 1 : 0
                        );
                    }
                }
            }
        } elseif ($this->isSyncGrouped() && $product->getTypeId() == 'grouped') {

            $attrId = $this->__('Option');

            $attributeOptions[$attrId]['attribute_id'] = 'option';
            $attributeOptions[$attrId]['attribute_code'] = 'entity_id';
            $attributeOptions[$attrId]['label'] = $this->__('Option');

            $associatedProducts = $product->getTypeInstance(true)->getAssociatedProducts($product);
            $helper = Mage::helper('rakuten');

            foreach ($associatedProducts as $childProduct) {
                if ($childId = $childProduct->getId()) {
                    $name = $helper->prepareName($childProduct);
                    if (mb_strlen($name) > 50) {
                        throw new Exception($this->__('Product "%s" has too long name to be used as variant (%s).', $childProduct->getSku(), $name));
                    }
                    $attributeOptions[$attrId]['values'][$childId] = array(
                        'label' => $name,
                        'is_percent' => 0,
                        'pricing_value' => $childProduct->getPrice(),
                    );
                }
            }
        } else {

            $productAttributeOptions = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);

            foreach ($productAttributeOptions as $productAttribute) {

                $attrId = $productAttribute['attribute_id'];

                $attributeOptions[$attrId]['attribute_id'] = $productAttribute['attribute_id'];
                $attributeOptions[$attrId]['attribute_code'] = $productAttribute['attribute_code'];
                $attributeOptions[$attrId]['label'] = $productAttribute['label'];
                foreach ($productAttribute['values'] as $attribute) {
                    $attributeOptions[$attrId]['values'][$attribute['value_index']] = array(
                        'label' => $attribute['label'],
                        'is_percent' => $attribute['is_percent'],
                        'pricing_value' => (float)$attribute['pricing_value'],
                    );
                }
            }
        }

        return $attributeOptions;
    }

    public function getStock(Mage_Catalog_Model_Product $product, $childProduct)
    {
        if ($product->getTypeId() == 'simple') {
            $qty = (int)$childProduct->getQty();
        } else {
            $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($childProduct);
            $qty = (int)$stock->getQty();
        }

        return $qty;
    }

    public function getUsedProducts(Mage_Catalog_Model_Product $product)
    {
        $productTypeId = $product->getTypeId();

        if ($productTypeId == 'simple') {
            $options = array();
            foreach ($product->getProductOptionsCollection() as $item) {
                $options[] = $item;
            }
            $usedProducts = $this->_getUsedProductsFromOptions($product, $options);
        } elseif ($this->isSyncBundle() && $productTypeId == 'bundle') {
            $usedProducts = $product->getTypeInstance(true)->getSelectionsCollection(
                $product->getTypeInstance(true)->getOptionsIds($product), $product
            );
        } elseif ($this->isSyncGrouped() && $productTypeId == 'grouped') {
            $usedProducts = $product->getTypeInstance(true)->getAssociatedProducts($product);
        } else {
            // $usedProducts = $product->getTypeInstance(true)->getUsedProducts(null, $product);
            $product = Mage::getModel('catalog/product_type_configurable')->setProduct($product);
            $usedProducts = $product->getUsedProductCollection()->addAttributeToSelect('*')->addFilterByRequiredOptions();
        }

        return $usedProducts;
    }

    protected function _getUsedProductsFromOptions(Mage_Catalog_Model_Product $product, array $options)
    {
        $children = array();
        $option = array_pop($options);

        if (count($options) < 1) {

            $sku_ = $product->getSku() . '_';
            foreach ($option->getValues() as $key => $value) {
                $child = new Varien_Object();
                $child->setSku($sku_ . $key);
                $child->setStatus($product->getStatus());
                $child->setQty(Mage::getModel('cataloginventory/stock_item')->loadByProduct($product)->getQty());
                $child->setData('variant_' . $value->getOptionId(), $key);
                $children[] = $child;
            }
        } else {

            foreach ($option->getValues() as $key => $value) {
                $subChildren = $this->_getUsedProductsFromOptions($product, $options);
                foreach ($subChildren as $child) {
                    $child->setSku($child->getSku() . '_' . $key);
                    $child->setData('variant_' . $value->getOptionId(), $key);
                    $children[] = $child;
                }
            }
        }

        return $children;
    }

    public function getProductHash(Mage_Catalog_Model_Product $product)
    {
        $json = $product->toJson();

        if ($product->getTypeId() == 'configurable') {
            $usedProducts = $product->getTypeInstance(true)->getUsedProducts(null, $product);
            foreach ($usedProducts as $childProduct) {
                $json .= $childProduct->toJson();
            }
        }

        if ($this->isSyncBundle() && $product->getTypeId() == 'bundle') {
            $usedProducts = $product->getTypeInstance(true)->getSelectionsCollection(
                $product->getTypeInstance(true)->getOptionsIds($product), $product
            );
            foreach ($usedProducts as $childProduct) {
                $json .= $childProduct->toJson();
            }
        }

        if ($this->isSyncGrouped() && $product->getTypeId() == 'grouped') {
            $usedProducts = $product->getTypeInstance(true)->getAssociatedProducts($product);
            foreach ($usedProducts as $childProduct) {
                $json .= $childProduct->toJson();
            }
        }

        return md5($json);
    }

    public function isChild(Mage_Catalog_Model_Product $product)
    {
        if ($product->getKeephidden()) {
            return true;
        }

        $configurable = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product->getId());
        if (count($configurable) > 0) {
            return true;
        }

        return false;
    }

}
