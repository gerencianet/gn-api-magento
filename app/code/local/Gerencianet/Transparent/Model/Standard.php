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
			'cpf' => $order->getCustomerTaxvat(),
			'email' => $order->getCustomerEmail(),
			'birth' => date('Y-m-d',strtotime($order->getCustomerDob())),
			'phone_number' => preg_replace( '/[^0-9]/', '', $address->getTelephone())
		);
		
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
		
		return $return;
	}
	
	public function getItems() {
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
		
		return array('items' => $return);
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