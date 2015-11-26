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
		$additionaldata['billet']['expires'] = date('Y-m-d',strtotime('+'.$expires.' days'));
		$info->setAdditionalData(serialize($additionaldata));
		
		return $this;
	}
	
	public function authorize(Varien_Object $payment, $amount)
	{
		$pay = $this->payCharge();
		Mage::log('PAY CHARGE BILLET: ' . var_export($pay,true),0,'gerencianet.log');
		
		if ($pay['code'] == 200) {
			$add_data = unserialize($this->getOrder()->getPayment()->getAdditionalData());
// 			$add_data['billet']['expires'] = $pay['data']['expire_at'];
			$add_data['billet']['barcode'] = $pay['data']['barcode'];
			$add_data['billet']['link'] = $pay['data']['link'];
			$add_data['charge_id'] = $pay['data']['charge_id'];
			$payment->setAdditionalData(serialize($add_data));
			$payment->save();
		} else {
			Mage::throwException($this->_getHelper()->__("Erro na emissão do boleto!\nMotivo: " . $pay->error_description . ".\nPor favor tente novamente mais tarde!"));
		}
		
	}
	
	protected function getPaymentData() {
		$expires = Mage::getStoreConfig('payment/gerencianet_billet/duedate');
		$add_data = unserialize($this->getOrder()->getPayment()->getAdditionalData());
		$instructions = array();
		for ($i=1;$i < 4;$i++) {
			$inst = Mage::getStoreConfig('payment/gerencianet_billet/instruction'.$i);
			if ($inst)
				$instructions[] = Mage::getStoreConfig('payment/gerencianet_billet/instruction'.$i);
		}
		
		$return = array(
			'banking_billet' => array(
					'expire_at' => $add_data['billet']['expires'],
					'customer' => $this->getCustomer(),
					'instructions' => $instructions
				)		
		);
		
		$return['banking_billet'] = $this->checkDiscount($return['banking_billet']);
		
		Mage::log("BANKING BILLET BODY: " . var_export($return,true),0,'gerencianet.log');
		return $return;
	}
}