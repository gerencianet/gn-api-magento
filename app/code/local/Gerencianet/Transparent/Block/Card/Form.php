<?php
class Gerencianet_Transparent_Block_Card_Form extends Mage_Payment_Block_Form_Cc {

    /**
     * Set block template
     */
    protected function _construct() {
    	parent::_construct();
        $this->setTemplate('gerencianet/card/form.phtml');
    }

	public function getSourceModel()
    {
		return Mage::getSingleton('gerencianet_transparent/source_cards');
    }

    /**
     * Retrieve availables credit card types
     *
     * @return array
     */
    public function getCcAvailableTypes()
    {
        //recupera os tipos disponiveis e preenche o vetor types
        $arrayCartoes = $this->getSourceModel()->toOptionArray();

        $types = array();
        foreach($arrayCartoes as $cartao) {
            $types[$cartao['value']] = $cartao['label'];
        }

        /* if ($method = $this->getMethod()) {
            $availableTypes = $method->getConfigData('cctypes');
            if ($availableTypes) {
                $availableTypes = explode(',', $availableTypes);

                foreach ($types as $code=>$name) {
                    if (!in_array($code, $availableTypes)) {
                        unset($types[$code]);
                    }
                }
            }
        } */

        return $types;
    }


    /**
     * Retreive payment method form html
     *
     * @return string
     */
    public function getMethodFormBlock() {
        return $this->getLayout()->createBlock('payment/form_cc')
                        ->setMethod($this->getMethod());
    }

    public function getInstallments() {
    	$api = Mage::getModel('gerencianet_transparent/api_paymentData');
    	$quote = Mage::getModel('checkout/session')->getQuote();
    	$totals = $quote->getTotals();
    	$parcelas = $api->value($totals['grand_total']->_data['value'])
    		->type('visa')
    		->run()
			->response();
        return $parcelas;
    }

}