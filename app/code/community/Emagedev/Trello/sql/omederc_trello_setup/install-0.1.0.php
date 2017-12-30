<?php

/** @var Mage_Catalog_Model_Resource_Setup $installer */
$installer = $this;

$installer->startSetup();

$table = $installer->getConnection()
    ->newTable($installer->getTable('trello/order'))
    ->addColumn('link_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Id')
    ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
    ), 'Connected Order')
    ->addColumn('card_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'  => false,
    ), 'Card that Connected To Order');
$installer->getConnection()->createTable($table);

$installer->getConnection()->addForeignKey(
    $installer->getFkName('trello/order', 'order_id', 'sales/order', 'entity_id'),
    $installer->getTable('trello/order'),
    'order_id',
    $installer->getTable('sales/order'),
    'entity_id',
    Varien_Db_Ddl_Table::ACTION_NO_ACTION,
    Varien_Db_Ddl_Table::ACTION_NO_ACTION
);

$table = $installer->getConnection()
    ->newTable($installer->getTable('trello/list'))
    ->addColumn('link_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Id')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'  => false,
    ), 'Order Status')
    ->addColumn('list_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'  => false,
    ), 'Card List that Connected To Order Status');
$installer->getConnection()->createTable($table);

$installer->endSetup(); 