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
class Gerencianet_Transparent_Model_Source_Cards extends Mage_Payment_Model_Source_Cctype
{
	/**
	 * Array of allowed card types
	 */
	protected $allowedTypes = array(
			'visa' 			=> 'Visa',
			'mastercard' 	=> 'Mastercard',
			'amex' 			=> 'American Express',
			'elo'			=> 'Elo',
			'diners'		=> 'Diners',
			'hipercard' => 'Hipercard'
		);

	/**
	 * Returns array of card types
	 * @return array
	 */
    public function toOptionArray()
    {
        $options = array();
        foreach ($this->allowedTypes as $code => $name) {
			$options[] = array(
            	'value' => $code,
                'label' => $name
			);
        }
        return $options;
    }
}