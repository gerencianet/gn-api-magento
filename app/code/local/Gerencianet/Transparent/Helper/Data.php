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
	public function updateOrderStatus($orderID,$status){
		$order = Mage::getModel('sales/order')->loadByIncrementId($orderID);
		if ($order) {
			switch($current['status']['current']) {
				case 'waiting':
				case 'contested':
					if($order->canHold()) {
						$order->hold();
					}
					break;
				case 'paid':
					if($order->canUnhold()) {
						$order->unhold();
					}
					$order->setState('processing', true);
					break;
				case 'unpaid':
				case 'refunded':
				case 'canceled':
					if($order->canUnhold()) {
						$order->unhold();
					}
					if($order->canCancel()) {
						$order->cancel();
					}
					break;
		
			}
			$order->save();
		}
	}    
}