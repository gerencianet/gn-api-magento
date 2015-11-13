<?php
/**
 * Módulo de Pagamento Gerencianet para Magento
 * Cobrança Online
 * controllers/PaymentController.php
 *
 * NÃO MODIFIQUE OS ARQUIVOS DESTE MÓDULO PARA O BOM FUNCIONAMENTO DO MESMO
 * Em caso de dúvidas entre em contato com a Gerencianet. Contatos através do site:
 * http://www.gerencianet.com.br
 */

class Gerencianet_Transparent_PaymentController extends Mage_Core_Controller_Front_Action {

	/**
	 * Order instance
	 */
	protected $_order;
	
	/**
	 *  Get order
	 *
	 *  @return	  Mage_Sales_Model_Order
	 */
	public function getOrder() {
		if ($this->_order == null) {
			$this->_order = Mage::getModel('sales/order')->load(Mage::getSingleton('checkout/session')->getLastOrderId());
		}
		return $this->_order;
	}
	
	/**
	 * Send expire header to ajax response
	 *
	 */
	protected function _expireAjax() {
		if (!Mage::getSingleton('checkout/session')->getQuote()->hasItems()) {
			$this->getResponse()->setHeader('HTTP/1.1', '403 Session Expired');
			exit;
		}
	}
	
	/**
	 * Get singleton with paypal strandard order transaction information
	 *
	 * @return Mage_Paypal_Model_Standard
	 */
	public function getStandard() {
		return Mage::getSingleton('gerencianet_transparent/standard');
	}
	
	public function notificationAction(){
		$token = $this->getRequest()->getParam('notification');
		if ($token) {
			$notifications = Mage::getModel('gerencianet_transparent/standard')->getNotification($token);
			$current = $notifications[count($notifications)-1];
			Mage::helper('gerencianet_transparent')->updateOrderStatus($current['custom_id'],$current['status']['current']);
		}
	}
	
	public function installmentsAction() {
		$brand = $this->getRequest()->getParam('brand');
		$api = Mage::getModel('gerencianet_transparent/standard')->getApi();
    	$quote = Mage::getModel('checkout/session')->getQuote();
    	$params = array(
    			'total' => number_format($quote->getGrandTotal(),2,'',''),
    			'brand' => $brand
    		);
    	$parcelas = $api->getInstallments($params, array());
    	
    	echo '<select id="gerencianet_card_cc_installments" name="payment[cc_installments]">';
    	foreach($parcelas['data']['installments'] as $installment): 
    		echo '<option value="' . $installment['installment'] . '">' . $installment['installment'] . 'x de R$ ' . $installment['currency'] . (($installment['has_interest']) ? ' com juros' : ' sem juros') . '</option>';
    	endforeach;
		echo '</select>';
		exit();
	}

}