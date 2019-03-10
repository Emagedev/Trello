<?php

/** @var Mage_Catalog_Model_Resource_Setup $installer */
$installer = $this;

$installer->startSetup();

$installer->getConnection()->addIndex(
    $installer->getTable('trello/card'),
    $installer->getIdxName(
        'trello/card',
        array(
            'order_id',
        ),
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    ),
    array(
        'order_id',
    ),
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
);

$installer->endSetup();