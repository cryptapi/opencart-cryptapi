<?php

class ControllerExtensionPaymentCryptapi extends Controller
{

    private $error = array();

    public function index()
    {
        require_once(DIR_SYSTEM . 'library/cryptapi.php');

        $this->load->language('extension/payment/cryptapi');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

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

        $data['action'] = $this->url->link('extension/payment/cryptapi', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], true);

        /**
         * Defining Cryptocurrencies
         */

        $supported_coins = CryptAPIHelper::get_supported_coins();

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

        $this->response->setOutput($this->load->view('extension/payment/cryptapi', $data));
    }

    public function install()
    {
        $this->load->model('extension/payment/cryptapi');
        $this->model_extension_payment_cryptapi->install();
    }

    public function uninstall()
    {
        $this->load->model('extension/payment/cryptapi');
        $this->model_extension_payment_cryptapi->uninstall();
    }

    public function order_info(&$route, &$data, &$output)
    {
        $order_id = $this->request->get['order_id'];
        $this->load->model('extension/payment/cryptapi');
        $order = $this->model_extension_payment_cryptapi->getOrder($order_id);
        if ($order) {
            $metaData = $order['response'];
            if (!empty($metaData)) {
                $metaData = json_decode($metaData, true);
                $fields = [];
                foreach ($metaData as $key => $val) {
                    $field = ['name' => $key, 'value' => $val];
                    $fields[] = $field;
                }
                if (isset($data['payment_custom_fields']) && is_array($data['payment_custom_fields'])) {
                    $data['payment_custom_fields'] = array_merge($data['payment_custom_fields'], $fields);
                } else {
                    $data['payment_custom_fields'] = $fields;
                }
            }
        }
    }
}
