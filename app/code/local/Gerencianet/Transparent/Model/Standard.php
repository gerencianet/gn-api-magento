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

require_once Mage::getBaseDir('lib'). DS . 'gerencianet' . DS . 'autoload.php';

class Gerencianet_Transparent_Model_Standard extends Mage_Payment_Model_Method_Abstract {

	/**
	 * Model properties
	 */
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
	protected $_sandboxClientId;
	protected $_sandboxClientSecret;
	protected $_environment;

	/**
	 * Define Gerencianet environment
	 */
	const ENV_PRODUCTION = 1;
	const ENV_TEST = 2;

	/**
	 * Model constructor
	 */
	public function __construct() {
		parent::__construct();
		$this->_environment = Mage::getStoreConfig($this->_configPath . 'environment');
		$this->_clientId = Mage::getStoreConfig($this->_configPath . 'client_id');
    	$this->_clientSecret = Mage::getStoreConfig($this->_configPath . 'client_secret');
    	$this->_sandboxClientId = Mage::getStoreConfig($this->_configPath . 'sandbox_client_id');
    	$this->_sandboxClientSecret = Mage::getStoreConfig($this->_configPath . 'sandbox_client_secret');
	}

	/**
	 * Check if module is available for use
	 * @param Mage_Sales_Model_Quote $quote
	 * @return boolean
	 */
	public function isAvailable($quote = null)
	{
		$available = parent::isAvailable($quote);
		return $available;
	}

	/**
	 * Validates all data before charge creation
	 * @param Varien_Object $payment
	 * @param double $amount
	 */
	public function validateData() {
		try {
			$this->getBillingAddress();
			$this->getCustomer();
		} catch (Exception $e) {
			Mage::throwException($e->getMessage());
			return false;
		}
		return true;
	}

	/**
	 * Returns API object
	 * @return Gerencianet\Gerencianet
	 */
	public function getApi($env=NULL) {
	    if ($env) {
	        if ($env !== $this->_environment) {
	            $this->_api = NULL;
	        }
	    } else {
	        $env = $this->_environment;
	    }

		if (!$this->_api) {

    	    if ($env == self::ENV_TEST) {
    	        $clientID = $this->_sandboxClientId;
    	        $clientSecret = $this->_sandboxClientSecret;
    	        $mode = true;
    	    } else {
    	        $clientID = $this->_clientId;
    	        $clientSecret = $this->_clientSecret;
    	        $mode = false;
    	    }

			$options = array(
				'client_id' => $clientID,
				'client_secret' => $clientSecret,
				'sandbox' => $mode,
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
		try {
			return $this->getApi()->payCharge($params,$this->getBody());
		} catch(Exception $e) {
			Mage::log('PAY CHARGE ERROR: ' . $e->getMessage(), 0, 'gerencianet.log');
			throw $e;
		}
	}

	/**
	 * Create a charge for current quote
	 *
	 * @return int
	 */
	public function createCharge() {
		$body = $this->getChargeBody();
		try{
			$charge = $this->getApi()->createCharge(array(), $body);
			Mage::log('CHARGE: ' . var_export($charge,true),0,'gerencianet.log');
			return $charge['data']['charge_id'];
		} catch(Exception $e) {
			Mage::log('CREATE CHARGE ERROR: ' . $e->getMessage(), 0, 'gerencianet.log');
			throw $e;
		}
	}

	/**
	 * Update charge meta data
	 * @param int $orderID
	 * @param int $chargeID
	 */
	public function updateCharge($orderID, $chargeID) {
		$metadata = array(
			'custom_id' => $orderID,
			);
		try{
			$charge = $this->getApi()->updateChargeMetadata(array('id' => $chargeID), $metadata);
			Mage::log('UPDATE CHARGE METADATA: ' . var_export($charge,true),0,'gerencianet.log');
		} catch(Exception $e) {
			Mage::log('UPDATE CHARGE ERROR: ' . $e->getMessage(), 0, 'gerencianet.log');
			throw $e;
		}
	}

	/**
	 * Retrieve notification's data
	 * @param string $token
	 * @return array
	 */
	public function getNotification($token) {
		try{
			$notification = $this->getApi(self::ENV_PRODUCTION)->getNotification(array('token' => $token), array());
			Mage::log('NOTIFICATION: ' . var_export($notification,true),0,'gerencianet.log');
			return $notification['data'];
		} catch(Exception $e) {
			Mage::log('GET NOTIFICATION ERROR: ' . $e->getMessage(), 0, 'gerencianet.log');
			throw $e;
		}
	}

	/**
	 * Retrieve sandbox notification's data
	 * @param string $token
	 * @return array
	 */
	public function getNotificationSB($token) {
			try{
	    	$notification = $this->getApi(self::ENV_TEST)->getNotification(array('token' => $token), array());
	    	Mage::log('NOTIFICATION SANDBOX: ' . var_export($notification,true),0,'gerencianet.log');
	    	return $notification['data'];
	    } catch(Exception $e) {
				Mage::log('GET NOTIFICATION SANDBOX ERROR: ' . $e->getMessage(), 0, 'gerencianet.log');
				throw $e;
			}
	}

	/**
	 * Retrieve order's charge ID
	 * @return int
	 */
	public function getChargeId() {
		$payData = $this->getOrder()->getPayment()->getAdditonalData();
		$payData = unserialize($payData);
		return $payData['charge_id'];
	}

	/**
	 * Get payment data for pay charge
	 * @return array
	 */
	public function getBody() {
		$body = array(
			'payment' => $this->getPaymentData()
		);
		Mage::log('BODY PAYMENT: ' . var_export($body,true),0,'gerencianet.log');
		return $body;
	}

	/**
	 * Retrieves customer's data for pay charge
	 * @return array
	 */
	public function getCustomer() {
		$order = $this->getOrder();
		$address = $this->getOrder()->getBillingAddress();
		$customer = array(
			'name' => $order->getCustomerFirstname() . " " . $order->getCustomerLastname(),
			'cpf' => preg_replace( '/[^0-9]/', '', $order->getCustomerTaxvat()),
			'email' => $order->getCustomerEmail(),
			'birth' => date('Y-m-d',strtotime($order->getCustomerDob())),
			'phone_number' => preg_replace( '/[^0-9]/', '', $address->getTelephone()),
			'address' => $this->getShippingAddress()
		);

		Mage::getModel('gerencianet_transparent/validator')->validate($customer);

		return $customer;
	}

	/**
	 * Retrieves billing address data for pay charge
	 * @return array
	 */
	public function getBillingAddress() {
		$address = $this->getOrder()->getBillingAddress();
		$return = array(
			'street' => $address->getStreet1(),
			'number' => $address->getStreet2(),
			'zipcode' => preg_replace('/[^0-9\s]/', '',$address->getPostcode()),
			'neighborhood' => $address->getStreet4(),
			'state' => $address->getRegionCode(),
			'city' => $address->getCity()
		);

		if($address->getStreet3())
			$return['complement'] = $address->getStreet3();

		Mage::getModel('gerencianet_transparent/validator')->validate($return);

		return $return;
	}

	/**
	 * Retrieves shipping address data for pay charge
	 * @return array
	 */
	public function getShippingAddress() {
		$address = $this->getOrder()->getShippingAddress();
		$return = array(
				'street' => $address->getStreet1(),
				'number' => $address->getStreet2(),
				'zipcode' => preg_replace('/[^0-9\s]/', '',$address->getPostcode()),
				'neighborhood' => $address->getStreet4(),
				'state' => $address->getRegionCode(),
				'city' => $address->getCity()
		);

		if($address->getStreet3())
			$return['complement'] = $address->getStreet3();

		Mage::getModel('gerencianet_transparent/validator')->validate($return);

		return $return;
	}

	/**
	 * Generates charge body
	 * @return array
	 */
	public function getChargeBody() {
		$order = $this->getOrder();
		$items = $order->getItemsCollection();
		$return = array();
		$orderTotal = 0;
		foreach ($items as $it) {
			$item = $order->getItemById($it->getItemId());
			$orderTotal += $item->getBasePrice();
			$return[] = array(
					'name' => $item->getName(),
					'value' => (int)number_format($item->getBasePrice(),2,'',''),
					'amount' => $item->getQty()
				);
		}

		$returnData = array();
		if (!$order->isVirtual()) { // CHECK ORDER IS NOT VIRTUAL
			$title = $order->getShippingAddress()->getShippingDescription();
			$price = $order->getShippingAddress()->getShippingAmount();
			$orderTotal += $price;
			$returnData['shippings'] = array(array(
						'name' => $title,
						'value'=> (int)number_format($price,2,'','')
					));
		}

		# Add taxes as a charge's item
		if ($orderTotal < $order->getBaseTotal()) {
			$taxValue = $order->getBaseTotal() - $orderTotal;
			$return[] = array(
					'name' => 'Taxa/Imposto',
					'value' => (int)number_format($taxValue,2,'',''),
					'amount' => 1
				);
		}

		$returnData['items'] = $return;

		$notification_url = 'gerencianet/payment/notification';
		if (Mage::helper('gerencianet_transparent')->isSandbox()) {
		    $notification_url .= 'sb';
		}
		$returnData['metadata'] = array('notification_url' => Mage::getUrl($notification_url));

		return $returnData;
	}

	/**
	 * Get current order object
	 * @return Mage_Sales_Model_Order
	 */
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

	/**
	 * Check discount data for pay charge
	 * @param array $paymentData
	 * @return array
	 */
	public function checkDiscount($paymentData) {
		$order = $this->getOrder();
		$discount = false;
		if ($order->getDiscountAmount()) {
			$discount = (int)number_format($order->getDiscountAmount(),2,'','');
		} else {
			$totals = $order->getTotals();
			if(isset($totals['discount'])) {
				$discount = (int)abs(number_format($totals['discount']->getValue(),2,'',''));
			}
		}

		if ($discount) {
			$paymentData['discount'] = array(
					'type' 		=> 'currency',
					'value'		=> $discount
			);
		}

		return $paymentData;
	}

}