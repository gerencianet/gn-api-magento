<?php
class Gerencianet_Transparent_Model_Observer
{
    public function updatecharge($event)
    {
        $order_id = $event->getData('order_ids'); 
    	$order = Mage::getModel('sales/order')->load($order_id);
    	$payment = $order->getPayment();
    	if (in_array($payment->getMethod(),array('gerencianet_billet','gerencianet_card'))) {
    		$data = unserialize($payment->getAdditionalData());
    		Mage::getModel('gerencianet_transparent/standard')->updateCharge($order->getIncrementID(),$data['charge_id']);
    		
    		# changes order state to PENDING
    		$state = $order->getState();
    		$status = $order->getStatus();
    		$defaultStatus = Mage::getSingleton('sales/order_config')->getStateDefaultStatus(Mage_Sales_Model_Order::STATE_PROCESSING);
    		if ($state == Mage_Sales_Model_Order::STATE_PROCESSING && $status == $defaultStatus) {
	    		$changeTo = Mage_Sales_Model_Order::STATE_NEW;
	    		$comment = utf8_encode('Pedido Recebido');
	    		$order->setState($changeTo, true, $comment, $notified = false);
	    		$order->save();
    		}
    	}
    }
}
