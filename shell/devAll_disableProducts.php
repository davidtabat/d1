<?php

require_once 'abstract.php';

class DevAll_Disable_Products extends Mage_Shell_Abstract
{
    public function run()
    {
        $storeCode = 'druckerhaus24';
        $storeId = Mage::getModel('core/store')->load($storeCode, 'code')->getId();

        /* @var $productCollection Mage_Catalog_Model_Product */
        $productCollection = Mage::getModel('catalog/product')
            ->getCollection()
            ->setStoreId($storeId)
            ->addAttributeToSelect('imported_at');

        // time before import started
        $importStart = $this->getArg('import_start');
        // make sure import_start was passed
        if ($importStart != false) {
            $importStart = strftime("%Y-%m-%d %H:%M:%S", $importStart);
        } else {
            echo 'Please specify import_start: --import_start ${IMPORT_START}';
            return false;
        }
        foreach ($productCollection as $product) {
            // getting imported_at date
            $importedAt = $product->getImportedAt();
            if ($product->getImportedAt() != null && $importStart > $importedAt) {
                $productId = $product->getId();
//                // disable the product
                Mage::getModel('catalog/product_status')->updateProductStatus($productId, $storeId, Mage_Catalog_Model_Product_Status::STATUS_DISABLED);
            }
        }
    }
}

$shell = new DevAll_Disable_Products();
$shell->run();