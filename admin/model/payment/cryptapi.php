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
			  `order_id` INT(11) NOT NULL,
			  `address_in` VARCHAR(255) NOT NULL DEFAULT '',
			  `response` TEXT,
			  PRIMARY KEY (`order_id`),
			  UNIQUE KEY `idx_address_in` (`address_in`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        $this->migrate();
    }

    public function uninstall(): void
    {
        $this->load->model('setting/event');

        foreach (['cryptapi_order_info', 'cryptapi_order_button', 'cryptapi_after_purchase'] as $code) {
            $this->model_setting_event->deleteEventByCode($code);
        }

        // `oc_cryptapi_order` is intentionally not dropped — keeps order
        // history available if the extension is reinstalled later.
    }

    private function migrate(): void
    {
        $columns = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "cryptapi_order` LIKE 'address_in'");
        if (!$columns->num_rows) {
            $this->db->query("ALTER TABLE `" . DB_PREFIX . "cryptapi_order` ADD COLUMN `address_in` VARCHAR(255) NOT NULL DEFAULT ''");

            $rows = $this->db->query("SELECT `order_id`, `response` FROM `" . DB_PREFIX . "cryptapi_order`");
            foreach ($rows->rows as $row) {
                $meta = json_decode($row['response'], true);
                $address = $meta['cryptapi_address'] ?? '';
                if ($address !== '') {
                    $this->db->query("UPDATE `" . DB_PREFIX . "cryptapi_order` SET `address_in` = '" . $this->db->escape($address) . "' WHERE `order_id` = " . (int)$row['order_id']);
                }
            }
        }

        $keys = $this->db->query("SHOW KEYS FROM `" . DB_PREFIX . "cryptapi_order` WHERE Key_name = 'PRIMARY'");
        if (!$keys->num_rows) {
            $this->db->query("ALTER TABLE `" . DB_PREFIX . "cryptapi_order` ADD PRIMARY KEY (`order_id`)");
        }

        $unique = $this->db->query("SHOW KEYS FROM `" . DB_PREFIX . "cryptapi_order` WHERE Key_name = 'idx_address_in'");
        if (!$unique->num_rows) {
            $empties = $this->db->query("SELECT COUNT(*) AS n FROM `" . DB_PREFIX . "cryptapi_order` WHERE `address_in` = ''");
            if ((int)$empties->row['n'] <= 1) {
                $this->db->query("ALTER TABLE `" . DB_PREFIX . "cryptapi_order` ADD UNIQUE KEY `idx_address_in` (`address_in`)");
            }
        }
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