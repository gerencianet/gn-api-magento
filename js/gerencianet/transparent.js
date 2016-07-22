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
 //v0.2.1
var GerencianetTransparent = function GerencianetTransparent(){};
var checkToken, cardOwner, cardNumber, cardCvv, cardExpM, cardExpY, generatedPaymentToken, brand, installmentSelected = null;

GerencianetTransparent.onlyNumbers = function(elm){
	value = elm.value;
	value = value.replace(/\D/g,"");
	elm.value = value;
};

GerencianetTransparent.sendError = function(msg) {
	if(isDebug)
		console.error(msg);
};

GerencianetTransparent.sendLog = function(msg) {
	if(isDebug)
		console.log(msg);
};

GerencianetTransparent.payCardAsJuridical = function(element) {
	if (element.checked) {
		document.getElementById('gerencianet_card_cc_juridical_data').style.display = 'block';
	} else {
		document.getElementById('gerencianet_card_cc_juridical_data').style.display = 'none';
	}
};


var cardDataChange = true;
GerencianetTransparent.getPaymentToken = function() {
	if (cardDataChange) {
		cardDataChange=false;
		if ($$('input:checked[name="payment[cc_type]"]') && typeof $$('input:checked[name="payment[cc_type]"]') !== undefined) {
			var type 		= $$('input:checked[name="payment[cc_type]"]').first().value;
		} else {
			var type 		= document.getElementById('brandSave').value;
		}
		var number		= document.getElementById('gerencianet_card_cc_number').value;
		var cvv			= document.getElementById('gerencianet_card_cc_cid').value;
		var exp_month	= document.getElementById('gerencianet_card_cc_expiration').value;
		var exp_year	= document.getElementById('gerencianet_card_cc_expiration_yr').value;
		if (!type || !number || !cvv || !exp_month || !exp_year) {
			return false;
		}

	    var callback = function(error, response) {
			if(error) {
				alert("Os dados digitados do cartão são inválidos. Verifique as informações e tente novamente.");
			  GerencianetTransparent.sendError(error);
			} else {
			  document.getElementById('gerencianet_card_token').value = response.data.payment_token;
			  generatedPaymentToken = response.data.payment_token;
			  GerencianetTransparent.sendLog(response);
			}
		};

		$gn.checkout.getPaymentToken({
			brand: type,
			number: number,
			cvv: cvv,
			expiration_month: exp_month,
			expiration_year: exp_year
		}, callback);

		return true;
	}
};

GerencianetTransparent.calculateInstallments = function() {
    newBrand = $$('input:checked[name=\"payment[cc_type]\"]').first().value;
    if (document.getElementById('gerencianet_card_cc_installments') && typeof document.getElementById('gerencianet_card_cc_installments') !== undefined) {

	    if(brand != newBrand || document.getElementById('gerencianet_card_cc_installments').length == 0) {
	        brand = newBrand;
	        document.getElementById('gerencianet_card_token').value = '';

	        if (newBrand === 'amex') {
				document.getElementById('gerencianet_card_cc_cid').setAttribute('maxlength', 4);
			} else {
				document.getElementById('gerencianet_card_cc_cid').setAttribute('maxlength', 3);
			}

	        if (newBrand === 'aura') {
	  		    document.getElementById('gerencianet_card_cc_number').setAttribute('maxlength', 19);
			} else {
			    document.getElementById('gerencianet_card_cc_number').setAttribute('maxlength', 16);
			}

	        new Ajax.Request( installmentsUrl, {
	                method: 'POST',
	                parameters: 'brand=' + brand,
	                evalScripts: true,
	                onLoading: function(transport) {
	                        $('installments').innerHTML = '<img src="'+loaderUrl+'" /> <span style=\"color: #888;\">Carregando Parcelas...</span>';
	                },
	                onSuccess: function(transport) {
	                    if (200 == transport.status) {
	                    	$('installments').innerHTML = transport.responseText;
	                    	if (installmentSelected!=null) {
	                    		$("installments select").val($('installmentSabe').val());
	                    	}
	                    }
					},
	                onFailure: function() {}
	        });
	    }
	}
};

GerencianetTransparent.addFieldsObservers = function() {
    var ccNumElm = $('gerencianet_card_cc_number');
    if (ccNumElm != undefined) {

        var ccExpMoElm = $('gerencianet_card_cc_expiration');
        var ccExpYrElm = $('gerencianet_card_cc_expiration_yr');
        var ccCvvElm = $('gerencianet_card_cc_cid');

        clearInterval(declareObservers);

        Element.observe(ccNumElm,'keyup',function(e){GerencianetTransparent.onlyNumbers(this);});
        Element.observe(ccCvvElm,'keyup',function(e){GerencianetTransparent.onlyNumbers(this);});
    }
};

GerencianetTransparent.cardPaymentValidate = function() {
	if (document.getElementById('gerencianet_card_cc_number').value.length >=14 && 
		document.getElementById('gerencianet_card_cc_data_name_card').value!="" && 
		document.getElementById('gerencianet_card_cc_cid').value.length>=3 && 
		document.getElementById('gerencianet_card_cc_expiration').value!="" && 
		document.getElementById('gerencianet_card_cc_expiration_yr').value!="") {
		cardDataChange = true;
		checkToken = GerencianetTransparent.getPaymentToken();
	}
	

	return true;
}

GerencianetTransparent.rebuildSave = function() {
	/* MAGENTO CHECKOUT METHODS SAVING */

	if (typeof OSCPayment !== "undefined") { // One Step Checkout Brasil 6 Pro
	    OSCPayment._savePayment = OSCPayment.savePayment;
	    OSCPayment.savePayment = function() {
	    	if (OSCForm.validate()) {
		    	if (OSCPayment.currentMethod == 'gerencianet_card') {
		    		GerencianetTransparent.getPaymentToken();
	            	if (generatedPaymentToken == null)
	            		return false;
	            }
		    	OSCPayment._savePayment();
	    	}
	    }
	} else if (typeof OPC !== "undefined") { // One Step Checkout Brasil 4
	    OPC.prototype._save = OPC.prototype.save;
	    OPC.prototype.save = function() {
	    	if (this.validator.validate()) {
	            if (payment.currentMethod == 'gerencianet_card') {
	            	GerencianetTransparent.getPaymentToken();
	            	if (generatedPaymentToken == null)
	            		return false;
	            }
	            this._save();
	    	}
	    }
	} else if(typeof IWD !== "undefined" && typeof IWD.OPC !== "undefined") { // One Page Checkout IWD
	    IWD.OPC._savePayment = IWD.OPC.savePayment;
	    IWD.OPC.savePayment = function() {
	    	IWD.OPC.Checkout.lockPlaceOrder();
	        if (payment.currentMethod == 'gerencianet_card') {
	        	GerencianetTransparent.getPaymentToken();
            	if (generatedPaymentToken == null)
            		return false;
	        }
	        this._savePayment();
	    }
	} else if (typeof AWOnestepcheckoutForm !== "undefined") { //One Step Checkout by aheadWorks
	    AWOnestepcheckoutForm.prototype._placeOrder = AWOnestepcheckoutForm.prototype.placeOrder;
	    AWOnestepcheckoutForm.prototype.placeOrder = function() {
	    	if (this.validate()) {
		        if (awOSCPayment.currentMethod == 'gerencianet_card') {
		        	GerencianetTransparent.getPaymentToken();
		        	if (generatedPaymentToken == null)
	            		return false;
		        }
		        this._placeOrder();
	    	}
	    }
	} else if (typeof FireCheckout !== "undefined") { // FireCheckout
	    FireCheckout.prototype._save = FireCheckout.prototype.save;
	    FireCheckout.prototype.save = function() {
	        if (payment.currentMethod == 'gerencianet_card') {
	        	GerencianetTransparent.getPaymentToken();
	        	if (generatedPaymentToken == null)
            		return false;
	        }
            this._save();
	    }
	} else { // Default Magento Checkout
	    Payment.prototype._save = Payment.prototype.save;
	    Payment.prototype.save = function() {
	    	var validator = new Validation(this.form);
	        if (this.validate() && validator.validate()) {
		    	if (this.currentMethod == 'gerencianet_card') {
		    		GerencianetTransparent.getPaymentToken();
		        	if (generatedPaymentToken == null)
	            		return false;
		        }
	            this._save();
	        }
	    };

	    Review.prototype._save = Review.prototype.save;
	    Review.prototype.save = function() {
	    	if (payment.currentMethod == 'gerencianet_card') {
	        	GerencianetTransparent.getPaymentToken();
            	if (generatedPaymentToken == null)
            		return false;
	        }
            this._save();
	    }
	}
};

document.observe("dom:loaded", function() {
    declareObservers = setInterval(GerencianetTransparent.addFieldsObservers, 3000);
    GerencianetTransparent.rebuildSave();
});