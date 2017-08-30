<?php

namespace Gerencianet;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Gerencianet\Exception\GerencianetException;
use Gerencianet\Exception\AuthorizationException;

class Request
{
    private $client;
    private $baseUri;
    private $auth;
    private $request;
    private $config;
    private $certified_path;

    public function __construct(array $options = null)
    {
        $this->config = Config::options($options);
        $composerData = json_decode(file_get_contents(__DIR__.'/../../composer.json'), true);
        $partner_token = isset($options['partner_token'])? $options['partner_token'] : "";
        $this->certified_path = file_exists(__DIR__."/../../../../../../certificado.crt")? __DIR__."/../../../../../../certificado.crt" : false;
        $this->client = new Client([
        'debug' => $this->config['debug'],
        'base_url' => $this->config['baseUri'],
        'headers' => [
          'Content-Type' => 'application/json',
          'api-sdk' => 'magento-0.3.0',
          'partner-token' => $partner_token
          ],
      ]);
    }

    public function send($method, $route, $requestOptions)
    {

        try {
            if($this->certified_path){
                $this->client->setDefaultOption("verify", $this->certified_path);
            }
            $this->request = $this->client->createRequest($method, $route, $requestOptions);
            $response = $this->client->send($this->request);

            return json_decode($response->getBody(), true);
        } catch (ClientException $e) {
            throw new AuthorizationException($e->getResponse()->getStatusCode(),
                       $e->getResponse()->getReasonPhrase(),
                       $e->getResponse()->getBody());
        } catch (ServerException $se) {
            throw new GerencianetException($se->getResponse()->getBody());
        }
    }

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }

    public function __set($property, $value)
    {
        if (property_exists($this, $property)) {
            $this->$property = $value;
        }
    }
}
