<?php

/** @var Mage_Catalog_Model_Resource_Setup $installer */
$installer = $this;

$installer->startSetup();

$table = $installer->getConnection()
    ->newTable($installer->getTable('trello/action'))
    ->addColumn('link_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Id')
    ->addColumn('history_comment_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
    ), 'Order Status History Entry Id')
    ->addColumn('action_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'  => false,
    ), 'Trello Action Id Order')
    ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'  => false,
    ), 'Action Connected To Order');
$installer->getConnection()->createTable($table);

$table = $installer->getConnection()
    ->newTable($installer->getTable('trello/member'))
    ->addColumn('link_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Id')
    ->addColumn('admin_user_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
    ), 'Connected Order')
    ->addColumn('trello_member_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'  => false,
    ), 'Connected Order');
$installer->getConnection()->createTable($table);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'trello/action',
        'history_comment_id',
        'sales/order_status_history',
        'entity_id',
        Varien_Db_Adapter_Interface::FK_ACTION_CASCADE
    ),
    $installer->getTable('trello/action'),
    'history_comment_id',
    $installer->getTable('sales/order_status_history'),
    'entity_id',
    Varien_Db_Adapter_Interface::FK_ACTION_CASCADE
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'trello/member',
        'admin_user_id',
        'admin/user',
        'user_id',
        Varien_Db_Adapter_Interface::FK_ACTION_CASCADE
    ),
    $installer->getTable('trello/member'),
    'admin_user_id',
    $installer->getTable('admin/user'),
    'user_id',
    Varien_Db_Adapter_Interface::FK_ACTION_CASCADE
);

$installer->endSetup();