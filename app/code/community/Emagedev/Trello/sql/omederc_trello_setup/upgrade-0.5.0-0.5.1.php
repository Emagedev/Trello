<?php

/** @var Mage_Catalog_Model_Resource_Setup $installer */
$installer = $this;

$installer->startSetup();

$table = $installer->getConnection()->dropTable($installer->getTable('trello/webhook'));

$installer->endSetup();