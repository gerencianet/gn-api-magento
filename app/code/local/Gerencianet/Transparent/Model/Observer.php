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
class Gerencianet_Transparent_Model_Observer
{
    /**
     * Updates recently created charge 
     * @param Varien_Object $event
     */
	public function updatecharge($event)
    {
        $order_id = $event->getEvent()->getOrder()->getId(); 
    	$order = Mage::getModel('sales/order')->load($order_id);
        $payment = $order->getPayment();
    	if (in_array($payment->getMethod(),array('gerencianet_billet','gerencianet_card'))) {
    		$data = unserialize($payment->getAdditionalData());
    		$payment->setGerencianetChargeId($data['charge_id']);
    		$payment->setGerencianetEnvironment((string)Mage::helper('gerencianet_transparent')->getEnvironment());
    		$payment->save();
    		
    		# changes order state to PENDING
		    $changeTo = Mage_Sales_Model_Order::STATE_PROCESSING;
		    $comment = utf8_encode('Pedido Recebido');
		    $order->setState($changeTo, 'gerencianet_new', $comment, $notified = false);
		    $order->save();
		    
    		Mage::getModel('gerencianet_transparent/standard')->updateCharge($order->getIncrementID(),$data['charge_id']);
    	}
    }
    
    public function processnotifications($observer) {
        $order_id = $observer->getData('order_ids');
        $order = Mage::getModel('sales/order')->load($order_id[0]);
        $payment = $order->getPayment();
        Mage::getModel('gerencianet_transparent/updater')->updatecharge($payment->getGerencianetChargeId());
    }
}
