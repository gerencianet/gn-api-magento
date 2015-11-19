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
			'status' => 'gerencianet_waiting',
			'label' => 'Aguardando Pagamento'
		),
		array(
			'status' => 'gerencianet_paid',
			'label' => 'Pagamento Confirmado'
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
			'status' => 'gerencianet_waiting',
			'state' => 'processing',
			'is_default' => 0
		),
		array(
			'status' => 'gerencianet_paid',
			'state' => 'processing',
			'is_default' => 0
		),
	)
);

$installer->endSetup();