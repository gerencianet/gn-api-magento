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

		$order = Mage::registry('current_order');
        if (!$order) {
            $order = Mage::getModel('checkout/session')->getQuote();
        }
		if ($order->getGrandTotal()<5) {
			return false;
		}
		return $available;
	}

	/**
	 * Validates all data before charge creation
	 * @param Varien_Object $payment
	 * @param double $amount
	 */
	public function validateData($paymentType) {
		try {
			if ($paymentType=="card") {
				$this->getBillingAddress($paymentType);
			}
			$this->getCustomer($paymentType);
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

			if ($e->getMessage()) {
				$e_message = $e->getMessage();
			} else {
				$e_message = "";
			}

			if ($e->getCode()) {
				$e_code = $e->getCode();
			} else {
				$e_code = "";
			}

			//Mage::throwException($e);
			Mage::throwException($this->errorHelper($e_message,$e_code,$e));
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
			Mage::throwException($e->getMessage());
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
			Mage::throwException($e->getMessage());
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
			Mage::throwException($e->getMessage());
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
				Mage::throwException($e->getMessage());
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
	public function getCustomer($paymentType) {
		$formData = unserialize($this->getOrder()->getPayment()->getAdditionalData());
		$payBilletAsJuridical = false;
		$payCardAsJuridical = false;

		if (isset($formData['juridical']['data_pay_billet_as_juridical'])) {
			
			if ($formData['juridical']['data_pay_billet_as_juridical']) {
			
				Mage::getModel('gerencianet_transparent/validator')->validateJuridicalPerson( $formData['juridical']['data_corporate_name'], $formData['juridical']['data_cnpj'], $paymentType);
				$juridical_data = array (
				  'corporate_name' => $formData['juridical']['data_corporate_name'],
				  'cnpj' => preg_replace( '/[^0-9]/', '',$formData['juridical']['data_cnpj'])
				);
				$payBilletAsJuridical = true;
			}
		}

		if (isset($formData['juridical']['data_pay_card_as_juridical'])) {
			
			if ($formData['juridical']['data_pay_card_as_juridical']) {
			
				Mage::getModel('gerencianet_transparent/validator')->validateJuridicalPerson( $formData['juridical']['cc_data_corporate_name'], $formData['juridical']['cc_data_cnpj'], $paymentType);
				$juridical_data = array (
				  'corporate_name' => $formData['juridical']['cc_data_corporate_name'],
				  'cnpj' => preg_replace( '/[^0-9]/', '',$formData['juridical']['cc_data_cnpj'])
				);
				$payCardAsJuridical = true;
			}
		}

		if ($paymentType=="billet") {
			if ($payBilletAsJuridical) {
				$customer = array(
					'name' => $formData['customer']['data_name'],
					'cpf' => preg_replace( '/[^0-9]/', '', $formData['customer']['data_cpf']),
					'email' => $formData['customer']['data_email'],
					'phone_number' => preg_replace( '/[^0-9]/', '',$formData['customer']['data_phone_number']),
					'juridical_person' => $juridical_data
				);
			} else {
				$customer = array(
					'name' => $formData['customer']['data_name'],
					'cpf' => preg_replace( '/[^0-9]/', '',$formData['customer']['data_cpf']),
					'email' => $formData['customer']['data_email'],
					'phone_number' => preg_replace( '/[^0-9]/', '',$formData['customer']['data_phone_number'])
				);
			}
		} else {
			$birth_date = $formData['customer']['cc_data_birth'];
			if (strpos($birth_date, '/') !== false) {
				$birth_date = date("Y-m-d", strtotime(str_replace('/', '-', $birth_date)));
			}

			if ($payCardAsJuridical) {
				$customer = array(
					'name' => $formData['customer']['cc_data_name'],
					'cpf' => preg_replace( '/[^0-9]/', '',$formData['customer']['cc_data_cpf']),
					'email' => $formData['customer']['cc_data_email'],
					'birth' => $birth_date,
					'phone_number' => preg_replace( '/[^0-9]/', '',$formData['customer']['cc_data_phone_number']),
					'juridical_person' => $juridical_data
				);
			} else {
				$customer = array(
					'name' => $formData['customer']['cc_data_name'],
					'cpf' => preg_replace( '/[^0-9]/', '',$formData['customer']['cc_data_cpf']),
					'email' => $formData['customer']['cc_data_email'],
					'birth' => $birth_date,
					'phone_number' => preg_replace( '/[^0-9]/', '',$formData['customer']['cc_data_phone_number'])
				);
			}

		}

		Mage::getModel('gerencianet_transparent/validator')->validate($customer, $paymentType);

		return $customer;
	}

	/**
	 * Retrieves billing address data for pay charge
	 * @return array
	 */
	public function getBillingAddress($paymentType) {
		$formData = unserialize($this->getOrder()->getPayment()->getAdditionalData());
		if ($paymentType=="card") {
			$address = $this->getOrder()->getBillingAddress($paymentType);

			$return = array(
				'street' => $formData['billing']['cc_data_street'],
				'number' => $formData['billing']['cc_data_number'],
				'zipcode' => $formData['billing']['cc_data_zipcode'],
				'neighborhood' => $formData['billing']['cc_data_neighborhood'],
				'state' => $formData['billing']['cc_data_state'],
				'city' => $formData['billing']['cc_data_city'],
			);

			if($formData['billing']['cc_data_complement'] != "")
				$return['complement'] = $formData['billing']['cc_data_complement'];

			Mage::getModel('gerencianet_transparent/validator')->validate($return, $paymentType);
		}
		return $return;
	}

	/**
	 * Retrieves shipping address data for pay charge
	 * @return array
	 */
	public function getShippingAddress($paymentType) {
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

		Mage::getModel('gerencianet_transparent/validator')->validate($return, $paymentType);

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

	public function errorHelper($message, $code, $error) {
		
		if (strpos($error, '/') !== false) {
			$property = explode("/",$error);
			$propertyName = end($property);
		} else {
			$propertyName="";
		}

		if ($code!="") {
			if ($message!="" && $propertyName=="") {
				$messageShow = $this->getErrorMessage(intval($code), $message, $message);
			} else {
				$messageShow = $this->getErrorMessage(intval($code), $propertyName, $message);
			}
		} else {
			if ($message!="") {
				$messageShow = $message;
			} else {
				$messageShow = $this->getErrorMessage(1, $propertyName, $message);
			}
		}

		return $messageShow;
	}


	public function getErrorMessage($error_code, $property, $originalMessage) {
		$messageErrorDefault = 'Ocorreu um erro ao tentar realizar a sua requisição. Verifique os campos informados nos formulários e, se o erro persistir, entre contato com o proprietário da loja.';
		switch($error_code) {
			case 3500000:
				$message = 'Erro interno do servidor.';
				break;
			case 3500001:
				$message = $messageErrorDefault;
				break;
			case 3500002:
				$message = $messageErrorDefault;
				break;
			case 3500007:
				$message = 'O tipo de pagamento informado não está disponível.';
				break;
			case 3500008:
				$message = 'Requisição não autorizada.';
				break;
			case 3500010:
				$message = $messageErrorDefault;
				break;
			case 3500021:
				$message = 'Não é permitido parcelamento para assinaturas.';
				break;
			case 3500030:
				$message = 'Esta transação já possui uma forma de pagamento definida.';
				break;
			case 3500034:
				if (trim($property)=="credit_card") {
					$message = 'Os dados digitados do cartão são inválidos. Verifique as informações e tente novamente.';
				} else if (trim($property)=="phone_number") {
					$message = 'O telefone digitado não é válido. Por favor, verifique se o DDD informado é válido e se o número está no padrão (XX)XXXX-XXXX.';
				}else {
					$message = 'O campo ' . $this->getFieldName($property) . ' do endereço de pagamento não está preenchido corretamente.';
				}
				break;
			case 3500042:
				$message = $messageErrorDefault;
				break;
			case 3500044:
				$message = 'A transação não pode ser paga. Entre em contato com o vendedor.';
				break;
			case 4600002:
				$message = $messageErrorDefault;
				break;
			case 4600012:
				$message = 'Ocorreu um erro ao tentar realizar o pagamento: ' . $property;
				break;
			case 4600022:
				$message = $messageErrorDefault;
				break;
			case 4600026:
				$message = 'cpf inválido';
				break;
			case 4600029:
				$message = 'pedido já existe';
				break;
			case 4600032:
				$message = $messageErrorDefault;
				break;
			case 4600035:
				$message = 'Serviço indisponível para a conta. Por favor, solicite que o recebedor entre em contato com o suporte Gerencianet.';
				break;
			case 4600037:
				$message = 'O valor da emissão é superior ao limite operacional da conta. Por favor, solicite que o recebedor entre em contato com o suporte Gerencianet.';
				break;
			case 4600111:
				$message = 'valor de cada parcela deve ser igual ou maior que R$5,00';
				break;
			case 4600142:
				$message = 'Transação não processada por conter incoerência nos dados cadastrais.';
				break;
			case 4600148:
				$message = 'já existe um pagamento cadastrado para este identificador.';
				break;
			case 4600196:
				$message = $messageErrorDefault;
				break;
			case 4600204:
				$message = 'cpf deve ter 11 números';
				break;
			case 4600209:
				$message = 'Limite de emissões diárias excedido. Por favor, solicite que o recebedor entre em contato com o suporte Gerencianet.';
				break;
			case 4600210:
				$message = 'não é possível emitir três emissões idênticas. Por favor, entre em contato com nosso suporte para orientações sobre o uso correto dos serviços Gerencianet.';
				break;
			case 4600212:
				$message = 'Número de telefone já associado a outro CPF. Não é possível cadastrar o mesmo telefone para mais de um CPF.';
				break;
			case 4600219:
				$message = 'Ocorreu um erro ao validar seus dados: ' . $property;
				break;
			case 4600224:
				$message = $messageErrorDefault;
				break;
			case 4600254:
				$message = 'identificador da recorrência não foi encontrado';
				break;
			case 4600257:
				$message = 'pagamento recorrente já executado';
				break;
			case 4600329:
				$message = 'código de segurança deve ter três digitos';
				break;
			case 4699999:
				$message = 'falha inesperada';
				break;
			default:
				$message = $originalMessage;
				break;
		}
		return $message;
	}
	
	public function getFieldName($name) {
		$field = trim($name);
		switch($field) {
			case "neighborhood":
				return 'Bairro';
				break;
			case "street":
				return 'Endereço';
				break;
			case "state":
				return 'Estado';
				break;
			case "complement":
				return 'Complemento';
				break;
			case "number":
				return 'Número';
				break;
			case "city":
				return 'Cidade';
				break;
			case "zipcode":
				return 'CEP';
				break;
			case "name":
				return 'Nome';
				break;
			case "cpf":
				return 'CPF';
				break;
			case "phone_number":
				return 'Telefone de contato';
				break;
			case "email":
				return 'Email';
				break;
			case "cnpj":
				return 'CNPJ';
				break;
			case "corporate_name":
				return 'Razão Social';
				break;
			case "birth":
				return 'Data de nascimento';
				break;
			default:
				return '';
				break;
		}
	}

}