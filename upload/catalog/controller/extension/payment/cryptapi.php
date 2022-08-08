<?php

class ControllerExtensionPaymentCryptapi extends Controller
{
    public function index()
    {
        require_once(DIR_SYSTEM . 'library/cryptapi.php');

        $this->load->language('extension/payment/cryptapi');

        $data['text_legend'] = $this->language->get('text_legend');
        $data['button_confirm'] = $this->language->get('button_confirm');
        $data['text_processing'] = $this->language->get('text_processing');
        $data['text_paywith'] = $this->language->get('text_paywith');
        $data['column_total'] = $this->language->get('column_total');

        $this->load->model('extension/payment/cryptapi');

        $data['title'] = $this->config->get('payment_cryptapi_title');

        $data['cryptocurrencies'] = array();

        $order = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $order_total = floatval($order['total']);

        foreach ($this->config->get('payment_cryptapi_cryptocurrencies') as $selected) {
            foreach (json_decode(str_replace("&quot;", '"', $this->config->get('payment_cryptapi_cryptocurrencies_array_cache')), true) as $token => $coin) {
                if ($selected === $token) {
                    $data['cryptocurrencies'] += [
                        $token => $coin,
                    ];
                }
            }
        }

        foreach ($data['cryptocurrencies'] as $token => $coin) {
            $data['payment_cryptapi_address_' . $token] = $this->config->get('payment_cryptapi_address_' . $token);
        }

        // Fee
        $fee = $this->config->get('payment_cryptapi_fees');
        $blockchain_fee = $this->config->get('payment_cryptapi_blockchain_fees');
        $currency = $order['currency_code'];
        $currencySymbolLeft = $this->model_localisation_currency->getCurrencies()[$order['currency_code']]['symbol_left'];
        $currencySymbolRight = $this->model_localisation_currency->getCurrencies()[$order['currency_code']]['symbol_right'];
        $data['symbol_left'] = $currencySymbolLeft;
        $data['symbol_right'] = $currencySymbolRight;

        $cryptapiFee = 0;

        if ($_POST) {
            if ($fee != 'none') {
                $cryptapiFee += floatval($fee) * $order_total;
            }

            if ($blockchain_fee) {
                $cryptapiFee += floatval(CryptAPIHelper::get_estimate($_POST["cryptapi_coin"])->$currency);
            }
        }

        $data['fee'] = $fee;
        $data['blockchain_fee'] = $blockchain_fee;
        $data['cryptapi_fee'] = $this->currency->format($cryptapiFee, $currency, 1.00000, false);
        $data['total'] = $this->currency->format($order_total + $cryptapiFee, $currency, 1.00000, false);

        $this->session->data['cryptapi_fee'] = round($cryptapiFee, 2);

        $this->load->model('checkout/order');

        return $this->load->view('extension/payment/cryptapi', $data);
    }

    public function confirm()
    {
        $json = array();

        if ($this->session->data['payment_method']['code'] == 'cryptapi') {
            $this->load->model('checkout/order');
            $this->load->model('extension/payment/cryptapi');

            $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
            $cryptoFee = empty($this->session->data['cryptapi_fee']) ? 0 : $this->session->data['cryptapi_fee'];
            $total = $this->currency->format($order_info['total'] + $cryptoFee, $order_info['currency_code'], 1.00000, false);
            $currency = $this->session->data['currency'];

            $selected = $this->request->post['cryptapi_coin'];
            $address = $this->config->get('payment_cryptapi_cryptocurrencies_address')[$selected];
            $apiKey = $this->config->get('payment_cryptapi_api_key');

            if (!empty($address) || !empty($apiKey)) {
                $nonce = $this->model_extension_payment_cryptapi->generateNonce();

                require_once(DIR_SYSTEM . 'library/cryptapi.php');

                $disable_conversion = $this->config->get('payment_cryptapi_disable_conversion');
                $qr_code_size = $this->config->get('payment_cryptapi_qrcode_size');

                $info = CryptAPIHelper::get_info($selected);
                $minTx = floatval($info->minimum_transaction_coin);

                $cryptoTotal = CryptAPIHelper::get_conversion($order_info['currency_code'], $selected, $total, $disable_conversion);

                if ($cryptoTotal < $minTx) {
                    $message = $this->module->l('Payment error: ', 'validation');
                    $message .= $this->module->l('Value too low, minimum is', 'validation');
                    $message .= ' ' . $minTx . ' ' . strtoupper($selected);
                    $json['error'] = $message;
                } else {
                    $callbackUrl = $this->url->link('extension/payment/cryptapi/callback', 'order_id=' . $this->session->data['order_id'] . '&nonce=' . $nonce, true);
                    $callbackUrl = str_replace('&amp;', '&', $callbackUrl);



                    $helper = new CryptAPIHelper($selected, $address, $apiKey, $callbackUrl, [], true);
                    $addressIn = $helper->get_address();

                    $qrCodeDataValue = $helper->get_qrcode($cryptoTotal, $qr_code_size);
                    $qrCodeData = $helper->get_qrcode('', $qr_code_size);

                    $paymentData = [
                        'cryptapi_fee' => $cryptoFee,
                        'cryptapi_nonce' => $nonce,
                        'cryptapi_address' => $addressIn,
                        'cryptapi_total' => $cryptoTotal,
                        'cryptapi_total_fiat' => $total,
                        'cryptapi_currency' => $selected,
                        'cryptapi_qrcode_value' => $qrCodeDataValue['qr_code'],
                        'cryptapi_qrcode' => $qrCodeData['qr_code'],
                        'cryptapi_last_price_update' => time(),
                        'cryptapi_order_timestamp' => time(),
                        'cryptapi_cancelled' => '0',
                        'cryptapi_min' => $minTx,
                        'cryptapi_history' => json_encode([]),
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

            if ($order && $order['payment_code'] != 'cryptapi') {
                $order = false;
            }

            if (!$status && $order && $order['order_status_id'] != $this->config->get('payment_cryptapi_order_status_id')) {
                $order = false;
            }
        }
        return $order;
    }

    public function before_checkout_success(&$route, &$data)
    {
        $this->load->model('setting/setting');


        // In case the extension is disabled, do nothing
        if (!$this->config->get('cryptapi_status')) {
            return;
        }

        $order = $this->isCryptapiOrder();

        if (!$order) {
            return;
        }

        $this->document->addScript('catalog/view/javascript/cryptapi/js/cryptapi_script.js');
        $this->document->addStyle('catalog/view/javascript/cryptapi/css/cryptapi_style.css');
    }

    public function after_purchase(&$route, &$data, &$output)
    {
        $this->load->model('setting/setting');

        // In case the extension is disabled, do nothing
        if (!$this->config->get('cryptapi_status')) {
            return;
        }

        $this->load->language('extension/payment/cryptapi');

        $order = $this->isCryptapiOrder();

        if (!$order) {
            return;
        }

        $this->load->model('extension/payment/cryptapi');

        require_once(DIR_SYSTEM . 'library/cryptapi.php');
        $this->load->model('localisation/currency');

        $metaData = $this->model_extension_payment_cryptapi->getPaymentData($order['order_id']);

        if (!empty($metaData)) {
            $metaData = json_decode($metaData, true);
        }
        $total = $metaData['cryptapi_total_fiat'];
        $currencySymbolLeft = $this->model_localisation_currency->getCurrencies()[$order['currency_code']]['symbol_left'];
        $currencySymbolRight = $this->model_localisation_currency->getCurrencies()[$order['currency_code']]['symbol_right'];

        $ajaxUrl = $this->url->link('extension/payment/cryptapi/status', 'order_id=' . $order['order_id'], true);
        $ajaxUrl = str_replace('&amp;', '&', $ajaxUrl);

        $allowed_to_value = array(
            'btc',
            'eth',
            'bch',
            'ltc',
            'miota',
            'xmr',
        );

        $cryptoCoin = $metaData['cryptapi_currency'];

        $crypto_allowed_value = false;

        if (in_array($cryptoCoin, $allowed_to_value, true)) {
            $crypto_allowed_value = true;
        }

        $conversion_timer = ((int)$metaData['cryptapi_last_price_update'] + (int)$this->config->get('payment_cryptapi_refresh_values')) - time();
        $cancel_timer = (int)$metaData['cryptapi_order_timestamp'] + (int)$this->config->get('payment_cryptapi_order_cancelation_timeout') - time();

        $params = [
            'module_path' => HTTPS_SERVER . 'image/catalog/cryptapi/',
            'header' => $this->load->controller('common/header'),
            'footer' => $this->load->controller('common/footer'),
            'currency_symbol_left' => $currencySymbolLeft,
            'currency_symbol_right' => $currencySymbolRight,
            'total' => floatval($total),
            'address_in' => $metaData['cryptapi_address'],
            'crypto_coin' => $cryptoCoin,
            'crypto_value' => $metaData['cryptapi_total'],
            'ajax_url' => $ajaxUrl,
            'qr_code_size' => $this->config->get('payment_cryptapi_qrcode_size'),
            'qr_code' => $metaData['cryptapi_qrcode'],
            'qr_code_value' => $metaData['cryptapi_qrcode_value'],
            'show_branding' => $this->config->get('payment_cryptapi_branding'),
            'qr_code_setting' => $this->config->get('payment_cryptapi_qrcode'),
            'order_timestamp' => $order['total'],
            'order_cancelation_timeout' => $this->config->get('payment_cryptapi_order_cancelation_timeout'),
            'refresh_value_interval' => $this->config->get('payment_cryptapi_refresh_values'),
            'last_price_update' => $metaData['cryptapi_last_price_update'],
            'min_tx' => $metaData['cryptapi_min'],
            'color_scheme' => $this->config->get('payment_cryptapi_color_scheme'),
            'qrcode_default' => $this->config->get('payment_cryptapi_qrcode_default'),
            'conversion_timer' => (int)$conversion_timer,
            'cancel_timer' => (int)$cancel_timer,
            'crypto_allowed_value' => $crypto_allowed_value,

            'text_copy' => $this->language->get('text_copy'),
            'text_copied' => $this->language->get('text_copied'),
            'wallet_text' => $this->language->get('wallet_text'),
            'qr_code_text' => $this->language->get('qr_code_text'),
            'qrcode_show_text' => $this->language->get('qrcode_show_text'),
            'qrcode_button_amount_text' => $this->language->get('qrcode_button_amount_text'),
            'qrcode_button_noamount_text' => $this->language->get('qrcode_button_noamount_text'),
            'qrcode_alt_value_text' => $this->language->get('qrcode_alt_value_text'),
            'qrcode_alt_text' => $this->language->get('qrcode_alt_text'),
            'qrcode_alt_show_text' => $this->language->get('qrcode_alt_show_text'),
            'qrcode_alt_show_value_text' => $this->language->get('qrcode_alt_show_value_text'),
            'qr_code_text_open' => $this->language->get('qr_code_text_open'),
            'qr_code_text_close' => $this->language->get('qr_code_text_close'),
            'payment_confirmed_text' => $this->language->get('payment_confirmed_text'),
            'waiting_payment_text' => $this->language->get('waiting_payment_text'),
            'waiting_network_confirmation_text' => $this->language->get('waiting_network_confirmation_text'),
            'payment_confirmed_simple_text' => $this->language->get('payment_confirmed_simple_text'),
            'please_send_text' => $this->language->get('please_send_text'),
            'payment_notification' => $this->language->get('payment_notification'),
            'order_value_refresh' => $this->language->get('order_value_refresh'),
            'text_moment' => $this->language->get('text_moment'),
            'notification_remaining' => $this->language->get('notification_remaining'),
            'order_valid_time' => $this->language->get('order_valid_time'),
            'branding_logo' => $this->language->get('branding_logo'),
            'table_time' => $this->language->get('table_time'),
            'table_value_paid' => $this->language->get('table_value_paid'),
            'table_fiat_value' => $this->language->get('table_fiat_value'),
            'payment_being_processed' => $this->language->get('payment_being_processed'),
            'processing_blockchain' => $this->language->get('processing_blockchain'),
        ];

        $output = $this->load->view('extension/payment/cryptapi_success', $params);
    }

    public function isOrderPaid($order)
    {
        $paid = 0;
        $succcessOrderStatues = [2, 3, 15];
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

        require_once(DIR_SYSTEM . 'library/cryptapi.php');
        $this->load->model('localisation/currency');
        $this->load->model('localisation/currency');

        $currencySymbolLeft = $this->model_localisation_currency->getCurrencies()[$order['currency_code']]['symbol_left'];
        $currencySymbolRight = $this->model_localisation_currency->getCurrencies()[$order['currency_code']]['symbol_right'];

        $showMinFee = '0';

        $history = json_decode($metaData['cryptapi_history'], true);

        $calc = CryptAPIHelper::calc_order($history, $metaData['cryptapi_total'], $metaData['cryptapi_total_fiat']);

        $already_paid = $calc['already_paid'];
        $already_paid_fiat = $calc['already_paid_fiat'] <= 0 ? 0 : $calc['already_paid_fiat'];

        $min_tx = floatval($metaData['cryptapi_min']);

        $remaining_pending = $calc['remaining_pending'];
        $remaining_fiat = $calc['remaining_fiat'];

        $cryptapi_pending = '0';
        if ($remaining_pending <= 0 && !$this->isOrderPaid($order)) {
            $cryptapi_pending = 1;
        }

        $counter_calc = (int)$metaData['cryptapi_last_price_update'] + (int)$this->config->get('payment_cryptapi_refresh_values') - time();
        if (!$this->isOrderPaid($order) && $counter_calc <= 0) {
            $this->cron();
        }

        if ($remaining_pending <= $min_tx && $remaining_pending > 0) {
            $remaining_pending = $min_tx;
            $showMinFee = '1';
        }

        $data = [
            'is_paid' => $this->isOrderPaid($order),
            'is_pending' => (int)($cryptapi_pending),
            'crypto_total' => floatval($metaData['cryptapi_total']),
            'qr_code_value' => $metaData['cryptapi_qrcode_value'],
            'cancelled' => $metaData['cryptapi_cancelled'],
            'remaining' => $remaining_pending < 0 ? 0 : $remaining_pending,
            'fiat_remaining' => $currencySymbolLeft . $remaining_fiat . $currencySymbolRight,
            'coin' => strtoupper($metaData['cryptapi_currency']),
            'show_min_fee' => (int)$showMinFee,
            'order_history' => $history,
            'already_paid' => $currencySymbolLeft . $already_paid . $currencySymbolRight,
            'already_paid_fiat' => floatval($already_paid_fiat) <= 0 ? 0 : floatval($already_paid_fiat), true, false,
            'counter' => (string)$counter_calc,
            'fiat_symbol_left' => $currencySymbolLeft,
            'fiat_symbol_right' => $currencySymbolRight,
        ];

        echo json_encode($data);
        die();
    }

    public function cron()
    {
        require_once(DIR_SYSTEM . 'library/cryptapi.php');
        $this->load->model('extension/payment/cryptapi');
        $this->load->model('checkout/order');
        $this->response->addHeader('Content-Type: application/json');

        $order_timeout = intval($this->config->get('payment_cryptapi_order_cancelation_timeout'));
        $value_refresh = intval($this->config->get('payment_cryptapi_refresh_value_interval'));
        $qrcode_size = $this->config->get('payment_cryptapi_qrcode_size');

        $response = $this->response->setOutput(json_encode(['status' => 'ok']));

        if ($order_timeout === 0 && $value_refresh === 0) {
            return $response;
        }

        $orders = $this->model_extension_payment_cryptapi->getOrders();

        if (empty($orders)) {
            return $response;
        }

        foreach ($orders as $order) {

            $order_id = $order['order_id'];

            $currency = $order['currency_code'];

            $metaData = json_decode($this->model_extension_payment_cryptapi->getPaymentData($order['order_id']), true);

            if (!empty($metaData['cryptapi_last_price_update'])) {
                $last_price_update = $metaData['cryptapi_last_price_update'];

                $history = json_decode($metaData['cryptapi_history'], true);

                $min_tx = floatval($metaData['cryptapi_min']);

                $calc = CryptAPIHelper::calc_order($history, $metaData['cryptapi_total'], floatval($metaData['cryptapi_total_fiat']));

                $remaining = $calc['remaining'];
                $remaining_pending = $calc['remaining_pending'];
                $already_paid = $calc['already_paid'];

                if ($value_refresh !== 'none' && $last_price_update + $value_refresh <= time() && !empty($last_price_update)) {

                    if ($remaining === $remaining_pending) {
                        $cryptapi_coin = $metaData['cryptapi_currency'];

                        $crypto_total = CryptAPIHelper::get_conversion($currency, $cryptapi_coin, $order['total'], $this->disable_conversion);
                        $this->model_extension_payment_cryptapi->updatePaymentData($order_id, 'cryptapi_total', $crypto_total);

                        $calc_cron = CryptAPIHelper::calc_order($history, $metaData['cryptapi_total'], $metaData['cryptapi_total_fiat']);
                        $crypto_remaining_total = $calc_cron['remaining_pending'];

                        if ($remaining_pending <= $min_tx && $remaining_pending > 0) {
                            $qr_code_data_value = CryptAPIHelper::get_static_qrcode($metaData['cryptapi_address'], $cryptapi_coin, $min_tx, $qrcode_size);
                        } else {
                            $qr_code_data_value = CryptAPIHelper::get_static_qrcode($metaData['cryptapi_address'], $cryptapi_coin, $crypto_remaining_total, $qrcode_size);
                        }

                        $this->model_extension_payment_cryptapi->updatePaymentData($order_id, 'cryptapi_qrcode_value', $qr_code_data_value['qr_code']);
                    }

                    $this->model_extension_payment_cryptapi->updatePaymentData($order_id, 'cryptapi_last_price_update', time());
                }

                if ($order_timeout !== 'none' && (strtotime($order['date_added']) + $order_timeout) <= time() && $already_paid <= 0 && (int)$metaData['cryptapi_cancelled'] === 0) {
                    $processing_state = 7;
                    $this->model_checkout_order->addOrderHistory($order['order_id'], $processing_state);
                }
            }
        }

        return $response;
    }

    public function callback()
    {
        require_once(DIR_SYSTEM . 'library/cryptapi.php');
        $this->load->model('extension/payment/cryptapi');

        $data = CryptAPIHelper::process_callback($_GET);

        $this->load->model('checkout/order');

        $order = $this->model_checkout_order->getOrder((int)$data['order_id']);

        $metaData = json_decode($this->model_extension_payment_cryptapi->getPaymentData($order['order_id']), true);

        if ($this->isOrderPaid($order) || $data['nonce'] !== $metaData['cryptapi_nonce']) {
            die("*ok*");
        }

        $disable_conversion = $this->config->get('payment_cryptapi_disable_conversion');

        $qrcode_size = $this->config->get('payment_cryptapi_qrcode_size');

        $paid = $data['value_coin'];

        $min_tx = floatval($metaData['cryptapi_min']);

        $history = json_decode($metaData['cryptapi_history'], true);

        if (empty($history[$data['uuid']])) {
            $fiat_conversion = CryptAPIHelper::get_conversion($metaData['cryptapi_currency'], $order['currency_code'], $paid, $disable_conversion);

            $history[$data['uuid']] = [
                'timestamp' => time(),
                'value_paid' => CryptAPIHelper::sig_fig($paid, 6),
                'value_paid_fiat' => $fiat_conversion,
                'pending' => $data['pending']
            ];
        } else {
            $history[$data['uuid']]['pending'] = $data['pending'];
        }

        $this->model_extension_payment_cryptapi->updatePaymentData($order['order_id'], 'cryptapi_history', json_encode($history));

        $metaData = json_decode($this->model_extension_payment_cryptapi->getPaymentData($order['order_id']), true);

        $history = json_decode($metaData['cryptapi_history'], true);// <<-something's wrong

        $calc = CryptAPIHelper::calc_order($history, $metaData['cryptapi_total'], $metaData['cryptapi_total_fiat']);

        $remaining = $calc['remaining'];
        $remaining_pending = $calc['remaining_pending'];

        if ($remaining_pending <= 0) {
            if ($remaining <= 0) {
                $processing_state = 2;
                $this->model_checkout_order->addOrderHistory($order['order_id'], $processing_state);
                $this->model_extension_payment_cryptapi->updatePaymentData($order['order_id'], 'cryptapi_txid', $data['txid_in']);
            }
            die('*ok*');
        }

        if ($remaining_pending <= $min_tx) {
            $qrcode_conv = CryptAPIHelper::get_static_qrcode($metaData['cryptapi_address'], $metaData['cryptapi_currency'], $min_tx, $qrcode_size)['qr_code'];
        } else {
            $qrcode_conv = CryptAPIHelper::get_static_qrcode($metaData['cryptapi_address'], $metaData['cryptapi_currency'], $remaining_pending, $qrcode_size)['qr_code'];
        }

        $this->model_extension_payment_cryptapi->updatePaymentData($order['order_id'], 'cryptapi_qrcode_value', $qrcode_conv);

        die("*ok*");
    }
}