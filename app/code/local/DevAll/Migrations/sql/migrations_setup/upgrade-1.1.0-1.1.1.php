<?php
$this->startSetup();

$resource = Mage::getSingleton('core/resource');

$readConnection = $resource->getConnection('core_read');

$query = "SELECT sku, general_id FROM m2epro_amazon_listing_product WHERE sku != ''";

$results = $readConnection->fetchAll($query);

for ($i = 0; $i < sizeof($results); $i++) {
    if ($productId = Mage::getModel('catalog/product')->getResource()->getIdBySku($results[$i]['sku'])) {
        $product = Mage::getModel('catalog/product')->load($productId);
        $product->setData('asin', $results[$i]['general_id']);
        $product->getResource()->saveAttribute($product, 'asin');
        unset($product);
    }
}

$this->endSetup();
