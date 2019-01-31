<?php
/**
 * @category  	Mageshops
 * @package    	Mageshops_Rakuten
 * @license    	http://license.mageshops.com/  Unlimited Commercial License
 * @copyright 	mageSHOPS.com 2014
 * @author 	    Taras Kapushchak with THANKS to mageSHOPS.com <info@mageshops.com>
 */


/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();


$table = $installer->getConnection()
    ->newTable($installer->getTable('rakuten/rakuten_product'))
    ->addColumn('rakuten_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
        ), 'Rakuten Id')
    ->addColumn('sku', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
            'nullable'  => false,
        ), 'Sku')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 100, array(
            'nullable'  => false,
        ), 'Name')
    ->addColumn('description', Varien_Db_Ddl_Table::TYPE_LONGVARCHAR, null, array(
            'nullable'  => true,
        ), 'Description')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Creation Time')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
            'nullable'  => false,
//             'default'   => 1,
        ), 'Update Time')
    ->addColumn('price', Varien_Db_Ddl_Table::TYPE_FLOAT, null, array(
        ), 'Price')
    ->addColumn('price_reduced', Varien_Db_Ddl_Table::TYPE_FLOAT, null, array(
        ), 'Special Price')
    ->addColumn('qty', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
        ), 'Qty')
    ->addColumn('visible', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'default'   => '0',
        ), 'Visibility')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'default'   => '0',
        ), 'Status')
    ->addColumn('external_shop_category_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        ), 'Category Ids List')
    ->addColumn('has_variants', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
            'nullable'  => true,
        ), 'Type')
    ->addColumn('variants_label', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        ), 'Variants Label')
    ->addColumn('product_hash', Varien_Db_Ddl_Table::TYPE_VARCHAR, 32, array(
        ), 'Product Hash')
    ->addIndex(
        'rakuten_product_rakuten_id',
        array('rakuten_id'))
    ->addIndex(
        'rakuten_product_sku',
        array('sku'));
//     ->setComment('Rakuten Products');


$installer->getConnection()->createTable($table);


$table = $installer->getConnection()
    ->newTable($installer->getTable('rakuten/rakuten_product_variant'))
    ->addColumn('variant_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
        ), 'Variant Id')
    ->addColumn('rakuten_product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
        ), 'Id')
    ->addColumn('variant_sku', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
            'nullable'  => false,
        ), 'Varaint Sku')
    ->addColumn('variant_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 100, array(
            'nullable'  => false,
        ), 'Name')
    ->addColumn('price', Varien_Db_Ddl_Table::TYPE_FLOAT, null, array(
        ), 'Price')
    ->addColumn('price_reduced', Varien_Db_Ddl_Table::TYPE_FLOAT, null, array(
        ), 'Special Price')
    ->addColumn('qty', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
        ), 'Qty')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
            'unsigned'  => true,
            'nullable'  => false,
        ), 'Status')
    ->addIndex(
        'rakuten_product_variant_id',
        array('variant_id'))
    ->addIndex(
        'rakuten_product_rakuten_product_id',
        array('rakuten_product_id'))
    ->addIndex(
        'rakuten_product_variant_sku',
        array('variant_sku'))
    ->addForeignKey(
//         $installer->getFkName(
            'rakuten_product_rakuten_id',
//         ),
        'rakuten_product_id', $installer->getTable('rakuten/rakuten_product'), 'rakuten_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE);
//     ->setComment('Rakuten Product Variant');
$installer->getConnection()->createTable($table);


// $table = $installer->getConnection()
//     ->newTable($installer->getTable('rakuten/rakuten_product'))
//     ->addColumn('rakuten_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
//             'unsigned'  => true,
//             'nullable'  => false,
//             'primary'   => true,
//         ), 'Rakuten Id')
//     ->addColumn('sku', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
//             'nullable'  => false,
//         ), 'Sku')
//     ->addColumn('name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 100, array(
//             'nullable'  => false,
//         ), 'Name')
//     ->addColumn('description', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
//             'nullable'  => true,
//         ), 'Description')
//     ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
//         ), 'Creation Time')
//     ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
//             'nullable'  => false,
//             'default'   => 1,
//         ), 'Update Time')
//     ->addColumn('price', Varien_Db_Ddl_Table::TYPE_FLOAT, null, array(
//         ), 'Price')
//     ->addColumn('price_reduced', Varien_Db_Ddl_Table::TYPE_FLOAT, null, array(
//         ), 'Special Price')
//     ->addColumn('qty', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
//             'unsigned'  => true,
//         ), 'Qty')
//     ->addColumn('visible', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
//             'unsigned'  => true,
//             'nullable'  => false,
//             'default'   => '0',
//         ), 'Visibility')
//     ->addColumn('status', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
//             'unsigned'  => true,
//             'nullable'  => false,
//             'default'   => '0',
//         ), 'Status')
//     ->addColumn('external_shop_category_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
//         ), 'Category Ids List')
//     ->addColumn('has_variants', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
//             'nullable'  => true,
//         ), 'Type')
//     ->addColumn('variants_label', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
//         ), 'Variants Label')
//     ->addColumn('product_hash', Varien_Db_Ddl_Table::TYPE_VARCHAR, 32, array(
//         ), 'Product Hash')
//     ->addIndex(
//         $installer->getIdxName('rakuten/rakuten_product', array('rakuten_id')),
//         array('rakuten_id'))
//     ->addIndex(
//         $installer->getIdxName('rakuten/rakuten_product', array('sku')),
//         array('sku'))
//     ->setComment('Rakuten Products');
//
//
// $installer->getConnection()->createTable($table);
//
// $table = $installer->getConnection()
//     ->newTable($installer->getTable('rakuten/rakuten_product_variant'))
//     ->addColumn('variant_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
//             'unsigned'  => true,
//             'nullable'  => false,
//             'primary'   => true,
//         ), 'Variant Id')
//     ->addColumn('rakuten_product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
//             'unsigned'  => true,
//             'nullable'  => false,
//             'primary'   => true,
//         ), 'Id')
//     ->addColumn('variant_sku', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
//             'nullable'  => false,
//         ), 'Varaint Sku')
//     ->addColumn('variant_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 100, array(
//             'nullable'  => false,
//         ), 'Name')
//     ->addColumn('price', Varien_Db_Ddl_Table::TYPE_FLOAT, null, array(
//         ), 'Price')
//     ->addColumn('price_reduced', Varien_Db_Ddl_Table::TYPE_FLOAT, null, array(
//         ), 'Special Price')
//     ->addColumn('qty', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
//             'unsigned'  => true,
//         ), 'Qty')
//     ->addColumn('status', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
//             'unsigned'  => true,
//             'nullable'  => false,
//         ), 'Status')
//     ->addIndex(
//         $installer->getIdxName('rakuten/rakuten_product', array('variant_id')),
//         array('variant_id'))
//     ->addIndex(
//         $installer->getIdxName('rakuten/rakuten_product', array('rakuten_product_id')),
//         array('rakuten_product_id'))
//     ->addIndex(
//         $installer->getIdxName('rakuten/rakuten_product', array('variant_sku')),
//         array('variant_sku'))
//     ->addForeignKey(
//         $installer->getFkName(
//             'rakuten/rakuten_product_variant',
//             'rakuten_product_id',
//             'rakuten/rakuten_product',
//             'rakuten_id'
//         ),
//         'rakuten_product_id', $installer->getTable('rakuten/rakuten_product'), 'rakuten_id',
//         Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
//     ->setComment('Rakuten Product Variant');
// $installer->getConnection()->createTable($table);


$installer->endSetup();
