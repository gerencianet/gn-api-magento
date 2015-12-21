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

$installer->startSetup();

$installer->addAttribute('order_payment', 'gerencianet_charge_id', array());

$installer->run("
    DROP TABLE IF EXISTS {$this->getTable('gerencianet_notifications')};
    CREATE TABLE {$this->getTable('gerencianet_notifications')} (
    `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    `order` VARCHAR(20),
    `status` VARCHAR(20),
    `charge_id` INTEGER,
    PRIMARY KEY (`id`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();