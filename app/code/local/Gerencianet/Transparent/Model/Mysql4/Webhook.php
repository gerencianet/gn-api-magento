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
class Gerencianet_Transparent_Model_Mysql4_Webhook extends Mage_Core_Model_Mysql4_Abstract {
    
	protected function _construct(){
        $this->_init('gerencianet_transparent/webhook', 'txid');
    }
	
    public function getAll($txid=NULL) {
        $read = $this->_getReadAdapter();
        $table = Mage::getSingleton('core/resource')->getTableName('gerencianet_transparent/webhook');

		$select = $read->select()->from($table);
		
		if ($txid) {
		    $select->where('txid = ' . $txid);
		}
		
		$rows = $read->fetchAll($select);
		
		return $rows;
    }
    
    public function deleteByE2eid($e2eid) {
    	$write = $this->_getWriteAdapter();
    	$table = Mage::getSingleton('core/resource')->getTableName('gerencianet_transparent/webhook');
		$endtoend = "endToEndId = \"" . $e2eid;
		$write->delete($table, $endtoend."\"");
    }
    
    public function update($data) {
    	$write = $this->_getWriteAdapter();
    	$table = Mage::getSingleton('core/resource')->getTableName('gerencianet_transparent/webhook');
    	$where = [];
		$where[] = $write->quoteInto('txid = ?', $data['txid']);

		$write->update($table, array(
			'endToEndId' 			=> $data['endToEndId'],
			'txid'			=> $data['txid'],
			'chave'			=> $data['chave'],
			'valor'			=> $data['valor'],
			'horario'			=> $data['horario']
		), $where);
	}

    public function preInsert($data) {
    	$write = $this->_getWriteAdapter();
    	$table = Mage::getSingleton('core/resource')->getTableName('gerencianet_transparent/webhook');
    	
		$write->insert($table, array(
			'txid'			=> $data['txid'],
			'order'			=> $data['order'],
		));
    }
    
    public function findOrderByTxid($txid) {
        $read = $this->_getReadAdapter();
        $table = Mage::getSingleton('core/resource')->getTableName('gerencianet_transparent/webhook');
		$select = $read->select()->from($table)->where("txid =  '". $txid."'");
		$row = $read->fetchRow($select);
		return $row['order'];
    }
    
}
