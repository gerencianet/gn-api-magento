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
 * @copyright  Copyright (c) 2016 Gerencianet (http://www.gerencianet.com.br)
 * @author     Gerencianet
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$installer = $this;

$installer->startSetup();

$installer->addAttribute('order_payment', 'gerencianet_endToEndId', array());

$installer->run(
    "DROP TABLE IF EXISTS {$this->getTable('gerencianet_webhook')};
    CREATE TABLE {$this->getTable('gerencianet_webhook')} (
    `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    `endToEndId` VARCHAR(32),
    `txid` VARCHAR(36),
    `chave` VARCHAR(40),
    `valor` FLOAT,
    `horario` VARCHAR(40),
    `order` VARCHAR(20),
    PRIMARY KEY (`id`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
);

$installer->endSetup();
