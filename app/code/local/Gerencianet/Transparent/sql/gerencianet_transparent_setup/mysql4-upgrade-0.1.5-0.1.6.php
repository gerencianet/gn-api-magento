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

$installer->run("
    DROP TABLE IF EXISTS {$this->getTable('gerencianet_tokens')};
    CREATE TABLE {$this->getTable('gerencianet_tokens')} (
    `token` VARCHAR(255),
    `quote_id` INTEGER,
    `status` TINYINT,
    PRIMARY KEY (`token`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();