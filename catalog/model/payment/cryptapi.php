<?php
namespace Opencart\Catalog\Model\Extension\CryptAPI\Payment;
class CryptAPI extends \Opencart\System\Engine\Model
{
    public function getMethod(array $address): array
    {
        $this->load->language('extension/cryptapi/payment/cryptapi');

        if ($this->config->get('payment_cryptapi_status')) {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('payment_eway_standard_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");
            if (!$this->config->get('payment_cryptapi_standard_geo_zone_id')) {
                $status = true;
            } elseif ($query->num_rows) {
                $status = true;
            } else {
                $status = false;
            }
        } else {
            $status = false;
        }

        if ($status) {
            $status = $this->validateCurrencies();
        }

        $method_data = array();

        if ($status) {
            $method_data = array(
                'code' => 'cryptapi',
                'title' => $this->config->get('payment_cryptapi_title'),
                'terms' => 'cryptocurrency',
                'sort_order' => $this->config->get('payment_cryptapi_sort_order')
            );
        }

        return $method_data;
    }

    public function validateCurrencies()
    {
        $status = false;

        $cryptocurrencies = array();

        foreach ($this->config->get('payment_cryptapi_cryptocurrencies') as $selected) {
            foreach (json_decode(str_replace("&quot;", '"', $this->config->get('payment_cryptapi_cryptocurrencies_array_cache')), true) as $token => $coin) {
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

    public function generateNonce($len = 32)
    {
        $data = str_split('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789');

        $nonce = [];
        for ($i = 0; $i < $len; $i++) {
            $nonce[] = $data[mt_rand(0, sizeof($data) - 1)];
        }

        return implode('', $nonce);
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
        $qry = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order` INNER JOIN `" . DB_PREFIX . "order_history` WHERE `" . DB_PREFIX . "order`.order_id=`" . DB_PREFIX . "order_history`.order_id AND `" . DB_PREFIX . "order`.payment_code ='cryptapi' AND `" . DB_PREFIX . "order`.order_status_id=1");

        if ($qry->num_rows) {
            return $orders = $qry->rows;
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
        $this->db->query("INSERT INTO `" . DB_PREFIX . "cryptapi_order` SET `order_id` = '" . (int)$order_id . "',  `response` = '" . $this->db->escape($response) . "'");
    }

    public function updatePaymentData($order_id, $param, $value)
    {
        $metaData = $this->getPaymentData($order_id);
        if (!empty($metaData)) {
            $metaData = json_decode($metaData, true);
            $metaData[$param] = $value;
            $paymentData = json_encode($metaData);
            $this->db->query("UPDATE " . DB_PREFIX . "cryptapi_order SET response = '" . $this->db->escape($paymentData) . "' WHERE order_id = '" . (int)$order_id . "'");
        }
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
