<?php
class Gerencianet_Transparent_Model_Billet extends Gerencianet_Transparent_Model_Standard {
	
	protected $_code 					= 'gerencianet_billet';
	protected $_infoBlockType 			= 'gerencianet_transparent/billet_info';
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
		$expires = Mage::getStoreConfig('payment/gerencianet_billet/duedate');
		$additionaldata['billet'] = array(
			'expires' => date('Y-m-d',strtotime('+'.$expires.' days'))
		);
		$info->setAdditionalData(serialize($additionaldata));
		
		return $this;
	}
	
	public function authorize(Varien_Object $payment, $amount)
	{
		$pay = $this->payCharge();
		Mage::log('PAY CHARGE BILLET: ' . var_export($pay,true),0,'gerencianet.log');
		
		if ($pay['code'] == 200) {
			$add_data = unserialize($payment->getAdditonalData());
			$add_data['billet']['barcode'] = $pay['data']['barcode'];
			$add_data['billet']['link'] = $pay['data']['link'];
			$payment->setAdditionalData(serialize($add_data));
			$payment->save();
		} else {
			Mage::throwException($this->_getHelper()->__("Erro na emissão do boleto!\nMotivo: " . $pay->error_description . ".\nPor favor tente novamente mais tarde!"));
		}
		
	}
	
	protected function getPaymentData() {
		$expires = Mage::getStoreConfig('payment/gerencianet_billet/duedate');
		$return = array(
			'banking_billet' => array(
					'expire_at' => date('Y-m-d',strtotime($this->getOrder()->getCreatedAt().' +' . $expires . 'days')),
					'customer' => $this->getCustomer()
				)		
		);
		return $return;
	}
}