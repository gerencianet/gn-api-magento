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
class Gerencianet_Transparent_Model_Updater
{
    /**
     * Updates orders with buffered notifications 
     */
	public function updatecharge($ID=NULL)
    {
        $notifications = Mage::getResourceModel('gerencianet_transparent/notifications')->getAll($ID);
        foreach ($notifications as $notification) {
            if (!$notification['order']) {
                $order = Mage::getResourceModel('gerencianet_transparent/notifications')->findOrderByCharge($notification['charge_id'], $notification['environment']);
                if (!$order) {
                    continue;
                }
                $notification['order'] = $order;
            }
            Mage::helper('gerencianet_transparent')->updateOrderStatus($notification);
            Mage::getResourceModel('gerencianet_transparent/notifications')->deleteById($notification['id']);
        }   
    }
}
