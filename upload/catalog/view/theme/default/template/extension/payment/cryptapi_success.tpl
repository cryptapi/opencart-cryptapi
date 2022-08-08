<?php echo $header; ?>
    <script>
        let ajax_url = '<?php echo $ajax_url;?>';
        check_status(ajax_url);
    </script>
    <div class="ca_payment-panel
<?php if ($color_scheme == 'auto'): ?>
 auto
<?php elseif ($color_scheme == 'light'): ?>
 light
<?php else: ?>
 dark
<?php endif; ?>">
        <div class="ca_payment_details">
            <div class="ca_payments_wrapper">
                <div class="ca_qrcode_wrapper"
                     style="<?php if (!$qrcode_default): ?>display: none;<?php endif; ?>; width: <?php echo $qr_code_size + 20; ?>px;">
                    <?php if ($crypto_allowed_value == true): ?>
                        <div class="inner-wrapper">
                            <figure>
                                <?php if ($qr_code_setting !== 'hide_without_ammount'): ?>
                                    <img class="ca_qrcode no_value"
                                        <?php if ($qr_code_setting == 'ammount'): ?>
                                            style="display:none;"
                                        <?php endif; ?>
                                         src="data:image/png;base64,<?php echo $qr_code; ?>" alt="<?php echo $qrcode_alt_text; ?>"/>
                                <?php endif ?>
                                <?php if ($qr_code_setting !== 'hide_ammount'): ?>
                                    <img class="ca_qrcode value"
                                        <?php if ($qr_code_setting == 'without_ammount'): ?>
                                            style="display:none;"
                                        <?php endif; ?> src="data:image/png;base64,<?php echo $qr_code_value; ?>"
                                         alt="<?php echo $qrcode_alt_value_text; ?>"/>
                                <?php endif; ?>
                            </figure>
                            <div class="ca_qrcode_buttons">
                                <?php if ($qr_code_setting != 'hide_without_ammount'): ?>
                                    <button class="ca_qrcode_btn no_value
                        <?php if ($qr_code_setting == 'without_ammount' || $qr_code_setting == 'hide_ammount'): ?>
                         active
                         <?php endif ?>" aria-label="<?php echo $qrcode_alt_show_value; ?>">
                                        <?php echo $qrcode_button_noamount_text; ?>
                                    </button>
                                <?php endif; ?>
                                <?php if ($qr_code_setting !== 'hide_ammount'): ?>
                                    <button class="ca_qrcode_btn value
                        <?php if ($qr_code_setting == 'ammount' || $qr_code_setting == 'hide_without_ammount'): ?>
                         active
                         <?php endif; ?>" aria-label="<?php echo $qrcode_alt_show_value; ?>">
                                        <?php echo $qrcode_button_amount_text; ?>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="inner-wrapper">
                            <figure>
                                <img class="ca_qrcode no_value" src="data:image/png;base64,<?php echo $qr_code_value; ?>"
                                     alt="<?php echo $qrcode_alt_text; ?>"/>
                            </figure>
                            <div class="ca_qrcode_buttons">
                                <button class="ca_qrcode_btn no_value active" aria-label="<?php echo $qrcode_alt_show_value; ?>">
                                    <?php echo $qrcode_button_noamount_text; ?>
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="ca_details_box">
                    <div class="ca_details_text">
                        <?php echo $please_send_text; ?>
                        <button class="ca_copy ca_details_copy" data-tocopy="<?php echo $crypto_value; ?>">
                            <span><b class="ca_value"><?php echo $crypto_value; ?></b></span>
                            <span><b><?php echo strtoupper($crypto_coin); ?></b></span>
                            <span class="ca_tooltip ca_copy_icon_tooltip tip"><?php echo $text_copy; ?></span>
                            <span class="ca_tooltip ca_copy_icon_tooltip success" style="display: none"><?php echo $text_copied; ?></span>
                        </button>
                        (<?php echo $currency_symbol_left; ?><?php echo $total; ?><?php echo $currency_symbol_right; ?>)
                    </div>
                    <div class="ca_payment_notification ca_notification_payment_received" style="display: none;">
                        <?php echo sprintf($payment_notification, '<strong><span class="ca_notification_ammount"></span></strong>'); ?>

                    </div>
                    <div class="ca_payment_notification ca_notification_remaining" style="display: none">
                        <?php echo sprintf($notification_remaining, $min_tx . strtoupper($crypto_coin)); ?>
                    </div>
                    <?php if ($refresh_value_interval !== '0'): ?>
                        <div class="ca_time_refresh">
                            <?php echo sprintf($order_value_refresh, strtoupper($crypto_coin)); ?>
                            <span class="ca_time_seconds_count" data-soon="<?php echo $text_moment; ?>"
                                  data-seconds="<?php echo $conversion_timer; ?>"><?php echo date('i:s', $conversion_timer); ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="ca_details_input">
                        <span><?php echo $address_in; ?></span>
                        <button class="ca_copy ca_copy_icon" data-tocopy="<?php echo $address_in; ?>">
                            <span class="ca_tooltip ca_copy_icon_tooltip tip"><?php echo $text_copy; ?></span>
                            <span class="ca_tooltip ca_copy_icon_tooltip success" style="display: none"><?php echo $text_copied; ?></span>
                        </button>
                        <div class="ca_loader"></div>
                    </div>
                </div>
                <?php if ($order_cancelation_timeout !== '0'): ?>
                    <span class="ca_notification_cancel" data-text="<?php $order_cancelled_minute; ?>">
                                <?php echo sprintf($order_valid_time, $cancel_timer, date('H:i', $cancel_timer)); ?>
             </span>
                <?php endif; ?>
                <div class="ca_buttons_container">
                    <a class="ca_show_qr" href="#" aria-label="<?php $qrcode_show; ?>">
                                <span class="ca_show_qr_open
                                <?php if (!$qrcode_default): ?>
                                    active
                                <?php endif; ?>"><?php echo $qr_code_text_open; ?>
                                </span>
                        <span class="ca_show_qr_close
                    <?php if ($qrcode_default): ?>
                    active
                    <?php endif; ?>"><?php echo $qr_code_text_close; ?></span>
                    </a>
                </div>
                <?php if ($show_branding): ?>
                    <div class="ca_branding">
                        <a href="https://cryptapi.io/" target="_blank">
                            <span>Powered by</span>
                            <?php echo $branding_logo; ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            <div class="ca_payment_processing" style="display: none;">
                <div class="ca_payment_processing_icon">
                    <div class="ca_loader_payment_processing"></div>
                </div>
                <h2><?php echo $payment_being_processed; ?></h2>
                <h5><?php echo $processing_blockchain; ?></h5>
            </div>
            <div class="ca_payment_confirmed" style="display: none;">
                <div class="ca_payment_confirmed_icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                        <path fill="#66BB6A"
                              d="M504 256c0 136.967-111.033 248-248 248S8 392.967 8 256 119.033 8 256 8s248 111.033 248 248zM227.314 387.314l184-184c6.248-6.248 6.248-16.379 0-22.627l-22.627-22.627c-6.248-6.249-16.379-6.249-22.628 0L216 308.118l-70.059-70.059c-6.248-6.248-16.379-6.248-22.628 0l-22.627 22.627c-6.248 6.248-6.248 16.379 0 22.627l104 104c6.249 6.249 16.379 6.249 22.628.001z"></path>
                    </svg>
                </div>
                <h2><?php echo $payment_confirmed_text; ?></h2>
            </div>
            <div class="ca_payment_cancelled" style="display: none;">
                <div class="ca_payment_cancelled_icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                        <path fill="#c62828"
                              d="M504 256c0 136.997-111.043 248-248 248S8 392.997 8 256C8 119.083 119.043 8 256 8s248 111.083 248 248zm-248 50c-25.405 0-46 20.595-46 46s20.595 46 46 46 46-20.595 46-46-20.595-46-46-46zm-43.673-165.346l7.418 136c.347 6.364 5.609 11.346 11.982 11.346h48.546c6.373 0 11.635-4.982 11.982-11.346l7.418-136c.375-6.874-5.098-12.654-11.982-12.654h-63.383c-6.884 0-12.356 5.78-11.981 12.654z"></path>
                    </svg>
                </div>
                <h2><?php echo $payment_order_cancelled; ?></h2>
            </div>
            <div class="ca_history" style="display: none;">
                <table class="ca_history_fill">
                    <tr class="ca_history_header">
                        <th><strong><?php echo $table_time; ?></strong></th>
                        <th><strong><?php echo $table_value_paid; ?></strong></th>
                        <th><strong><?php echo $table_fiat_value; ?></strong></th>
                    </tr>
                </table>
            </div>

            <div class="ca_progress">
                <div class="ca_progress_icon waiting_payment done">
                    <svg width="60" height="60" viewBox="0 0 50 50" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M49.2188 25C49.2188 38.3789 38.3789 49.2188 25 49.2188C11.6211 49.2188 0.78125 38.3789 0.78125 25C0.78125 11.6211 11.6211 0.78125 25 0.78125C38.3789 0.78125 49.2188 11.6211 49.2188 25ZM35.1953 22.1777L28.125 29.5508V11.7188C28.125 10.4199 27.0801 9.375 25.7812 9.375H24.2188C22.9199 9.375 21.875 10.4199 21.875 11.7188V29.5508L14.8047 22.1777C13.8965 21.2305 12.3828 21.2109 11.4551 22.1387L10.3906 23.2129C9.47266 24.1309 9.47266 25.6152 10.3906 26.5234L23.3398 39.4824C24.2578 40.4004 25.7422 40.4004 26.6504 39.4824L39.6094 26.5234C40.5273 25.6055 40.5273 24.1211 39.6094 23.2129L38.5449 22.1387C37.6172 21.2109 36.1035 21.2305 35.1953 22.1777V22.1777Z"
                              fill="#0B4B70"/>
                    </svg>
                    <p><?php echo $waiting_payment_text; ?></p>
                </div>
                <div class="ca_progress_icon waiting_network">
                    <svg width="60" height="60" viewBox="0 0 50 50" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M46.875 15.625H3.125C1.39912 15.625 0 14.2259 0 12.5V6.25C0 4.52412 1.39912 3.125 3.125 3.125H46.875C48.6009 3.125 50 4.52412 50 6.25V12.5C50 14.2259 48.6009 15.625 46.875 15.625ZM42.1875 7.03125C40.8931 7.03125 39.8438 8.08057 39.8438 9.375C39.8438 10.6694 40.8931 11.7188 42.1875 11.7188C43.4819 11.7188 44.5312 10.6694 44.5312 9.375C44.5312 8.08057 43.4819 7.03125 42.1875 7.03125ZM35.9375 7.03125C34.6431 7.03125 33.5938 8.08057 33.5938 9.375C33.5938 10.6694 34.6431 11.7188 35.9375 11.7188C37.2319 11.7188 38.2812 10.6694 38.2812 9.375C38.2812 8.08057 37.2319 7.03125 35.9375 7.03125ZM46.875 31.25H3.125C1.39912 31.25 0 29.8509 0 28.125V21.875C0 20.1491 1.39912 18.75 3.125 18.75H46.875C48.6009 18.75 50 20.1491 50 21.875V28.125C50 29.8509 48.6009 31.25 46.875 31.25ZM42.1875 22.6562C40.8931 22.6562 39.8438 23.7056 39.8438 25C39.8438 26.2944 40.8931 27.3438 42.1875 27.3438C43.4819 27.3438 44.5312 26.2944 44.5312 25C44.5312 23.7056 43.4819 22.6562 42.1875 22.6562ZM35.9375 22.6562C34.6431 22.6562 33.5938 23.7056 33.5938 25C33.5938 26.2944 34.6431 27.3438 35.9375 27.3438C37.2319 27.3438 38.2812 26.2944 38.2812 25C38.2812 23.7056 37.2319 22.6562 35.9375 22.6562ZM46.875 46.875H3.125C1.39912 46.875 0 45.4759 0 43.75V37.5C0 35.7741 1.39912 34.375 3.125 34.375H46.875C48.6009 34.375 50 35.7741 50 37.5V43.75C50 45.4759 48.6009 46.875 46.875 46.875ZM42.1875 38.2812C40.8931 38.2812 39.8438 39.3306 39.8438 40.625C39.8438 41.9194 40.8931 42.9688 42.1875 42.9688C43.4819 42.9688 44.5312 41.9194 44.5312 40.625C44.5312 39.3306 43.4819 38.2812 42.1875 38.2812ZM35.9375 38.2812C34.6431 38.2812 33.5938 39.3306 33.5938 40.625C33.5938 41.9194 34.6431 42.9688 35.9375 42.9688C37.2319 42.9688 38.2812 41.9194 38.2812 40.625C38.2812 39.3306 37.2319 38.2812 35.9375 38.2812Z"
                              fill="#0B4B70"/>
                    </svg>
                    <p><?php echo $waiting_network_confirmation_text; ?></p>
                </div>
                <div class="ca_progress_icon payment_done">
                    <svg width="60" height="60" viewBox="0 0 50 50" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M45.0391 12.5H7.8125C6.94922 12.5 6.25 11.8008 6.25 10.9375C6.25 10.0742 6.94922 9.375 7.8125 9.375H45.3125C46.1758 9.375 46.875 8.67578 46.875 7.8125C46.875 5.22363 44.7764 3.125 42.1875 3.125H6.25C2.79785 3.125 0 5.92285 0 9.375V40.625C0 44.0771 2.79785 46.875 6.25 46.875H45.0391C47.7754 46.875 50 44.7725 50 42.1875V17.1875C50 14.6025 47.7754 12.5 45.0391 12.5ZM40.625 32.8125C38.8994 32.8125 37.5 31.4131 37.5 29.6875C37.5 27.9619 38.8994 26.5625 40.625 26.5625C42.3506 26.5625 43.75 27.9619 43.75 29.6875C43.75 31.4131 42.3506 32.8125 40.625 32.8125Z"
                              fill="#0B4B70"/>
                    </svg>
                    <p><?php echo $payment_confirmed_simple_text; ?></p>
                </div>
            </div>
        </div>
    </div>
<?php echo $footer; ?>