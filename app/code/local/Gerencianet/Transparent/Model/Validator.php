<?php
class Gerencianet_Transparent_Model_Validator {
	
	protected $_messages = array(
			'name'			=> "Motivo: O nome informado não é válido.\nPor favor, informe nome e sobrenome válidos!",
			'cpf'			=> "Motivo: O CPF informado não é válido.\nPor favor, informe um número de CPF válido!",
			'email'			=> "Motivo: O e-mail informado não é válido.\nPor favor, informe um e-mail no padrão: seu@email.com!",
			'birth'			=> "Motivo: Data de aniversário inválida.\nPor favor, informe uma data válida!",
			'street'		=> "Motivo: Endereço não informado.\nPor favor, informe um endereço válido!",
			'number'		=> "Motivo: Número do endereço não informado.\nPor favor, informe um número do endereço válido!",
			'neighborhood'	=> "Motivo: Bairro não informado.\nPor favor, informe um bairro válido!",
			'city'			=> "Motivo: Cidade não informada.\nPor favor, informe uma cidade válida!",
			'state'			=> "Motivo: Estado não informado.\nPor favor, informe um estado válido!",
			);
	
	protected $_validators = array(
			'name'			=> '_name',
			'cpf'			=> '_cpf',
			'email'			=> '_email',
			'birth'			=> '_birth',
			'street'		=> '_empty',
			'number'		=> '_empty',
			'neighborhood'	=> '_empty',
			'city'			=> '_empty',
			'state'			=> '_empty',
		);
	
	public function validate($data) {
		if (!is_array($data))
			return false;
		
		foreach ($data as $field=>$value) {
			if (!$this->isValid($field, $value))
				return false;
		}
		
		return true;
	}
	
	protected function isValid($field,$data) {
		if (isset($this->_validators[$field])) {
			if (!$this->{$this->_validators[$field]}($data)) {
				$this->_sendError($field);
			}
		}
		return true;
	}
	
	protected function _name($data) {
		$validation = new Zend_Validate_Regex('/^[A-zÀ-ú.\-\s]*$/');
		if (!$validation->isValid($data) || str_word_count($data) < 2) {
			return false;
		}
		
		return true;
	}
	
	protected function _email($data) {
		$validation = new Zend_Validate_EmailAddress();
		if (!$validation->isValid($data)) {
			return false;
		}
		return true;
	}
	
	protected function _birth($data) {
		$validation = new Zend_Validate_Date('YYYY-MM-DD');
		if (!$validation->isValid($data)) {
			return false;
		}
		return true;
	}
	
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
	
	protected function _empty($data) {
		$validation = new Zend_Validate_NotEmpty();
		if (!$validation->isValid($data)) {
			return false;
		}
		return true;
	} 
	
	protected function _sendError($msg) {
		Mage::throwException(Mage::helper('gerencianet_transparent')->__("Erro ao efetuar o pagamento!\n" . $this->_messages[$msg]));
	}
	
	
}