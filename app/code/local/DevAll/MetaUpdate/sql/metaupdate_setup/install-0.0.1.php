<?php
$resource = Mage::getSingleton('core/resource');
$writeConnection = $resource->getConnection('core_write');

$query = "UPDATE {$resource->getTableName('catalog_product_entity_varchar')} SET value = REPLACE(value, 'Generalüberholt', 'Aufbereitet') " .
          "WHERE attribute_id = 84 and store_id = 11";

$writeConnection->query($query);