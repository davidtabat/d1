<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run('
insert into `' . $this->getTable('datafeedmanager_attributes') . '`(`attribute_id`,`attribute_name`,`attribute_script`) values 
(NULL,\'shipping_cost\',\' 
if ($product->package_id) {
    $service = $product->getAttributeText("package_id");

    if ($service == "Europalette (120 cm x 80 cm)") {
        $price = "75.00 EUR";
    }
    if ($service == "Paket") {
        $price = "8.90 EUR";
    }
    if ($service == "kleine Palette (80 cm x 60 cm)") {
        $price = "35.00 EUR";
    }

    $shippingXml="<g:shipping>
<g:country>DE</g:country>
<g:service>$service</g:service>
<g:price>$price</g:price>
</g:shipping>
";
    return $shippingXml;
}
 \');
 ');

$installer->endSetup();
