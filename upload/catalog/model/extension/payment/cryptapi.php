<?php

class ModelExtensionPaymentCryptapi extends Model
{
    public function getMethod($address, $total)
    {
        $this->load->language('extension/payment/cryptapi');

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
                'title' => $this->language->get('text_title'),
                'terms' => '',
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
                if ($coin && !empty($this->config->get('payment_cryptapi_cryptocurrencies_address_' . $token))) {
                    $status = true;
                    break;
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

    public function roundSig($number, $sigdigs = 5)
    {
        $multiplier = 1;
        while ($number < 0.1) {
            $number *= 10;
            $multiplier /= 10;
        }
        while ($number >= 1) {
            $number /= 10;
            $multiplier *= 10;
        }
        return round($number, $sigdigs) * $multiplier;
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
