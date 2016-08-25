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
class Gerencianet_Transparent_Block_Card_Form extends Mage_Payment_Block_Form_Cc {

    /**
     * Block constructor
     */
    protected function _construct() {
    	parent::_construct();

        $order = Mage::getModel('checkout/session')->getQuote();
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

        if ($order->getCustomerDob()) {
            $dt_birth = date('d/m/Y',strtotime($order->getCustomerDob()));
        } else {
            $dt_birth = "";
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
            $name = "";
        }

        if (strlen($name)<1 || !preg_match("/^[ ]*(?:[^\\s]+[ ]+)+[^\\s]+[ ]*$/",$name) || $name=="undefined") {
            $name = "";
        }


        $dataOrder = array(
            'customer_cc_data_name' => $name, 
            'customer_cc_data_document' => $customerDocument, 
            'customer_cc_data_juridical' => $juridical, 
            'customer_cc_data_email' => $email,
            'customer_cc_data_birth' => $dt_birth,
            'customer_cc_data_phone_number' => preg_replace( '/[^0-9]/', '', $phone_number),
            'billing_cc_data_street' => $address->getStreet1(),
            'billing_cc_data_number' => $address->getStreet2(),
            'billing_cc_data_zipcode' => preg_replace('/[^0-9\s]/', '',$address->getPostcode()),
            'billing_cc_data_neighborhood' => $address->getStreet4(),
            'billing_cc_data_state' => $address->getRegionCode(),
            'billing_cc_data_city' => $address->getCity(),
            'billing_cc_data_complement' => $address->getStreet3(),
            'order_total' => $order_total
            );

        $this->setData($dataOrder)->setTemplate('gerencianet/card/form.phtml');
    }

    /**
     * Returns card types from source model
     */
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
        $ccTypes = $this->getSourceModel()->toOptionArray();

        $return = array();
        foreach($ccTypes as $type) {
            $return[$type['value']] = $type['label'];
        }

        return $return;
    }
    
    /**
     * Prepares layout for show on screen
     */
    protected function _prepareLayout(){

        $addToJS = "
        <script type='text/javascript' src='".Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS, true)."gerencianet/jquery-1.12.3.min.js'; ?>'></script>
        <script type='text/javascript' src='".Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS, true)."gerencianet/jquery.maskedinput.js'; ?>'></script>
        <script type='text/javascript'>
            //<![CDATA[
            jQuery.noConflict();
            var $"."gn = jQuery.noConflict();
            //]]>
        </script>";

    	$testmode = Mage::helper('gerencianet_transparent')->isSandbox();
    	$account_id = Mage::getStoreConfig('payment/gerencianet_transparent/account_id');
    	$debugMode = Mage::getStoreConfig('payment/gerencianet_transparent/debug');

        $isSecure = Mage::app()->getStore()->isCurrentlySecure();
        if ($isSecure) {
            $url_installments = str_replace("http://","https://",Mage::getUrl("gerencianet/payment/installments"));
        } else {
            $url_installments = Mage::getUrl("gerencianet/payment/installments");
        }

    	// add Gerencianet JS after <body>
    	$jsBlock = Mage::app()->getLayout()->createBlock('core/text', 'js_gerencianet');
    	$jsBlock->setText($addToJS."<script type='text/javascript'>
            //v 0.2.3
    		//<![CDATA[
			var installmentsUrl = '" . $url_installments . "';
        	var loaderUrl = '".$this->getSkinUrl('images/opc-ajax-loader.gif')."';
    	    var brand = '';
    	    var isDebug = " . (($debugMode) ? "true" : "false" ) . ";
	        var declareObservers = '';
    	    //]]>
            </script>
    	    <script type='text/javascript' src='".Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS, true) . 'gerencianet/transparent.js'."'></script>
    	    <script type='text/javascript'>
    	    //<![CDATA[
    	    
				var s=document.createElement('script');
				s.type='text/javascript';
				var v=parseInt(Math.random()*1000000);
				s.src='https://".( ($testmode) ? 'sandbox' : 'api' ) . ".gerencianet.com.br/v1/cdn/" . $account_id . "/'+v;
				s.async=false;
				s.id='". $account_id . "';
				if(!document.getElementById('" . $account_id . "')){
					document.getElementsByTagName('head')[0].appendChild(s);
				};
    			$"."gn={validForm:true,processed:false,done:{},ready:function(fn){ $"."gn.done=fn; }};
				$"."gn.ready(function(checkout) {});
			
		//]]>
		</script>

        ");
    			
    	$head = Mage::app()->getLayout()->getBlock('after_body_start');
    
    	if($head) {
    		$head->append($jsBlock);
    	}

    	return parent::_prepareLayout();
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