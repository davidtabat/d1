<?php

$installer = $this;

$installer->startSetup();

$installer->run(
        "ALTER TABLE `{$this->getTable('market_place_data')}`
         ADD mp_marketplace_status VARCHAR(50);"
);

$installer->endSetup();
?>
