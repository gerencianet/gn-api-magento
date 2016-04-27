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
class Gerencianet_Transparent_Block_Billet_Form extends Mage_Payment_Block_Form {

    /**
     * Block constructor
     */
    protected function _construct() {
    	parent::_construct();
    	$order = Mage::getModel('checkout/session')->getQuote();
		$address = $order->getBillingAddress();

		$customerDocument = preg_replace( '/[^0-9]/', '', $order->getCustomerTaxvat());
		if (strlen($customerDocument)==11) {
			$juridical=false;
		} else if (strlen($customerDocument)==14) {
			$juridical = true;
		} else {
			$customerDocument = "";
			$juridical=false;
		}

		if ($order->getCustomerEmail()) {
            $email = $order->getCustomerEmail();
        } else if ($address->getEmail()) {
            $email = $address->getEmail();
        } else {
            $email = "";
        }

        $phone_number = preg_replace( '/[^0-9]/', '', $address->getTelephone());
        if (strlen($phone_number)<10 || strlen($phone_number)>11) {
        	$phone_number = "";
        }
    	
		$dataOrder = array(
            'customer_data_name' => $address->getFirstname() . " " . $address->getLastname(), 
            'customer_data_document' => $customerDocument, 
            'customer_data_juridical' => $juridical, 
            'customer_data_email'=>$email,
            'customer_data_phone_number'=>$phone_number
        );
		
        $this->setData($dataOrder)->setTemplate('gerencianet/billet/form.phtml');
    }

    /**+
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

}