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
    		
    		/*# changes order state to PENDING
		    $changeTo = Mage_Sales_Model_Order::STATE_PROCESSING;
		    $comment = utf8_encode('Pedido Recebido');
		    $order->setState($changeTo, 'gerencianet_new', $comment, $notified = false);
		    $order->save();*/
		    
    		Mage::getModel('gerencianet_transparent/standard')->updateCharge($order->getIncrementID(),$data['charge_id']);
            Mage::getModel('gerencianet_transparent/updater')->updatecharge($data['charge_id']);
    	}
    }
    
    public function setNew($event) {
        $payment = $event->getEvent()->getPayment();
        if (in_array($payment->getMethodInstance()->getCode(),array('gerencianet_billet','gerencianet_card'))) {
            # changes order state to PENDING
            $order = $payment->getOrder();
            $changeTo = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
            $comment = utf8_encode('Pedido Recebido');
            $order->setState($changeTo, 'gerencianet_new', $comment, $notified = false);
            $order->save();
        }
    }
    
    public function adminPrepareLayoutBefore()
    {
        $this->_prepareLayoutBefore('admin');
    }

    public function frontendPrepareLayoutBefore()
    {
        $this->_prepareLayoutBefore('frontend');
    }

    protected function _prepareLayoutBefore($area)
    {
        switch($area){
            case 'admin':
                Mage::getModel('core/session')->setSessionQuote('adminhtml/session_quote');
                break;

            case 'frontend':
                Mage::getModel('core/session')->setSessionQuote('checkout/session');
                break;
        }
    }

    public function prepareLayoutBefore(Varien_Event_Observer $observer)
    {
        $block = $observer->getEvent()->getBlock();
        if ("head" == $block->getNameInLayout()) {
            $block->addJs('gerencianet/jquery-1.12.3.min.js');
            $block->addJs('gerencianet/jquery.maskedinput.js');
            $block->addJs('gerencianet/jquery.noconflict.js');
        }

        return this;
    }
}
