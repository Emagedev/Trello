<?php

/** @var Mage_Catalog_Model_Resource_Setup $installer */
$installer = $this;

$installer->startSetup();

$installer->getConnection()
    ->addColumn($installer->getTable('trello/action'),'origin_external', array(
        'type'      => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
        'default'   => '0',
        'nullable'  => false,
        'after'     => 'history_comment_id',
        'comment'   => 'Origin Of Comment: Magento = 0, Trello = 1'
    ));

$installer->endSetup();