<?php

class ControllerExtensionPaymentCryptapi extends Controller
{

    private $error = array();

    public function index()
    {
        require_once(DIR_SYSTEM . 'library/cryptapi.php');

        $this->load->language('extension/payment/cryptapi');

        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_cryptapi_image'] = $this->language->get('text_cryptapi_image');
        $data['text_connect_cryptapi'] = $this->language->get('text_connect_cryptapi');
        $data['text_cryptapi_support'] = $this->language->get('text_cryptapi_support');
        $data['entry_status'] = $this->language->get('entry_status');
        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['title'] = $this->language->get('title');
        $data['api_key'] = $this->language->get('api_key');
        $data['api_key_info'] = $this->language->get('api_key_info');
        $data['branding'] = $this->language->get('branding');
        $data['qrcode'] = $this->language->get('qrcode');
        $data['blockchain_fees'] = $this->language->get('blockchain_fees');
        $data['text_blockchain_fees'] = $this->language->get('text_blockchain_fees');
        $data['fees'] = $this->language->get('fees');
        $data['text_fees'] = $this->language->get('text_fees');
        $data['qrcode_without_ammount'] = $this->language->get('qrcode_without_ammount');
        $data['qrcode_ammount'] = $this->language->get('qrcode_ammount');
        $data['qrcode_hide_ammount'] = $this->language->get('qrcode_hide_ammount');
        $data['qrcode_hide_without_ammount'] = $this->language->get('qrcode_hide_without_ammount');
        $data['qrcode_size'] = $this->language->get('qrcode_size');
        $data['color_scheme'] = $this->language->get('color_scheme');
        $data['scheme_light'] = $this->language->get('scheme_light');
        $data['scheme_dark'] = $this->language->get('scheme_dark');
        $data['scheme_auto'] = $this->language->get('scheme_auto');
        $data['refresh_values'] = $this->language->get('refresh_values');
        $data['text_refresh_values'] = $this->language->get('text_refresh_values');
        $data['never'] = $this->language->get('never');
        $data['five_minutes'] = $this->language->get('five_minutes');
        $data['ten_minutes'] = $this->language->get('ten_minutes');
        $data['fifteen_minutes'] = $this->language->get('fifteen_minutes');
        $data['thirty_minutes'] = $this->language->get('thirty_minutes');
        $data['forty_five_minutes'] = $this->language->get('forty_five_minutes');
        $data['sixty_minutes'] = $this->language->get('sixty_minutes');
        $data['order_cancelation_timeout'] = $this->language->get('order_cancelation_timeout');
        $data['one_hour'] = $this->language->get('one_hour');
        $data['six_hours'] = $this->language->get('six_hours');
        $data['twelve_hours'] = $this->language->get('twelve_hours');
        $data['eighteen_hours'] = $this->language->get('eighteen_hours');
        $data['twenty_four_hours'] = $this->language->get('twenty_four_hours');
        $data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
        $data['text_all_zones'] = $this->language->get('text_all_zones');
        $data['entry_order_status'] = $this->language->get('entry_order_status');
        $data['entry_cryptocurrencies'] = $this->language->get('entry_cryptocurrencies');
        $data['entry_sort_order'] = $this->language->get('entry_sort_order');
        $data['disable_conversion'] = $this->language->get('entry_sort_order');
        $data['disable_conversion_warn_bold'] = $this->language->get('disable_conversion_warn_bold');
        $data['disable_conversion_warn'] = $this->language->get('disable_conversion_warn');
        $data['help_cryptocurrency'] = $this->language->get('help_cryptocurrency');
        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');
        $data['text_order_cancelation_timeout'] = $this->language->get('text_order_cancelation_timeout');
        $data['qrcode_default'] = $this->language->get('qrcode_default');
        $data['help_cryptocurrencies'] = $this->language->get('help_cryptocurrencies');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');
        $this->load->model('extension/payment/cryptapi');

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $this->model_setting_setting->editSetting('payment_cryptapi', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true));
        }

        $this->load->model('localisation/geo_zone');

        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        $this->load->model('localisation/order_status');

        $data['token'] = $this->session->data['token'];

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
            'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'token=' . $this->session->data['token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/payment/cryptapi', 'token=' . $this->session->data['token'], true)
        );

        $data['action'] = $this->url->link('extension/payment/cryptapi', 'token=' . $this->session->data['token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'token=' . $this->session->data['token'], true);


        if (isset($this->request->post['cryptapi_status'])) {
            $data['cryptapi_status'] = $this->request->post['cryptapi_status'];
        } else {
            $data['cryptapi_status'] = $this->config->get('cryptapi_status');
        }

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
            if (isset($this->request->post['payment_cryptapi_cryptocurrencies_address'][$ticker])) {
                $data['payment_cryptapi_cryptocurrencies_address'][$ticker] = $this->request->post['payment_cryptapi_cryptocurrencies_address'][$ticker];
            } else {
                $data['payment_cryptapi_cryptocurrencies_address'][$ticker] = $this->config->get('payment_cryptapi_cryptocurrencies_address')[$ticker];
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