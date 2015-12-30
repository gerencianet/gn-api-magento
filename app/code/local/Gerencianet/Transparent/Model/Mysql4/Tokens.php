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
class Gerencianet_Transparent_Model_Mysql4_Tokens extends Mage_Core_Model_Mysql4_Abstract {
    
    CONST ST_ACTIVE = 1;
    CONST ST_USED   = 2;
    
	protected function _construct(){
        $this->_init('gerencianet_transparent/tokens', 'token');
    }
	
    public function deleteById($id) {
    	$write = $this->_getWriteAdapter();
    	$table = Mage::getSingleton('core/resource')->getTableName('gerencianet_transparent/tokens');
    	$write->delete($table, "quote_id = " . $id);
    }
    
    public function insert($data) {
        $this->deleteById($data['quote_id']);
    	$write = $this->_getWriteAdapter();
    	$table = Mage::getSingleton('core/resource')->getTableName('gerencianet_transparent/tokens');
    	
		$write->insert($table, array(
			'token' 			=> $data['token'],
			'quote_id'   		=> $data['quote_id'],
			'status'			=> self::ST_ACTIVE
		));
    }
    
    public function exists($token) {
        $read = $this->_getReadAdapter();
        $table = Mage::getSingleton('core/resource')->getTableName('gerencianet_transparent/tokens');
        $select = $read->select()->from($table,array("*"))->where("token = '" . $token . "'");
        $row = $read->fetchRow($select);

        if ($row) {
            return $row;
        }
        
        return false;
    }
    
    public function active($token) {
        if ($token['status'] == self::ST_ACTIVE)
            return true;
        
        return false;
    }
    
    public function setUsed($token) {
        $write = $this->_getWriteAdapter();
        $table = Mage::getSingleton('core/resource')->getTableName('gerencianet_transparent/tokens');
        $write->update($table, array('status' => self::ST_USED), 'token = "' . $token . '"');
    }
}
