<?php
/**
 * @package		CryptAPIHelper
 * @author		CryptAPI
 */

/**
 * CryptAPIHelper class
 */

class CryptAPIHelper
{
    private static $base_url = "https://api.cryptapi.io";
    private static $pro_url = "https://pro-api.cryptapi.io";
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
        } elseif(!empty($api_key) && !empty($this->own_address)) {
            $ca_params = [
                'apikey' => $api_key,
                'callback' => $callback_url,
                'address' => $this->own_address,
                'pending' => $this->pending,
            ];
        }

        $response = CryptAPIHelper::_request($this->coin, 'create', $ca_params);

        if (is_object($response) && isset($response->status) && $response->status === 'success') {
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

        if (is_object($response) && isset($response->status) && $response->status === 'success') {
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

        if (is_object($response) && isset($response->status) && $response->status === 'success') {
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

        if (is_object($response) && isset($response->status) && $response->status === 'success') {
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
                $coins[$chain] = $data['coin'];
                continue;
            }

            $base_ticker = "{$chain}_";
            foreach ($data as $token => $subdata) {
                $chain_upper = strtoupper($chain);

                $coins[$base_ticker . $token] = "{$subdata['coin']} ({$chain_upper})";
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

        if (empty($coin) || (is_object($response) && isset($response->status) && $response->status === 'success')) {
            return $response;
        }

        return null;
    }

    public static function process_callback($_get)
    {
        // Pending callbacks don't carry every field.
        $params = [];
        foreach (['uuid', 'address_in', 'address_out', 'txid_in', 'txid_out', 'confirmations', 'value', 'value_coin', 'value_forwarded', 'value_forwarded_coin', 'coin'] as $key) {
            $params[$key] = isset($_get[$key]) ? $_get[$key] : null;
        }
        $params['pending'] = isset($_get['pending']) ? $_get['pending'] : false;

        foreach ($_get as $k => $v) {
            if (isset($params[$k])) continue;
            $params[$k] = $_get[$k];
        }

        foreach ($params as &$val) {
            $val = is_string($val) ? trim($val) : null;
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

        if (is_object($response) && isset($response->status) && $response->status === 'success') {
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

        if (is_object($response) && isset($response->status) && $response->status === 'success') {

            return $response->estimated_cost_currency;
        }

        return null;
    }


    /*
     * Fetches CryptAPI's public key. The same key signs callbacks for addresses
     * made with or without an API key. Returns the PEM key, or null.
     */
    public static function fetch_pubkey()
    {
        $response = CryptAPIHelper::_request('', 'pubkey');

        if (is_object($response) && isset($response->status) && $response->status === 'success' && !empty($response->pubkey)) {
            return $response->pubkey;
        }

        return null;
    }

    public static function verify_signature($signed_payload, $signature_b64, $pubkey_pem)
    {
        if ((string)$signed_payload === '' || (string)$signature_b64 === '' || (string)$pubkey_pem === '') {
            return false;
        }

        $signature = base64_decode((string)$signature_b64, true);

        if ($signature === false) {
            return false;
        }

        return openssl_verify((string)$signed_payload, $signature, (string)$pubkey_pem, OPENSSL_ALGO_SHA256) === 1;
    }

    /*
     * CryptAPI signs the full URL it called. Rebuild its origin from a URL the
     * store made itself, never from the Host or X-Forwarded-* headers a caller sends.
     */
    public static function build_signed_url($callback_url, $request_uri)
    {
        $parts = parse_url((string)$callback_url);

        if ($parts === false || empty($parts['scheme']) || empty($parts['host'])) {
            return '';
        }

        $origin = $parts['scheme'] . '://' . $parts['host'];

        if (!empty($parts['port'])) {
            $origin .= ':' . $parts['port'];
        }

        return $origin . $request_uri;
    }

    // Constant-time compare; hash_equals() needs PHP 5.6.
    public static function safe_equals($known, $given)
    {
        $known = (string)$known;
        $given = (string)$given;

        if (function_exists('hash_equals')) {
            return hash_equals($known, $given);
        }

        if (strlen($known) !== strlen($given)) {
            return false;
        }

        $diff = 0;
        for ($i = 0; $i < strlen($known); $i++) {
            $diff |= ord($known[$i]) ^ ord($given[$i]);
        }

        return $diff === 0;
    }

    // A random secret for the callback URL; random_bytes() needs PHP 7.
    public static function random_token()
    {
        if (function_exists('random_bytes')) {
            return bin2hex(random_bytes(16));
        }

        return bin2hex(openssl_random_pseudo_bytes(16));
    }

    /*
     * True for a positive plain decimal. A failed API call returns null, and
     * earlier versions saved that as the order's total, so every total goes
     * through this.
     */
    public static function is_positive_number($value)
    {
        if (is_int($value) || is_float($value)) {
            $value = number_format($value, 8, '.', '');
        }

        if (!is_string($value) || !preg_match('/^[0-9]+(\.[0-9]+)?$/', trim($value))) {
            return false;
        }

        return bccomp(trim($value), '0', 8) > 0;
    }

    public static function sig_fig($value, $digits)
    {
        if (!is_numeric($value)) {
            // Was number_format(null), which read as 0.
            return '0';
        }

        if ($value == 0) {
            $decimalPlaces = $digits - 1;
        } elseif ($value < 0) {
            $decimalPlaces = $digits - floor(log10($value * -1)) - 1;
        } else {
            $decimalPlaces = $digits - floor(log10($value)) - 1;
        }

        $answer = ($decimalPlaces > 0) ?
            number_format($value, $decimalPlaces, '.', '') : round($value, $decimalPlaces);
        return $answer;
    }

    public static function calc_order($history, $total, $total_fiat)
    {
        // A missing or invalid total means nothing is payable yet. Never treat it
        // as 0, or any payment completes the order.
        if (!CryptAPIHelper::is_positive_number($total)) {
            return [
                'already_paid' => 0.0,
                'already_paid_fiat' => 0.0,
                'remaining' => 1.0,
                'remaining_pending' => 1.0,
                'remaining_fiat' => is_numeric($total_fiat) ? floatval($total_fiat) : 0.0,
            ];
        }

        $already_paid = 0;
        $already_paid_fiat = 0;
        $remaining = $total;
        $remaining_pending = $total;
        $remaining_fiat = $total_fiat;

        if (!empty($history)) {
            foreach ($history as $uuid => $item) {
                $value_paid = CryptAPIHelper::sig_fig(isset($item['value_paid']) ? $item['value_paid'] : '0', 8);
                $value_paid_fiat = CryptAPIHelper::sig_fig(isset($item['value_paid_fiat']) ? $item['value_paid_fiat'] : '0', 8);

                // A missing flag counts as confirmed, as it always has here.
                if ((int)(isset($item['pending']) ? $item['pending'] : 0) === 0) {
                    $remaining = bcsub(CryptAPIHelper::sig_fig($remaining, 6), $value_paid, 8);
                }

                $remaining_pending = bcsub(CryptAPIHelper::sig_fig($remaining_pending, 6), $value_paid, 8);
                $remaining_fiat = bcsub(CryptAPIHelper::sig_fig($remaining_fiat, 6), $value_paid_fiat, 8);

                $already_paid = bcadd(CryptAPIHelper::sig_fig($already_paid, 6), $value_paid, 8);
                $already_paid_fiat = bcadd(CryptAPIHelper::sig_fig($already_paid_fiat, 6), $value_paid_fiat, 8);
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

        if (!empty($params)) {
            $data = http_build_query($params);
        }

        if (!empty($coin)) {
            $coin = str_replace('_', '/', $coin);
            $url = "{$base_url}/{$coin}/{$endpoint}/";
        } else {
            $url = "{$base_url}/{$endpoint}/";
        }

        if (!empty($data)) {
            $url .= "?{$data}";
        }

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        // Check the certificate: the callback check trusts the key fetched here,
        // and addresses and prices come over this connection too.
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($curl, CURLOPT_TIMEOUT, 15);

        $response = curl_exec($curl);

        if (curl_error($curl) || !$response) {
            return null;
        }

        return json_decode($response, $assoc);
    }
}