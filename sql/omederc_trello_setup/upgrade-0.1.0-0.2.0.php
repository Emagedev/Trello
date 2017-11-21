<?php

/** @var Mage_Catalog_Model_Resource_Setup $installer */
$installer = $this;

$installer->startSetup();

$installer->getConnection()
    ->addColumn($installer->getTable('trello/order'),'archived', array(
        'type'      => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
        'default'   => '0',
        'nullable'  => false,
        'after'     => 'card_id',
        'comment'   => 'Is card archived and should be ignored'
    ));


$installer->getConnection()->addIndex(
    $installer->getTable('trello/list'),
    $installer->getIdxName(
        'trello/list',
        array(
            'status',
        ),
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    ),
    array(
        'status',
    ),
    array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
);

$installer->getConnection()->addIndex(
    $installer->getTable('trello/order'),
    $installer->getIdxName(
        'trello/order',
        array(
            'order_id',
        ),
        Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX
    ),
    array(
        'order_id',
    ),
    array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX)
);

$installer->endSetup();