<?php

/** @var Mage_Catalog_Model_Resource_Setup $installer */
$installer = $this;

$installer->startSetup();

$installer->getConnection()
    ->addColumn($installer->getTable('trello/member'),'full_name', array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'nullable'  => false,
        'after'     => 'admin_user_id',
        'comment'   => 'Name From Trello Board'
    ));

$installer->endSetup();