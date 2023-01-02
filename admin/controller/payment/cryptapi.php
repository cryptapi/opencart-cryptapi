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

        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
            $this->model_setting_setting->editSetting('payment_cryptapi', $this->request->post);

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

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
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

        if (isset($this->request->post['payment_cryptapi_api_key'])) {
            $data['payment_cryptapi_api_key'] = $this->request->post['payment_cryptapi_api_key'];
        } else {
            $data['payment_cryptapi_api_key'] = $this->config->get('payment_cryptapi_api_key');
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

    public function order_info(&$route, &$data, &$output)
    {
        $order_id = $this->request->get['order_id'];
        $this->load->model('extension/cryptapi/payment/cryptapi');
        $order = $this->model_extension_cryptapi_payment_cryptapi->getOrder($order_id);
        if ($order) {
            $metaData = $order['response'];

            if (!empty($metaData)) {
                $metaData = json_decode($metaData, true);
                $fields = '';
                foreach ($metaData as $key => $val) {
                    if ($key === 'cryptapi_qrcode_value' || $key === 'cryptapi_qrcode') {
                        $fields .= '<tr><td>' . $key . '</td><td style="line-break: anywhere"><img width="100" class="img-fluid" src="data:image/png;base64,' . $val . '"/></td></tr>';
                    } else if ($key === 'cryptapi_history') {
                        $history = json_decode($val);
                        $historyObj = '<table class="table table-bordered">';
                        foreach ($history as $h_key => $h_val) {
                            $historyObj .= '<tr><td colspan="2"><strong>UUID:</strong> ' . $h_key . '</td>';
                            foreach ($h_val as $hrow_key => $hrow_value) {
                                $historyObj .= '<tr><td>' . $hrow_key . '</td><td>' . $hrow_value . '</td>';
                            }
                            $historyObj .= '</tr>';
                        }
                        $historyObj .= '</table>';
                        $fields .= '<tr><td>' . $key . '</td><td>' . $historyObj . '</td></tr>';

                    } else if ($key === 'cryptapi_last_price_update' || $key === 'cryptapi_order_timestamp') {
                        $fields .= '<tr><td>' . $key . '</td><td style="line-break: anywhere">' . date('d-m-Y H:i:s', (int)$val) . '</td></tr>';

                    } else {
                        $fields .= '<tr><td>' . $key . '</td><td style="line-break: anywhere">' . $val . '</td></tr>';
                    }
                }

                if ($data['tabs'][0]['code'] === 'cryptapi') {
                    $data['tabs'][0]['content'] = '<table style="font-size: 13px;" class="table table-bordered">' . $fields . '<table>';
                }
            }
        }
    }

    public function install(): void
    {
        $this->load->model('extension/cryptapi/payment/cryptapi');

        $this->model_extension_cryptapi_payment_cryptapi->install();
    }
}
