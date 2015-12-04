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
class Gerencianet_Transparent_Block_Card_Info extends Mage_Payment_Block_Info_Ccsave
{
	/**
	 * Block constructor
	 */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('gerencianet/card/info.phtml');
    }

	/**
     * Retrieve current order model instance
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        $order = Mage::registry('current_order');

		$info = $this->getInfo();

		if (!$order) {
			if ($this->getInfo() instanceof Mage_Sales_Model_Order_Payment) {
				$order = $this->getInfo()->getOrder();
			}
		}
		return($order);
    }
}