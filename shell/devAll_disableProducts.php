<?php

require_once 'abstract.php';

class DevAll_Disable_Products extends Mage_Shell_Abstract
{
    public function run()
    {
        /* @var $productCollection Mage_Catalog_Model_Product */
        $productCollection = Mage::getModel('catalog/product')
            ->getCollection()
            ->addAttributeToSelect('imported_at');

        // subtract 10h to the current datetime to give additional time to the program for running
        $today = date("Y-m-d H:m:s", (time()-(60*60*10)));

        // druckerhaus24
        $storeid=10;

        foreach ($productCollection as $product) {
            // getting imported_at date
            $importedAt = $product->getImportedAt();
            if ($product->getImportedAt() != null && $today > $importedAt) {
                $productid = $product->getId();
                // disable the product
                Mage::getModel('catalog/product_status')->updateProductStatus($productid, $storeid, Mage_Catalog_Model_Product_Status::STATUS_DISABLED);
            }
        }
    }
}

$shell = new DevAll_Disable_Products();
$shell->run();