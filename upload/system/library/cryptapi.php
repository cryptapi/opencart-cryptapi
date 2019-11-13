<?php
class CryptAPIHelper {
    private static $base_url = "https://cryptapi.io/api";
    private $valid_coins = ['btc', 'bch', 'eth', 'ltc', 'xmr', 'iota'];
    private $own_address = null;
    private $callback_url = null;
    private $coin = null;
    private $pending = false;
    private $parameters = [];

    public static $COIN_MULTIPLIERS = [
        'btc' => 100000000,
        'bch' => 100000000,
        'ltc' => 100000000,
        'eth' => 1000000000000000000,
        'iota' => 1000000,
        'xmr' => 1000000000000,
    ];

    public function __construct($coin, $own_address, $callback_url, $parameters=[], $pending=false) {

        if (!in_array($coin, $this->valid_coins)) {
            $vc = print_r($this->valid_coins, true);
            throw new Exception("Unsupported Coin: {$coin}, Valid options are: {$vc}");
        }

        $this->own_address = $own_address;
        $this->callback_url = $callback_url;
        $this->coin = $coin;
        $this->pending = $pending ? 1 : 0;
        $this->parameters = $parameters;

    }

    public function get_address() {

        if (empty($this->own_address) || empty($this->coin) || empty($this->callback_url)) return null;

        $callback_url = $this->callback_url;
        if (!empty($this->parameters)) {
            $req_parameters = http_build_query($this->parameters);
            $callback_url = "{$this->callback_url}?{$req_parameters}";
        }

        $ca_params = [
            'callback' => $callback_url,
            'address' => $this->own_address,
            'pending' => $this->pending,
        ];

        $response = CryptAPIHelper::_request($this->coin, 'create', $ca_params);

        if ($response->status == 'success') {
            return $response->address_in;
        }

        return null;
    }

    public function check_logs() {

        if (empty($this->coin) || empty($this->callback_url)) return null;

        $params = [
            'callback' => $this->callback_url,
        ];

        $response = CryptAPIHelper::_request($this->coin, 'logs', $params);

        if ($response->status == 'success') {
            return $response;
        }

        return null;
    }

    public static function get_info($coin) {
        $response = CryptAPIHelper::_request($coin, 'info');

        if ($response->status == 'success') {
            return $response;
        }

        return null;
    }

    public static function process_callback($_get, $convert=false) {
        $params = [
            'address_in' => $_get['address_in'],
            'address_out' => $_get['address_out'],
            'txid_in' => $_get['txid_in'],
            'txid_out' => isset($_get['txid_out']) ? $_get['txid_out'] : null,
            'confirmations' => $_get['confirmations'],
            'value' => $convert ? CryptAPIHelper::convert_div($_get['value'], $_get['coin']) : $_get['value'],
            'value_forwarded' => isset($_get['value_forwarded']) ? ($convert ? CryptAPIHelper::convert_div($_get['value_forwarded'], $_get['coin']) : $_get['value_forwarded']) : null,
            'coin' => $_get['coin'],
            'pending' => isset($_get['pending']) ? $_get['pending'] : false,
        ];
        
        foreach ($_get as $k => $v) {
            if (isset($params[$k])) continue;
            $params[$k] = $_get[$k];
        }

        foreach ($params as &$val) {
            $val = trim($val);
        }

        return $params;
    }

    public static function convert_div($val, $coin) {
        return $val / CryptAPIHelper::$COIN_MULTIPLIERS[$coin];
    }

    public static function convert_mul($val, $coin) {
        return $val * CryptAPIHelper::$COIN_MULTIPLIERS[$coin];
    }

    private static function _request($coin, $endpoint, $params=[]) {

        $base_url = CryptAPIHelper::$base_url;

        if (!empty($params)) $data = http_build_query($params);

        $url = "{$base_url}/{$coin}/{$endpoint}/";

        if (!empty($data)) {
            $url .= "?{$data}";
        }
        
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($curl);

        $json = array();

        if (curl_error($curl)) {
                $json['error'] = 'ERROR: ' . curl_errno($curl) . '::' . curl_error($curl);
              return $json;
        } elseif ($response) {
            return json_decode($response);
        }
    }
}