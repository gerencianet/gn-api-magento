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
class Gerencianet_Transparent_Helper_Data extends Mage_Core_Helper_Data
{
	/**
	 * Update order status accordingly parameter notification
	 * @param array $notification
	 */
	public function updateOrderStatus($notification){
		$order = Mage::getModel('sales/order')->loadByIncrementId($notification['order']);
		if ($order) {
			switch($notification['status']) {
				case 'new':
					if($order->canUnhold()) {
					    $order->unhold();
					}
					$changeTo = Mage_Sales_Model_Order::STATE_PROCESSING;
					$comment = utf8_encode('Pedido Recebido');
					$order->setState($changeTo, 'gerencianet_new', $comment, $notified = false);
					break;
				case 'waiting':
					if($order->canUnhold()) {
						$order->unhold();
					}
					$changeTo = Mage_Sales_Model_Order::STATE_PROCESSING;
					$comment = utf8_encode('Aguardando Pagamento');
					$order->setState($changeTo, 'gerencianet_waiting', $comment, $notified = false);
					break;
				case 'contested':
					if($order->canHold()) {
						$changeTo = Mage_Sales_Model_Order::STATE_HOLDED;
						$comment = utf8_encode('Pagamento Contestado - Aguardando Posicionamento');
						$order->setHoldBeforeState($order->getState());
						$order->setHoldBeforeStatus($order->getStatus());
						$order->setState($changeTo, true, $comment, $notified = false);
					}
					break;
				case 'paid':
					if($order->canUnhold()) {
						$order->unhold();
					}
					if ($order->canInvoice()) {
						$changeTo = Mage_Sales_Model_Order::STATE_PROCESSING;
					
						$invoice = $order->prepareInvoice();
						$invoice->register()->pay();
						$invoice_msg = utf8_encode(sprintf('Pagamento confirmado. Transa&ccedil;&atilde;o Gerencianet: %s', $notification['identifiers']['charge_id']));
						$invoice->addComment($invoice_msg, true);
						$invoice->sendEmail(true, $invoice_msg);
						$invoice->setEmailSent(true);
					
						Mage::getModel('core/resource_transaction')
							->addObject($invoice)
							->addObject($invoice->getOrder())
							->save();
						$comment = utf8_encode(sprintf('Fatura #%s criada.', $invoice->getIncrementId()));
						$order->setState($changeTo, 'gerencianet_paid', $comment, $notified = true);
					}
					break;
				case 'unpaid':
					if($order->canUnhold()) {
						$order->unhold();
					}
					if($order->canCancel()) {
						$order->cancel();
						$comment = utf8_encode('Pagamento Inadimplente');
						$order->addStatusHistoryComment($comment, 'gerencianet_unpaid');
					}
					break;
				case 'refunded':
					if($order->canUnhold()) {
						$order->unhold();
					}
					if($order->canCancel()) {
						$order->cancel();
						$comment = utf8_encode('Pagamento Devolvido');
						$order->addStatusHistoryComment($comment, 'gerencianet_refunded');
					} else { // ORDER ALREADY PAID, MUST CREATE CREDIT MEMO
					    $orderItem = $order->getItemsCollection();
					    $service = Mage::getModel('sales/service_order', $order);
					    $qtys = array();
					    foreach ($orderItem as $item) {
					        $qtys[$item->getId()] = $item->getQtyOrdered();
					    }
					    $data = array(
					        'qtys' => $qtys
					    );
					    $creditMemo = $service->prepareCreditmemo($data)->register()->save();
					    $comment = utf8_encode('Pagamento Devolvido');
					    $order->addStatusHistoryComment($comment, 'gerencianet_refunded');
					}
					break;
				case 'canceled':
					if($order->canUnhold()) {
						$order->unhold();
					}
					if($order->canCancel()) {
						$order->getPayment()->setMessage("Pagamento cancelado.");
						$order->cancel();
					} else { // ORDER ALREADY PAID, MUST CREATE CREDIT MEMO
					    $orderItem = $order->getItemsCollection();
					    $service = Mage::getModel('sales/service_order', $order);
					    $qtys = array();
					    foreach ($orderItem as $item) {
					        $qtys[$item->getId()] = $item->getQtyOrdered();
					    }
					    $data = array(
					        'qtys' => $qtys
					    );
					    $creditMemo = $service->prepareCreditmemo($data)->register()->save();
					    $comment = utf8_encode('Pagamento cancelado');
					    $order->addStatusHistoryComment($comment, 'canceled');
					}
					break;
		
			}
			$order->save();
		}
	}

	/**
	 * Checks if is sandbox mode
	 * @return boolean
	 */
	public function isSandbox() {
		$environment = $this->getEnvironment();
		if ($environment == Gerencianet_Transparent_Model_Standard::ENV_TEST) {
			return true;
		}
		
		return false;
	}
	
	public function getEnvironment() {
	    return Mage::getStoreConfig('payment/gerencianet_transparent/environment');
	}
}