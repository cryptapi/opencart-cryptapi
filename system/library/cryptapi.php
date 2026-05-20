<?php
namespace Opencart\Extension\CryptAPI\System\Library;
class CryptAPIHelper
{
    private static $base_url = "https://api.cryptapi.io";
    private static $pro_url = "https://api.blockbee.io";
    private $own_address = null;
    private $payment_address = null;
    private $callback_url = null;
    private $coin = null;
    private $pending = false;
    private $parameters = [];
    private $api_key = null;

    public function __construct($coin, $own_address, $api_key, $callback_url, $parameters = [], $pending = false)
    {
        $this->own_address = $own_address;
        $this->callback_url = $callback_url;
        $this->api_key = $api_key;
        $this->coin = $coin;
        $this->pending = $pending ? 1 : 0;
        $this->parameters = $parameters;
    }

    public function get_address()
    {

        if (empty($this->coin) || empty($this->callback_url)) {
            return null;
        }

        $api_key = $this->api_key;

        if (empty($api_key) && empty($this->own_address)) {
            return null;
        }

        $callback_url = $this->callback_url;
        if (!empty($this->parameters)) {
            $req_parameters = http_build_query($this->parameters);
            $callback_url = "{$this->callback_url}?{$req_parameters}";
        }

        if (!empty($api_key) && empty($this->own_address)) {
            $ca_params = [
                'apikey' => $api_key,
                'callback' => $callback_url,
                'pending' => $this->pending,
            ];
        } elseif (empty($api_key) && !empty($this->own_address)) {
            $ca_params = [
                'callback' => $callback_url,
                'address' => $this->own_address,
                'pending' => $this->pending,
            ];
        } elseif (!empty($api_key) && !empty($this->own_address)) {
            $ca_params = [
                'apikey' => $api_key,
                'callback' => $callback_url,
                'address' => $this->own_address,
                'pending' => $this->pending,
            ];
        }

        $response = CryptAPIHelper::_request($this->coin, 'create', $ca_params);

        if (is_object($response) && ($response->status ?? '') === 'success') {
            $this->payment_address = $response->address_in;

            return $response->address_in;
        }

        return null;
    }

    public function checklogs()
    {

        if (empty($this->coin) || empty($this->callback_url)) return null;

        $params = [
            'callback' => $this->callback_url,
        ];

        $response = CryptAPIHelper::_request($this->coin, 'logs', $params);

        if (is_object($response) && ($response->status ?? '') === 'success') {
            return $response;
        }

        return null;
    }

    public function get_qrcode($value, $size)
    {
        if (empty($this->coin)) return null;

        if (empty($value)) {
            $params = [
                'address' => $this->payment_address,
                'size' => $size,
            ];
        } else {
            $params = [
                'address' => $this->payment_address,
                'value' => $value,
                'size' => $size,
            ];
        }

        $response = CryptAPIHelper::_request($this->coin, 'qrcode', $params);

        if (is_object($response) && ($response->status ?? '') === 'success') {
            return ['qr_code' => $response->qr_code, 'uri' => $response->payment_uri];
        }

        return null;
    }

    public static function get_static_qrcode($address, $coin, $value, $size = 300)
    {
        if (empty($address)) {
            return null;
        }

        if (!empty($value)) {
            $params = [
                'address' => $address,
                'value' => $value,
                'size' => $size,
            ];
        } else {
            $params = [
                'address' => $address,
                'size' => $size,
            ];
        }

        $response = CryptAPIHelper::_request($coin, 'qrcode', $params);

        if (is_object($response) && ($response->status ?? '') === 'success') {
            return ['qr_code' => $response->qr_code, 'uri' => $response->payment_uri];
        }

        return null;
    }

    public static function get_supported_coins()
    {
        $info = CryptAPIHelper::get_info(null, true);

        if (empty($info)) {
            return null;
        }

        unset($info['fee_tiers']);

        $coins = [];

        foreach ($info as $chain => $data) {
            $is_base_coin = in_array('ticker', array_keys($data));
            if ($is_base_coin) {
                $coins[$chain] = [
                    'name' => $data['coin'],
                    'logo' => $data['logo'],
                ];
                continue;
            }

            $base_ticker = "{$chain}_";
            foreach ($data as $token => $subdata) {
                $chain_upper = strtoupper($chain);
                $coins[$base_ticker . $token] = [
                    'name' => "{$subdata['coin']} ({$chain_upper})",
                    'logo' => $subdata['logo']
                ];
            }
        }

        return $coins;
    }


    public static function get_info($coin = null, $assoc = false)
    {
        $params = [];

        if (empty($coin)) {
            $params['prices'] = '0';
        }

        $response = CryptAPIHelper::_request($coin, 'info', $params, $assoc);

        if (empty($response)) {
            return null;
        }

        if (empty($coin)) {
            return $response;
        }

        $status = is_object($response) ? ($response->status ?? '') : ($response['status'] ?? '');
        return $status === 'success' ? $response : null;
    }

    /**
     * Fetches the public key from either api.cryptapi.io (non-pro) or
     * api.blockbee.io (pro). Returns the PEM-encoded key or null.
     */
    public static function fetch_pubkey(bool $pro = false): ?string
    {
        $base = $pro ? CryptAPIHelper::$pro_url : CryptAPIHelper::$base_url;
        $response = CryptAPIHelper::_raw_request($base . '/pubkey/');

        if (!is_object($response) || ($response->status ?? '') !== 'success' || empty($response->pubkey)) {
            return null;
        }

        return $response->pubkey;
    }

    public static function verify_signature(string $signed_payload, string $signature_b64, string $pubkey_pem): bool
    {
        if ($signed_payload === '' || $signature_b64 === '' || $pubkey_pem === '') {
            return false;
        }

        $signature = base64_decode($signature_b64, true);
        if ($signature === false) {
            return false;
        }

        return openssl_verify($signed_payload, $signature, $pubkey_pem, OPENSSL_ALGO_SHA256) === 1;
    }

    public static function process_callback($_get)
    {
        // Keep both `value`/`value_forwarded` (CryptAPI's older naming) and
        // `value_coin`/`value_forwarded_coin` (newer naming used by the pro
        // API) — the contract differs between api.cryptapi.io and api.blockbee.io.
        $params = [
            'uuid' => $_get['uuid'] ?? null,
            'address_in' => $_get['address_in'] ?? null,
            'address_out' => $_get['address_out'] ?? null,
            'txid_in' => $_get['txid_in'] ?? null,
            'txid_out' => $_get['txid_out'] ?? null,
            'confirmations' => $_get['confirmations'] ?? null,
            'value' => $_get['value'] ?? null,
            'value_coin' => $_get['value_coin'] ?? null,
            'value_forwarded' => $_get['value_forwarded'] ?? null,
            'value_forwarded_coin' => $_get['value_forwarded_coin'] ?? null,
            'coin' => $_get['coin'] ?? null,
            'pending' => $_get['pending'] ?? 1,
        ];

        foreach ($_get as $k => $v) {
            if (isset($params[$k])) continue;
            $params[$k] = $_get[$k];
        }

        foreach ($params as &$val) {
            $val = is_string($val) ? trim($val) : $val;
        }

        return $params;
    }

    public static function get_conversion($from, $to, $value, $disable_conversion)
    {

        if ($disable_conversion) {
            return $value;
        }

        $params = [
            'from' => $from,
            'to' => $to,
            'value' => $value,
        ];

        $response = CryptAPIHelper::_request('', 'convert', $params);

        if (is_object($response) && ($response->status ?? '') === 'success') {
            return $response->value_coin;
        }

        return null;
    }

    public static function get_estimate($coin)
    {

        $params = [
            'addresses' => 1,
            'priority' => 'default',
        ];

        $response = CryptAPIHelper::_request($coin, 'estimate', $params);

        if (is_object($response) && ($response->status ?? '') === 'success') {

            return $response->estimated_cost_currency;
        }

        return null;
    }


    public static function sig_fig($value, $digits)
    {
        $value = (string) $value;
        if (strpos($value, '.') !== false) {
            if ($value[0] != '-') {
                return bcadd($value, '0.' . str_repeat('0', $digits) . '5', $digits);
            }

            return bcsub($value, '0.' . str_repeat('0', $digits) . '5', $digits);
        }

        return $value;
    }

    public static function calc_order($history, $total, $total_fiat): array
    {
        $already_paid = 0;
        $already_paid_fiat = 0;
        $remaining = $total;
        $remaining_pending = $total;
        $remaining_fiat = $total_fiat;

        if (!empty($history)) {
            foreach ($history as $uuid => $item) {
                if ((int)$item['pending'] === 0) {
                    $remaining = bcsub(CryptAPIHelper::sig_fig($remaining, 6), $item['value_paid'], 8);
                }

                $remaining_pending = bcsub(CryptAPIHelper::sig_fig($remaining_pending, 6), $item['value_paid'], 8);
                $remaining_fiat = bcsub(CryptAPIHelper::sig_fig($remaining_fiat, 6), $item['value_paid_fiat'], 8);

                $already_paid = bcadd(CryptAPIHelper::sig_fig($already_paid, 6), $item['value_paid'], 8);
                $already_paid_fiat = bcadd(CryptAPIHelper::sig_fig($already_paid_fiat, 6), $item['value_paid_fiat'], 8);
            }
        }

        return [
            'already_paid' => floatval($already_paid),
            'already_paid_fiat' => floatval($already_paid_fiat),
            'remaining' => floatval($remaining),
            'remaining_pending' => floatval($remaining_pending),
            'remaining_fiat' => floatval($remaining_fiat)
        ];
    }

    private static function _request($coin, $endpoint, $params = [], $assoc = false)
    {
        $base_url = CryptAPIHelper::$base_url;

        if (!empty($params['apikey']) && $endpoint !== 'info') {
            $base_url = CryptAPIHelper::$pro_url;
        }

        if (!empty($coin)) {
            $coin = str_replace('_', '/', $coin);
            $url = "{$base_url}/{$coin}/{$endpoint}/";
        } else {
            $url = "{$base_url}/{$endpoint}/";
        }

        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return CryptAPIHelper::_raw_request($url, $assoc);
    }

    private static function _raw_request(string $url, bool $assoc = false)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($curl);
        curl_close($curl);

        if ($response === false || $response === '') {
            return null;
        }

        return json_decode($response, $assoc);
    }
}