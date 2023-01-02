<?php
namespace Opencart\Admin\Model\Extension\CryptAPI\Payment;
class CryptAPI extends \Opencart\System\Engine\Model {

    public function install() {
        // Create events
        $this->load->model('setting/event');

        if (!$this->model_setting_event->getEventByCode('cryptapi_order_info')) {
            $this->model_setting_event->addEvent(['code' => 'cryptapi_order_info', 'description' => '', 'trigger' => 'admin/view/sale/order_info/before', 'action' => 'extension/cryptapi/payment/cryptapi|order_info', 'status' => 1, 'sort_order' => '1']);
        }

        if (!$this->model_setting_event->getEventByCode('cryptapi_order_button')) {
            $this->model_setting_event->addEvent(['code' => 'cryptapi_order_button', 'description' => '', 'trigger' => 'catalog/view/account/order_info/before', 'action' => 'extension/cryptapi/payment/cryptapi|order_pay_button', 'status' => 1, 'sort_order' => '1']);
        }

        if (!$this->model_setting_event->getEventByCode('cryptapi_after_purchase')) {
            $this->model_setting_event->addEvent(['code' => 'cryptapi_after_purchase', 'description' => '', 'trigger' => 'catalog/view/common/success/after', 'action' => 'extension/cryptapi/payment/cryptapi|after_purchase', 'status' => 1, 'sort_order' => '1']);
        }

        $this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "cryptapi_order` (
			  `order_id` int(11) NOT NULL,
			  `response` TEXT
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
    }

    public function getOrder($order_id): array
    {
        $qry = $this->db->query("SELECT * FROM `" . DB_PREFIX . "cryptapi_order` WHERE `order_id` = '" . (int)$order_id . "' LIMIT 1");

        if ($qry->num_rows) {
            $order = $qry->row;
            return $order;
        } else {
            return [];
        }
    }
}