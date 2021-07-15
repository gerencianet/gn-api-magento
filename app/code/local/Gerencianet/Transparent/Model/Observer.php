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
 * @copyright  Copyright (c) 2016 Gerencianet (http://www.gerencianet.com.br)
 * @author     Gerencianet
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Gerencianet_Transparent_Model_Observer
{
    protected $tlsOk = true;
    protected $_configPath = 'payment/gerencianet_transparent/';

    /**
     * Updates recently created charge 
     * @param Varien_Object $event
     */
    public function updatecharge($event)
    {
        $order_id = $event->getEvent()->getOrder()->getId();
        $order = Mage::getModel('sales/order')->load($order_id);
        $payment = $order->getPayment();
        if (in_array($payment->getMethod(), array('gerencianet_billet', 'gerencianet_card'))) {
            $data = unserialize($payment->getAdditionalData());
            $payment->setGerencianetChargeId($data['charge_id']);
            $payment->setGerencianetEnvironment((string)Mage::helper('gerencianet_transparent')->getEnvironment());
            $payment->save();

            /*# changes order state to PENDING
		    $changeTo = Mage_Sales_Model_Order::STATE_PROCESSING;
		    $comment = utf8_encode('Pedido Recebido');
		    $order->setState($changeTo, 'gerencianet_new', $comment, $notified = false);
		    $order->save();*/

            Mage::getModel('gerencianet_transparent/standard')->updateCharge($order->getIncrementID(), $data['charge_id']);
            Mage::getModel('gerencianet_transparent/updater')->updatecharge($data['charge_id']);
        }
    }

    public function setNew($event)
    {
        $payment = $event->getEvent()->getPayment();
        if (in_array($payment->getMethodInstance()->getCode(), array('gerencianet_billet', 'gerencianet_card'))) {
            # changes order state to PENDING
            $order = $payment->getOrder();
            $changeTo = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
            $comment = utf8_encode('Pedido Recebido');
            $order->setState($changeTo, 'gerencianet_new', $comment, $notified = false);
            $order->save();
        }
    }

    public function adminPrepareLayoutBefore()
    {
        $this->_prepareLayoutBefore('admin');
    }

    public function frontendPrepareLayoutBefore()
    {
        $this->_prepareLayoutBefore('frontend');
    }

    protected function _prepareLayoutBefore($area)
    {
        switch ($area) {
            case 'admin':
                Mage::getModel('core/session')->setSessionQuote('adminhtml/session_quote');
                break;

            case 'frontend':
                Mage::getModel('core/session')->setSessionQuote('checkout/session');
                break;
        }
    }

    public function prepareLayoutBefore(Varien_Event_Observer $observer)
    {
        $block = $observer->getEvent()->getBlock();
        if ("head" == $block->getNameInLayout()) {
            $block->addJs('gerencianet/jquery-1.12.3.min.js');
            $block->addJs('gerencianet/jquery.maskedinput.js');
            $block->addJs('gerencianet/jquery.noconflict.js');
        }

        return $this;
    }

    public function checkTLS()
    {
        $ch = curl_init();
        $options = array(
            CURLOPT_URL         => "https://tls.testegerencianet.com.br",
            CURLOPT_RETURNTRANSFER         => true,
            CURLOPT_FOLLOWLOCATION         => true,
            CURLOPT_HEADER         => false,  // don't return headers
            CURLOPT_MAXREDIRS      => 10,     // stop after 10 redirects
            CURLOPT_AUTOREFERER    => true,   // set referrer on redirect
            CURLOPT_CONNECTTIMEOUT => 5,    // time-out on connect
            CURLOPT_TIMEOUT        => 5,    // time-out on response
        );
        curl_setopt_array($ch, $options);
        $content = curl_exec($ch);
        $info = curl_getinfo($ch);

        if (($info['http_code'] !== 200) && ($content !== 'Gerencianet_Connection_TLS1.2_OK!')) {
            $this->tlsOk = false;
            Mage::getModel('core/session')->addError('Identificamos que a sua hospedagem não suporta uma versão segura do TLS(Transport Layer Security) para se comunicar  com a Gerencianet. Para conseguir gerar transações, será necessário que contate o administrador do seu servidor e solicite que a hospedagem seja atualizada para suportar comunicações por meio do TLS na versão mínima 1.2. Em caso de dúvidas e para maiores informações, contate  a Equipe Técnica da Gerencianet através do suporte da empresa.');
        } else {
            $this->tlsOk = true;
            if (isset($_COOKIE["gnTestTlsLog"])) {
                setcookie("gnTestTlsLog", false, time() - 1);
            }
        }
        curl_close($ch);

        if (!$this->tlsOk && !isset($_COOKIE["gnTestTlsLog"])) {
            setcookie("gnTestTlsLog", true);
            // register log
            $account = Mage::getStoreConfig($this->_configPath . 'account_id');
            $ip = $_SERVER['SERVER_ADDR'];
            $modulo = 'magento';
            $control = md5($account . $ip . 'modulologs-tls');
            $data = array(
                'user_agent' => $_SERVER['HTTP_USER_AGENT'],
                'modulo' => $modulo,
            );
            $post = array(
                'control' => $control,
                'account' => $account,
                'ip' => $ip,
                'origin' => 'modulo',
                'data' => json_encode($data)
            );
            $ch1 = curl_init();
            $options1 = array(
                CURLOPT_URL         => "https://fortunus.gerencianet.com.br/logs/tls",
                CURLOPT_RETURNTRANSFER         => true,
                CURLOPT_FOLLOWLOCATION         => true,
                CURLOPT_HEADER         => true,  // don't return headers
                CURLOPT_MAXREDIRS      => 10,     // stop after 10 redirects
                CURLOPT_AUTOREFERER    => true,   // set referrer on redirect
                CURLOPT_CONNECTTIMEOUT => 5,    // time-out on connect
                CURLOPT_TIMEOUT        => 5,    // time-out on response
                CURLOPT_POST        => true,
                CURLOPT_POSTFIELDS        => json_encode($post),
            );
            curl_setopt_array($ch1, $options1);
            $content1 = curl_exec($ch1);
            $info1 = curl_getinfo($ch1);
            curl_close($ch1);
        }
    }

    public function addWebhook(){
        $pixEnable = Mage::getStoreConfig('payment/gerencianet_pix/active');
        $params = ['chave' => Mage::getStoreConfig('payment/gerencianet_pix/pix_key')];
        $body = ['webhookUrl' => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB)];
        if($pixEnable){
            $webhook = Mage::getModel('gerencianet_transparent/standard')->getApiPix()->pixConfigWebhook($params, $body);
        }
    }
}
