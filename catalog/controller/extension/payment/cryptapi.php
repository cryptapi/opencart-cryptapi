<?php

class ControllerExtensionPaymentCryptapi extends Controller
{
    public function index()
    {
        require_once(DIR_SYSTEM . 'library/cryptapi.php');

        $this->load->language('extension/payment/cryptapi');

        $this->load->model('extension/payment/cryptapi');

        $data['title'] = $this->config->get('payment_cryptapi_title');

        $data['cryptocurrencies'] = array();

        $order = $this->model_checkout_order->getOrder($this->session->data['order_id']);

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
        $currencySymbolLeft = $this->model_localisation_currency->getCurrencies()[$order['currency_code']]['symbol_left'];
        $currencySymbolRight = $this->model_localisation_currency->getCurrencies()[$order['currency_code']]['symbol_right'];
        $data['symbol_left'] = $currencySymbolLeft;
        $data['symbol_right'] = $currencySymbolRight;

        // The checkout reloads this with the chosen coin to show its fee.
        $coin = isset($this->request->post['cryptapi_coin']) ? (string)$this->request->post['cryptapi_coin'] : '';
        $amounts = $this->orderAmounts($order, isset($data['cryptocurrencies'][$coin]) ? $coin : '');

        $data['fee'] = $fee;
        $data['blockchain_fee'] = $blockchain_fee;
        $data['cryptapi_fee'] = $amounts['fee'];
        $data['total'] = $amounts['total'];

        $this->load->model('checkout/order');

        return $this->load->view('extension/payment/cryptapi', $data);
    }

    /*
     * What the customer sees and pays, in the order's currency. OpenCart stores
     * order totals in the store's default currency, so convert with the order's
     * own rate: formatting the raw total with a rate of 1 quoted a 100 USD order
     * as 100 JPY. Worked out here for both the checkout and confirm(), never
     * taken from the session.
     */
    private function orderAmounts($order, $coin)
    {
        $currency = $order['currency_code'];
        $order_total = (float)$this->currency->format((float)$order['total'], $currency, (float)$order['currency_value'], false);

        $fee = 0.0;

        if ($coin !== '') {
            $fee_rate = (float)$this->config->get('payment_cryptapi_fees');

            if ($fee_rate > 0) {
                $fee += $fee_rate * $order_total;
            }

            if ($this->config->get('payment_cryptapi_blockchain_fees')) {
                $estimate = CryptAPIHelper::get_estimate($coin);

                if (is_object($estimate) && isset($estimate->$currency)) {
                    $fee += (float)$estimate->$currency;
                } elseif (is_object($estimate) && isset($estimate->USD)) {
                    $fee += $this->currency->convert((float)$estimate->USD, 'USD', $currency);
                }
            }
        }

        // Already in the order's currency: a rate of 1 only rounds to its decimals.
        $fee = (float)$this->currency->format($fee, $currency, 1.00000, false);

        return [
            'fee' => $fee,
            'total' => (float)$this->currency->format($order_total + $fee, $currency, 1.00000, false),
        ];
    }

    public function confirm()
    {
        $this->load->language('extension/payment/cryptapi');
        $json = array();
        $err_coin = '';

        if ($this->session->data['payment_method']['code'] == 'cryptapi') {
            require_once(DIR_SYSTEM . 'library/cryptapi.php');

            $this->load->model('checkout/order');
            $this->load->model('extension/payment/cryptapi');

            $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

            $apiKey = $this->config->get('payment_cryptapi_api_key');
            if (empty($this->request->post['cryptapi_coin'])) {
                $err_coin = $this->language->get('error_coin');
            } else {
                $selected = (string)$this->request->post['cryptapi_coin'];

                // Only a coin the store has turned on.
                if (!in_array($selected, (array)$this->config->get('payment_cryptapi_cryptocurrencies'), true)) {
                    $err_coin = $this->language->get('error_coin');
                }

                $address = $this->config->get('payment_cryptapi_cryptocurrencies_address_' . $selected);
                if (empty($err_coin) && empty($address) && empty($apiKey)) {
                    $err_coin = $this->language->get('error_apikey');
                }
            }



            if (empty($err_coin) && (!empty($address) || !empty($apiKey))) {
                // The callback secret. It goes only into the callback URL that
                // CryptAPI calls, never into a URL the customer sees.
                $nonce = CryptAPIHelper::random_token();

                $disable_conversion = $this->config->get('payment_cryptapi_disable_conversion');
                $qr_code_size = $this->config->get('payment_cryptapi_qrcode_size');

                $amounts = $this->orderAmounts($order_info, $selected);
                $cryptoFee = $amounts['fee'];
                $total = $amounts['total'];

                $info = CryptAPIHelper::get_info($selected);
                $minTx = isset($info->minimum_transaction_coin) ? floatval($info->minimum_transaction_coin) : 0;

                $cryptoTotal = CryptAPIHelper::get_conversion($order_info['currency_code'], $selected, $total, $disable_conversion);
                $callbackUrl = $this->url->link('extension/payment/cryptapi/callback', 'order_id=' . $this->session->data['order_id'] . '&nonce=' . $nonce, true);
                $callbackUrl = str_replace('&amp;', '&', $callbackUrl);

                // A failed rate lookup returns null. Never make an order without a price.
                if (!CryptAPIHelper::is_positive_number($cryptoTotal)) {
                    $err_coin = $this->language->get('error_conversion');
                } else {
                    $helper = new CryptAPIHelper($selected, $address, $apiKey, $callbackUrl, [], true);
                    $addressIn = $helper->get_address();

                    if (!isset($addressIn)) {
                        $err_coin = $this->language->get('error_adress');
                    } elseif ($cryptoTotal < $minTx) {
                        $err_coin = $this->language->get('value_minim') . ' ' . $minTx . ' ' . strtoupper($selected);
                    }
                }

                if (empty($err_coin)) {

                    $qrCodeDataValue = $helper->get_qrcode($cryptoTotal, $qr_code_size);
                    $qrCodeData = $helper->get_qrcode('', $qr_code_size);
                    $paymentURL = $this->url->link('extension/payment/cryptapi/pay', 'order_id=' . $this->session->data['order_id'], true);

                    $paymentData = [
                        'cryptapi_fee' => $cryptoFee,
                        'cryptapi_nonce' => $nonce,
                        'cryptapi_address' => $addressIn,
                        'cryptapi_total' => $cryptoTotal,
                        'cryptapi_total_fiat' => $total,
                        'cryptapi_currency' => $selected,
                        'cryptapi_qrcode_value' => is_array($qrCodeDataValue) ? $qrCodeDataValue['qr_code'] : '',
                        'cryptapi_qrcode' => is_array($qrCodeData) ? $qrCodeData['qr_code'] : '',
                        'cryptapi_last_price_update' => time(),
                        'cryptapi_order_timestamp' => time(),
                        'cryptapi_cancelled' => '0',
                        'cryptapi_min' => $minTx,
                        'cryptapi_history' => json_encode([]),
                        'cryptapi_payment_url' => $paymentURL,
                        // The callback's signature is checked against this URL's origin.
                        'cryptapi_callback_url' => $callbackUrl,
                    ];

                    $paymentData = json_encode($paymentData);
                    $this->model_extension_payment_cryptapi->addPaymentData($this->session->data['order_id'], $paymentData);

                    $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('payment_cryptapi_order_status_id'));
                    $json['redirect'] = $this->url->link('checkout/success', 'order_id=' . $this->session->data['order_id'], true);
                } else {
                    $json['error']['warning'] = sprintf($this->language->get('error_payment'), $err_coin);
                }
            } else {
                $json['error']['warning'] = sprintf($this->language->get('error_payment'), $err_coin);
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

        if (isset($order_id)) {
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

    public function pay()
    {
        $this->document->addScript('catalog/view/javascript/cryptapi/js/cryptapi_script.js');
        $this->document->addStyle('catalog/view/javascript/cryptapi/css/cryptapi_style.css');

        // In case the extension is disabled, do nothing
        if (!$this->config->get('payment_cryptapi_status')) {
            $this->response->redirect($this->url->link('common/home', '', true));
        }

        // Library
        require_once(DIR_SYSTEM . 'library/cryptapi.php');

        $this->load->language('extension/payment/cryptapi');

        $order = $this->isCryptapiOrder();

        if (!$order) {
            $this->response->redirect($this->url->link('common/home', '', true));
        }

        $this->load->model('extension/payment/cryptapi');
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
            'total' => floatval($total) < 0 ? 0 : floatval($total),
            'address_in' => $metaData['cryptapi_address'],
            'crypto_coin' => $cryptoCoin,
            'crypto_value' => $metaData['cryptapi_total'],
            'ajax_url' => $ajaxUrl,
            'qr_code_size' => $this->config->get('payment_cryptapi_qrcode_size'),
            'qr_code' => $metaData['cryptapi_qrcode'],
            'qr_code_value' => $metaData['cryptapi_qrcode_value'],
            'show_branding' => $this->config->get('payment_cryptapi_branding'),
            'branding_logo' => HTTPS_SERVER . 'image/catalog/cryptapi/payment.png',
            'qr_code_setting' => $this->config->get('payment_cryptapi_qrcode'),
            'order_timestamp' => $order['total'],
            'order_cancelation_timeout' => $this->config->get('payment_cryptapi_order_cancelation_timeout'),
            'refresh_value_interval' => $this->config->get('payment_cryptapi_refresh_values'),
            'last_price_update' => $metaData['cryptapi_last_price_update'],
            'min_tx' => $metaData['cryptapi_min'],
            'min_tx_notice' => (string)$metaData['cryptapi_min'] . ' ' . strtoupper($cryptoCoin),
            'color_scheme' => $this->config->get('payment_cryptapi_color_scheme'),
            'conversion_timer' => (int)$conversion_timer,
            'cancel_timer' => (int)$cancel_timer,
            'crypto_allowed_value' => $crypto_allowed_value,
        ];

        return $this->response->setOutput($this->load->view('extension/payment/cryptapi_success', $params));
    }

    public function after_purchase(&$route, &$data, &$output)
    {
        // In case the extension is disabled, do nothing
        if (!$this->config->get('payment_cryptapi_status')) {
            return;
        }

        $order = $this->isCryptapiOrder();

        if (!$order) {
            return;
        }

        $this->load->model('extension/payment/cryptapi');
        $metaData = $this->model_extension_payment_cryptapi->getPaymentData($order['order_id']);

        if (!empty($metaData)) {
            $metaData = json_decode($metaData, true);
        }

        $this->load->language('extension/payment/cryptapi');

        /**
         * Tries sending an e-mail. Will fail if configuration is not set but won't throw an error.
         */
        try {
            // Send the E-mail with the order URL
            $mail = new Mail($this->config->get('config_mail_engine'));
            $mail->parameter = $this->config->get('config_mail_parameter');
            $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
            $mail->smtp_username = $this->config->get('config_mail_smtp_username');
            $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
            $mail->smtp_port = $this->config->get('config_mail_smtp_port');
            $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

            $subject = sprintf($this->language->get('order_subject'), $order['order_id'], strtoupper($metaData['cryptapi_currency']));

            $data['order_greeting'] = sprintf($this->language->get('order_greeting'), $order['order_id'], strtoupper($metaData['cryptapi_currency']));
            $data['order_url'] = $metaData['cryptapi_payment_url'];
            $data['store'] = html_entity_decode($order['store_name'], ENT_QUOTES, 'UTF-8');
            $data['store_url'] = $order['store_url'];

            $html = $this->load->view('extension/payment/cryptapi_email', $data);

            $mail->setTo($order['email']);
            $mail->setFrom($this->config->get('config_email'));
            $mail->setSender(html_entity_decode($order['store_name'], ENT_QUOTES, 'UTF-8'));
            $mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
            $mail->setHtml($html);
            $mail->send();
        } catch (\Exception $exception) {
            # don't do anything
        }

        return $this->response->redirect($this->url->link('extension/payment/cryptapi/pay', 'order_id=' . $order['order_id'], true));
    }

    public function isOrderPaid($order)
    {
        $paid = 0;
        $successOrderStatuses = [2, 3, 15];
        if (in_array($order['order_status_id'], $successOrderStatuses)) {
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

        $showMinFee = 0;

        $history = json_decode($metaData['cryptapi_history'], true);

        $calc = CryptAPIHelper::calc_order($history, $metaData['cryptapi_total'], $metaData['cryptapi_total_fiat']);

        $already_paid = $calc['already_paid'];
        $already_paid_fiat = $calc['already_paid_fiat'] <= 0 ? 0 : $calc['already_paid_fiat'];

        $min_tx = floatval($metaData['cryptapi_min']);

        $remaining_pending = $calc['remaining_pending'];
        $remaining_fiat = $calc['remaining_fiat'];

        $cryptapi_pending = 0;
        if ($remaining_pending <= 0 && !$this->isOrderPaid($order)) {
            $cryptapi_pending = 1;
        }

        $counter_calc = (int)$metaData['cryptapi_last_price_update'] + (int)$this->config->get('payment_cryptapi_refresh_values') - time();
        if (!$this->isOrderPaid($order) && $counter_calc <= 0) {
            $this->cron();
        }

        if ($remaining_pending <= $min_tx && $remaining_pending > 0) {
            $remaining_pending = $min_tx;
            $showMinFee = 1;
        }

        $data = [
            'is_paid' => $this->isOrderPaid($order),
            'is_pending' => $cryptapi_pending,
            'crypto_total' => floatval($metaData['cryptapi_total']),
            'qr_code_value' => $metaData['cryptapi_qrcode_value'],
            'cancelled' => (int)$metaData['cryptapi_cancelled'],
            'remaining' => $remaining_pending < 0 ? 0 : $remaining_pending,
            'fiat_remaining' => $currencySymbolLeft . ($remaining_fiat < 0 ? 0 : $remaining_fiat) . $currencySymbolRight,
            'coin' => strtoupper($metaData['cryptapi_currency']),
            'show_min_fee' => $showMinFee,
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
        $value_refresh = intval($this->config->get('payment_cryptapi_refresh_values'));
        $qrcode_size = intval($this->config->get('payment_cryptapi_qrcode_size'));

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

                if ($value_refresh !== 0 && $last_price_update + $value_refresh <= time()) {

                    if ($remaining === $remaining_pending) {
                        $cryptapi_coin = $metaData['cryptapi_currency'];

                        // $this->disable_conversion never existed, so the setting was ignored here.
                        $crypto_total = CryptAPIHelper::get_conversion($currency, $cryptapi_coin, $metaData['cryptapi_total_fiat'], $this->config->get('payment_cryptapi_disable_conversion'));

                        // A failed call returns null. Never save that as the total
                        // (any payment then completed the order); keep the old one.
                        if (CryptAPIHelper::is_positive_number($crypto_total)) {
                            $this->model_extension_payment_cryptapi->updatePaymentData($order_id, 'cryptapi_total', $crypto_total);

                            $calc_cron = CryptAPIHelper::calc_order($history, $crypto_total, $metaData['cryptapi_total_fiat']);

                            $crypto_remaining_total = $calc_cron['remaining_pending'];

                            if ($remaining_pending <= $min_tx && $remaining_pending > 0) {
                                $qr_code_data_value = CryptAPIHelper::get_static_qrcode($metaData['cryptapi_address'], $cryptapi_coin, $min_tx, $qrcode_size);
                            } else {
                                $qr_code_data_value = CryptAPIHelper::get_static_qrcode($metaData['cryptapi_address'], $cryptapi_coin, $crypto_remaining_total, $qrcode_size);
                            }

                            if (is_array($qr_code_data_value)) {
                                $this->model_extension_payment_cryptapi->updatePaymentData($order_id, 'cryptapi_qrcode_value', $qr_code_data_value['qr_code']);
                            }
                        }
                    }

                    $this->model_extension_payment_cryptapi->updatePaymentData($order_id, 'cryptapi_last_price_update', time());
                }

                if ($order_timeout !== 0 && (strtotime($order['date_added']) + $order_timeout) <= time() && $already_paid <= 0 && (int)$metaData['cryptapi_cancelled'] === 0) {
                    $this->model_checkout_order->addOrderHistory($order['order_id'], 7);
                    $this->model_extension_payment_cryptapi->updatePaymentData($order_id, 'cryptapi_cancelled', '1');
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
        $data['order_id'] = isset($data['order_id']) ? $data['order_id'] : 0;
        $data['nonce'] = isset($data['nonce']) ? $data['nonce'] : '';

        $this->load->model('checkout/order');

        $order = $this->model_checkout_order->getOrder((int)$data['order_id']);

        $metaData = $order ? json_decode((string)$this->model_extension_payment_cryptapi->getPaymentData($order['order_id']), true) : null;

        if (!is_array($metaData)) {
            http_response_code(403);
            die('unknown order');
        }

        // Earlier versions trusted any request that knew the nonce, and showed the
        // nonce to the customer in the payment page's URL. Now every callback must
        // carry CryptAPI's RSA signature over the URL it called, checked against a
        // store-made origin. Orders from before this version have no stored
        // callback URL, so try the store's own base URLs.
        $pubkey = $this->cache->get('cryptapi.pubkey');

        if (empty($pubkey)) {
            $pubkey = CryptAPIHelper::fetch_pubkey();

            if (!empty($pubkey)) {
                $this->cache->set('cryptapi.pubkey', $pubkey);
            }
        }

        $signature = isset($_SERVER['HTTP_X_CA_SIGNATURE']) ? (string)$_SERVER['HTTP_X_CA_SIGNATURE'] : '';
        $request_uri = isset($_SERVER['REQUEST_URI']) ? (string)$_SERVER['REQUEST_URI'] : '';

        $bases = !empty($metaData['cryptapi_callback_url'])
            ? [$metaData['cryptapi_callback_url']]
            : array_filter([defined('HTTPS_SERVER') ? HTTPS_SERVER : '', defined('HTTP_SERVER') ? HTTP_SERVER : '']);

        $verified = false;

        foreach ($bases as $base) {
            if (!empty($pubkey) && CryptAPIHelper::verify_signature(CryptAPIHelper::build_signed_url($base, $request_uri), $signature, $pubkey)) {
                $verified = true;
                break;
            }
        }

        if (!$verified) {
            http_response_code(403);
            die('invalid signature');
        }

        if (!CryptAPIHelper::safe_equals(isset($metaData['cryptapi_nonce']) ? $metaData['cryptapi_nonce'] : '', $data['nonce'])) {
            http_response_code(403);
            die('invalid nonce');
        }

        // Anyone can ask CryptAPI for an address that calls this URL, so a signed
        // callback only counts for the address made for this order.
        $stored_address = strtolower(trim(isset($metaData['cryptapi_address']) ? (string)$metaData['cryptapi_address'] : ''));

        if ($stored_address === '' || !CryptAPIHelper::safe_equals($stored_address, strtolower(trim((string)$data['address_in'])))) {
            http_response_code(403);
            die('address mismatch');
        }

        if ($data["coin"] !== $metaData['cryptapi_currency']) {
            die("*ok*");
        }

        if ($this->isOrderPaid($order)) {
            die("*ok*");
        }

        if ((string)$data['uuid'] === '') {
            http_response_code(403);
            die('missing uuid');
        }

        // Never work out payment state from an invalid total. CryptAPI sends the
        // callback again, and the next price refresh repairs the total.
        if (!CryptAPIHelper::is_positive_number($metaData['cryptapi_total'])) {
            http_response_code(503);
            die('total not set');
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

        $history = json_decode($metaData['cryptapi_history'], true); // <<-something's wrong

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

        $qrcode_conv = CryptAPIHelper::get_static_qrcode($metaData['cryptapi_address'], $metaData['cryptapi_currency'], $remaining_pending <= $min_tx ? $min_tx : $remaining_pending, $qrcode_size);

        if (is_array($qrcode_conv)) {
            $this->model_extension_payment_cryptapi->updatePaymentData($order['order_id'], 'cryptapi_qrcode_value', $qrcode_conv['qr_code']);
        }

        die("*ok*");
    }

    function order_pay_button(&$route, &$data, &$output)
    {
        $order_id = $this->request->get['order_id'];

        $this->load->model('extension/payment/cryptapi');
        $this->load->model('checkout/order');

        $orderFetch = $this->model_checkout_order->getOrder($order_id);
        $order = $this->model_extension_payment_cryptapi->getOrder($order_id);

        $orderObj = isset($order['response']) ? json_decode($order['response']) : '';

        if (!$orderObj) {
            return;
        }

        if ((int)$orderObj->cryptapi_cancelled === 0 && isset($orderObj->cryptapi_payment_url) && (int)$orderFetch['order_status_id'] === 1) {
            $data['button_continue'] = 'Pay Order';
            $data['continue'] = $orderObj->cryptapi_payment_url;
        }
    }
}
