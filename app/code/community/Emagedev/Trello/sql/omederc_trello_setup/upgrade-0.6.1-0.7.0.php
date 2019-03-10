<?php

/** @var Mage_Catalog_Model_Resource_Setup $installer */
$installer = $this;

$installer->startSetup();

$installer->getConnection()
    ->addColumn($installer->getTable('trello/member'),'active', array(
        'type'      => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
        'default'   => '1',
        'nullable'  => false,
        'after'     => 'trello_member_id',
        'comment'   => 'Is User Connected And Allowed To Change Status'
    ));

$installer->endSetup();