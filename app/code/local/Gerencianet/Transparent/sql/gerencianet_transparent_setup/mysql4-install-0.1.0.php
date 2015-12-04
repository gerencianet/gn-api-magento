<?php
/**
 * Gerencianet
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL).
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Payment
 * @package    Gerencianet_Transparent
 * @copyright  Copyright (c) 2015 Gerencianet (http://www.gerencianet.com.br)
 * @author     AV5 Tecnologia <anderson@av5.com.br>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$installer = $this;

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