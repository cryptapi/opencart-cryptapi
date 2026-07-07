<?php
namespace Opencart\Catalog\Model\Extension\CryptAPI\Payment;
class CryptAPI extends \Opencart\System\Engine\Model
{
    public function getMethods(array $address = []): array
    {
        $this->load->language('extension/cryptapi/payment/cryptapi');

        if (!$this->config->get('payment_cryptapi_status')) {
            return [];
        }

        $geo_zone_id = (int)$this->config->get('payment_cryptapi_standard_geo_zone_id');
        if ($geo_zone_id > 0) {
            $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone_to_geo_zone` WHERE `geo_zone_id` = '" . $geo_zone_id . "' AND `country_id` = '" . (int)($address['country_id'] ?? 0) . "' AND (`zone_id` = '" . (int)($address['zone_id'] ?? 0) . "' OR `zone_id` = '0')");
            if (!$query->num_rows) {
                return [];
            }
        }

        if (!$this->validateCurrencies()) {
            return [];
        }

        $name = $this->config->get('payment_cryptapi_title') ?: $this->language->get('heading_title');

        return [
            'code'       => 'cryptapi',
            'name'       => $name,
            'option'     => [
                'cryptapi' => [
                    'code' => 'cryptapi.cryptapi',
                    'name' => $name,
                ],
            ],
            'sort_order' => $this->config->get('payment_cryptapi_sort_order'),
        ];
    }

    public function validateCurrencies()
    {
        $status = false;

        $cryptocurrencies = array();

        foreach ($this->config->get('payment_cryptapi_cryptocurrencies') as $selected) {
            foreach (json_decode(html_entity_decode($this->config->get('payment_cryptapi_cryptocurrencies_array_cache'), ENT_QUOTES | ENT_HTML5, 'UTF-8'), true) as $token => $coin) {
                if ($selected === $token) {
                    $cryptocurrencies += [
                        $token => $coin,
                    ];
                }
            }
        }

        if (count($cryptocurrencies) > 0) {
            foreach ($cryptocurrencies as $token => $coin) {
                if ($coin) {
                    if(!empty($this->config->get('payment_cryptapi_cryptocurrencies_address_' . $token) || !empty($this->config->get('payment_cryptapi_api_key')))) {
                        $status = true;
                        break;
                    }
                }
            }
        }
        return $status;
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

    public function getOrders()
    {
        // OC 4.x: `payment_method` is JSON-encoded; filter on the serialized
        // form (PHP's json_encode never inserts spaces, so the match is exact).
        // `age_seconds` is computed server-side so cron timeout math is TZ-safe.
        $qry = $this->db->query("SELECT *, (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(`date_added`)) AS `age_seconds`
            FROM `" . DB_PREFIX . "order`
            WHERE `payment_method` LIKE '%\"code\":\"cryptapi.cryptapi\"%' AND `order_status_id` = 1");

        if ($qry->num_rows) {
            return $qry->rows;
        } else {
            return false;
        }
    }

    public function getPaymentData($order_id)
    {

        $qry = $this->db->query('select response FROM ' . DB_PREFIX . 'cryptapi_order WHERE order_id=' . (int)($order_id));
        if ($qry->num_rows) {
            $row = $qry->row;
            return $row['response'];
        } else {
            return false;
        }
    }

    public function addPaymentData($order_id, $response)
    {
        $meta = json_decode($response, true);
        $address_in = $meta['cryptapi_address'] ?? '';

        $this->db->query("INSERT INTO `" . DB_PREFIX . "cryptapi_order` SET
            `order_id` = '" . (int)$order_id . "',
            `address_in` = '" . $this->db->escape($address_in) . "',
            `response` = '" . $this->db->escape($response) . "'
            ON DUPLICATE KEY UPDATE
            `address_in` = VALUES(`address_in`),
            `response` = VALUES(`response`)");
    }

    public function getOrderByAddress($address_in): array
    {
        if ($address_in === '') {
            return [];
        }
        $qry = $this->db->query("SELECT * FROM `" . DB_PREFIX . "cryptapi_order` WHERE `address_in` = '" . $this->db->escape($address_in) . "' LIMIT 1");
        return $qry->num_rows ? $qry->row : [];
    }

    // Atomic read-modify-write under an InnoDB row lock.
    public function updatePaymentData($order_id, $param, $value): void
    {
        $order_id = (int)$order_id;
        try {
            $this->db->query("START TRANSACTION");
            $row = $this->db->query("SELECT `response` FROM `" . DB_PREFIX . "cryptapi_order` WHERE `order_id` = '" . $order_id . "' FOR UPDATE");
            if ($row->num_rows) {
                $metaData = json_decode($row->row['response'], true);
                if (!is_array($metaData)) { $metaData = []; }
                $metaData[$param] = $value;
                $this->db->query("UPDATE `" . DB_PREFIX . "cryptapi_order` SET `response` = '" . $this->db->escape(json_encode($metaData)) . "' WHERE `order_id` = '" . $order_id . "'");
            }
            $this->db->query("COMMIT");
        } catch (\Throwable $e) {
            $this->db->query("ROLLBACK");
        }
    }

    // Atomically merge one payment (keyed by $uuid) into cryptapi_history.
    public function addHistoryEntry($order_id, string $uuid, array $entry): void
    {
        if ($uuid === '') { return; }
        $order_id = (int)$order_id;
        try {
            $this->db->query("START TRANSACTION");
            $row = $this->db->query("SELECT `response` FROM `" . DB_PREFIX . "cryptapi_order` WHERE `order_id` = '" . $order_id . "' FOR UPDATE");
            if ($row->num_rows) {
                $metaData = json_decode($row->row['response'], true);
                if (!is_array($metaData)) { $metaData = []; }
                $history = json_decode($metaData['cryptapi_history'] ?? '[]', true);
                if (!is_array($history)) { $history = []; }
                if (empty($history[$uuid])) {
                    $history[$uuid] = $entry;                                   // value_paid locked
                } else {
                    $history[$uuid]['pending'] = $entry['pending'] ?? ($history[$uuid]['pending'] ?? 1);
                }
                $metaData['cryptapi_history'] = json_encode($history);
                $this->db->query("UPDATE `" . DB_PREFIX . "cryptapi_order` SET `response` = '" . $this->db->escape(json_encode($metaData)) . "' WHERE `order_id` = '" . $order_id . "'");
            }
            $this->db->query("COMMIT");
        } catch (\Throwable $e) {
            $this->db->query("ROLLBACK");
        }
    }

    // Single-statement atomic compare-and-set of cryptapi_paid; true exactly once.
    public function claimPaidTransition($order_id): bool
    {
        $order_id = (int)$order_id;
        // Requires MariaDB 10.2+/MySQL 5.7+ (JSON funcs); target ships MariaDB 10.6.
        $this->db->query(
            "UPDATE `" . DB_PREFIX . "cryptapi_order`
                SET `response` = JSON_SET(`response`, '$.cryptapi_paid', '1')
              WHERE `order_id` = '" . $order_id . "'
                AND JSON_VALID(`response`)
                AND COALESCE(JSON_UNQUOTE(JSON_EXTRACT(`response`, '$.cryptapi_paid')), '0') <> '1'"
        );
        return $this->db->countAffected() === 1;
    }

    public function deletePaymentData($order_id, $param)
    {
        $metaData = $this->getPaymentData($order_id);
        if (!empty($metaData)) {
            $metaData = json_decode($metaData, true);
            if (isset($metaData[$param])) {
                unset($metaData[$param]);
            }
            $paymentData = json_encode($metaData);

            $this->db->query("UPDATE " . DB_PREFIX . "cryptapi_order SET response = '" . $this->db->escape($paymentData) . "' WHERE order_id = '" . (int)$order_id . "'");
        }
    }
}
