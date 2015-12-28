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
class Gerencianet_Transparent_Model_Mysql4_Notifications extends Mage_Core_Model_Mysql4_Abstract {
    
	protected function _construct(){
        $this->_init('gerencianet_transparent/notifications', 'id');
    }
	
    public function getAll($ID=NULL) {
        $read = $this->_getReadAdapter();
        $table = Mage::getSingleton('core/resource')->getTableName('gerencianet_transparent/notifications');

		$select = $read->select()->from($table);
		
		if ($ID) {
		    $select->where('charge_id = ' . $ID);
		}
		
		$rows = $read->fetchAll($select);
		
		return $rows;
    }
    
    public function deleteById($id) {
    	$write = $this->_getWriteAdapter();
    	$table = Mage::getSingleton('core/resource')->getTableName('gerencianet_transparent/notifications');
    	$write->delete($table, "id = " . $id);
    }
    
    public function insert($data) {
    	$write = $this->_getWriteAdapter();
    	$table = Mage::getSingleton('core/resource')->getTableName('gerencianet_transparent/notifications');
    	
		$write->insert($table, array(
			'order' 			=> $data['order'],
			'status'			=> $data['status'],
			'charge_id'			=> $data['charge_id']
		));
    }
    
    public function findOrderByCharge($charge_id, $environment) {
        $read = $this->_getReadAdapter();
        
        $table = Mage::getSingleton('core/resource')->getTableName('sales/order_payment');
        $select = $read->select()->from($table)->where('gerencianet_charge_id = ' . $charge_id);

        $row = $read->fetchRow($select);

        if (!$row) {
            return false;
        }
        
        $order = Mage::getModel('sales/order')->load($row['parent_id']);
        
        return $order->getIncrementId();
    }
    
}
