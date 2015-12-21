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
class Gerencianet_Transparent_Model_Source_Environments
{

    /**
     * Returns array of environment types
     * @return array
     */
	public function toOptionArray()
    {
        return array(
            array('value'=>'1', 'label'=>Mage::helper('adminhtml')->__('Produção')),
            array('value'=>'2', 'label'=>Mage::helper('adminhtml')->__('Desenvolvimento')),
        );
    }

}