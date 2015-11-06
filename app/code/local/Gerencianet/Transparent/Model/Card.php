<?php
class Gerencianet_Transparent_Model_Card extends Gerencianet_Transparent_Model_Standard {
	
	protected $_code 					= 'gerencianet_card';
	protected $_formBlockType 			= 'gerencianet_transparent/card_form';
	protected $_infoBlockType 			= 'gerencianet_transparent/card_info';
	protected $_isGateway               = true;
    protected $_canAuthorize            = true;
    protected $_canCapture              = false;
    protected $_canCapturePartial       = false;
    protected $_canRefund               = false;
    protected $_canVoid                 = true;
    protected $_canUseInternal          = true;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = true;
	
	public function assignData($data) {
		$info = $this->getInfoInstance();
		$quote = Mage::getModel('checkout/session')->getQuote();
		$additionaldata['card'] = array(
			'cc_token' => $data->getCcToken(),
			'cc_installments' => $data->getCcInstallments() ,
		);
		$info->setAdditionalData(serialize($additionaldata));
		
		$info->setCcType($data->getCcType());
		$info->setCcOwner($data->getCcOwner());
		$info->setCcExpMonth($data->getCcExpMonth());
		$info->setCcExpYear($data->getCcExpYear());
		$info->setCcNumberEnc($info->encrypt($data->getCcNumber()));
		$info->setCcCidEnc($info->encrypt($data->getCcCid()));
		$info->setCcLast4(substr($data->getCcNumber(), -4));
		
		return $this;
	}
	
	public function authorize(Varien_Object $payment, $amount)
	{
		$pay = $this->payCharge();
		$payData = unserialize($payment->getAdditonalData());
		$payData['charge_id'] = $pay['data']['charge_id'];
		$payment->setAdditionalData(serialize($payData));
		$payment->save();
		Mage::log('PAY CHARGE CARD: ' . var_export($pay,true),0,'gerencianet.log');
	}
	
	protected function getPaymentData() {
		$payment = unserialize($this->getOrder()->getPayment()->getAdditionalData());
		$return = array( 'credit_card' => array() );
		
		if($payment['card']['cc_installments']) {
			$return['credit_card']['installments'] = (int)$payment['card']['cc_installments'];
		}
		
		if($payment['card']['cc_token']) {
			$return['credit_card']['payment_token'] = $payment['card']['cc_token'];
		}
		
		$return['credit_card']['billing_address'] = $this->getBillingAddress();
		$return['credit_card']['customer'] = $this->getCustomer();
		
		return $return;
	}
}