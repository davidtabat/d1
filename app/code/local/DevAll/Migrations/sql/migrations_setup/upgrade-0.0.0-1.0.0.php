<?php

$resource = Mage::getSingleton('core/resource');

$writeConnection = $resource->getConnection('core_write');

$core_store = $resource->getTableName('core_store');
$core_config_data = $resource->getTableName('core_config_data');

$query = "DELETE FROM {$core_store} WHERE group_id IN ('3','4','5','6','10','11','13','14')";
$query2 = "DELETE FROM {$core_config_data} WHERE scope = 'stores' AND scope_id IN ('4','6','7','8','12','13','15','16')";

$writeConnection->query($query);
$writeConnection->query($query2);