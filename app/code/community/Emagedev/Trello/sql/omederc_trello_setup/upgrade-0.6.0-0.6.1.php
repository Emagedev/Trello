<?php

/** @var Mage_Catalog_Model_Resource_Setup $installer */
$installer = $this;

$installer->startSetup();

$installer->getConnection()->dropForeignKey(
    $installer->getTable('trello/member'),
    $installer->getFkName(
        'trello/member',
        'admin_user_id',
        'admin/user',
        'user_id',
        Varien_Db_Adapter_Interface::FK_ACTION_CASCADE
    )
);

$installer->getConnection()->modifyColumn($installer->getTable('trello/member'), 'admin_user_id', array(
    'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
    'length'    => 10,
    'nullable'  => true,
    'comment'   => 'Magento Admin User Id'
));

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