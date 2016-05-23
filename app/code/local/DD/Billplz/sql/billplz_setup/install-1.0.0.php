<?php

$installer = $this;
/* @var $installer Mage_Catalog_Model_Resource_Setup */

$installer->startSetup();

$installer->getConnection()
    ->addColumn($installer->getTable('sales/order'), 'billplz_bill_id', [
        'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'   => 20,
        'nullable' => true,
        'default'  => null,
        'comment'  => 'Billplz Bill ID',
    ]);

$installer->endSetup();