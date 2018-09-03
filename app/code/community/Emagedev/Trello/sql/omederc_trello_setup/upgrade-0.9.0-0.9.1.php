<?php

/** @var Mage_Catalog_Model_Resource_Setup $installer */
$installer = $this;

$installer->startSetup();

$installer->getConnection()
    ->addColumn($installer->getTable('trello/order'),'label_ids', array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'after'     => 'card_id',
        'comment'   => 'Comma-Separated List Of Label Ids'
    ));

$installer->endSetup();