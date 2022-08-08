<?php echo $header; ?><?php echo $column_left; ?>
    <div id="content">
        <div class="page-header">
            <div class="container-fluid">
                <div class="pull-right">
                    <button type="submit" form="form-free-checkout" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
                    <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
                <h1><?php echo $heading_title; ?></h1>
                <ul class="breadcrumb">
                    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
                        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
                    <?php } ?>
                </ul>
            </div>
        </div>
        <div class="container-fluid">
            <?php if ($error_warning) { ?>
                <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i>
                    <?php echo $error_warning; ?>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            <?php } ?>
            <div class="alert alert-info" style="overflow: hidden;">
                <div class="row">
                    <?php echo $text_cryptapi_image; ?><br/>
                    <span style="margin-left:12px; line-height: 25px">
                    <?php echo $text_connect_cryptapi; ?>
                </span><br/>
                    <span style="margin-left:12px; line-height: 25px">
                    <?php echo $text_cryptapi_support; ?>
                </span>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="fa fa-pencil"></i>
                        <?php echo $text_edit; ?>
                    </h3>
                </div>
                <div class="panel-body">
                    <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-payment" class="form-horizontal">
                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="cryptapi_status"><?php echo $entry_status; ?></label>
                            <div class="col-sm-10">
                                <select name="cryptapi_status" id="cryptapi_status" class="form-control">
                                    <?php if ($cryptapi_status): ?>
                                        <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                                        <option value="0"><?php echo $text_disabled; ?></option>
                                    <?php else: ?>
                                        <option value="1"><?php echo $text_enabled; ?></option>
                                        <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="cryptapi_cryptapi_title">
                                <?php echo $title; ?>
                            </label>
                            <div class="col-sm-10">
                                <input class="form-control" type="text" id="cryptapi_cryptapi_title"
                                       name="payment_cryptapi_title" <?php if (empty($payment_cryptapi_title)): ?>value="Cryptocurrency"
                                       <?php else: ?>value="<?php echo $payment_cryptapi_title; ?>"
                                    <?php endif; ?>>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="cryptapi_branding">
                                <?php echo $branding; ?>
                            </label>
                            <div class="col-sm-10">
                                <select name="payment_cryptapi_branding" id="cryptapi_branding" class="form-control">
                                    <?php if (empty($payment_cryptapi_branding) && $payment_cryptapi_branding != '0'): ?>
                                        <option value="1" selected="selected">
                                            <?php echo $text_enabled; ?>
                                        </option>
                                        <option value="0">
                                            <?php echo $text_disabled; ?>
                                        </option>
                                    <?php elseif ($payment_cryptapi_branding == 1): ?>
                                        <option value="1" selected="selected">
                                            <?php echo $text_enabled; ?>
                                        </option>
                                        <option value="0">
                                            <?php echo $text_disabled; ?>
                                        </option>
                                    <?php elseif ($payment_cryptapi_branding == 0): ?>
                                        <option value="1">
                                            <?php echo $text_enabled; ?>
                                        </option>
                                        <option value="0" selected="selected">
                                            <?php echo $text_disabled; ?>
                                        </option>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="cryptapi_qrcode_default">
                                <?php echo $qrcode_default; ?>
                            </label>
                            <div class="col-sm-10">
                                <select name="payment_cryptapi_qrcode_default" id="cryptapi_qrcode_default" class="form-control">
                                    <?php if (empty($payment_cryptapi_qrcode_default) && $payment_cryptapi_qrcode_default !== '0'): ?>
                                        <option value="1" selected="selected">
                                            <?php echo $text_enabled; ?>
                                        </option>
                                        <option value="0">
                                            <?php echo $text_disabled; ?>
                                        </option>
                                    <?php elseif ($payment_cryptapi_qrcode_default == 1): ?>
                                        <option value="1" selected="selected">
                                            <?php echo $text_enabled; ?>
                                        </option>
                                        <option value="0">
                                            <?php echo $text_disabled; ?>
                                        </option>
                                    <?php elseif ($payment_cryptapi_qrcode_default == 0): ?>
                                        <option value="1">
                                            <?php echo $text_enabled; ?>
                                        </option>
                                        <option value="0" selected="selected">
                                            <?php echo $text_disabled; ?>
                                        </option>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="cryptapi_qrcode">
                                <?php echo $qrcode; ?>
                            </label>
                            <div class="col-sm-10">
                                <select name="payment_cryptapi_qrcode" id="cryptapi_qrcode" class="form-control">
                                    <option value="without_ammount"
                                        <?php if (empty($payment_cryptapi_qrcode) or $payment_cryptapi_qrcode == 'without_ammount'): ?>
                                            selected="selected"
                                        <?php endif; ?>>
                                        <?php echo $qrcode_without_ammount; ?>
                                    </option>
                                    <option value="ammount"
                                        <?php if ($payment_cryptapi_qrcode == 'ammount'): ?>
                                            selected="selected"
                                        <?php endif; ?>
                                    ><?php echo $qrcode_ammount; ?></option>
                                    <option value="hide_ammount"
                                        <?php if ($payment_cryptapi_qrcode == 'hide_ammount'): ?>
                                            selected="selected"
                                        <?php endif; ?>
                                    ><?php echo $qrcode_hide_ammount; ?></option>
                                    <option value="hide_without_ammount"
                                        <?php if ($payment_cryptapi_qrcode == 'hide_without_ammount'): ?>
                                            selected="selected"
                                        <?php endif; ?>
                                    ><?php echo $qrcode_hide_without_ammount; ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="cryptapi_blockchain_fees">
                            <span data-toggle="tooltip" title="<?php echo $text_blockchain_fees; ?>">
                                <?php echo $blockchain_fees; ?>
                            </span>
                            </label>
                            <div class="col-sm-10">
                                <select name="payment_cryptapi_blockchain_fees" id="cryptapi_blockchain_fees"
                                        class="form-control">
                                    <?php if ($payment_cryptapi_blockchain_fees == 1): ?>
                                        <option value="1" selected="selected">
                                            <?php echo $text_enabled; ?>
                                        </option>
                                        <option value="0">
                                            <?php echo $text_disabled; ?>
                                        </option>
                                    <?php else: ?>
                                        <option value="1">
                                            <?php echo $text_enabled; ?>
                                        </option>
                                        <option value="0" selected="selected">
                                            <?php echo $text_disabled; ?>
                                        </option>
                                    <?php endif ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="payment_cryptapi_fees">
                            <span data-toggle="tooltip" title="<?php echo $text_fees; ?>">
                                <?php echo $fees; ?>
                            </span>
                            </label>
                            <div class="col-sm-10">
                                <select name="payment_cryptapi_fees" id="payment_cryptapi_fees" class="form-control">
                                    <option value="0.05" <?php if ($payment_cryptapi_fees == "0.05"): ?>selected="selected"<?php endif; ?>>
                                        5%
                                    </option>
                                    <option value="0.048" <?php if ($payment_cryptapi_fees == "0.048"): ?>selected="selected"<?php endif; ?>>
                                        4.8%
                                    </option>
                                    <option value="0.045"<?php if ($payment_cryptapi_fees == "0.045"): ?> selected="selected" <?php endif; ?>>
                                        4.5%
                                    </option>
                                    <option value="0.042"
                                        <?php if ($payment_cryptapi_fees == "0.042"): ?>
                                            selected="selected"
                                        <?php endif; ?>
                                    >4.2%
                                    </option>
                                    <option value="0.04"
                                        <?php if ($payment_cryptapi_fees == "0.04"): ?>
                                            selected="selected"
                                        <?php endif; ?>
                                    >4%
                                    </option>
                                    <option value="0.038"
                                        <?php if ($payment_cryptapi_fees == "0.038"): ?>
                                            selected="selected"
                                        <?php endif; ?>
                                    >3.8%
                                    </option>
                                    <option value="0.035"
                                        <?php if ($payment_cryptapi_fees == "0.035"): ?>
                                            selected="selected"
                                        <?php endif; ?>
                                    >3.5%
                                    </option>
                                    <option value="0.032"
                                        <?php if ($payment_cryptapi_fees == "0.032"): ?>
                                            selected="selected"
                                        <?php endif; ?>
                                    >3.2%
                                    </option>
                                    <option value="0.03"
                                        <?php if ($payment_cryptapi_fees == "0.03"): ?>
                                            selected="selected"
                                        <?php endif; ?>
                                    >3%
                                    </option>
                                    <option value="0.028"
                                        <?php if ($payment_cryptapi_fees == "0.028"): ?>
                                            selected="selected"
                                        <?php endif; ?>
                                    >2.8%
                                    </option>
                                    <option value="0.025"
                                        <?php if ($payment_cryptapi_fees == "0.025"): ?>
                                            selected="selected"
                                        <?php endif; ?>
                                    >2.5%
                                    </option>
                                    <option value="0.022"
                                        <?php if ($payment_cryptapi_fees == "0.022"): ?>
                                            selected="selected"
                                        <?php endif; ?>
                                    >2.2%
                                    </option>
                                    <option value="0.02"
                                        <?php if ($payment_cryptapi_fees == "0.02"): ?>
                                            selected="selected"
                                        <?php endif; ?>
                                    >2%
                                    </option>
                                    <option value="0.018"
                                        <?php if ($payment_cryptapi_fees == "0.018"): ?>
                                            selected="selected"
                                        <?php endif; ?>
                                    >1.8%
                                    </option>
                                    <option value="0.015"
                                        <?php if ($payment_cryptapi_fees == "0.015"): ?>
                                            selected="selected"
                                        <?php endif; ?>
                                    >1.5%
                                    </option>
                                    <option value="0.012"
                                        <?php if ($payment_cryptapi_fees == "0.012"): ?>
                                            selected="selected"
                                        <?php endif; ?>
                                    >1.2%
                                    </option>
                                    <option value="0.01"
                                        <?php if ($payment_cryptapi_fees == "0.01"): ?>
                                            selected="selected"
                                        <?php endif; ?>
                                    >1%
                                    </option>
                                    <option value="0.0090"
                                        <?php if ($payment_cryptapi_fees == "0.0090"): ?>
                                            selected="selected"
                                        <?php endif; ?>
                                    >0.90%
                                    </option>
                                    <option value="0.0085"
                                        <?php if ($payment_cryptapi_fees == "0.0085"): ?>
                                            selected="selected"
                                        <?php endif; ?>
                                    >0.85%
                                    </option>
                                    <option value="0.0080"
                                        <?php if ($payment_cryptapi_fees == "0.0080"): ?>
                                            selected="selected"
                                        <?php endif; ?>
                                    >0.80%
                                    </option>
                                    <option value="0.0075"
                                        <?php if ($payment_cryptapi_fees == "0.0075"): ?>
                                            selected="selected"
                                        <?php endif; ?>
                                    >0.75%
                                    </option>
                                    <option value="0.0070"
                                        <?php if ($payment_cryptapi_fees == "0.0070"): ?>
                                            selected="selected"
                                        <?php endif; ?>
                                    >0.70%
                                    </option>
                                    <option value="0.0065"
                                        <?php if ($payment_cryptapi_fees == "0.0065"): ?>
                                            selected="selected"
                                        <?php endif; ?>
                                    >0.65%
                                    </option>
                                    <option value="0.0060"
                                        <?php if ($payment_cryptapi_fees == "0.0060"): ?>
                                            selected="selected"
                                        <?php endif; ?>
                                    >0.60%
                                    </option>
                                    <option value="0.0055"
                                        <?php if ($payment_cryptapi_fees == "0.0055"): ?>
                                            selected="selected"
                                        <?php endif; ?>
                                    >0.55%
                                    </option>
                                    <option value="0.0050"
                                        <?php if ($payment_cryptapi_fees == "0.0050"): ?>
                                            selected="selected"
                                        <?php endif; ?>
                                    >0.50%
                                    </option>
                                    <option value="0.0040"
                                        <?php if ($payment_cryptapi_fees == "0.0040"): ?>
                                            selected="selected"
                                        <?php endif; ?>
                                    >0.40%
                                    </option>
                                    <option value="0.0030"
                                        <?php if ($payment_cryptapi_fees == "0.0030"): ?>
                                            selected="selected"
                                        <?php endif; ?>
                                    >0.30%
                                    </option>
                                    <option value="0.0025"
                                        <?php if ($payment_cryptapi_fees == "0.0025"): ?>
                                            selected="selected"
                                        <?php endif; ?>
                                    >0.25%
                                    </option>
                                    <option value="never"
                                        <?php if (empty($payment_cryptapi_fees) ||
                                            $payment_cryptapi_fees == 'never'): ?>
                                            selected="selected"
                                        <?php endif; ?>
                                    >0%
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="cryptapi_qrcode_size">
                                <?php echo $qrcode_size; ?>
                            </label>
                            <div class="col-sm-10">
                                <input class="form-control" type="text" id="cryptapi_qrcode_size"
                                       name='payment_cryptapi_qrcode_size' <?php if
                                       (empty($payment_cryptapi_qrcode_size)): ?>value="300"
                                       <?php else: ?>value="<?php echo $payment_cryptapi_qrcode_size; ?>"
                                    <?php endif; ?>>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="cryptapi_color_scheme">
                                <?php echo $color_scheme; ?>
                            </label>
                            <div class="col-sm-10">
                                <select name="payment_cryptapi_color_scheme" id="cryptapi_color_scheme"
                                        class="form-control">
                                    <option value="light"
                                        <?php if (empty($payment_cryptapi_color_scheme) or
                                            $payment_cryptapi_color_scheme == 'light'): ?>
                                            selected="selected"
                                        <?php endif; ?>
                                    >
                                        <?php echo $scheme_light; ?>
                                    </option>
                                    <option value="dark"
                                        <?php if ($payment_cryptapi_color_scheme == 'dark'): ?>
                                            selected="selected"
                                        <?php endif; ?>
                                    >
                                        <?php echo $scheme_dark; ?>
                                    </option>
                                    <option value="auto"
                                        <?php if ($payment_cryptapi_color_scheme == 'auto'): ?>
                                            selected="selected"
                                        <?php endif; ?>
                                    >
                                        <?php echo $scheme_auto; ?>
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="cryptapi_refresh_values">
                            <span data-toggle="tooltip" title="<?php echo $text_refresh_values; ?>">
                                <?php echo $refresh_values; ?>
                            </span>
                            </label>
                            <div class="col-sm-10">
                                <select name="payment_cryptapi_refresh_values" id="cryptapi_refresh_values"
                                        class="form-control">
                                    <option value="never"
                                        <?php if ($payment_cryptapi_refresh_values == 'never'): ?>
                                            selected="selected"
                                        <?php endif; ?>
                                    >
                                        <?php echo $never; ?>
                                    </option>
                                    <option value="300"
                                        <?php if (empty($payment_cryptapi_refresh_values) || $payment_cryptapi_refresh_values == '300'): ?>
                                            selected="selected"
                                        <?php endif; ?>
                                    >
                                        <?php echo $five_minutes; ?>
                                    </option>
                                    <option value="600"
                                        <?php if ($payment_cryptapi_refresh_values == '600'): ?>
                                            selected="selected"
                                        <?php endif; ?>
                                    >
                                        <?php echo $ten_minutes; ?>
                                    </option>
                                    <option value="900"
                                        <?php if ($payment_cryptapi_refresh_values == '900'): ?>
                                            selected="selected"
                                        <?php endif; ?>
                                    >
                                        <?php echo $fifteen_minutes; ?>
                                    </option>
                                    <option value="1800"
                                        <?php if ($payment_cryptapi_refresh_values == '1800'): ?>
                                            selected="selected"
                                        <?php endif; ?>
                                    >
                                        <?php echo $thirty_minutes; ?>
                                    </option>
                                    <option value="2700"
                                        <?php if ($payment_cryptapi_refresh_values == '2700'): ?>
                                            selected="selected"
                                        <?php endif; ?>
                                    >
                                        <?php echo $forty_five_minutes; ?>
                                    </option>
                                    <option value="3600"
                                        <?php if ($payment_cryptapi_refresh_values == '3600'): ?>
                                            selected="selected"
                                        <?php endif; ?>
                                    >
                                        <?php echo $sixty_minutes; ?>
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="cryptapi_order_cancelation_timeout">
                            <span data-toggle="tooltip" title="<?php echo $text_order_cancelation_timeout; ?>">
                                <?php echo $order_cancelation_timeout; ?>
                            </span>
                            </label>
                            <div class="col-sm-10">
                                <select name="payment_cryptapi_order_cancelation_timeout"
                                        id="cryptapi_order_cancelation_timeout" class="form-control">
                                    <option value="never"
                                        <?php if ($payment_cryptapi_order_cancelation_timeout == 'never'):
                                            ?>
                                            selected="selected"
                                        <?php endif; ?>
                                    >
                                        <?php echo $never; ?>
                                    </option>
                                    <option value="3600"
                                        <?php if (empty($payment_cryptapi_order_cancelation_timeout) ||
                                            $payment_cryptapi_order_cancelation_timeout == '3600'): ?>
                                            selected="selected"
                                        <?php endif; ?>
                                    >
                                        <?php echo $one_hour; ?>
                                    </option>
                                    <option value="21600"
                                        <?php if ($payment_cryptapi_order_cancelation_timeout == '21600'):
                                            ?>
                                            selected="selected"
                                        <?php endif; ?>
                                    >
                                        <?php echo $six_hours; ?>
                                    </option>
                                    <option value="43200"
                                        <?php if ($payment_cryptapi_order_cancelation_timeout == '43200'):
                                            ?>
                                            selected="selected"
                                        <?php endif; ?>
                                    >
                                        <?php echo $twelve_hours; ?>
                                    </option>
                                    <option value="64800"
                                        <?php if ($payment_cryptapi_order_cancelation_timeout == '64800'):
                                            ?>
                                            selected="selected"
                                        <?php endif; ?>
                                    >
                                        <?php echo $eighteen_hours; ?>
                                    </option>
                                    <option value="86400"
                                        <?php if ($payment_cryptapi_order_cancelation_timeout == '86400'):
                                            ?>
                                            selected="selected"
                                        <?php endif; ?>
                                    >
                                        <?php echo $twenty_four_hours; ?>
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="input-geo-zone">
                                <?php echo $entry_geo_zone; ?>
                            </label>
                            <div class="col-sm-10">
                                <select name="payment_cryptapi_standard_geo_zone_id" id="input-geo-zone"
                                        class="form-control">
                                    <option value="0">
                                        <?php echo $text_all_zones; ?>
                                    </option>
                                    <?php foreach ($geo_zone as $geo_zones): ?>
                                        <?php if ($geo_zone['geo_zone_id'] == $payment_cryptapi_standard_geo_zone_id): ?>
                                            <option value="<?php $geo_zone['geo_zone_id']; ?>" selected="selected">
                                                <?php echo $geo_zone['name']; ?>
                                            </option>
                                        <?php else: ?>
                                            <option value="<?php $geo_zone['geo_zone_id']; ?>">
                                                <?php echo $geo_zone['name']; ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="input-order-status">
                                <?php echo $entry_order_status; ?>
                            </label>
                            <div class="col-sm-10">
                                <select name="payment_cryptapi_order_status_id" id="input-order-status"
                                        class="form-control">
                                    <?php foreach ($order_statuses as $order_status): ?>
                                        <?php $selected = ($order_status['order_status_id'] == $divido_order_status_id) ? 'selected' : ''; ?>
                                        <option value="<?php echo $order_status['order_status_id']; ?>"
                                            <?php echo $selected; ?>
                                        >
                                            <?php echo $order_status['name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="cryptapi_cryptapi_api_key">
                                <?php echo $api_key; ?>
                            </label>
                            <div class="col-sm-10">
                                <input class="form-control" type="text" id="cryptapi_cryptapi_api_key"
                                       name='payment_cryptapi_api_key' <?php if (empty($payment_cryptapi_api_key)): ?>value=""
                                       <?php else: ?>value="<?php echo $payment_cryptapi_api_key; ?>"
                                    <?php endif; ?>>
                                <p class="mt-2">
                                    <?php echo $api_key_info; ?>
                                </p>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-12 text-center" style="margin-bottom: 20px">
                                <label class="control-label text-center" style="float: none !important;">
                                    <?php echo $entry_cryptocurrencies; ?>
                                </label>
                                <p><?php echo $help_cryptocurrencies; ?></p>
                            </div>
                            <input type="hidden" id="cryptapi_cryptocurrencies_array_cache"
                                   name="payment_cryptapi_cryptocurrencies_array_cache"
                                   value='<?php echo json_encode($payment_cryptapi_cryptocurrencies_array); ?>'>
                            <?php foreach ($payment_cryptapi_cryptocurrencies_array as $ticker => $coin): ?>
                                <div class="col-sm-2 text-right">
                                    <input type='checkbox' name='payment_cryptapi_cryptocurrencies[<?php echo $ticker; ?>]'
                                           id="cryptapi_cryptocurrencies_<?php echo $ticker; ?>" value='<?php echo $ticker; ?>' <?php
                                           if (!empty($payment_cryptapi_cryptocurrencies[$ticker]) &&
                                    $payment_cryptapi_cryptocurrencies[$ticker] === $ticker): ?>checked="checked"
                                        <?php endif; ?>/>
                                    <label class="control-label" for="cryptapi_cryptocurrencies_<?php echo $ticker; ?>">
                                <span data-toggle="tooltip" title="<?php echo $help_cryptocurrency . ' ' . $coin; ?>">
                                    <?php echo $coin; ?>
                                </span>
                                    </label>
                                </div>
                                <div class="col-sm-10">
                                    <p>
                                        <input class="form-control" type="text"
                                               id="cryptapi_cryptocurrencies_address_<?php echo $ticker; ?>"
                                               name='payment_cryptapi_cryptocurrencies_address[<?php echo $ticker; ?>]'
                                               placeholder="<?php echo $coin; ?>"
                                               value="<?php if (!empty($payment_cryptapi_cryptocurrencies_address[$ticker])): echo $payment_cryptapi_cryptocurrencies_address[$ticker]; endif; ?>">
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="input-sort-order">
                                <?php echo $entry_sort_order; ?>
                            </label>
                            <div class="col-sm-10">
                                <input type="text" name="payment_cryptapi_sort_order"
                                       value="<?php echo $payment_cryptapi_sort_order; ?>"
                                       placeholder="<?php echo $entry_sort_order; ?>" id="input-sort-order"
                                       class="form-control"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="input-status">
                                <?php echo $disable_conversion; ?>
                            </label>
                            <div class="col-sm-10">
                                <input type='checkbox' name='payment_cryptapi_disable_conversion'
                                       id="cryptapi_disable_conversion" value='1' <?php
                                       if ($payment_cryptapi_disable_conversion === 1): ?>checked="checked"
                                    <?php endif; ?>/>
                                <p><strong>
                                        <?php echo $disable_conversion_warn_bold; ?>
                                    </strong>
                                    <?php echo $disable_conversion_warn; ?>
                                </p>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php echo $footer; ?>