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
    ->newTable($installer->getTable('rakuten/rakuten_product_image'))
    ->addColumn('image_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 15, array(
            'nullable'  => false,
            'primary'   => true,
        ), 'Image Id')
    ->addColumn('rakuten_product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
        ), 'Id')
    ->addColumn('src', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
            'nullable'  => false,
        ), 'Src')
    ->addColumn('default', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
            'unsigned'  => true,
            'nullable'  => false,
        ), 'Default')
    ->addColumn('comment', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
            'nullable'  => false,
        ), 'Comment')
    ->addIndex(
        'rakuten_product_image_image_id',
        array('image_id'))
    ->addIndex(
        'rakuten_product_image_rakuten_product_id',
        array('rakuten_product_id'))
    ->addForeignKey(
//         $installer->getFkName(
            'rakuten_product_image_rakuten_product_id',
//         ),
        'rakuten_product_id', $installer->getTable('rakuten/rakuten_product'), 'rakuten_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE);
//     ->setComment('Rakuten Product Image');
$installer->getConnection()->createTable($table);


// $table = $installer->getConnection()
//     ->newTable($installer->getTable('rakuten/rakuten_product_image'))
//     ->addColumn('image_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 15, array(
//             'nullable'  => false,
//             'primary'   => true,
//         ), 'Image Id')
//     ->addColumn('rakuten_product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
//             'unsigned'  => true,
//             'nullable'  => false,
//             'primary'   => true,
//         ), 'Id')
//     ->addColumn('src', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
//             'nullable'  => false,
//         ), 'Src')
//     ->addColumn('default', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
//             'unsigned'  => true,
//             'nullable'  => false,
//         ), 'Status')
//     ->addColumn('comment', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
//             'nullable'  => false,
//         ), 'Comment')
//     ->addIndex(
//         $installer->getIdxName('rakuten/rakuten_product_image', array('image_id')),
//         array('image_id'))
//     ->addIndex(
//         $installer->getIdxName('rakuten/rakuten_product_image', array('rakuten_product_id')),
//         array('rakuten_product_id'))
//     ->addForeignKey(
//         $installer->getFkName(
//             'rakuten/rakuten_product_image',
//             'rakuten_product_id',
//             'rakuten/rakuten_product',
//             'rakuten_id'
//         ),
//         'rakuten_product_id', $installer->getTable('rakuten/rakuten_product'), 'rakuten_id',
//         Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
//     ->setComment('Rakuten Product Image');
// $installer->getConnection()->createTable($table);


$installer->endSetup();
