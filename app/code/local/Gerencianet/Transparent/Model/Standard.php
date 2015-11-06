<?php

require_once Mage::getBaseDir('lib'). DS . 'gerencianet' . DS . 'autoload.php';

class Gerencianet_Transparent_Model_Standard extends Mage_Payment_Model_Method_Abstract {
	
	protected $_code = 'gerencianet_transparent';
	protected $_isGateway = true;
	protected $_isInitializeNeeded = false;
	protected $_canUseInternal = false;
	protected $_canUseForMultishipping = false;
	protected $_canUseCheckout = false;
	protected $_configFile = null;
	protected $_api = null;
	protected $_order = null;
	protected $_configPath = 'payment/gerencianet_transparent/';
	protected $_clientId;
	protected $_clientSecret;
	protected $_environment;
	
	/**
	 * Define Gerencianet environment
	 */
	const ENV_PRODUCTION = 1;
	const ENV_TEST = 2;
	
	public function __construct() {
		parent::__construct();
		$this->_clientId = Mage::getStoreConfig($this->_configPath . 'client_id');
		$this->_clientSecret = Mage::getStoreConfig($this->_configPath . 'client_secret');
		$this->_environment = Mage::getStoreConfig($this->_configPath . 'environment');
	}
	
	public function isAvailable($quote = null)
	{
		$available = parent::isAvailable($quote);
		return $available;
	}
	
	public function getApi() {
		if (!$this->_api) {
			
			$options = array(
				'client_id' => $this->_clientId,
				'client_secret' => $this->_clientSecret,
				'sandbox' => ($this->_environment == 2) ? true : false,
				'debug' => false
			); 
			
			$this->_api = new Gerencianet\Gerencianet($options);
		}
		
		return $this->_api;
	}
	
	/**
	 * Generate a payment to charge using checkout
	 *
	 * @return ApiPayCharge
	 */
	public function payCharge() {
		$params = array('id' => $this->createCharge());
		return $this->getApi()->payCharge($params,$this->getBody());
	}
	
	public function createCharge() {
		$charge = $this->getApi()->createCharge(array(), $this->getItems());
		Mage::log('CHARGE: ' . var_export($charge,true),0,'gerencianet.log');
		return $charge['data']['charge_id'];
	}
	
	public function updateCharge() {
		$metadata = array(
			'custom_id' => $this->getOrder()->getIncrementalId(),
			'notification_url' => Mage::getUrl('gerencianet/payment/notification')
			);
		$charge = $this->getApi()->updateChargeMetadata(array('id' => $this->getChargeId()), $metadata);
		Mage::log('UPDATE CHARGE METADATA: ' . var_export($charge,true),0,'gerencianet.log');
	}
	
	public function getChargeId() {
		$payData = $this->getOrder()->getPayment()->getAdditonalData();
		$payData = unserialize($payData);
		return $payData['charge_id'];
	}
	
	public function getBody() {
		$body = array(
			'payment' => $this->getPaymentData()
		);
		return $body;
	}
	
	public function getCustomer() {
		$order = $this->getOrder();
		$address = $this->getOrder()->getBillingAddress();
		$customer = array(
			'name' => $order->getCustomerFirstname() . " " . $order->getCustomerLastname(),
			'cpf' => preg_replace( '/[^0-9]/', '', $order->getCustomerTaxvat()),
			'email' => $order->getCustomerEmail(),
			'birth' => date('Y-m-d',strtotime($order->getCustomerDob())),
			'phone_number' => preg_replace( '/[^0-9]/', '', $address->getTelephone())
		);
		
		Mage::getModel('gerencianet_transparent/validator')->validate($customer);
		
		return $customer;
	}
	
	public function getBillingAddress() {
		$address = $this->getOrder()->getBillingAddress();
		$return = array(
			'street' => $address->getStreet1(),
			'number' => $address->getStreet2(),
			'zipcode' => $address->getPostcode(),
			'neighborhood' => $address->getStreet4(),
			'state' => $address->getRegionCode(),
			'city' => $address->getCity()
		);
			
		if($address->getStreet3())
			$return['complement'] = $address->getStreet3();
		
		Mage::getModel('gerencianet_transparent/validator')->validate($return);
		
		return $return;
	}
	
	public function getChargeBody() {
		$order = $this->getOrder();
		$items = $order->getItemsCollection();
		$return = array();
		foreach ($items as $it) {
			$item = $order->getItemById($it->getItemId());
			$return[] = array(
					'name' => $item->getName(),
					'value' => (int)number_format($item->getBasePrice(),2,'',''),
					'amount' => $item->getQty()
				);
		}
		
		$retShipping = array();
		if ($order->getShippingAddress()->getShippingAmount() > 0) {
			$title = $order->getShippingAddress()->getShippingDescription();
			$price = $order->getShippingAddress()->getShippingAmount();
			$retShipping[] = array(
						'name' => $title,
						'value'=> (int)number_format($price,2,'','')
					);
		}
		
		return array('items' => $return, 'shippings' => $retShipping);
	}
	
	public function getOrder() {
		if (!$this->_order) {
			$this->_order = Mage::registry('current_order');
			if (!$this->_order) {
				$this->_order = Mage::getModel('checkout/session')->getQuote();
				if (!$this->_order) {
					return false;
				}
			}
		}
		return $this->_order;
	}
	
}