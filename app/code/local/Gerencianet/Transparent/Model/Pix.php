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
class Gerencianet_Transparent_Model_Pix extends Gerencianet_Transparent_Model_Standard {

  /**
   * Model Properties
   */
  protected $_code 					          = 'gerencianet_pix';
  protected $_formBlockType 			    = 'gerencianet_transparent/pix_form';
  protected $_infoBlockType 			    = 'gerencianet_transparent/pix_info';
  protected $_isGateway               = true;
  protected $_canAuthorize            = true;
  protected $_canCapture              = false;
  protected $_canCapturePartial       = false;
  protected $_canRefund               = true;
  protected $_canVoid                 = true;
  protected $_canUseInternal          = true;
  protected $_canUseCheckout          = true;
  protected $_canUseForMultishipping  = true;

  /**
   * Assign data to current payment info instance
   *
   * @param $data - paymentData
   *
   * @return Gerencianet_Transparent_Model_Standard
   */
  public function assignData($data) {
    $info = $this->getInfoInstance();
    $sessionInstance = Mage::getModel("core/session")->getSessionQuote();
    $quote = Mage::getModel($sessionInstance)->getQuote();
    $order_total = $quote->getGrandTotal();

    //devedor
    $additionaldata['devedor']['nome'] = $quote->getCustomer()->getName();

    if(strlen($data->getDataCpfCnpj()) == 11){
      $additionaldata['devedor']['cpf'] = $data->getDataCpfCnpj();
    }else if(strlen($data->getDataCpfCnpj()) == 14){
      $additionaldata['devedor']['cnpj'] = $data->getDataCpfCnpj();
    }

    $info->setAdditionalData(serialize($additionaldata));

    return $this;
  }

  /**
   * Authorizes payment request
   *
   * @param Varien_Object $payment
   * @param double $amount
   */
  public function authorize(Varien_Object $payment, $amount)
  {
    $sessionInstance = Mage::getModel("core/session")->getSessionQuote();
    $quote = Mage::getModel($sessionInstance)->getQuote();

    if ($this->validateData('pix')) {
        $pay = $this->payPix();
        Mage::log('PAY PIX: '.var_export($pay,true),0,'gerencianet.log');

        if (isset($pay['imagemQrcode'])) {
          $add_data = unserialize($this->getOrder()->getPayment()->getAdditionalData());
          $add_data['pix']['imagemQrcode'] = $pay['imagemQrcode'];
          $add_data['pix']['copiaecola'] = $pay['qrcode'];

          $payment->setAdditionalData(serialize($add_data));
          $payment->save();
        } else {
          Mage::throwException($this->_getHelper()->__("Erro na emissão do Pix!\nMotivo: " . $pay->error_description . ".\nPor favor tente novamente mais tarde!"));
        }
      }
    return $this;
  }
}
