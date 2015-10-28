<?php
class Gerencianet_Transparent_Model_Source_Cards extends Mage_Payment_Model_Source_Cctype
{
	protected $allowedTypes = array(
			'amex' 			=> 'American Express',
			'visa' 			=> 'Visa',
			'mastercard' 	=> 'Mastercard',
			'jcb'			=> 'JCB',
			'elo'			=> 'Elo',
			'diners'		=> 'Diners',
			'discover'		=> 'Discover'
		);

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