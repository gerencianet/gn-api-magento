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
class Gerencianet_Transparent_Model_Billet extends Gerencianet_Transparent_Model_Standard {

  /**
   * Model Properties
   */
  protected $_code 					= 'gerencianet_billet';
  protected $_formBlockType 			= 'gerencianet_transparent/billet_form';
  protected $_infoBlockType 			= 'gerencianet_transparent/billet_info';
  protected $_isGateway               = true;
  protected $_canAuthorize            = true;
  protected $_canCapture              = false;
  protected $_canCapturePartial       = false;
  protected $_canRefund               = false;
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
    $quote = Mage::getModel('checkout/session')->getQuote();
    $expires = Mage::getStoreConfig('payment/gerencianet_billet/duedate');
    $additionaldata['billet']['expires'] = date('Y-m-d',strtotime('+'.$expires.' days'));
    $additionaldata['juridical']['data_pay_billet_as_juridical'] = $data->getDataPayBilletAsJuridical();
    $additionaldata['juridical']['data_corporate_name'] = $data->getDataCorporateName();
    $additionaldata['juridical']['data_cnpj'] = $data->getDataCnpj();
    $additionaldata['customer']['data_name'] = $data->getDataName();
    $additionaldata['customer']['data_cpf'] = $data->getDataCpf();
    $additionaldata['customer']['data_email'] = $data->getDataEmail();
    $additionaldata['customer']['data_phone_number'] = $data->getDataPhoneNumber();
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
    if ($this->validateData('billet')) {
      $pay = $this->payCharge();
      Mage::log('PAY CHARGE BILLET: ' . var_export($pay,true),0,'gerencianet.log');

      if ($pay['code'] == 200) {
        $add_data = unserialize($this->getOrder()->getPayment()->getAdditionalData());
        $add_data['billet']['barcode'] = $pay['data']['barcode'];
        $add_data['billet']['link'] = $pay['data']['link'];
        $add_data['charge_id'] = $pay['data']['charge_id'];

        $payment->setAdditionalData(serialize($add_data));
        $payment->save();
      } else {
        Mage::throwException($this->_getHelper()->__("Erro na emissão do boleto!\nMotivo: " . $pay->error_description . ".\nPor favor tente novamente mais tarde!"));
      }
    }

    return $this;

  }

  /**
   * Returns payment data formatted to API
   *
   * @return array
   */
   protected function getPaymentData() {
     $expires = Mage::getStoreConfig('payment/gerencianet_billet/duedate');
     $add_data = unserialize($this->getOrder()->getPayment()->getAdditionalData());

     $instructions = array();
     for ($i=1;$i<=4;$i++) {
       $inst = Mage::getStoreConfig('payment/gerencianet_billet/instruction'.$i);
       if ($inst)
         $instructions[] = Mage::getStoreConfig('payment/gerencianet_billet/instruction'.$i);
     }

     $return = array(
       'banking_billet' => array(
           'expire_at' => $add_data['billet']['expires'],
           'customer' => $this->getCustomer('billet')
         )
     );

     if(count($instructions) > 0) {
       $return['banking_billet']['instructions'] = $instructions;
     }

     $return['banking_billet'] = $this->checkDiscount($return['banking_billet']);

     Mage::log("BANKING BILLET BODY: " . var_export($return,true),0,'gerencianet.log');
     return $return;
   }
}
