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
class Gerencianet_Transparent_Model_Validator {
	/*
	 * Model properties
	 */
	protected $_messages = array(
			'name'			=> "Motivo: O nome informado não é válido.\nPor favor, informe nome e sobrenome válidos!",
			'cpf'			=> "Motivo: O CPF informado não é válido.\nPor favor, informe um número de CPF válido!",
			'corporate'		=> "Motivo: A Razão Social informada não é válida.\nPor favor, informe uma Razão Social válida!",
			'cnpj'			=> "Motivo: O CNPJ informado não é válido.\nPor favor, informe um número de CNPJ válido!",
			'email'			=> "Motivo: O e-mail informado não é válido.\nPor favor, informe um e-mail no padrão: seu@email.com!",
			'birth'			=> "Motivo: Data de aniversário inválida.\nPor favor, informe uma data válida!",
			'street'		=> "Motivo: Endereço não informado.\nPor favor, informe um endereço válido!",
			'number'		=> "Motivo: Número do endereço não informado.\nPor favor, informe um número do endereço válido!",
			'neighborhood'	=> "Motivo: Bairro não informado.\nPor favor, informe um bairro válido!",
			'city'			=> "Motivo: Cidade não informada.\nPor favor, informe uma cidade válida!",
			'state'			=> "Motivo: Estado não informado.\nPor favor, informe um estado válido!",
	        'zipcode'		=> "Motivo: O CEP informado não é válido.\nPor favor, informe um CEP válido com 8 dígitos!",
			);
	
	protected $_validators_billet = array(
			'name'			=> '_name',
			'cpf'			=> '_cpf',
			'email'			=> '_email',
		);

	protected $_validators_card = array(
			'name'			=> '_name',
			'cpf'			=> '_cpf',
			'email'			=> '_email',
			'birth'			=> '_birth',
			'street'		=> '_empty',
			'number'		=> '_empty',
			'neighborhood'	=> '_empty',
			'city'			=> '_empty',
			'state'			=> '_empty',
	        'zipcode'		=> '_zipcode',
		);
	
	/**
	 * Validates given data array
	 * @param array $data
	 * @return boolean
	 */
	public function validate($data, $paymentType) {

		if (!is_array($data))
			return false;
		
		foreach ($data as $field=>$value) {
			if (!$this->isValid($field, $value, $paymentType))
				return false;
		}
		
		return true;
	}
	
	/**
	 * Validate Juridical Person
	 * @param array $data
	 * @return boolean
	 */
	public function validateJuridicalPerson($corporate_name, $cnpj, $paymentType) {
		if (!$this->_empty($corporate_name)) {
			$this->_sendError('corporate', $paymentType);
			return false;
		}
		if (!$this->_cnpj($cnpj)) {
			$this->_sendError('cnpj', $paymentType);
			return false;
		}
		
		return true;
	}
	

	/**
	 * Check a field has valid data
	 * @param string $field
	 * @param string|int $data
	 * @return boolean
	 */
	protected function isValid($field,$data,$paymentType) {
		if ($paymentType=="card") {
			if (isset($this->_validators_card[$field])) {
				if (!$this->{$this->_validators_card[$field]}($data)) {
					$this->_sendError($field, $paymentType);
				}
			}			
		} else {
			if (isset($this->_validators_billet[$field])) {
				if (!$this->{$this->_validators_billet[$field]}($data)) {
					$this->_sendError($field, $paymentType);
				}
			}
		}
		return true;
	}
	
	/**
	 * Validates name field
	 * @param string $data
	 * @return boolean
	 */
	protected function _name($data) {
		$validation = new Zend_Validate_Regex('/^[A-zÀ-ú.\-\s]*$/');
		if (!$validation->isValid($data) || str_word_count($data) < 2) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Validates email field
	 * @param string $data
	 * @return boolean
	 */
	protected function _email($data) {
		$validation = new Zend_Validate_EmailAddress();
		if (!$validation->isValid($data)) {
			return false;
		}
		return true;
	}
	
	/**
	 * Validates birthdate fields
	 * @param string $data
	 * @return boolean
	 */
	protected function _birth($data) {
		$validation = new Zend_Validate_Date('YYYY-MM-DD');
		if (!$validation->isValid($data)) {
			return false;
		}
		return true;
	}
	
	/**
	 * Validates CPF data
	 * @param string $data
	 * @return boolean
	 */
	protected function _cpf($data) {
		if(empty($data)) {
			return false;
		}
		
		$cpf = preg_replace('[^0-9]', '', $data);
		$cpf = str_pad($cpf, 11, '0', STR_PAD_LEFT);
		 
		if (strlen($cpf) != 11) {
			return false;
		} elseif ($cpf == '00000000000' ||
				$cpf == '11111111111' ||
				$cpf == '22222222222' ||
				$cpf == '33333333333' ||
				$cpf == '44444444444' ||
				$cpf == '55555555555' ||
				$cpf == '66666666666' ||
				$cpf == '77777777777' ||
				$cpf == '88888888888' ||
				$cpf == '99999999999') {
			return false;
		} else {
			for ($t = 9; $t < 11; $t++) {
				 
				for ($d = 0, $c = 0; $c < $t; $c++) {
					$d += $cpf{$c} * (($t + 1) - $c);
				}
				$d = ((10 * $d) % 11) % 10;
				if ($cpf{$c} != $d) {
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * Validates CNPJ data
	 * @param string $data
	 * @return boolean
	 */
	protected function _cnpj($cnpj) {
		if(empty($cnpj)) {
			return false;
		}
		
		$cnpj = preg_replace('/[^0-9]/', '', (string) $cnpj);
		
		if (strlen($cnpj) != 14)
			return false;
		
		for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++)
		{
			$soma += $cnpj{$i} * $j;
			$j = ($j == 2) ? 9 : $j - 1;
		}
		$resto = $soma % 11;
		if ($cnpj{12} != ($resto < 2 ? 0 : 11 - $resto))
			return false;
		
		for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++)
		{
			$soma += $cnpj{$i} * $j;
			$j = ($j == 2) ? 9 : $j - 1;
		}
		$resto = $soma % 11;
		return $cnpj{13} == ($resto < 2 ? 0 : 11 - $resto);
	}
	
	/**
	 * Validates zipcode fields
	 * @param string $data
	 * @return boolean
	 */
	protected function _zipcode($data) {
	    $zipcode = preg_replace('/[^0-9\s]/', '',$data);
		if (strlen($zipcode) < 8) {
	        return false;
	    }
	    return true;
	}
	
	/**
	 * Validate field is empty
	 * @param string $data
	 * @return boolean
	 */
	protected function _empty($data) {
		$validation = new Zend_Validate_NotEmpty();
		if (!$validation->isValid($data)) {
			return false;
		}
		return true;
	} 
	
	/**
	 * Send error message
	 * @param Exception $msg
	 */
	protected function _sendError($msg, $paymentType) {
		Mage::throwException(Mage::helper('gerencianet_transparent')->__("Erro ao efetuar o pagamento!\n" . $this->_messages[$msg]));
	}
	
	
}