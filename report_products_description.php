<?php 
if(!$_GET['enable'] && $_GET['enable'] != 'true') die('Contact admin for more info.');
header('Content-Type: text/csv; charset=utf-8');

require_once 'app/Mage.php';
umask(0);
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
$userModel = Mage::getModel('admin/user');
$userModel->setUserId(0);

$storeName = array(
    0 => 'Default',
    10 => 'Druckerhaus24',
    11 => 'Kopiererhaus',
);

$storeId = Mage::app()->getRequest()->getParam('store') == null ? 0 : Mage::app()->getRequest()->getParam('store');
$websiteId = Mage::app()->getStore($storeId)->getWebsiteId();

header('Content-Disposition: attachment; filename=' . date('dmY_His_') . $storeName[$storeId] .  "_{$storeId}_report_products.csv");

if (!in_array((int)$storeId, array(10, 11, (int)Mage::app()->getWebsite()->getDefaultGroup()->getDefaultStoreId()))) {
    die('store not found');
}

$resource = Mage::getSingleton('core/resource');
$readConnection = $resource->getConnection('core_read');

$query_stores = trim("
SELECT CHAR_LENGTH(IF(at_description.value_id > 0, at_description.value, at_description_default.value)) AS 'caracteres',
       CASE CHAR_LENGTH(IF(at_description.value_id > 0, at_description.value, at_description_default.value))
           WHEN CHAR_LENGTH(IF(at_description.value_id > 0, at_description.value, at_description_default.value)) LIKE '%.%'THEN 'no'
           ELSE 'yes'
       END AS 'dot',
       CHAR_LENGTH(IF(at_description.value_id > 0, at_description.value, at_description_default.value)) AS 'size',
       IF(at_name.value_id > 0, at_name.value, at_name_default.value) AS `name`,
       IF(at_description.value_id > 0, at_description.value, at_description_default.value) AS `description`,
       `e`.*
FROM `catalog_product_entity` AS `e`
INNER JOIN `catalog_product_website` AS `product_website` ON product_website.product_id = e.entity_id
AND product_website.website_id = '{$websiteId}'
INNER JOIN `catalog_product_entity_varchar` AS `at_name_default` ON (`at_name_default`.`entity_id` = `e`.`entity_id`)
AND (`at_name_default`.`attribute_id` = '71')
AND `at_name_default`.`store_id` = 0
LEFT JOIN `catalog_product_entity_varchar` AS `at_name` ON (`at_name`.`entity_id` = `e`.`entity_id`)
AND (`at_name`.`attribute_id` = '71')
AND (`at_name`.`store_id` = {$storeId})
INNER JOIN `catalog_product_entity_text` AS `at_description_default` ON (`at_description_default`.`entity_id` = `e`.`entity_id`)
AND (`at_description_default`.`attribute_id` = '72')
AND `at_description_default`.`store_id` = 0
LEFT JOIN `catalog_product_entity_text` AS `at_description` ON (`at_description`.`entity_id` = `e`.`entity_id`)
AND (`at_description`.`attribute_id` = '72')
AND (`at_description`.`store_id` = {$storeId})
WHERE (IF(at_description.value_id > 0, at_description.value, at_description_default.value) IS NULL)
  OR (CHAR_LENGTH(IF(at_description.value_id > 0, at_description.value, at_description_default.value)) < 500)
  OR (CHAR_LENGTH(IF(at_description.value_id > 0, at_description.value, at_description_default.value)) = '')
ORDER BY CHAR_LENGTH(IF(at_description.value_id > 0, at_description.value, at_description_default.value)) ASC");

$query_default = trim("
SELECT CHAR_LENGTH(at_description_default.value) AS 'caracteres',
       CASE CHAR_LENGTH(at_description_default.value)
           WHEN CHAR_LENGTH(at_description_default.value) LIKE '%.%'THEN 'no'
           ELSE 'yes'
       END AS 'dot',
       at_name.value AS `name`,
       at_description_default.value AS `description`,
       `e`.*
FROM `catalog_product_entity` AS `e`
LEFT JOIN `catalog_product_entity_varchar` AS `at_name` ON (`at_name`.`entity_id` = `e`.`entity_id`)
AND (`at_name`.`attribute_id` = '71')
AND (`at_name`.`store_id` = 0)
LEFT JOIN `catalog_product_entity_text` AS `at_description_default` ON (`at_description_default`.`entity_id` = `e`.`entity_id`)
AND (`at_description_default`.`attribute_id` = '72')
AND `at_description_default`.`store_id` = 0
WHERE (at_description_default.value IS NULL)
  OR (CHAR_LENGTH(at_description_default.value) < 500)
  OR (CHAR_LENGTH(at_description_default.value) = '')
ORDER BY CHAR_LENGTH(at_description_default.value) ASC");

$collection = $readConnection->fetchAll($storeId==0 ? $query_default : $query_stores);

$output = fopen('php://output', 'w');

fputcsv($output, array('ID Number','Contains dot (yes / no)', 'Title', 'Description', 'Websites'));

$websites = array(
    7 => 'Amazon',
    1 => 'Base',
    4 => 'druckerhaus24',
    6 => 'Ebay GmbH',
    8 => 'Imprireco GmbH',
    3 => 'Kopiererhaus',
    5 => 'Rakuten',
    9 => 'Tech Tiger GmbH',
    2 => 'Druckerfachhandel (Verwaltung)'
);

foreach ($collection as $prod) {
    $product = Mage::getModel('catalog/product')->load($prod['entity_id']);
    
    $webs = '';
    foreach($product->getWebsiteIds() as $websiteId){
        $webs .= $websites[$websiteId] . ",";
    }
    
    fputcsv($output, array($prod['entity_id'], $prod['dot'], $prod['name'], $prod['description'], $webs));
}
