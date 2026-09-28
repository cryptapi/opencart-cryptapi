<?php

namespace Opencart\Admin\Controller\Extension\CryptAPI\Payment;

use Opencart\System\Engine\Config;

class CryptAPI extends \Opencart\System\Engine\Controller
{
    private $error = [];

    public function index()
    {
        require(DIR_EXTENSION . 'cryptapi/system/library/cryptapi.php');

        $this->load->language('extension/cryptapi/payment/cryptapi');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('extension/cryptapi/payment/cryptapi');

        // Core's startup only enforces 'access'. Saving needs 'modify': these settings
        // hold the receiving addresses, so a view-only admin must not change them.
        $can_modify = $this->user->hasPermission('modify', 'extension/cryptapi/payment/cryptapi');

        // Repair events on stores upgraded from an older release (install() doesn't re-run on upgrade).
        if ($can_modify) {
            $this->model_extension_cryptapi_payment_cryptapi->syncEvents();
        }

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && !$can_modify) {
            $this->error['warning'] = $this->language->get('error_permission');
        } elseif (($this->request->server['REQUEST_METHOD'] == 'POST')) {
            $paid_statuses = [];
            if (isset($_POST['payment_cryptapi_paid_order_status_ids'])) {
                foreach ($_POST['payment_cryptapi_paid_order_status_ids'] as $value) {
                    $paid_statuses[] = (int)$value;
                }
            }
            $this->request->post['payment_cryptapi_paid_order_status_ids'] = $paid_statuses;

            // Core's 'admin_currency_setting' event runs the currency-rate engine after
            // every editSetting(). On PHP 8.5, OpenCart <= 4.1.0.3's ECB engine prints a
            // curl_close() deprecation there, and the redirect below then fails with
            // "headers already sent". The settings are saved by then, so drop that output.
            $ob_level = ob_get_level();
            ob_start();
            $this->model_setting_setting->editSetting('payment_cryptapi', $this->request->post);
            while (ob_get_level() > $ob_level) {
                ob_end_clean();
            }

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
        }

        $this->load->model('localisation/geo_zone');

        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        $this->load->model('localisation/order_status');

        $orderStatuses = $this->model_localisation_order_status->getOrderStatuses();
        $orderStatusesFiltered = [];
        $orderStatusesIgnore = [
            'Canceled',
            'Canceled Reversal',
            'Chargeback',
            'Complete',
            'Denied',
            'Expired',
            'Failed',
            'Processed',
            'Processing',
            'Refunded',
            'Reversed',
            'Shipped',
            'Voided'
        ];
        foreach ($orderStatuses as $orderStatus) {
            if (!in_array($orderStatus['name'], $orderStatusesIgnore)) {
                $orderStatusesFiltered[] = $orderStatus;
            }
        }
        $data['order_statuses'] = $orderStatusesFiltered;
        $data['all_order_statuses'] = $orderStatuses;

        if (isset($this->request->post['payment_cryptapi_paid_order_status_ids'])) {
            $data['payment_cryptapi_paid_order_status_ids'] = $this->request->post['payment_cryptapi_paid_order_status_ids'];
        } else {
            $saved_paid = $this->config->get('payment_cryptapi_paid_order_status_ids');
            $data['payment_cryptapi_paid_order_status_ids'] = is_array($saved_paid) ? $saved_paid : [2, 3, 15];
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        $data['currency_warning'] = '';
        $store_currency = $this->config->get('config_currency');
        if (!empty($store_currency)) {
            $supported = $this->cache->get('cryptapi.fiat_currencies');
            if (empty($supported) || !is_array($supported)) {
                $estimate = \Opencart\Extension\CryptAPI\System\Library\CryptAPIHelper::get_estimate('btc');
                if (is_object($estimate)) {
                    $supported = array_keys(get_object_vars($estimate));
                    $this->cache->set('cryptapi.fiat_currencies', $supported);
                }
            }
            if (is_array($supported) && !in_array($store_currency, $supported, true)) {
                $data['currency_warning'] = sprintf($this->language->get('warning_currency_unsupported'), $store_currency);
            }
        }

        // Pre-3.5.0 API key left in settings; the next save drops it (editSetting replaces the group).
        $data['api_key_warning'] = '';
        if (!empty($this->config->get('payment_cryptapi_api_key'))) {
            $data['api_key_warning'] = $this->language->get('warning_api_key_removed');
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/payment/cryptapi', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['action'] = $this->url->link('extension/cryptapi/payment/cryptapi', 'user_token=' . $this->session->data['user_token']);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment');

        /**
         * Defining Cryptocurrencies
         */

        $supported_coins = \Opencart\Extension\CryptAPI\System\Library\CryptAPIHelper::get_supported_coins();

        $data['payment_cryptapi_cryptocurrencies_array'] = $supported_coins;

        if (isset($this->request->post['payment_cryptapi_cryptocurrencies'])) {
            $data['payment_cryptapi_cryptocurrencies'] = $this->request->post['payment_cryptapi_cryptocurrencies'];
        } else {
            $data['payment_cryptapi_cryptocurrencies'] = $this->config->get('payment_cryptapi_cryptocurrencies');
        }

        foreach ($supported_coins as $ticker => $coin) {
            if (isset($this->request->post['payment_cryptapi_cryptocurrencies_address_' . $ticker])) {
                $data['payment_cryptapi_cryptocurrencies_address_' . $ticker] = $this->request->post['payment_cryptapi_cryptocurrencies_address_' . $ticker];
            } else {
                $data['payment_cryptapi_cryptocurrencies_address_' . $ticker] = $this->config->get('payment_cryptapi_cryptocurrencies_address_' . $ticker);
            }
        }

        if (isset($this->request->post['payment_cryptapi_disable_conversion'])) {
            $data['payment_cryptapi_disable_conversion'] = $this->request->post['payment_cryptapi_disable_conversion'];
        } else {
            $data['payment_cryptapi_disable_conversion'] = $this->config->get('payment_cryptapi_disable_conversion');
        }

        if (isset($this->request->post['payment_cryptapi_title'])) {
            $data['payment_cryptapi_title'] = $this->request->post['payment_cryptapi_title'];
        } else {
            $data['payment_cryptapi_title'] = $this->config->get('payment_cryptapi_title');
        }

        // Cron secret read-back (POST handler already persists it via editSetting).
        if (isset($this->request->post['payment_cryptapi_cron_secret'])) {
            $data['payment_cryptapi_cron_secret'] = $this->request->post['payment_cryptapi_cron_secret'];
        } else {
            $data['payment_cryptapi_cron_secret'] = $this->config->get('payment_cryptapi_cron_secret');
        }

        if (isset($this->request->post['payment_cryptapi_standard_geo_zone_id'])) {
            $data['payment_cryptapi_standard_geo_zone_id'] = $this->request->post['payment_cryptapi_standard_geo_zone_id'];
        } else {
            $data['payment_cryptapi_standard_geo_zone_id'] = $this->config->get('payment_cryptapi_standard_geo_zone_id');
        }

        if (isset($this->request->post['payment_cryptapi_order_status_id'])) {
            $data['payment_cryptapi_order_status_id'] = $this->request->post['payment_cryptapi_order_status_id'];
        } else {
            $data['payment_cryptapi_order_status_id'] = $this->config->get('payment_cryptapi_order_status_id');
            if (!$data['payment_cryptapi_order_status_id']) {
                $data['payment_cryptapi_order_status_id'] = 1;
            }
        }

        if (isset($this->request->post['payment_cryptapi_status'])) {
            $data['payment_cryptapi_status'] = $this->request->post['payment_cryptapi_status'];
        } else {
            $data['payment_cryptapi_status'] = $this->config->get('payment_cryptapi_status');
        }

        if (isset($this->request->post['payment_cryptapi_blockchain_fees'])) {
            $data['payment_cryptapi_blockchain_fees'] = $this->request->post['payment_cryptapi_blockchain_fees'];
        } else {
            $data['payment_cryptapi_blockchain_fees'] = $this->config->get('payment_cryptapi_blockchain_fees');
        }

        if (isset($this->request->post['payment_cryptapi_fees'])) {
            $data['payment_cryptapi_fees'] = $this->request->post['payment_cryptapi_fees'];
        } else {
            $data['payment_cryptapi_fees'] = $this->config->get('payment_cryptapi_fees');
        }

        if (isset($this->request->post['payment_cryptapi_color_scheme'])) {
            $data['payment_cryptapi_color_scheme'] = $this->request->post['payment_cryptapi_color_scheme'];
        } else {
            $data['payment_cryptapi_color_scheme'] = $this->config->get('payment_cryptapi_color_scheme');
        }

        if (isset($this->request->post['payment_cryptapi_refresh_values'])) {
            $data['payment_cryptapi_refresh_values'] = $this->request->post['payment_cryptapi_refresh_values'];
        } else {
            $data['payment_cryptapi_refresh_values'] = $this->config->get('payment_cryptapi_refresh_values');
        }

        if (isset($this->request->post['payment_cryptapi_order_cancelation_timeout'])) {
            $data['payment_cryptapi_order_cancelation_timeout'] = $this->request->post['payment_cryptapi_order_cancelation_timeout'];
        } else {
            $data['payment_cryptapi_order_cancelation_timeout'] = $this->config->get('payment_cryptapi_order_cancelation_timeout');
        }

        if (isset($this->request->post['payment_cryptapi_branding'])) {
            $data['payment_cryptapi_branding'] = $this->request->post['payment_cryptapi_branding'];
        } else {
            $data['payment_cryptapi_branding'] = $this->config->get('payment_cryptapi_branding');
        }

        if (isset($this->request->post['payment_cryptapi_qrcode'])) {
            $data['payment_cryptapi_qrcode'] = $this->request->post['payment_cryptapi_qrcode'];
        } else {
            $data['payment_cryptapi_qrcode'] = $this->config->get('payment_cryptapi_qrcode');
        }

        if (isset($this->request->post['payment_cryptapi_qrcode_default'])) {
            $data['payment_cryptapi_qrcode_default'] = $this->request->post['payment_cryptapi_qrcode_default'];
        } else {
            $data['payment_cryptapi_qrcode_default'] = $this->config->get('payment_cryptapi_qrcode_default');
        }

        if (isset($this->request->post['payment_cryptapi_qrcode_size'])) {
            $data['payment_cryptapi_qrcode_size'] = $this->request->post['payment_cryptapi_qrcode_size'];
        } else {
            $data['payment_cryptapi_qrcode_size'] = $this->config->get('payment_cryptapi_qrcode_size');
        }

        if (isset($this->request->post['payment_cryptapi_sort_order'])) {
            $data['payment_cryptapi_sort_order'] = $this->request->post['payment_cryptapi_sort_order'];
        } else {
            $data['payment_cryptapi_sort_order'] = $this->config->get('payment_cryptapi_sort_order');
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/cryptapi/payment/cryptapi', $data));
    }

    // Core's sale/order page calls <payment extension>|order / .order and shows
    // the returned HTML as a tab on every OC 4 version; no event needed.
    public function order(): string
    {
        $order_id = (int)($this->request->get['order_id'] ?? 0);
        $this->load->model('extension/cryptapi/payment/cryptapi');
        $order = $this->model_extension_cryptapi_payment_cryptapi->getOrder($order_id);
        if (!$order) { return ''; }

        $metaData = $order['response'];
        if (empty($metaData)) { return ''; }
        $metaData = json_decode($metaData, true);
        if (!is_array($metaData)) { return ''; }

        // Escape every dynamic segment before concatenating into admin HTML.
        $esc = static function ($v): string {
            if (!is_scalar($v)) { $v = json_encode($v); }
            return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
        };
        $is_b64 = static function ($v): bool {
            return is_string($v) && $v !== '' && (bool)preg_match('#^[A-Za-z0-9+/=\r\n]+$#', $v) && base64_decode($v, true) !== false;
        };
        // Never render per-order secrets in admin (nonce/token are secrets; callback_url embeds &nonce=).
        $skip = ['cryptapi_nonce' => true, 'cryptapi_token' => true, 'cryptapi_callback_url' => true];

        $fields = '';
        foreach ($metaData as $key => $val) {
            if (isset($skip[$key])) { continue; }
            if ($key === 'cryptapi_qrcode_value' || $key === 'cryptapi_qrcode') {
                $img = $is_b64($val)
                    ? '<img width="100" class="img-fluid" src="data:image/png;base64,' . $esc($val) . '"/>'
                    : '(invalid image data)';
                $fields .= '<tr><td>' . $esc($key) . '</td><td style="line-break: anywhere">' . $img . '</td></tr>';
            } elseif ($key === 'cryptapi_history') {
                $history = json_decode($val, true);
                $historyObj = '<table class="table table-bordered">';
                if (is_array($history)) {
                    foreach ($history as $h_key => $h_val) {
                        $historyObj .= '<tr><td colspan="2"><strong>UUID:</strong> ' . $esc($h_key) . '</td></tr>';
                        if (is_array($h_val)) {
                            foreach ($h_val as $hrow_key => $hrow_value) {
                                $historyObj .= '<tr><td>' . $esc($hrow_key) . '</td><td>' . $esc($hrow_value) . '</td></tr>';
                            }
                        }
                    }
                }
                $historyObj .= '</table>';
                $fields .= '<tr><td>' . $esc($key) . '</td><td>' . $historyObj . '</td></tr>';
            } elseif ($key === 'cryptapi_last_price_update' || $key === 'cryptapi_order_timestamp') {
                $fields .= '<tr><td>' . $esc($key) . '</td><td style="line-break: anywhere">' . $esc(date('d-m-Y H:i:s', (int)$val)) . '</td></tr>';
            } else {
                $fields .= '<tr><td>' . $esc($key) . '</td><td style="line-break: anywhere">' . $esc($val) . '</td></tr>';
            }
        }

        return '<table style="font-size: 13px;" class="table table-bordered">' . $fields . '</table>';
    }

    // OC 4.0.0.0 only (registered by the model's syncEvents()): core builds the
    // payment tab route as 'extension/payment/cryptapi|order', which doesn't
    // exist, so add the tab here instead.
    public function order_tab(&$route, &$data, &$output)
    {
        if (($data['payment_code'] ?? '') !== 'cryptapi') {
            return;
        }

        foreach ((array)($data['tabs'] ?? []) as $tab) {
            if (($tab['code'] ?? '') === 'cryptapi') {
                return;
            }
        }

        $content = $this->order();
        if ($content === '') {
            return;
        }

        $this->load->language('extension/cryptapi/payment/cryptapi', 'cryptapi');

        $data['tabs'][] = [
            'code'    => 'cryptapi',
            'title'   => $this->language->get('cryptapi_heading_title'),
            'content' => $content,
        ];
    }

    public function install(): void
    {
        $this->load->model('extension/cryptapi/payment/cryptapi');

        $this->model_extension_cryptapi_payment_cryptapi->install();
    }

    public function uninstall(): void
    {
        $this->load->model('extension/cryptapi/payment/cryptapi');

        $this->model_extension_cryptapi_payment_cryptapi->uninstall();
    }
}
