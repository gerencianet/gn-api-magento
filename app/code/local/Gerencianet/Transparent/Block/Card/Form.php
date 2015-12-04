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
        $this->setTemplate('gerencianet/card/form.phtml');
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
    	// add Gerencianet JS after <body>
    	$jsBlock = Mage::app()->getLayout()->createBlock('core/text', 'js_gerencianet');
    	$jsBlock->setText("<script type='text/javascript'>
    		//<![CDATA[
			var brand = '';

			function onlyNumbers(evt){
				var e = event || evt; // for trans-browser compatibility
				var charCode = e.which || e.keyCode;
				if (charCode > 31 && (charCode < 48 || charCode > 57))
					return false;
				return true;
			}

			function checkToken() {
				var cards = document.getElementsByName('payment[cc_type]');
				for (i = 0; i < cards.length; i++) {
					if (cards[i].checked) {
						var type = cards[i].value;
						if (cards[i].value === 'amex') {
							document.getElementById('gerencianet_card_cc_cid').setAttribute('maxlength', 4);
						} else {
							document.getElementById('gerencianet_card_cc_cid').setAttribute('maxlength', 3);
						}
					}
				}
				var number		= document.getElementById('gerencianet_card_cc_number').value;
				var cvv			= document.getElementById('gerencianet_card_cc_cid').value;
				var exp_month	= document.getElementById('gerencianet_card_cc_expiration').value;
				var exp_year	= document.getElementById('gerencianet_card_cc_expiration_yr').value;
		
				if (!type) {
					return false;
				}
				
				if (!number) {
					return false;
				}
				if (!cvv) {
					return false;
				}
				if (!exp_month) {
					return false;
				}
				if (!exp_year) {
					return false;
				}
		
				var callback = function(error, response) {
					if(error) {
					  console.error(error);
					} else {
					  document.getElementById('gerencianet_card_token').value = response.data.payment_token;
					  console.log(response);
					}
				};
		
				$"."gn.checkout.getPaymentToken({
					brand: type,
					number: number,
					cvv: cvv,
					expiration_month: exp_month,
					expiration_year: exp_year
				}, callback);
			}

			function calculateInstallments(newBrand) {
		        if(brand != newBrand) {
		                brand = newBrand;
		        var urlAjax = location.href;
		        var validarUrl = /^https:\/\//;
		
		        if ( urlAjax.match( validarUrl ) ) {
		                urlAjax = '" . Mage::getBaseUrl("link", true) . "gerencianet/payment/installments';
		        } else {
		                urlAjax = '" . Mage::getUrl("gerencianet/payment/installments") . "';
		        }
		
		        new Ajax.Request( urlAjax, {
		                method: 'POST',
		                parameters: 'brand=' + brand,
		                evalScripts: true,
		                onLoading: function(transport) {
		                        $('installments').innerHTML = '<img src=\"".$this->getSkinUrl('images/opc-ajax-loader.gif')."\" /> <span style=\"color: #888;\">Carregando Parcelas...</span>';
		                },
		                onSuccess: function(transport) {
	                        if (200 == transport.status) {
	                        	$('installments').innerHTML = transport.responseText;
	                        }
						},
		                onFailure: function() {}
		        });
		        }
			}
		
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