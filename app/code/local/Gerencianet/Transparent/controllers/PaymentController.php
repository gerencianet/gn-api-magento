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
class Gerencianet_Transparent_PaymentController extends Mage_Core_Controller_Front_Action {

	/**
	 * Send expire header to ajax response
	 *
	 */
	protected function _expireAjax() {
		$sessionInstance = Mage::getModel("core/session")->getSessionQuote();
		if (!Mage::getSingleton($sessionInstance)->getQuote()->hasItems()) {
			$this->getResponse()->setHeader('HTTP/1.1', '403 Session Expired');
			exit;
		}
	}
	
	/**
	 * Get singleton with  strandard order transaction information
	 *
	 * @return Gerencianet_Transparent_Model_Standard
	 */
	public function getStandard() {
		return Mage::getSingleton('gerencianet_transparent/standard');
	}
	
	/**
	 * Receives and process charge notifications
	 */
	public function notificationAction(){
		$token = $this->getRequest()->getParam('notification');

		if ($token) {
			$notifications = $this->getStandard()->getNotification($token);
			$current = $notifications[count($notifications)-1];
			$notificationData = array(
                'order'          => $current['custom_id'],
			    'status'         => $current['status']['current'],
			    'charge_id'      => $current['identifiers']['charge_id'],
			    'environment'    => Gerencianet_Transparent_Model_Standard::ENV_PRODUCTION
			);
			Mage::getResourceModel('gerencianet_transparent/notifications')->insert($notificationData);
		    Mage::getModel('gerencianet_transparent/updater')->updatecharge();
		}
	}
	
	/**
	 * Receives and process charge notifications
	 */
	public function notificationsbAction(){
	    $token = $this->getRequest()->getParam('notification');
	    if ($token) {
	        $notifications = $this->getStandard()->getNotificationSB($token);
	        $current = $notifications[count($notifications)-1];
	        $notificationData = array(
	            'order'          => $current['custom_id'],
	            'status'         => $current['status']['current'],
	            'charge_id'      => $current['identifiers']['charge_id'],
			    'environment'    => Gerencianet_Transparent_Model_Standard::ENV_TEST
	        );
	        Mage::getResourceModel('gerencianet_transparent/notifications')->insert($notificationData);
	        Mage::getModel('gerencianet_transparent/updater')->updatecharge();
	    }
	}
	
	/**
	 * Receives and process pix webhook
	 */
	public function pixWebhookAction(){

		$infoBody = json_decode($this->getRequest()->getRawBody());
		if ($infoBody) {

			$webhookData = array(
                'endToEndId'          => $infoBody->pix[0]->endToEndId,
			    'txid'         => $infoBody->pix[0]->txid,
			    'chave'      => $infoBody->pix[0]->chave,
			    'valor'      => $infoBody->pix[0]->valor,
			    'horario'      => $infoBody->pix[0]->horario
			);
			
			Mage::getResourceModel('gerencianet_transparent/webhook')->insert($webhookData);
			Mage::getModel('gerencianet_transparent/updater')->updatecharge();
		}
		
	}

	/**
	 * Retrieve installments for current quote
	 */
	public function installmentsAction() {
		$brand = $this->getRequest()->getParam('brand');
		$api = $this->getStandard()->getApi();
    	$sessionInstance = Mage::getModel("core/session")->getSessionQuote();
    	$quote = Mage::getModel($sessionInstance)->getQuote();
    	$params = array(
    			'total' => number_format($quote->getGrandTotal(),2,'',''),
    			'brand' => $brand
    		);
    	$installments = $api->getInstallments($params, array());
    	
    	echo '<select id="gerencianet_card_cc_installments" name="payment[cc_installments]">';
    	foreach($installments['data']['installments'] as $installment): 
    		echo '<option value="' . $installment['installment'] . '">' . $installment['installment'] . 'x de R$ ' . $installment['currency'] . (($installment['has_interest']) ? ' com juros' : ' sem juros') . '</option>';
    	endforeach;
		echo '</select>';
		exit();
	}
	
	/**
	 * Receives and process charge notifications
	 */
	public function updateAction(){
	    Mage::getModel('gerencianet_transparent/updater')->updatecharge();
	    echo 'PEDIDOS ATUALIZADOS';
	}

}
