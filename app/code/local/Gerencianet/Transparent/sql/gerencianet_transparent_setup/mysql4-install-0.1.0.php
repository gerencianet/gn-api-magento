<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

// Required tables
$statusTable = $installer->getTable('sales/order_status');
$statusStateTable = $installer->getTable('sales/order_status_state');

// Insert statuses
$installer->getConnection()->insertArray(
	$statusTable,
	array(
		'status',
		'label'
	),
	array(
		array(
			'status' => 'gerencianet_refunded', 
			'label' => 'Pagamento Devolvido'
		),
		array(
			'status' => 'gerencianet_unpaid', 
			'label' => 'Pagamento Inadimplente'
		),
	)
);

// Insert states and mapping of statuses to states
$installer->getConnection()->insertArray(
	$statusStateTable,
	array(
		'status',
		'state',
		'is_default'
	),
	array(
		array(
			'status' => 'gerencianet_refunded',
			'state' => 'canceled',
			'is_default' => 0
		),
		array(
			'status' => 'gerencianet_unpaid',
			'state' => 'canceled',
			'is_default' => 0
		),
	)
);

$installer->endSetup();