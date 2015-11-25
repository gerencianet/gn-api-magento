<?php
/**
 * Módulo de Pagamento Gerencianet para Magento
 * Cobrança Online
 * Helper/Data.php
 *
 * NÃO MODIFIQUE OS ARQUIVOS DESTE MÓDULO PARA O BOM FUNCIONAMENTO DO MESMO
 * Em caso de dúvidas entre em contato com a Gerencianet. Contatos através do site:
 * http://www.gerencianet.com.br
 */

class Gerencianet_Transparent_Helper_Data extends Mage_Core_Helper_Data
{
	public function updateOrderStatus($notification){
		$order = Mage::getModel('sales/order')->loadByIncrementId($notification['custom_id']);
		if ($order) {
			switch($notification['status']['current']) {
				case 'new':
					if($order->canUnhold()) {
						$order->unhold();
					}
					$changeTo = Mage_Sales_Model_Order::STATE_NEW;
					$comment = utf8_encode('Pedido Recebido');
					$order->setState($changeTo, true, $comment, $notified = false);
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
						$order->hold();
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
					}
					break;
				case 'canceled':
					if($order->canUnhold()) {
						$order->unhold();
					}
					if($order->canCancel()) {
						$order->getPayment()->setMessage("Pagamento cancelado.");
						$order->cancel();
					}
					break;
		
			}
			$order->save();
		}
	}    
}