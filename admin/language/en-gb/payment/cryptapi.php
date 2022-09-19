<?php

$base_url = sprintf(
    "%s://%s",
    $_SERVER['HTTPS'] ? 'https' : 'http',
    $_SERVER['SERVER_NAME']
);

// Heading
$_['heading_title'] = 'CryptAPI';

$_['title'] = 'Title';

$_['blockchain_fees'] = 'Add the blockchain fee to the order';
$_['fees'] = 'Service fee manager fees';

$_['never'] = 'Never';

// Text
$_['text_extension'] = 'Extensions';
$_['text_success'] = 'Success: You have modified your CryptAPI details!';
$_['text_edit'] = 'Edit CryptAPI';
$_['text_cryptapi'] = '<a target="_BLANK" href="https://cryptapi.io/"><img src="' . $base_url . '/extension/cryptapi/admin/view/image/payment/cryptapi.png" alt="cryptapi" title="cryptapi" style="border: 1px solid #EEEEEE; height:37px" /></a>';
$_['text_connect_cryptapi'] = 'This module allows you to accept CryptAPI Payments securely.';
$_['text_cryptapi_image'] = '<a target="_BLANK" href="https://cryptapi.io/"><img src="' . $base_url . '/extension/cryptapi/admin/view/image/payment/cryptapi.png" alt="cryptapi" title="cryptapi" class="img-fluid" /></a>';
$_['text_cryptapi_suppport'] = 'If you need any help or have any suggestion, contact us via the live chat on our <a target="_blank" href="https://cryptapi.io">website</a>';
$_['text_blockchain_fees'] = 'This will add an estimation of the blockchain fee to the order value';
$_['text_fees'] = 'Set the CryptAPI service fee you want to charge the costumer. Note: Fee you want to charge your costumers (to cover CryptAPI\'s fees fully or partially)';
$_['text_qrcode'] = 'Select how you want to show the QR Code to the user. Either select a default to show first, or hide one of them.';
$_['text_btc'] = 'Bitcoin';
$_['text_refresh_values'] = 'The system will automatically update the conversion value of the invoices (with real-time data), every X minutes. This feature is helpful whenever a customer takes long time to pay a generated invoice and the selected crypto a volatile coin/token (not stable coin). Warning: Setting this setting to none might create conversion issues, as we advise you to keep it at 5 minutes.';
$_['text_order_cancelation_timeout'] = 'Selects the ammount of time the user has to pay for the order. When this time is over, order will be marked as \'Cancelled\' and every paid value will be ignored. Notice: If the user still sends money to the generated address, value will still be redirected to you. Warning: We do not advice more than 1 Hour.';

$_['text_tab_general'] = 'General';
$_['text_tab_crypto'] = 'Cryptocurrencies';
$_['text_tab_advanced'] = 'Advanced';

// Entry
$_['entry_cryptocurrencies'] = 'Accepted Cryptocurrencies';
$_['entry_btc_address'] = $_['text_btc'] . ' Address';

$_['entry_order_status'] = 'Order status';
$_['entry_status'] = 'Status';

$_['branding'] = 'Show CryptAPI logo and credits below the QR code';

$_['qrcode_default'] = 'Show QR Code';
$_['qrcode'] = 'QR Code to show';
$_['qrcode_size'] = 'QR Code size';
$_['qrcode_without_ammount'] = 'Default Without Ammount';
$_['qrcode_ammount'] = 'Default Ammount';
$_['qrcode_hide_ammount'] = 'Hide Ammount';
$_['qrcode_hide_without_ammount'] = 'Hide Without Ammount';

$_['color_scheme'] = 'Color Scheme';
$_['scheme_light'] = 'Light';
$_['scheme_dark'] = 'Dark';
$_['scheme_auto'] = 'Auto';

$_['refresh_values'] = 'Refresh converted value';
$_['five_minutes'] = 'Every 5 Minutes';
$_['ten_minutes'] = 'Every 10 Minutes';
$_['fifteen_minutes'] = 'Every 15 Minutes';
$_['thirty_minutes'] = 'Every 30 Minutes';
$_['forty_five_minutes'] = 'Every 45 Minutes';
$_['sixty_minutes'] = 'Every 60 Minutes';

$_['order_cancelation_timeout'] = 'Order cancelation timeout';
$_['one_hour'] = '1 Hour';
$_['six_hours'] = '6 Hours';
$_['twelve_hours'] = '12 Hours';
$_['eighteen_hours'] = '18 Hours';
$_['twenty_four_hours'] = '24 Hours';

$_['entry_geo_zone'] = 'Geo Zone';
$_['entry_sort_order'] = 'Sort order';

// Error
$_['error_permission'] = 'Warning: You do not have permission to modify the CryptAPI payment module';

// Help hints
$_['help_cryptocurrencies'] = 'If you are using BlockBee you can choose if setting the receiving addresses here bellow or in your BlockBee settings page.<br/>In order to set the addresses on plugin settings, you need to select “Address Override” while creating the API key.<br/>In order to set the addresses on BlockBee settings, you need to NOT select “Address Override” while creating the API key.';
$_['help_cryptocurrency'] = 'Click the checkbox to enable the cryptocurrency';


// Order page - payment tab
$_['text_payment_info'] = 'Payment information';

$_['disable_conversion'] = 'Disable Conversion';
$_['disable_conversion_warn_bold'] = 'Attention: This option will disable the price conversion for ALL cryptocurrencies!';
$_['disable_conversion_warn'] = 'If you check this, pricing will not be converted from the currency of your shop to the cryptocurrency selected by the user, and users will be requested to pay the same value as shown on your shop, regardless of the cryptocurrency selected';


$_['api_key'] = 'BlockBee API Key';
$_['api_key_info'] = "Insert here your BlockBee API Key. You can get one with BlockBee. Notice: If API permission 'Address Override' is not enabled you must set the address in the dashboard otherwise payments may fail.";

$_['info_icon'] = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="ms-1 bi bi-info-circle" viewBox="0 0 16 16">
                                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                        <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533L8.93 6.588zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
                                    </svg>';