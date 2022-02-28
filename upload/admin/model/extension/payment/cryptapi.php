<?php

class ModelExtensionPaymentCryptapi extends Model {

    public function install() {
        $this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "cryptapi_order` (
			  `order_id` int(11) NOT NULL,
			  `response` TEXT
			) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;");
        $this->load->model('setting/event');
        $this->model_setting_event->addEvent('payment_cryptapi', 'catalog/view/common/success/after', 'extension/payment/cryptapi/after_purchase');
        $this->model_setting_event->addEvent('payment_cryptapi', 'catalog/controller/checkout/success/before', 'extension/payment/cryptapi/before_checkout_success');
        $this->model_setting_event->addEvent('payment_cryptapi', 'admin/view/sale/order_info/before', 'extension/payment/cryptapi/order_info');
    }

    public function uninstall() {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "cryptapi_order`;");
        $this->load->model('setting/event');
        $this->model_setting_event->deleteEventByCode('payment_cryptapi');
    }

    public function getOrder($order_id) {
        $qry = $this->db->query("SELECT * FROM `" . DB_PREFIX . "cryptapi_order` WHERE `order_id` = '" . (int)$order_id . "' LIMIT 1");

        if ($qry->num_rows) {
            $order = $qry->row;
            return $order;
        } else {
            return false;
        }
    }
}