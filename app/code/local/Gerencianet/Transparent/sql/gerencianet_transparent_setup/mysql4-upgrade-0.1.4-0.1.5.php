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

$country_region = $this->getTable('directory/country_region'); 
$sql = "SELECT count(*) as total FROM {$country_region} WHERE country_id = 'BR'"; 
$readConn= Mage::getSingleton('core/resource')->getConnection('core_read');
$brStatesCount = $readConn->fetchRow($sql);

if($brStatesCount['total'] == 0){

    $installer->run("
        INSERT INTO `{$country_region}` (`country_id`, `code`, `default_name`) VALUES
        ('BR', 'AC', 'Acre'),
        ('BR', 'AL', 'Alagoas'),
        ('BR', 'AP', 'Amapá'),
        ('BR', 'AM', 'Amazonas'),
        ('BR', 'BA', 'Bahia'),
        ('BR', 'CE', 'Ceará'),
        ('BR', 'DF', 'Distrito Federal'),
        ('BR', 'ES', 'Espírito Santo'),
        ('BR', 'GO', 'Goiás'),
        ('BR', 'MA', 'Maranhão'),
        ('BR', 'MT', 'Mato Grosso'),
        ('BR', 'MS', 'Mato Grosso do Sul'),
        ('BR', 'MG', 'Minas Gerais'),
        ('BR', 'PA', 'Para'),
        ('BR', 'PB', 'Paraíba'),
        ('BR', 'PR', 'Paraná'),
        ('BR', 'PE', 'Pernambuco'),
        ('BR', 'PI', 'Piauí'),
        ('BR', 'RJ', 'Rio de Janeiro'),
        ('BR', 'RN', 'Rio Grande do Norte'),
        ('BR', 'RS', 'Rio Grande do Sul'),
        ('BR', 'RO', 'Rondônia'),
        ('BR', 'RR', 'Roraima'),
        ('BR', 'SC', 'Santa Catarina'),
        ('BR', 'SP', 'São Paulo'),
        ('BR', 'SE', 'Sergipe'),
        ('BR', 'TO', 'Tocantins');
    ");
    
    $sql = "SELECT region_id, code FROM {$country_region} WHERE code = 'AC' and country_id = 'BR'";
    $firstState = $readConn->fetchRow($sql);
    $firstId = $firstState['region_id']; 

    $country_region_name = $this->getTable('directory/country_region_name');
    $installer->run("
        INSERT INTO `{$country_region_name}` (`locale`, `region_id`, `name`) VALUES
        ('pt_BR', '".   $firstId   ."', 'Acre'),
        ('pt_BR', '". ($firstId+1) ."', 'Alagoas'),
        ('pt_BR', '". ($firstId+2) ."', 'Amapá'),
        ('pt_BR', '". ($firstId+3) ."', 'Amazonas'),
        ('pt_BR', '". ($firstId+4) ."', 'Bahia'),
        ('pt_BR', '". ($firstId+5) ."', 'Ceará'),
        ('pt_BR', '". ($firstId+6) ."', 'Distrito Federal'),
        ('pt_BR', '". ($firstId+7) ."', 'Espírito Santo'),
        ('pt_BR', '". ($firstId+8) ."', 'Goiás'),
        ('pt_BR', '". ($firstId+9) ."', 'Maranhão'),
        ('pt_BR', '". ($firstId+10) ."', 'Mato Grosso'),
        ('pt_BR', '". ($firstId+11) ."', 'Mato Grosso do Sul'),
        ('pt_BR', '". ($firstId+12) ."', 'Minas Gerais'),
        ('pt_BR', '". ($firstId+13) ."', 'Pará'),
        ('pt_BR', '". ($firstId+14) ."', 'Paraíba'),
        ('pt_BR', '". ($firstId+15) ."', 'Paraná'),
        ('pt_BR', '". ($firstId+16) ."', 'Pernambuco'),
        ('pt_BR', '". ($firstId+17) ."', 'Piauí'),
        ('pt_BR', '". ($firstId+18) ."', 'Rio de Janeiro'),
        ('pt_BR', '". ($firstId+19) ."', 'Rio Grande do Norte'),
        ('pt_BR', '". ($firstId+20) ."', 'Rio Grande do Sul'),
        ('pt_BR', '". ($firstId+21) ."', 'Rondônia'),
        ('pt_BR', '". ($firstId+22) ."', 'Roraima'),
        ('pt_BR', '". ($firstId+23) ."', 'Santa Catarina'),
        ('pt_BR', '". ($firstId+24) ."', 'São Paulo'),
        ('pt_BR', '". ($firstId+25) ."', 'Sergipe'),
        ('pt_BR', '". ($firstId+26) ."', 'Tocantins');
    ");
};

$installer->endSetup();