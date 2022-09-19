<?php

$base_url = sprintf(
    "%s://%s",
    $_SERVER['HTTPS'] ? 'https' : 'http',
    $_SERVER['SERVER_NAME']
);

// Text
$_['text_title'] = 'Cryptocurrency' . ' <img src="' . $base_url . 'image/catalog/cryptapi/payment.png" alt="cryptapi" style="height:23px" />';
$_['text_legend'] = 'Pay with cryptocurrency';
$_['text_basket'] = 'Basket';
$_['text_checkout'] = 'Checkout';
$_['text_success'] = 'Success';
$_['text_shipping'] = 'Shipping';
$_['text_copy'] = 'COPY';
$_['text_copied'] = 'COPIED!';
$_['text_processing'] = 'Processing...';
$_['text_paywith'] = 'Pay with';
$_['text_notice'] = 'Notice';
$_['text_moment'] = 'a moment';

$_['button_pay'] = 'Pay now';

$_['branding_logo'] = '<img src="' . $base_url . 'image/catalog/cryptapi/payment_success.png" alt="cryptapi" style="width:122px;" />';
$_['wallet_text'] = 'WALLET';
$_['qr_code_text_open'] = 'Open QR CODE';
$_['qr_code_text_close'] = 'Close QR CODE';

$_['qrcode_show_text'] = 'Show the QR code';
$_['qrcode_button_noamount_text'] = 'ADDRESS';
$_['qrcode_button_amount_text'] = 'WITH AMMOUNT';
$_['qrcode_alt_value_text'] = 'QR Code with value';
$_['qrcode_alt_text'] = 'QR Code without value';
$_['qrcode_alt_show_text'] = 'Show QR Code without value';
$_['qrcode_alt_show_value_text'] = 'Show QR Code with value';

$_['payment_notification'] = 'So far you sent %1s. Please send a new payment to complete the order, as requested above';
$_['notification_remaining'] = 'For technical reasons, the minimum amount for each transaction is %1s, so we adjusted the value by adding the remaining to it.';

$_['please_send_text'] = 'PLEASE SEND';
$_['payment_confirmed_text'] = 'Your payment has been confirmed!';
$_['payment_order_cancelled'] = 'Order cancelled due to lack of payment.';
$_['waiting_payment_text'] = 'Waiting for payment';
$_['waiting_network_confirmation_text'] = 'Waiting for network confirmation';
$_['payment_confirmed_simple_text'] = 'Payment confirmed';
$_['payment_being_processed'] = 'Your payment is being processed';
$_['processing_blockchain'] = 'Processing can take some time depending on the blockchain.';
$_['order_cancelled_minute'] = 'Order will be cancelled in less than a minute.';
$_['order_value_refresh'] = 'The %1s conversion rate will be adjusted in';
$_['order_valid_time'] = 'This order will be valid for <strong><span class="ca_cancel_timer" data-timestamp="%1s">%2s</span></strong>';

$_['table_time'] = 'TIME';
$_['table_value_paid'] = 'VALUE PAID';
$_['table_fiat_value'] = 'FIAT VALUE';

$_['order_subject'] = 'New Order %1$s. Please send a %2$s payment';
$_['order_greeting'] = 'Hello. Order %1$s was created successfully and a payment address was generated for the cryptocurrency %2$s.';
$_['order_send_payment'] = 'To complete your order or check payment status, use the button provided bellow:';