<?php
ini_set('display_errors', 1);
ini_set('memory_limit', '-1');

header('Content-Type: text/plain; charset=utf-8');
require_once 'app/Mage.php';
umask(0);
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
$userModel = Mage::getModel('admin/user');
$userModel->setUserId(0);

#select products that belong to druckerhaus24 category eBay Feed (ID: 2103

$c_id = 2103; #eBay Feed (ID: 2103
$category = new Mage_Catalog_Model_Category();
$category->load($c_id);

$collection = $category->getProductCollection();
$collection->addStoreFilter(10); #druckerhaus24
$collection->addAttributeToSelect('*');
$collection->setOrder('entity_id', 'DESC');

echo "UPDATED PRODUCTS FOR EBAY FEED FIX\n\n";

$ids = array();
foreach ($collection as $_product) {
    $active = true;
    if ($active) {
        $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product->getId());
        if ($stockItem->getId() && $stockItem->getUseConfigBackorders() == 1) {
            $ids[] = $_product->getId();
            echo $_product->getId() . ' - ' . $_product->getName() . "\n";

            $stockItem->setBackorders(0); # ORIGINAL STATE 0 
            $stockItem->setUseConfigBackorders(0); # ORIGINAL STATE 1
            $stockItem->save();
        }
    }
}

echo "Updated products:\n\n" . 'SELECT backorders, use_config_backorders FROM `cataloginventory_stock_item` where product_id in (' . implode(",", $ids) . ") order by product_id desc;\n\n";
