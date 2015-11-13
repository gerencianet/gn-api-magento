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
				case 'waiting':
					if($order->canHold()) {
						$changeTo = Mage_Sales_Model_Order::STATE_HOLDED;
						$comment = utf8_encode('Aguardando Pagamento');
						$order->setHoldBeforeState($order->getState());
						$order->setHoldBeforeStatus($order->getStatus());
						$order->setState($changeTo, true, $comment, $notified = false);
						$order->hold();
					}
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
						$order->setState($changeTo, true, $comment, $notified = true);
					}
					break;
				case 'unpaid':
				case 'refunded':
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