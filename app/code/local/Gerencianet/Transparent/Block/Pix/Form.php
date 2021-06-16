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
 * @copyright  Copyright (c) 2016 Gerencianet (http://www.gerencianet.com.br)
 * @author     Gerencianet
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Gerencianet_Transparent_Block_Pix_Form extends Mage_Payment_Block_Form {

    /**
     * Block constructor
     */
    protected function _construct() {
    	parent::_construct();
        $sessionInstance = Mage::getModel("core/session")->getSessionQuote();
    	$order = Mage::getModel($sessionInstance)->getQuote();
		$address = $order->getBillingAddress();
        $order_total = $order->getGrandTotal();

		$customerDocument = preg_replace( '/[^0-9]/', '', $order->getCustomerTaxvat());
		if (strlen($customerDocument)==11 && $this->validaCPF($customerDocument)) {
			$juridical=false;
		} else if (strlen($customerDocument)==14 && $this->validaCNPJ($customerDocument)) {
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

        if ($customerDocument!="") {
            if ($juridical) {
                if ($order->getCustomer()->getRazaoSocial()) {
                    $name = $order->getCustomer()->getRazaoSocial();
                } else {
                    if ($order->getCustomerFirstname!="") {
                        $name = $order->getCustomerFirstname();
                    } else if ($address->getFirstname()!="") {
                        $name = $address->getFirstname();
                    }
                }
            } else {
                if ($order->getCustomerName()) {
                    $name = $order->getCustomerName();
                } else if ($order->getCustomerFirstname!="" && $order->getCustomerLastname()!="") {
                    $name = $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname();
                } else {
                    $name = $address->getFirstname() . " " . $address->getLastname();
                }

            }
        } else {
            $name="";
        }
            
        if (strlen($name)<1 || !preg_match("/^[ ]*(?:[^\\s]+[ ]+)+[^\\s]+[ ]*$/",$name) || $name=="undefined") {
        	$name = "";
        }

		$dataOrder = array(
            'customer_data_name' => $name, 
            'customer_data_document' => $customerDocument, 
            'customer_data_juridical' => $juridical, 
            'customer_data_email'=>$email,
            'customer_data_phone_number'=>$phone_number,
            'order_total' => $order_total
        );
		
        $this->setData($dataOrder)->setTemplate('gerencianet/pix/form.phtml');
        
    }


    /**
     * Prepares layout for show on screen
     */
    protected function _prepareLayout(){

        $jsBlock = Mage::app()->getLayout()->createBlock('core/text', 'js_gerencianet_pix');
        $jsBlock->setText("
        <script type='text/javascript' src='".Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS, true)."gerencianet/jquery-1.12.3.min.js'; ?>'></script>
        <script type='text/javascript' src='".Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS, true)."gerencianet/jquery.maskedinput.js'; ?>'></script>
        <script type='text/javascript'>
            //<![CDATA[
            jQuery.noConflict();
            //]]>
        </script>
        ");
                
        $head = Mage::app()->getLayout()->getBlock('after_body_start');
    
        if($head) {
            $head->append($jsBlock);
        }

    }


    /**+
	 * Get current order object
	 * @return Mage_Sales_Model_Order
	 */
	public function getOrder() {
		if (!$this->_order) {
			$this->_order = Mage::registry('current_order');
			if (!$this->_order) {
				$sessionInstance = Mage::getModel("core/session")->getSessionQuote();
                $this->_order = Mage::getModel($sessionInstance)->getQuote();
				if (!$this->_order) {
					return false;
				}
			}
		}
		return $this->_order;
	}

    private function validaCPF($cpf) {
        $soma = 0;
      
        if (strlen($cpf) <> 11)
            return false;
          
        for ($i = 0; $i < 9; $i++) {         
            $soma += (($i+1) * $cpf[$i]);
        }

        $d1 = ($soma % 11);
          
        if ($d1 == 10) {
            $d1 = 0;
        }
        
        $soma = 0;
          

        for ($i = 9, $j = 0; $i > 0; $i--, $j++) {
            $soma += ($i * $cpf[$j]);
        }
          
        $d2 = ($soma % 11);
          
        if ($d2 == 10) {
            $d2 = 0;
        }      
         
        if ($d1 == $cpf[9] && $d2 == $cpf[10]) {
            return true;
        }
        else {
            return false;
        }
    }
   
    private function validaCNPJ($cnpj) {
   
        if (strlen($cnpj) <> 14)
            return false; 

        $soma = 0;
          
        $soma += ($cnpj[0] * 5);
        $soma += ($cnpj[1] * 4);
        $soma += ($cnpj[2] * 3);
        $soma += ($cnpj[3] * 2);
        $soma += ($cnpj[4] * 9); 
        $soma += ($cnpj[5] * 8);
        $soma += ($cnpj[6] * 7);
        $soma += ($cnpj[7] * 6);
        $soma += ($cnpj[8] * 5);
        $soma += ($cnpj[9] * 4);
        $soma += ($cnpj[10] * 3);
        $soma += ($cnpj[11] * 2); 

        $d1 = $soma % 11; 
        $d1 = $d1 < 2 ? 0 : 11 - $d1; 

        $soma = 0;
        $soma += ($cnpj[0] * 6); 
        $soma += ($cnpj[1] * 5);
        $soma += ($cnpj[2] * 4);
        $soma += ($cnpj[3] * 3);
        $soma += ($cnpj[4] * 2);
        $soma += ($cnpj[5] * 9);
        $soma += ($cnpj[6] * 8);
        $soma += ($cnpj[7] * 7);
        $soma += ($cnpj[8] * 6);
        $soma += ($cnpj[9] * 5);
        $soma += ($cnpj[10] * 4);
        $soma += ($cnpj[11] * 3);
        $soma += ($cnpj[12] * 2); 
          
          
        $d2 = $soma % 11; 
        $d2 = $d2 < 2 ? 0 : 11 - $d2; 
          
        if ($cnpj[12] == $d1 && $cnpj[13] == $d2) {
            return true;
        }
        else {
            return false;
        }
    } 

}