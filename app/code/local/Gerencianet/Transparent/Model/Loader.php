<?php
class Gerencianet_Transparent_Model_Loader
{
	public function __construct() {
		# Load Gerencianet library
		$this->_load('Gerencianet');
		
		# Load GuzzleHttp library
		$this->_load('GuzzleHttp');
	}
	
	public function load($folder)
	{
		$dir = Mage::getBaseDir('lib') . "folder";
		$dh  = opendir($dir);
		$dir_list = array($dir);
		while (false !== ($filename = readdir($dh))) {
			if($filename!="."&&$filename!=".."&&is_dir($dir.$filename))
				array_push($dir_list, $dir.$filename."/");
		}
		foreach ($dir_list as $dir) {
			foreach (glob($dir."*.php") as $filename)
				require_once $filename;
		}
	}

}