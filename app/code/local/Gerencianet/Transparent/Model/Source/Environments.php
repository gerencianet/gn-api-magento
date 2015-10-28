<?php
/**
 * AV5 Tecnologia
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL).
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Av5
 * @package    Av5_ClearSaleTotal
 * @copyright  Copyright (c) 2015 AV5 Tecnologia (http://www.av5.com.br)
 * @author     Anderson Vincoletto <anderson@av5.com.br>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Gerencianet_Transparent_Model_Source_Environments
{

    public function toOptionArray()
    {
        return array(
            array('value'=>'1', 'label'=>Mage::helper('adminhtml')->__('Produção')),
            array('value'=>'2', 'label'=>Mage::helper('adminhtml')->__('Homologação')),
        );
    }

}