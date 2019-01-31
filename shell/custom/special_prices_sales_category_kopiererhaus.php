<?php
ini_set('display_errors', 1);
ini_set('memory_limit', '-1');

header('Content-Type: text/plain; charset=utf-8');

require_once 'app/Mage.php';
umask(0);
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
$userModel = Mage::getModel('admin/user');
$userModel->setUserId(0);

// '10' = Druckerhaus24 | 11 = Kopiererhaus
$col = Mage::getModel('catalog/product')
	->getCollection()
	->addStoreFilter(11)
	->addAttributeToSelect(array('special_price'), 'inner')
	->addAttributeToFilter('special_price', array("neq"=>0))
	->load();

$show = false;

foreach ($col as $_product) {
	$p = Mage::getModel('catalog/product')->load($_product->getId());
	if ($show) { print $_product->getId(). " - ". $_product->getSpecialPrice() . " - " . $p->getName()  . " - "; }

	$categories = $_product->getCategoryIds();
	$categoryId = 839;

	if(!in_array($categoryId, $categories)) {
		$categories[] = $categoryId;
		$_product->setCategoryIds($categories);
		$_product->save();
		if ($show) { echo "UPDATED \n"; }
	} else {
		if ($show) { echo "already at sales category \n"; }
	}

}
