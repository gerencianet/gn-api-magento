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
    	}
    }
}
