<?php
$this->startSetup();

$resource = Mage::getSingleton('core/resource');

$readConnection = $resource->getConnection('core_read');

$query = "SELECT sku, general_id FROM m2epro_amazon_listing_product WHERE sku != ''";

$results = $readConnection->fetchAll($query);

for ($i = 0; $i < sizeof($results); $i++) {
    if ($product = Mage::getModel('catalog/product')->loadByAttribute('sku', $results[$i]['sku'])) {
        $data = array('asin' => $results[$i]['general_id']);
        $product->setData($data);
        $product->save();
        unset($product);
    }
}

$this->endSetup();
