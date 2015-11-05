<?php
class Gerencianet_Transparent_Model_Loader {
	protected $_files = array(
			'Psr' => array(
					'Http/Message/MessageInterface',
					'Http/Message/RequestInterface',
					'Http/Message/ResponseInterface',
					'Http/Message/ServerRequestInterface',
					'Http/Message/StreamInterface',
					'Http/Message/UploadedFileInterface',
					'Http/Message/UriInterface',
					),
			'GuzzleHttp' => array(
					# Promises
					'Promise/functions_include',
					'Promise/PromiseInterface',
					'Promise/Promise',
					'Promise/RejectionException',
					'Promise/AggregateException',
					'Promise/CancellationException',
					'Promise/PromisorInterface',
					'Promise/EachPromise',
					'Promise/FulfilledPromise',
					'Promise/RejectedPromise',
					'Promise/TaskQueue',
						
					# Psr7
					'Psr7/functions_include',
					'Psr7/StreamDecoratorTrait',
					'Psr7/AppendStream',
					'Psr7/BufferStream',
					'Psr7/CachingStream',
					'Psr7/DroppingStream',
					'Psr7/FnStream',
					'Psr7/InflateStream',
					'Psr7/LazyOpenStream',
					'Psr7/LimitStream',
					'Psr7/MessageTrait',
					'Psr7/MultipartStream',
					'Psr7/NoSeekStream',
					'Psr7/PumpStream',
					'Psr7/Request',
					'Psr7/Response',
					'Psr7/Stream',
					'Psr7/StreamWrapper',
					'Psr7/Uri',
					
					# Guzzle
					'Guzzle/Cookie/CookieJarInterface',
					'Guzzle/Cookie/CookieJar',
					'Guzzle/Cookie/FileCookieJar',
					'Guzzle/Cookie/SessionCookieJar',
					'Guzzle/Cookie/SetCookie',
					'Guzzle/Exception/GuzzleException',
					'Guzzle/Exception/TransferException',
					'Guzzle/Exception/RequestException',
					'Guzzle/Exception/BadResponseException',
					'Guzzle/Exception/ClientException',
					'Guzzle/Exception/ConnectException',
					'Guzzle/Exception/SeekException',
					'Guzzle/Exception/ServerException',
					'Guzzle/Exception/TooManyRedirectsException',
					'Guzzle/Handler/CurlFactoryInterface',
					'Guzzle/Handler/CurlFactory',
					'Guzzle/Handler/CurlHandler',
					'Guzzle/Handler/CurlMultiHandler',
					'Guzzle/Handler/EasyHandle',
					'Guzzle/Handler/MockHandler',
					'Guzzle/Handler/Proxy',
					'Guzzle/Handler/StreamHandler',
					'Guzzle/functions_include',
					'Guzzle/ClientInterface',
					'Guzzle/Client',
					'Guzzle/HandlerStack',
					'Guzzle/MessageFormatter',
					'Guzzle/Middleware',
					'Guzzle/Pool',
					'Guzzle/PrepareBodyMiddleware',
					'Guzzle/RedirectMiddleware',
					'Guzzle/RequestOptions',
					'Guzzle/RetryMiddleware',
					'Guzzle/TransferStats',
					'Guzzle/UriTemplate',
					),
			'Gerencianet' => array(
					'Exception/AuthorizationException',
					'Exception/GerencianetException',
					'ApiRequest',
					'Auth',
					'Config',
					'Endpoints',
					'Request',
					'Gerencianet'),
			);
	
	public function getLibs() {
		# Load Psr library
		$this->_load('Psr');
		
		# Load GuzzleHttp library
		$this->_load('GuzzleHttp');
		
		# Load Gerencianet library
		$this->_load('Gerencianet');
	}
	
	protected function _load($folder)
	{
		$dir = Mage::getBaseDir('lib') . DS . $folder . DS;
// 		Mage::log('MAIN DIR:' . $dir,0,'gerencianet.log');
		
		foreach ($this->_files[$folder] as $file) {
			$filename = $dir . $file . ".php";
// 			Mage::log('FILE:' . $filename,0,'gerencianet.log');
			if (file_exists($filename)) 
				require_once $filename;
		}
	}

}