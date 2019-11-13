<?php
class ControllerExtensionPaymentCryptapi extends Controller {
	public function index() {
		$this->load->language('extension/payment/cryptapi');

		$data['cryptocurrencies'] = $this->config->get('payment_cryptapi_cryptocurrencies');
                $data['payment_cryptapi_btc_address'] = $this->config->get('payment_cryptapi_btc_address');
                $data['payment_cryptapi_bch_address'] = $this->config->get('payment_cryptapi_bch_address');
                $data['payment_cryptapi_ltc_address'] = $this->config->get('payment_cryptapi_ltc_address');
                $data['payment_cryptapi_eth_address'] = $this->config->get('payment_cryptapi_eth_address');
		$data['payment_cryptapi_xmr_address'] = $this->config->get('payment_cryptapi_xmr_address');
		$data['payment_cryptapi_iota_address'] = $this->config->get('payment_cryptapi_iota_address');

		$this->load->model('checkout/order');
		
		return $this->load->view('extension/payment/cryptapi', $data);
	}

	public function confirm() {
		$json = array();
		
		if ($this->session->data['payment_method']['code'] == 'cryptapi') {
                    $this->load->model('checkout/order');
                    $this->load->model('extension/payment/cryptapi');
                    
                    $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
                    $total = $this->currency->format($order_info['total'], $order_info['currency_code'], 1.00000, false);
                    $currency = $this->session->data['currency'];

                    $selected = $this->request->post['cryptapi_coin'];
                    $address = $this->config->get('payment_cryptapi_'.$selected.'_address');

                    if (!empty($address)) {
                        $nonce = $this->model_extension_payment_cryptapi->generateNonce();
                        
                        require_once(DIR_SYSTEM . 'library/cryptapi.php');
                        
                        $info = CryptAPIHelper::get_info($selected);
                        $minTx = CryptAPIHelper::convert_div($info->minimum_transaction, $selected);

                        $price = (float)($info->prices->USD);
                        if (isset($info->prices->{$currency})) {
                            $price = (float)($info->prices->{$currency});
                        }

                        $cryptoTotal = $this->model_extension_payment_cryptapi->roundSig($total / $price, 5);
                        if ($cryptoTotal < $minTx) {
                            $message = $this->module->l('Payment error: ', 'validation');
                            $message .= $this->module->l('Value too low, minimum is', 'validation');
                            $message .= ' '.$minTx . ' ' . strtoupper($selected);
                            $json['error'] = $message;
                        } else {
                            $callbackUrl = $this->url->link('extension/payment/cryptapi/callback', 'order_id=' . $this->session->data['order_id'].'&nonce='.$nonce, true);
                            $callbackUrl = str_replace('&amp;', '&', $callbackUrl);

                            $helper = new CryptAPIHelper($selected, $address, $callbackUrl, [], true);
                            $addressIn = $helper->get_address();

                            $paymentData = [
                                'cryptapi_nonce' => $nonce,
                                'cryptapi_address' => $addressIn,
                                'cryptapi_total' => $cryptoTotal,
                                'cryptapi_currency' => $selected,
                            ];
                            $paymentData = json_encode($paymentData);
                            $this->model_extension_payment_cryptapi->addPaymentData($this->session->data['order_id'], $paymentData);
                            
                            $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('payment_cryptapi_order_status_id'));
                            $json['redirect'] = $this->url->link('checkout/success', 'order_id=' . $this->session->data['order_id'], true);
                        }
                    }
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));		
	}
        
        public function isCryptapiOrder($status = false)
        {
            $order = false;
            if (isset($this->request->get['order_id'])) {
                $order_id = (int)($this->request->get['order_id']);
            } else if (isset($this->request->get['amp;order_id'])) {
                $order_id = (int)($this->request->get['amp;order_id']);
            }
            
            if ($order_id > 0) {
                $this->load->model('checkout/order');
                $order = $this->model_checkout_order->getOrder($order_id);
                
                $this->load->model('setting/setting');
                $setting = $this->model_setting_setting;
                
                if ($order && $order['payment_code'] != 'cryptapi') {
                    $order = false;
                }
                
                if (!$status && $order && $order['order_status_id'] != $setting->getSettingValue('payment_cryptapi_order_status_id')) {
                    $order = false;
                }
            }
            return $order;
        }
        
        public function before_checkout_success(&$route, &$data)
        {
            $this->load->model('setting/setting');
            $setting = $this->model_setting_setting;
            
             // In case the extension is disabled, do nothing
            if (!$setting->getSettingValue('payment_cryptapi_status')) {
                return;
            }
            
            $order = $this->isCryptapiOrder();
            
            if (!$order) {
                return;
            }
            
            $this->document->addScript('catalog/view/javascript/cryptapi/js/jquery-qrcode-0.17.0.min.js');
            $this->document->addScript('catalog/view/javascript/cryptapi/js/payment.js');
            $this->document->addStyle('catalog/view/javascript/cryptapi/css/loader.css');
        }

        public function after_purchase(&$route, &$data, &$output)
        {
            $this->load->model('setting/setting');
            $setting = $this->model_setting_setting;
            
             // In case the extension is disabled, do nothing
            if (!$setting->getSettingValue('payment_cryptapi_status')) {
                return;
            }
            
            $order = $this->isCryptapiOrder();
            
            if (!$order) {
                return;
            }
            
            $this->load->model('extension/payment/cryptapi');
            
            require_once(DIR_SYSTEM . 'library/cryptapi.php');
            
            $total = $order['total'];
            $currencySymbol = $order['currency_code'];
            $metaData = $this->model_extension_payment_cryptapi->getPaymentData($order['order_id']);

            if (!empty($metaData)) {
                $metaData = json_decode($metaData, true);
            }
            $addressIn = $metaData['cryptapi_address'];
            $cryptoValue = $metaData['cryptapi_total'];
            $cryptoCoin = $metaData['cryptapi_currency'];

            $showCryptoCoin = $cryptoCoin;
            if ($showCryptoCoin == 'iota') {
                $showCryptoCoin = 'miota';
            }

            $qrValue = $cryptoValue;
            if (in_array($cryptoCoin, array('eth', 'iota'))) {
                $qrValue = CryptAPIHelper::convert_mul($cryptoValue, $cryptoCoin);
            }

            $ajaxUrl = $this->url->link('extension/payment/cryptapi/status', 'order_id=' . $order['order_id'], true);
            $ajaxUrl = str_replace('&amp;', '&', $ajaxUrl);
  
            $params = [
                'module_path' => HTTPS_SERVER .'image/catalog/cryptapi/',
                'crypto_value' => $cryptoValue,
                'show_crypto_coin' => strtoupper($showCryptoCoin),
                'currency_symbol' => $currencySymbol,
                'total' => $total,
                'address_in' => $addressIn,
                'qr_value' => $qrValue,
                'crypto_coin' => $cryptoCoin,
                'ajax_url' => $ajaxUrl

            ];

            $content = $this->load->view('extension/payment/cryptapi_success', $params);
            $output = str_replace('<div class="buttons">', $content . '<div class="buttons">', $output);
        }
        
        public function isOrderPaid($order)
        {
            $paid = 0;
            $succcessOrderStatues = [2,3,15];
            if (in_array($order['order_status_id'], $succcessOrderStatues)) {
                $paid = 1;
            }
            return $paid;
        }

        public function status()
        {
            $order = $this->isCryptapiOrder(true);
            
            if (!$order) {
                return;
            }
            
            $this->load->model('extension/payment/cryptapi');
            $metaData = $this->model_extension_payment_cryptapi->getPaymentData($order['order_id']);
            if (!empty($metaData)) {
                $metaData = json_decode($metaData, true);
            }

            $cryptapi_pending = 0;
            if (isset($metaData['cryptapi_pending'])) {
                $cryptapi_pending = $metaData['cryptapi_pending'];
            }

            $data = [
                'is_paid' => $this->isOrderPaid($order),
                'is_pending' => (int)($cryptapi_pending),
            ];

            echo json_encode($data);
            die();
        }
        
        public function callback()
        {
            require_once(DIR_SYSTEM . 'library/cryptapi.php');
            $data = CryptAPIHelper::process_callback($_GET);
            
            $this->load->model('checkout/order');
            $order = $this->model_checkout_order->getOrder((int)$data['order_id']);
            
            $this->load->model('extension/payment/cryptapi');
            $metaData = $this->model_extension_payment_cryptapi->getPaymentData($order['order_id']);
            if (!empty($metaData)) {
                $metaData = json_decode($metaData, true);
            }
            
            if ($this->isOrderPaid($order) || $data['nonce'] != $metaData['cryptapi_nonce']) {
                die("*ok*");
            }
            
            $valueConvert = CryptAPIHelper::convert_div($data['value'], $data['coin']);
            
            $alreadyPaid = 0;
            if (isset($metaData['cryptapi_paid'])) {
                $alreadyPaid = $metaData['cryptapi_paid'];
            }
            $paid = $alreadyPaid + $valueConvert;
            if (!$data['pending']) {
                $this->model_extension_payment_cryptapi->updatePaymentData($order['order_id'], 'cryptapi_paid', $paid);
            }

            if ($paid >= $metaData['cryptapi_total']) {
                if ($data['pending']) {
                    $this->model_extension_payment_cryptapi->updatePaymentData($order['order_id'], 'cryptapi_pending', "1");
                } else {
                    $this->model_extension_payment_cryptapi->deletePaymentData($order['order_id'], 'cryptapi_pending');
                    $processing_state = 2;
                    $this->model_checkout_order->addOrderHistory($order['order_id'], $processing_state);
                    $this->model_extension_payment_cryptapi->updatePaymentData($order['order_id'], 'cryptapi_txid', $data['txid_in']);
                }
            }
            die("*ok*");
        }
}