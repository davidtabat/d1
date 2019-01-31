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
    ->newTable($installer->getTable('rakuten/rakuten_order'))
    ->addColumn('rakuten_order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'auto_increment' => true,
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
        ), 'Rakuten Id')
    ->addColumn('order_no', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
            'nullable'  => false,
        ), 'Order No')
    ->addColumn('total', Varien_Db_Ddl_Table::TYPE_FLOAT, null, array(
        ), 'Total')
    ->addColumn('shipping', Varien_Db_Ddl_Table::TYPE_FLOAT, null, array(
        ), 'Shipping Cost')
    ->addColumn('max_shipping_date', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Max Shipping Date')
    ->addColumn('payment', Varien_Db_Ddl_Table::TYPE_VARCHAR, 32, array(
        ), 'Payment')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_VARCHAR, 32, array(
        ), 'Status')
    ->addColumn('invoice_no', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
        ), 'Invoice No')
    ->addColumn('comment_client', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        ), 'Comment Client')
    ->addColumn('comment_merchant', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        ), 'Comment Merchant')
    ->addColumn('created', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Created')

    // Customer
    ->addColumn('gender', Varien_Db_Ddl_Table::TYPE_VARCHAR, 32, array(
        ), 'Status')
    ->addColumn('first_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
        ), 'Status')
    ->addColumn('last_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
        ), 'Status')
    ->addColumn('company', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
        ), 'Status')
    ->addColumn('street', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        ), 'Status')
    ->addColumn('street_no', Varien_Db_Ddl_Table::TYPE_VARCHAR, 32, array(
        ), 'Status')
    ->addColumn('address_add', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        ), 'Status')
    ->addColumn('zip_code', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Status')
    ->addColumn('city', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
        ), 'Status')
    ->addColumn('country', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
        ), 'Status')
    ->addColumn('email', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        ), 'Status')
    ->addColumn('phone', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
        ), 'Status')

    // Delivery address
    ->addColumn('delivery_gender', Varien_Db_Ddl_Table::TYPE_VARCHAR, 32, array(
        ), 'Status')
    ->addColumn('delivery_first_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
        ), 'Status')
    ->addColumn('delivery_last_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
        ), 'Status')
    ->addColumn('delivery_company', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
        ), 'Status')
    ->addColumn('delivery_street', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        ), 'Status')
    ->addColumn('delivery_street_no', Varien_Db_Ddl_Table::TYPE_VARCHAR, 32, array(
        ), 'Status')
    ->addColumn('delivery_address_add', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        ), 'Status')
    ->addColumn('delivery_zip_code', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Status')
    ->addColumn('delivery_city', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
        ), 'Status')
    ->addColumn('delivery_country', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
        ), 'Status')

    // Coupon
    ->addColumn('coupon_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Status')
    ->addColumn('coupon_total', Varien_Db_Ddl_Table::TYPE_FLOAT, null, array(
        ), 'Status')
    ->addColumn('coupon_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
        ), 'Status')
    ->addColumn('coupon_comment', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        ), 'Status')

    // Magento data
    ->addColumn('magento_increment_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 32, array(
        ), 'Magento order increment ID')

    ->addIndex(
        'rakuten_order_rakuten_order_id',
        array('rakuten_order_id'))
    ->addIndex(
        'rakuten_order_order_no',
        array('order_no'))
    ->addIndex(
        'rakuten_order_magento_increment_id',
        array('magento_increment_id'));
//     ->setComment('Rakuten Orders');


$installer->getConnection()->createTable($table);

$table = $installer->getConnection()
    ->newTable($installer->getTable('rakuten/rakuten_order_item'))
    ->addColumn('item_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
        ), 'Item Id')
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
        ), 'Product Id')
    ->addColumn('variant_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
        ), 'Variant Id')
    ->addColumn('product_art_no', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
            'nullable'  => false,
        ), 'Sku')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 100, array(
        ), 'Name')
    ->addColumn('name_add', Varien_Db_Ddl_Table::TYPE_VARCHAR, 100, array(
        ), 'Name Add')
    ->addColumn('qty', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
        ), 'Qty')
    ->addColumn('price', Varien_Db_Ddl_Table::TYPE_FLOAT, null, array(
        ), 'Price')
    ->addColumn('price_sum', Varien_Db_Ddl_Table::TYPE_FLOAT, null, array(
        ), 'Sum Price')
    ->addColumn('tax', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
        ), 'Tax class')

    ->addColumn('rakuten_order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
        ), 'Rakuten Id')

    ->addIndex(
        'rakuten_order_item_item_id',
        array('item_id'))
    ->addIndex(
        'rakuten_order_item_product_id',
        array('product_id'))
    ->addIndex(
        'rakuten_order_item_variant_id',
        array('variant_id'))
    ->addIndex(
        'rakuten_order_item_product_art_no',
        array('product_art_no'))
    ->addIndex(
        'rakuten_order_item_rakuten_order_id',
        array('rakuten_order_id'))
    ->addForeignKey(
//         $installer->getFkName(
            'rakuten_order_item_rakuten_order_id',
//         ),
        'rakuten_order_id', $installer->getTable('rakuten/rakuten_order'), 'rakuten_order_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE);
//     ->setComment('Rakuten Order Item');
$installer->getConnection()->createTable($table);


// $table = $installer->getConnection()
//     ->newTable($installer->getTable('rakuten/rakuten_order'))
//     ->addColumn('rakuten_order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
//             'auto_increment' => true,
//             'unsigned'  => true,
//             'nullable'  => false,
//             'primary'   => true,
//         ), 'Rakuten Id')
//     ->addColumn('order_no', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
//             'nullable'  => false,
//         ), 'Order No')
//     ->addColumn('total', Varien_Db_Ddl_Table::TYPE_FLOAT, null, array(
//         ), 'Total')
//     ->addColumn('shipping', Varien_Db_Ddl_Table::TYPE_FLOAT, null, array(
//         ), 'Shipping Cost')
//     ->addColumn('max_shipping_date', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
//         ), 'Max Shipping Date')
//     ->addColumn('payment', Varien_Db_Ddl_Table::TYPE_VARCHAR, 32, array(
//         ), 'Payment')
//     ->addColumn('status', Varien_Db_Ddl_Table::TYPE_VARCHAR, 32, array(
//         ), 'Status')
//     ->addColumn('invoice_no', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
//         ), 'Invoice No')
//     ->addColumn('comment_client', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
//         ), 'Comment Client')
//     ->addColumn('comment_merchant', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
//         ), 'Comment Merchant')
//     ->addColumn('created', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
//         ), 'Created')
//
//     // Customer
//     ->addColumn('gender', Varien_Db_Ddl_Table::TYPE_VARCHAR, 32, array(
//         ), 'Status')
//     ->addColumn('first_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
//         ), 'Status')
//     ->addColumn('last_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
//         ), 'Status')
//     ->addColumn('company', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
//         ), 'Status')
//     ->addColumn('street', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
//         ), 'Status')
//     ->addColumn('street_no', Varien_Db_Ddl_Table::TYPE_VARCHAR, 32, array(
//         ), 'Status')
//     ->addColumn('address_add', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
//         ), 'Status')
//     ->addColumn('zip_code', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
//         ), 'Status')
//     ->addColumn('city', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
//         ), 'Status')
//     ->addColumn('country', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
//         ), 'Status')
//     ->addColumn('email', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
//         ), 'Status')
//     ->addColumn('phone', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
//         ), 'Status')
//
//     // Delivery address
//     ->addColumn('delivery_gender', Varien_Db_Ddl_Table::TYPE_VARCHAR, 32, array(
//         ), 'Status')
//     ->addColumn('delivery_first_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
//         ), 'Status')
//     ->addColumn('delivery_last_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
//         ), 'Status')
//     ->addColumn('delivery_company', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
//         ), 'Status')
//     ->addColumn('delivery_street', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
//         ), 'Status')
//     ->addColumn('delivery_street_no', Varien_Db_Ddl_Table::TYPE_VARCHAR, 32, array(
//         ), 'Status')
//     ->addColumn('delivery_address_add', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
//         ), 'Status')
//     ->addColumn('delivery_zip_code', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
//         ), 'Status')
//     ->addColumn('delivery_city', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
//         ), 'Status')
//     ->addColumn('delivery_country', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
//         ), 'Status')
//
//     // Coupon
//     ->addColumn('coupon_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
//         ), 'Status')
//     ->addColumn('coupon_total', Varien_Db_Ddl_Table::TYPE_FLOAT, null, array(
//         ), 'Status')
//     ->addColumn('coupon_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
//         ), 'Status')
//     ->addColumn('coupon_comment', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
//         ), 'Status')
//
//     // Magento data
//     ->addColumn('magento_increment_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 32, array(
//         ), 'Magento order increment ID')
//
//     ->addIndex(
//         $installer->getIdxName('rakuten/rakuten_order', array('rakuten_order_id')),
//         array('rakuten_order_id'))
//     ->addIndex(
//         $installer->getIdxName('rakuten/rakuten_order', array('order_no')),
//         array('order_no'))
//     ->addIndex(
//         $installer->getIdxName('rakuten/rakuten_order', array('magento_increment_id')),
//         array('magento_increment_id'))
//     ->setComment('Rakuten Orders');
//
//
// $installer->getConnection()->createTable($table);
//
// $table = $installer->getConnection()
//     ->newTable($installer->getTable('rakuten/rakuten_order_item'))
//     ->addColumn('item_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
//             'unsigned'  => true,
//             'nullable'  => false,
//             'primary'   => true,
//         ), 'Item Id')
//     ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
//             'unsigned'  => true,
//             'nullable'  => false,
//             'primary'   => true,
//         ), 'Product Id')
//     ->addColumn('variant_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
//             'unsigned'  => true,
//             'nullable'  => false,
//             'primary'   => true,
//         ), 'Variant Id')
//     ->addColumn('product_art_no', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
//             'nullable'  => false,
//         ), 'Sku')
//     ->addColumn('name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 100, array(
//         ), 'Name')
//     ->addColumn('name_add', Varien_Db_Ddl_Table::TYPE_VARCHAR, 100, array(
//         ), 'Name Add')
//     ->addColumn('qty', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
//             'unsigned'  => true,
//         ), 'Qty')
//     ->addColumn('price', Varien_Db_Ddl_Table::TYPE_FLOAT, null, array(
//         ), 'Price')
//     ->addColumn('price_sum', Varien_Db_Ddl_Table::TYPE_FLOAT, null, array(
//         ), 'Sum Price')
//     ->addColumn('tax', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
//             'unsigned'  => true,
//         ), 'Tax class')
//
//     ->addColumn('rakuten_order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
//             'unsigned'  => true,
//             'nullable'  => false,
//         ), 'Rakuten Id')
//
//     ->addIndex(
//         $installer->getIdxName('rakuten/rakuten_order_item', array('item_id')),
//         array('item_id'))
//     ->addIndex(
//         $installer->getIdxName('rakuten/rakuten_order_item', array('product_id')),
//         array('product_id'))
//     ->addIndex(
//         $installer->getIdxName('rakuten/rakuten_order_item', array('variant_id')),
//         array('variant_id'))
//     ->addIndex(
//         $installer->getIdxName('rakuten/rakuten_order_item', array('product_art_no')),
//         array('product_art_no'))
//     ->addIndex(
//         $installer->getIdxName('rakuten/rakuten_order_item', array('rakuten_order_id')),
//         array('rakuten_order_id'))
//     ->addForeignKey(
//         $installer->getFkName(
//             'rakuten/rakuten_order_item',
//             'rakuten_order_id',
//             'rakuten/rakuten_order',
//             'rakuten_order_id'
//         ),
//         'rakuten_order_id', $installer->getTable('rakuten/rakuten_order'), 'rakuten_order_id',
//         Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
//     ->setComment('Rakuten Order Item');
// $installer->getConnection()->createTable($table);


$installer->endSetup();
