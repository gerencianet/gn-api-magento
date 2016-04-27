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
class Gerencianet_Transparent_Block_Card_Form extends Mage_Payment_Block_Form_Cc {

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

        if ($order->getCustomerDob()) {
            $dt_birth = date('d/m/Y',strtotime($order->getCustomerDob()));
        } else {
            $dt_birth = "";
        }

        $phone_number = preg_replace( '/[^0-9]/', '', $address->getTelephone());
        if (strlen($phone_number)<10 || strlen($phone_number)>11) {
            $phone_number = "";
        }

        $dataOrder = array(
            'customer_cc_data_name' => $address->getFirstname() . " " . $address->getLastname(), 
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

        

    	$testmode = Mage::helper('gerencianet_transparent')->isSandbox();
    	$account_id = Mage::getStoreConfig('payment/gerencianet_transparent/account_id');
    	$debugMode = Mage::getStoreConfig('payment/gerencianet_transparent/debug');
    	// add Gerencianet JS after <body>
    	$jsBlock = Mage::app()->getLayout()->createBlock('core/text', 'js_gerencianet');
    	$jsBlock->setText("<script type='text/javascript'>
    		//<![CDATA[
			var installmentsUrl = '" . Mage::getUrl("gerencianet/payment/installments") . "';
        	var loaderUrl = '".$this->getSkinUrl('images/opc-ajax-loader.gif')."';
    	    var brand = '';
    	    var isDebug = " . (($debugMode) ? "true" : "false" ) . ";
	        var declareObservers = '';
    	    //]]>
            </script>
    	    <script type='text/javascript' src='".Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS, true) . 'gerencianet/transparent.js'."'></script>
    	    <script type='text/javascript'>
    	    //<![CDATA[
    	    window.onload = function(){
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
			};
		//]]>
		</script>");
    			
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

}