<?php

namespace Opencart\Catalog\Controller\Extension\CryptAPI\Payment;

class CryptAPI extends \Opencart\System\Engine\Controller
{
    public function index(): string
    {
        if ($this->config->get('payment_cryptapi_status')) {
            // Library
            require(DIR_EXTENSION . 'cryptapi/system/library/cryptapi.php');

            $this->load->language('extension/cryptapi/payment/cryptapi');
            $this->load->model('extension/cryptapi/payment/cryptapi');
            $this->load->model('localisation/country');
            $this->load->model('checkout/order');

            $data['title'] = $this->config->get('payment_cryptapi_title');

            $data['cryptocurrencies'] = array();

            $order = $this->model_checkout_order->getOrder($this->session->data['order_id']);

            $order_total = floatval($order['total']);

            foreach ($this->config->get('payment_cryptapi_cryptocurrencies') as $selected) {
                foreach (json_decode(html_entity_decode($this->config->get('payment_cryptapi_cryptocurrencies_array_cache'), ENT_QUOTES | ENT_HTML5, 'UTF-8'), true) as $token => $coin) {
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
            $selected = $this->session->data['cryptapi_selected'] ?? '';
            $cryptapiFee = 0;

            if ($selected) {
                if ($fee !== 0) {
                    $cryptapiFee += floatval($fee) * $order_total;
                }

                if ($blockchain_fee) {
                    $estimate = \Opencart\Extension\CryptAPI\System\Library\CryptAPIHelper::get_estimate($this->session->data['cryptapi_selected']);
                    if (is_object($estimate) && isset($estimate->$currency)) {
                        $cryptapiFee += floatval($estimate->$currency);
                    } elseif (is_object($estimate) && isset($estimate->USD)) {
                        $cryptapiFee += floatval($this->currency->convert($estimate->USD, 'USD', $currency));
                    }
                }
            }

            $data['fee'] = $fee;
            $data['blockchain_fee'] = $blockchain_fee;
            $data['cryptapi_fee'] = $this->currency->format($cryptapiFee, $currency, 1.00000, false);
            $data['total'] = $this->currency->format($order_total + $cryptapiFee, $currency, 1.00000, false);
            $data['language'] = $this->config->get('config_language');
            $data['selected'] = $selected;

            $this->session->data['cryptapi_fee'] = round($cryptapiFee, 2);

            $this->load->model('checkout/order');

            return $this->load->view('extension/cryptapi/payment/cryptapi', $data);
        }
        return false;
    }

    public function sel_crypto()
    {
        $this->load->model('extension/cryptapi/payment/cryptapi');

        // Only accept a coin present in the configured allow-list.
        $selected = (string)($this->request->post['cryptapi_coin'] ?? '');
        $allowed  = $this->config->get('payment_cryptapi_cryptocurrencies');
        if (is_array($allowed) && in_array($selected, $allowed, true)) {
            $this->session->data['cryptapi_selected'] = $selected;
        }
    }

    // Bind pay()/status() visibility to the requester. Self-sufficient —
    // loads its own payment model so it is safe to call before the caller does.
    private function authorizeOrderAccess(array $order): bool
    {
        $order_id = (int)($order['order_id'] ?? 0);
        if ($order_id <= 0) {
            return false;
        }
        $this->load->model('extension/cryptapi/payment/cryptapi');

        // (a) same-session owner: confirm->pay redirect + live pay-page polling.
        if ((int)($this->session->data['order_id'] ?? 0) === $order_id) {
            return true;
        }
        // (a') logged-in customer owns the order. In OC4 $this->customer is a
        // registry magic-property, so isset() is unreliable — call isLogged() directly.
        $customer_id = (int)($order['customer_id'] ?? 0);
        if ($customer_id > 0 && $this->customer->isLogged() && (int)$this->customer->getId() === $customer_id) {
            return true;
        }
        // (b) per-order access token: guest checkout / email link / account button / new session.
        $token = (string)($this->request->get['token'] ?? '');
        if ($token !== '') {
            $meta = json_decode((string)$this->model_extension_cryptapi_payment_cryptapi->getPaymentData($order_id), true);
            $stored = is_array($meta) ? (string)($meta['cryptapi_token'] ?? '') : '';
            if ($stored !== '' && hash_equals($stored, $token)) {
                return true;
            }
        }
        return false;
    }

    // Gate the public |cron route — CLI always; configured secret always;
    // loopback only when the request did NOT arrive through a proxy.
    private function isCronAuthorized(): bool
    {
        if (PHP_SAPI === 'cli' || php_sapi_name() === 'cli') {
            return true;
        }
        // Configured secret works everywhere (including behind proxies).
        $secret = (string)$this->config->get('payment_cryptapi_cron_secret');
        if ($secret !== '') {
            $provided = (string)($this->request->get['secret'] ?? '');
            if ($provided !== '' && hash_equals($secret, $provided)) {
                return true;
            }
        }
        // Trust loopback ONLY when the request did NOT arrive through a proxy.
        // Behind a same-host reverse proxy REMOTE_ADDR is loopback for ALL external
        // traffic, so require the absence of forwarding headers.
        $behind_proxy = !empty($_SERVER['HTTP_X_FORWARDED_FOR'])
            || !empty($_SERVER['HTTP_X_FORWARDED_HOST'])
            || !empty($_SERVER['HTTP_X_REAL_IP'])
            || !empty($_SERVER['HTTP_FORWARDED']);
        $remote = $_SERVER['REMOTE_ADDR'] ?? '';
        if (!$behind_proxy && in_array($remote, ['127.0.0.1', '::1', '::ffff:127.0.0.1'], true)) {
            return true;
        }
        return false;
    }

    // Extracted per-order refresh/cancel (the old cron loop body) with a positive-total guard.
    private function refreshOrder(array $order): void
    {
        $order_timeout = (int)$this->config->get('payment_cryptapi_order_cancelation_timeout');
        $value_refresh = (int)$this->config->get('payment_cryptapi_refresh_values');
        $qrcode_size   = (int)$this->config->get('payment_cryptapi_qrcode_size');
        $lib           = '\\Opencart\\Extension\\CryptAPI\\System\\Library\\CryptAPIHelper';

        if ($order_timeout === 0 && $value_refresh === 0) {
            return;
        }

        $order_id = (int)$order['order_id'];
        $currency = $order['currency_code'];
        $metaData = json_decode($this->model_extension_cryptapi_payment_cryptapi->getPaymentData($order_id), true);
        if (empty($metaData['cryptapi_last_price_update'])) {
            return;
        }

        $last_price_update = $metaData['cryptapi_last_price_update'];
        $history = json_decode($metaData['cryptapi_history'], true) ?: [];
        $min_tx  = floatval($metaData['cryptapi_min']);

        $calc = $lib::calc_order($history, $metaData['cryptapi_total'], floatval($metaData['cryptapi_total_fiat']));
        $remaining         = $calc['remaining'];
        $remaining_pending = $calc['remaining_pending'];
        $already_paid      = $calc['already_paid'];

        if ($value_refresh !== 0 && $last_price_update + $value_refresh <= time()) {
            if ($remaining === $remaining_pending) {
                $cryptapi_coin = $metaData['cryptapi_currency'];
                $crypto_total  = $lib::get_conversion($currency, $cryptapi_coin, $metaData['cryptapi_total_fiat'], $this->config->get('payment_cryptapi_disable_conversion'));

                // Never persist a null/non-positive re-priced total; keep prior good value.
                if ($lib::is_positive_number($crypto_total)) {
                    $this->model_extension_cryptapi_payment_cryptapi->updatePaymentData($order_id, 'cryptapi_total', $crypto_total);
                    $calc_cron = $lib::calc_order($history, $crypto_total, $metaData['cryptapi_total_fiat']);
                    $crypto_remaining_total = $calc_cron['remaining_pending'];
                    if ($remaining_pending <= $min_tx && $remaining_pending > 0) {
                        $qr = $lib::get_static_qrcode($metaData['cryptapi_address'], $cryptapi_coin, $min_tx, $qrcode_size);
                    } else {
                        $qr = $lib::get_static_qrcode($metaData['cryptapi_address'], $cryptapi_coin, $crypto_remaining_total, $qrcode_size);
                    }
                    if (is_array($qr) && isset($qr['qr_code'])) {
                        $this->model_extension_cryptapi_payment_cryptapi->updatePaymentData($order_id, 'cryptapi_qrcode_value', $qr['qr_code']);
                    }
                }
            }
            $this->model_extension_cryptapi_payment_cryptapi->updatePaymentData($order_id, 'cryptapi_last_price_update', time());
        }

        $age_seconds = isset($order['age_seconds']) ? (int)$order['age_seconds'] : (time() - strtotime($order['date_added']));
        if ($order_timeout !== 0 && $age_seconds >= $order_timeout && $already_paid <= 0) {
            $this->model_checkout_order->addHistory($order_id, 7);
            $this->model_extension_cryptapi_payment_cryptapi->updatePaymentData($order_id, 'cryptapi_cancelled', '1');
        }
    }

    public function confirm()
    {
        // Library
        $this->load->language('extension/cryptapi/payment/cryptapi');
        require(DIR_EXTENSION . 'cryptapi/system/library/cryptapi.php');
        $lib = '\\Opencart\\Extension\\CryptAPI\\System\\Library\\CryptAPIHelper';

        $json = [];
        $err_coin = '';

        if (!$this->config->get('payment_cryptapi_status')) {
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }

        $this->load->model('checkout/order');
        $this->load->model('extension/cryptapi/payment/cryptapi');

        $order_id = (int)($this->session->data['order_id'] ?? 0);
        $order_info = $this->model_checkout_order->getOrder($order_id);
        if (empty($order_info)) {
            $json['error']['warning'] = sprintf($this->language->get('error_payment'), $this->language->get('error_coin'));
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }

        // Idempotency: never clobber a paid / partially-paid order.
        $prevAddresses = [];
        $existingRaw = $this->model_extension_cryptapi_payment_cryptapi->getPaymentData($order_id);
        if (!empty($existingRaw)) {
            $em   = json_decode($existingRaw, true) ?: [];
            $hist = json_decode($em['cryptapi_history'] ?? '[]', true) ?: [];
            $alreadyPaid = $this->isOrderPaid($order_info) || (!empty($em['cryptapi_paid']) && (string)$em['cryptapi_paid'] === '1');
            $hasPayments = is_array($hist) && count($hist) > 0;
            if ($alreadyPaid || $hasPayments) {
                $redir = $em['cryptapi_payment_url']
                    ?? $this->url->link('extension/cryptapi/payment/cryptapi|pay', 'order_id=' . $order_id . '&token=' . ($em['cryptapi_token'] ?? ''), true);
                $json['redirect'] = str_replace('&amp;', '&', $redir);
                $this->response->addHeader('Content-Type: application/json');
                $this->response->setOutput(json_encode($json));
                return;
            }
            // Unpaid + empty history => re-selection allowed. Preserve prior address(es)
            // so a payment already sent to a now-replaced address is still creditable.
            $prevAddresses = is_array($em['cryptapi_prev_addresses'] ?? null) ? $em['cryptapi_prev_addresses'] : [];
            if (!empty($em['cryptapi_address'])) {
                $prevAddresses[] = (string)$em['cryptapi_address'];
            }
            $prevAddresses = array_values(array_unique(array_filter($prevAddresses, 'strlen')));
        }

        $apiKey = $this->config->get('payment_cryptapi_api_key');
        $address = '';

        // Coin allow-list; API key OR per-coin own address is sufficient.
        if (empty($this->request->post['cryptapi_coin'])) {
            $err_coin = $this->language->get('error_coin');
        } else {
            $selected = $this->request->post['cryptapi_coin'];

            $allowed = $this->config->get('payment_cryptapi_cryptocurrencies');
            if (!is_array($allowed) || !in_array($selected, $allowed, true)) {
                $err_coin = $this->language->get('error_coin');
            }

            $address = $this->config->get('payment_cryptapi_cryptocurrencies_address_' . $selected);
            if (empty($err_coin) && empty($address) && empty($apiKey)) {
                $err_coin = $this->language->get('error_apikey');
            }
        }

        if (empty($err_coin) && (!empty($address) || !empty($apiKey))) {
            $disable_conversion = $this->config->get('payment_cryptapi_disable_conversion');
            $qr_code_size = $this->config->get('payment_cryptapi_qrcode_size');
            $currency = $order_info['currency_code'];

            // Server-side fee (never trust session['cryptapi_fee']).
            $order_total = floatval($order_info['total']);
            $fee = $this->config->get('payment_cryptapi_fees');
            $blockchain_fee = $this->config->get('payment_cryptapi_blockchain_fees');
            $cryptapiFeeRaw = 0;
            if ($fee !== 0) {
                $cryptapiFeeRaw += floatval($fee) * $order_total;
            }
            if ($blockchain_fee) {
                $estimate = $lib::get_estimate($selected);
                if (is_object($estimate) && isset($estimate->$currency)) {
                    $cryptapiFeeRaw += floatval($estimate->$currency);
                } elseif (is_object($estimate) && isset($estimate->USD)) {
                    $cryptapiFeeRaw += floatval($this->currency->convert($estimate->USD, 'USD', $currency));
                }
            }
            $cryptoFee = round($cryptapiFeeRaw, 2);
            $total = $this->currency->format($order_info['total'] + $cryptoFee, $currency, 1.00000, false);

            $info = $lib::get_info($selected, false);
            $minTx = floatval($info->minimum_transaction_coin ?? 0);

            $cryptoTotal = $lib::get_conversion($currency, $selected, $total, $disable_conversion);

            // Refuse to create an order with a non-positive total.
            if (!$lib::is_positive_number($cryptoTotal)) {
                $err_coin = $this->language->get('error_conversion');
            }

            if (empty($err_coin)) {
                // Per-order nonce + access token (distinct CSPRNG secrets).
                $nonce = bin2hex(random_bytes(16));
                $token = bin2hex(random_bytes(16));

                // Registered callback URL — server-fixed base; decode &amp; so
                // the registered string, the signed string, and REQUEST_URI all agree.
                $callbackUrl = $this->url->link('extension/cryptapi/payment/cryptapi|callback', 'order_id=' . $order_id . '&nonce=' . $nonce, true);
                $callbackUrl = str_replace('&amp;', '&', $callbackUrl);

                // NOTE: CryptAPI constructor takes the extra own-address arg vs BlockBee.
                $helper = new \Opencart\Extension\CryptAPI\System\Library\CryptAPIHelper($selected, $address, $apiKey, $callbackUrl, [], true);
                $addressIn = $helper->get_address();
                if (!isset($addressIn)) {
                    $err_coin = $this->language->get('error_adress');
                } elseif ($cryptoTotal < $minTx) {
                    $err_coin = $this->language->get('value_minim') . ' ' . $minTx . ' ' . strtoupper($selected);
                }

                if (empty($err_coin)) {
                    $qrCodeDataValue = $helper->get_qrcode($cryptoTotal, $qr_code_size);
                    $qrCodeData = $helper->get_qrcode('', $qr_code_size);

                    // Token baked into pay URL (email + account button + redirect).
                    // Two params now => MUST decode &amp;.
                    $paymentURL = $this->url->link('extension/cryptapi/payment/cryptapi|pay', 'order_id=' . $order_id . '&token=' . $token, true);
                    $paymentURL = str_replace('&amp;', '&', $paymentURL);

                    $paymentData = [
                        'cryptapi_fee' => $cryptoFee,
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
                        'cryptapi_payment_url' => $paymentURL,
                        'cryptapi_nonce' => $nonce,                // provider-shared callback secret
                        'cryptapi_token' => $token,                // customer-facing access token
                        'cryptapi_callback_url' => $callbackUrl,    // server-fixed base
                        'cryptapi_paid' => '0',                     // atomic paid marker
                        'cryptapi_prev_addresses' => $prevAddresses, // re-selection safety
                    ];

                    $encoded = json_encode($paymentData);
                    $this->model_extension_cryptapi_payment_cryptapi->addPaymentData($order_id, $encoded);

                    $this->model_checkout_order->addHistory($order_id, $this->config->get('payment_cryptapi_order_status_id'), '', true);

                    $order_info = $this->model_checkout_order->getOrder($order_id);
                    $this->sendPaymentInstructionsEmail($order_info, json_decode($encoded, true), $paymentURL);

                    $json['redirect'] = $paymentURL;   // already &amp;-decoded
                } else {
                    $json['error']['warning'] = sprintf($this->language->get('error_payment'), $err_coin);
                }
            } else {
                $json['error']['warning'] = sprintf($this->language->get('error_payment'), $err_coin);
            }
        } else {
            $json['error']['warning'] = sprintf($this->language->get('error_payment'), $err_coin);
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function isCryptapiOrder($status = false)
    {
        $order = false;
        if (isset($this->request->get['order_id'])) {
            $order_id = (int)($this->request->get['order_id']);
        }

        if (isset($order_id)) {
            $this->load->model('checkout/order');
            $order = $this->model_checkout_order->getOrder($order_id);

            // OC 4.x: getOrder() auto-decodes payment_method JSON; the stored
            // code is the full `<method>.<option>` form.
            $payment_code = $order['payment_method']['code'] ?? '';
            if ($order && $payment_code !== 'cryptapi.cryptapi') {
                $order = false;
            }

            if (!$status && $order && $order['order_status_id'] != $this->config->get('payment_cryptapi_order_status_id')) {
                $order = false;
            }
        }
        return $order;
    }

    public function pay()
    {
        // In case the extension is disabled, do nothing
        if (!$this->config->get('payment_cryptapi_status')) {
            $this->response->redirect($this->url->link('common/home', '', true));
        }

        // Library
        require(DIR_EXTENSION . 'cryptapi/system/library/cryptapi.php');

        $this->load->language('extension/cryptapi/payment/cryptapi');

        $this->response->addHeader('Referrer-Policy: no-referrer');   // don't leak ?token= via Referer

        // Load the payment model before the gate so authorizeOrderAccess() is usable.
        $this->load->model('extension/cryptapi/payment/cryptapi');

        $order = $this->isCryptapiOrder();

        if (!$order || !$this->authorizeOrderAccess($order)) {
            $this->response->redirect($this->url->link('common/home', '', true));
        }

        $this->load->model('localisation/currency');

        $metaData = $this->model_extension_cryptapi_payment_cryptapi->getPaymentData($order['order_id']);

        if (!empty($metaData)) {
            $metaData = json_decode($metaData, true);
        }

        $total = $metaData['cryptapi_total_fiat'];
        $currencySymbolLeft = $this->model_localisation_currency->getCurrencies()[$order['currency_code']]['symbol_left'];
        $currencySymbolRight = $this->model_localisation_currency->getCurrencies()[$order['currency_code']]['symbol_right'];

        // Carry the per-order token into status polling. Two params now => decode &amp;.
        $token   = is_array($metaData) ? (string)($metaData['cryptapi_token'] ?? '') : '';
        $ajaxUrl = $this->url->link('extension/cryptapi/payment/cryptapi|status', 'order_id=' . $order['order_id'] . '&token=' . $token, true);
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
            'module_path' => HTTP_SERVER . 'extension/cryptapi/catalog/view/image/',
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
            'branding_logo' => HTTP_SERVER . 'extension/cryptapi/catalog/view/image/payment.png',
            'qr_code_setting' => $this->config->get('payment_cryptapi_qrcode'),
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

        return $this->response->setOutput($this->load->view('extension/cryptapi/payment/cryptapi_success', $params));
    }

    public function after_purchase(&$route, &$data, &$output)
    {
        if (!$this->config->get('payment_cryptapi_status')) {
            return;
        }

        $order = $this->isCryptapiOrder();
        if (!$order) {
            return;
        }
        $this->load->model('extension/cryptapi/payment/cryptapi');
        $meta = json_decode((string)$this->model_extension_cryptapi_payment_cryptapi->getPaymentData($order['order_id']), true);
        // Prefer the stored payment URL (already &amp;-decoded and carries the token).
        if (is_array($meta) && !empty($meta['cryptapi_payment_url'])) {
            return $this->response->redirect($meta['cryptapi_payment_url']);
        }
        $token = is_array($meta) ? (string)($meta['cryptapi_token'] ?? '') : '';
        $url = $this->url->link('extension/cryptapi/payment/cryptapi|pay', 'order_id=' . $order['order_id'] . '&token=' . $token, true);
        return $this->response->redirect(str_replace('&amp;', '&', $url));   // two params => decode &amp;
    }

    private function sendPaymentInstructionsEmail(array $order, array $metaData, string $paymentURL): void
    {
        try {
            $mail = new \Opencart\System\Library\Mail($this->config->get('config_mail_engine'));
            $mail->parameter = $this->config->get('config_mail_parameter');
            $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
            $mail->smtp_username = $this->config->get('config_mail_smtp_username');
            $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
            $mail->smtp_port = $this->config->get('config_mail_smtp_port');
            $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

            $coin = strtoupper($metaData['cryptapi_currency'] ?? '');

            $data = [
                'order_greeting' => sprintf($this->language->get('order_greeting'), $order['order_id'], $coin),
                'order_url'      => $paymentURL,
                'store'          => html_entity_decode($order['store_name'], ENT_QUOTES, 'UTF-8'),
                'store_url'      => $order['store_url'],
            ];

            $subject = sprintf($this->language->get('order_subject'), $order['order_id'], $coin);
            $html = $this->load->view('extension/cryptapi/payment/cryptapi_email', $data);

            $mail->setTo($order['email']);
            $mail->setFrom($this->config->get('config_email'));
            $mail->setSender(html_entity_decode($order['store_name'], ENT_QUOTES, 'UTF-8'));
            $mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
            $mail->setHtml($html);
            $mail->send();
        } catch (\Exception $exception) {
            // Silent — SMTP not configured.
        }
    }


    public function isOrderPaid($order)
    {
        $configured = $this->config->get('payment_cryptapi_paid_order_status_ids');
        $successOrderStatuses = is_array($configured) && !empty($configured)
            ? array_map('intval', $configured)
            : [2, 3, 15];
        return in_array((int)$order['order_status_id'], $successOrderStatuses, true) ? 1 : 0;
    }

    public function status()
    {

        // Library
        require(DIR_EXTENSION . 'cryptapi/system/library/cryptapi.php');

        $this->response->addHeader('Referrer-Policy: no-referrer');

        $order = $this->isCryptapiOrder(true);                 // keep true: paid/cancelled view needed

        if (!$order || !$this->authorizeOrderAccess($order)) { // authorizeOrderAccess loads the model
            return false;
        }

        $this->load->model('extension/cryptapi/payment/cryptapi');
        $this->load->model('localisation/currency');

        $metaData = $this->model_extension_cryptapi_payment_cryptapi->getPaymentData($order['order_id']);

        if (!empty($metaData)) {
            $metaData = json_decode($metaData, true);
        }

        $currencySymbolLeft = $this->model_localisation_currency->getCurrencies()[$order['currency_code']]['symbol_left'];
        $currencySymbolRight = $this->model_localisation_currency->getCurrencies()[$order['currency_code']]['symbol_right'];

        $showMinFee = 0;

        $history = json_decode($metaData['cryptapi_history'], true);

        $calc = \Opencart\Extension\CryptAPI\System\Library\CryptAPIHelper::calc_order($history, $metaData['cryptapi_total'], $metaData['cryptapi_total_fiat']);

        $already_paid = $calc['already_paid'];
        $already_paid_fiat = $calc['already_paid_fiat'] <= 0 ? 0 : $calc['already_paid_fiat'];

        $min_tx = floatval($metaData['cryptapi_min']);

        $remaining_pending = $calc['remaining_pending'];
        $remaining_fiat = $calc['remaining_fiat'];

        $cryptapi_pending = 0;
        if ($remaining_pending <= 0 && !$this->isOrderPaid($order)) {
            $cryptapi_pending = 1;
        }

        // Refresh ONLY this already-authorized order — not a global sweep.
        $refresh_values = (int)$this->config->get('payment_cryptapi_refresh_values');
        $counter_calc = (int)$metaData['cryptapi_last_price_update'] + $refresh_values - time();
        if (!$this->isOrderPaid($order) && $counter_calc <= 0) {
            $this->refreshOrder($order);
            // refreshOrder() may have advanced last_price_update; recompute so the
            // client gets the fresh remaining time, not the stale (<= 0) value that
            // would make the countdown reset to ~0 instead of the full interval.
            $fresh = json_decode((string)$this->model_extension_cryptapi_payment_cryptapi->getPaymentData($order['order_id']), true);
            if (is_array($fresh) && isset($fresh['cryptapi_last_price_update'])) {
                $counter_calc = (int)$fresh['cryptapi_last_price_update'] + $refresh_values - time();
            }
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
            'already_paid_fiat' => floatval($already_paid_fiat) <= 0 ? 0 : floatval($already_paid_fiat),
            'counter' => (string)max(0, $counter_calc),
            'fiat_symbol_left' => $currencySymbolLeft,
            'fiat_symbol_right' => $currencySymbolRight,
        ];

        $this->response->addHeader('Content-Type: application/json');

        return $this->response->setOutput(json_encode($data));
    }

    public function cron($load_class = true)
    {
        if ($load_class) {
            // Library
            require(DIR_EXTENSION . 'cryptapi/system/library/cryptapi.php');
        }

        $this->load->model('checkout/order');
        $this->load->model('extension/cryptapi/payment/cryptapi');
        $this->response->addHeader('Content-Type: application/json');

        // Gate the public route (CLI / configured secret / proxy-safe loopback).
        if (!$this->isCronAuthorized()) {
            http_response_code(403);
            return $this->response->setOutput(json_encode(['status' => 'forbidden']));
        }

        $response = $this->response->setOutput(json_encode(['status' => 'ok']));

        $order_timeout = (int)$this->config->get('payment_cryptapi_order_cancelation_timeout');
        $value_refresh = (int)$this->config->get('payment_cryptapi_refresh_values');
        if ($order_timeout === 0 && $value_refresh === 0) {
            return $response;
        }

        $orders = $this->model_extension_cryptapi_payment_cryptapi->getOrders();
        if (empty($orders)) {
            return $response;
        }
        foreach ($orders as $order) {
            $this->refreshOrder($order);
        }
        return $response;
    }

    public function callback()
    {
        require(DIR_EXTENSION . 'cryptapi/system/library/cryptapi.php');
        $lib = '\\Opencart\\Extension\\CryptAPI\\System\\Library\\CryptAPIHelper';

        $this->load->model('extension/cryptapi/payment/cryptapi');
        $this->load->model('checkout/order');

        $data = $lib::process_callback($_GET);

        // Resolve the order from the deposit address; fall back to the signed order_id.
        $bound = $this->model_extension_cryptapi_payment_cryptapi->getOrderByAddress((string)($data['address_in'] ?? ''));
        $order_id = !empty($bound['order_id']) ? (int)$bound['order_id'] : (int)($data['order_id'] ?? 0);

        $metaRaw = $this->model_extension_cryptapi_payment_cryptapi->getPaymentData($order_id);
        if (empty($metaRaw)) { http_response_code(403); die('unknown order'); }
        $metaData = json_decode($metaRaw, true);
        if (!is_array($metaData)) { http_response_code(403); die('bad order data'); }

        // Verify signature over a SERVER-FIXED reconstructed URL (no header trust).
        $base = !empty($metaData['cryptapi_callback_url'])
            ? $metaData['cryptapi_callback_url']
            : (defined('HTTPS_SERVER') ? HTTPS_SERVER : (defined('HTTP_SERVER') ? HTTP_SERVER : ''));
        $signed_url = $lib::build_signed_url($base, $_SERVER['REQUEST_URI'] ?? '');
        $signature  = $_SERVER['HTTP_X_CA_SIGNATURE'] ?? '';

        // api.cryptapi.io and api.blockbee.io are the same service and sign with the
        // SAME RSA key (verified: /pubkey/ is byte-identical on both hosts), so a single
        // pubkey verifies every callback regardless of pro/own-address mode.
        $pubkey = $this->cache->get('cryptapi.pubkey');
        if (empty($pubkey)) {
            $pubkey = $lib::fetch_pubkey();
            if (!empty($pubkey)) { $this->cache->set('cryptapi.pubkey', $pubkey); }
        }
        if (empty($pubkey) || $signed_url === '' || !$lib::verify_signature($signed_url, $signature, $pubkey)) {
            http_response_code(403); die('invalid signature');
        }

        // Per-order nonce, BEFORE any state change (empty==empty passes for legacy rows).
        if (!hash_equals((string)($metaData['cryptapi_nonce'] ?? ''), (string)($data['nonce'] ?? ''))) {
            http_response_code(403); die('invalid nonce');
        }

        // Address binding (current or a prior re-selected address) + order_id consistency.
        $cb_addr     = strtolower(trim((string)($data['address_in'] ?? '')));
        $stored_addr = strtolower(trim((string)($metaData['cryptapi_address'] ?? '')));
        $addr_ok = ($cb_addr !== '' && $stored_addr !== '' && hash_equals($stored_addr, $cb_addr));
        if (!$addr_ok && $cb_addr !== '' && !empty($metaData['cryptapi_prev_addresses']) && is_array($metaData['cryptapi_prev_addresses'])) {
            foreach ($metaData['cryptapi_prev_addresses'] as $prev) {
                if (hash_equals(strtolower(trim((string)$prev)), $cb_addr)) { $addr_ok = true; break; }
            }
        }
        if (!$addr_ok) { http_response_code(403); die('address mismatch'); }
        if ((int)($data['order_id'] ?? 0) !== $order_id) {
            http_response_code(403); die('order mismatch');
        }

        $order = $this->model_checkout_order->getOrder($order_id);

        if (($data['coin'] ?? null) !== ($metaData['cryptapi_currency'] ?? null)) {
            die('*ok*');
        }
        if ($this->isOrderPaid($order) || (!empty($metaData['cryptapi_paid']) && (string)$metaData['cryptapi_paid'] === '1')) {
            die('*ok*');
        }

        // Required total must be positive.
        if (!$lib::is_positive_number($metaData['cryptapi_total'] ?? null)) {
            http_response_code(403); die('total not set');
        }

        $disable_conversion = $this->config->get('payment_cryptapi_disable_conversion');
        $qrcode_size = $this->config->get('payment_cryptapi_qrcode_size');

        // value/value_coin: own-address (api.cryptapi.io) uses `value`; pro (api.blockbee.io) uses `value_coin`.
        $paid = $data['value_coin'] ?? null;
        if ($paid === null || $paid === '') {
            $paid = $data['value'] ?? null;   // api.cryptapi.io (non-pro / own-address) naming
        }

        $min_tx = floatval($metaData['cryptapi_min']);
        $uuid   = (string)($data['uuid'] ?? '');
        if ($uuid === '') { http_response_code(403); die('missing uuid'); }

        // Build the entry, then atomic per-uuid merge.
        $history = json_decode($metaData['cryptapi_history'], true) ?: [];
        if (empty($history[$uuid])) {
            $fiat_conversion = $lib::get_conversion($metaData['cryptapi_currency'], $order['currency_code'], $paid, $disable_conversion);
            $entry = [
                'timestamp'       => time(),
                'value_paid'      => $lib::sig_fig($paid, 6),
                'value_paid_fiat' => $fiat_conversion,
                'pending'         => $data['pending'],
            ];
        } else {
            $entry = ['pending' => $data['pending']];
        }
        $this->model_extension_cryptapi_payment_cryptapi->addHistoryEntry($order_id, $uuid, $entry);

        // Re-read merged state.
        $metaData = json_decode($this->model_extension_cryptapi_payment_cryptapi->getPaymentData($order_id), true);
        $history  = json_decode($metaData['cryptapi_history'], true) ?: [];
        $calc = $lib::calc_order($history, $metaData['cryptapi_total'], $metaData['cryptapi_total_fiat']);
        $remaining         = $calc['remaining'];
        $remaining_pending = $calc['remaining_pending'];

        if ($remaining_pending <= 0) {
            if ($remaining <= 0) {
                // Mark paid at most once; release the claim if side effects fail.
                if ($this->model_extension_cryptapi_payment_cryptapi->claimPaidTransition($order_id)) {
                    try {
                        $this->model_checkout_order->addHistory($order_id, 2);
                        $this->model_extension_cryptapi_payment_cryptapi->updatePaymentData($order_id, 'cryptapi_txid', $data['txid_in']);
                    } catch (\Throwable $e) {
                        $this->model_extension_cryptapi_payment_cryptapi->updatePaymentData($order_id, 'cryptapi_paid', '0');
                        http_response_code(500); die('processing error');   // provider will retry
                    }
                }
            }
            die('*ok*');
        }

        if ($remaining_pending <= $min_tx) {
            $qrcode_conv = $lib::get_static_qrcode($metaData['cryptapi_address'], $metaData['cryptapi_currency'], $min_tx, $qrcode_size)['qr_code'];
        } else {
            $qrcode_conv = $lib::get_static_qrcode($metaData['cryptapi_address'], $metaData['cryptapi_currency'], $remaining_pending, $qrcode_size)['qr_code'];
        }
        $this->model_extension_cryptapi_payment_cryptapi->updatePaymentData($order_id, 'cryptapi_qrcode_value', $qrcode_conv);
        die('*ok*');
    }

    function order_pay_button(&$route, &$data, &$output)
    {
        $order_id = (int)($this->request->get['order_id'] ?? 0);
        if ($order_id <= 0) {
            return;
        }

        $this->load->model('extension/cryptapi/payment/cryptapi');
        $this->load->model('checkout/order');

        $orderFetch = $this->model_checkout_order->getOrder($order_id);
        $order = $this->model_extension_cryptapi_payment_cryptapi->getOrder($order_id);

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
