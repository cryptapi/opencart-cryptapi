<form action="" method="POST" class="form-horizontal" id="cryptapi-payment-form">
    <fieldset id="payment">
        <legend><?php echo $title;?></legend>
        <div class="form-group">
            <div class="col-sm-12">
                <ul style="list-style: none outside;">
                    <?php foreach($cryptocurrencies as $ticker => $coin):?>
                    <li>
                        <input type="radio" name="cryptapi_coin" id="cryptapi_coin_<?php echo $ticker;?>" value="<?php echo $ticker;?>"/>
                        <label for="cryptapi_coin_<?php echo $ticker;?>"><?php echo $text_paywith;?> <?php echo $coin;?></label>
                    </li>
                    <?php endforeach;?>
                </ul>
            </div>
        </div>
    </fieldset>
</form>
<div class="buttons">
    <div class="pull-right">
        <input type="button" value="<?php echo $button_confirm;?>" id="button-confirm" data-loading-text="<?php echo $text_processing;?>" class="btn btn-primary"/>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        <?php if($blockchain_fee || $fee !== 'none'): ?>
            let symbol_left = '<?php echo $symbol_left;?>';
            let symbol_right = '<?php echo $symbol_right;?>';
            let table = $('#collapse-checkout-confirm > .panel-body');
            table.find('tfoot > tr:last-child').remove();
            table.find('tfoot').append('<tr><td colspan="4" class="text-right"><strong>Fee:</strong></td><td class="text-right">' + symbol_left + <?php echo $cryptapi_fee;?> + symbol_right + '</td></tr>')
            table.find('tfoot').append('<tr><td colspan="4" class="text-right"><strong><?php echo $column_total;?>:</strong></td><td class="text-right">' + symbol_left + <?php echo $total;?> + symbol_right + '</td></tr>')

            $('#cryptapi-payment-form').on('change', function () {
                var val = $('input[name="cryptapi_coin"]:checked').val();

                $.ajax({
                    type: "POST",
                    url: "index.php?route=checkout/confirm",
                    data: $(this).serialize(),
                    success: function (res) {
                        let response = $(res);

                        $('#collapse-checkout-confirm > .panel-body')
                            .empty()
                            .append(response);

                        $("input[name=cryptapi_coin][value=" + val + "]").prop("checked", true);
                    }
                });
            })

        $('#button-confirm').on('click', function () {
            $.ajax({
                url: 'index.php?route=extension/payment/cryptapi/confirm',
                type: 'post',
                data: $('#cryptapi-payment-form input:checked'),
                dataType: 'json',
                cache: false,
                beforeSend: function () {
                    $('#button-confirm').button('loading');
                },
                complete: function () {
                    $('#button-confirm').button('reset');
                },
                success: function (json) {
                    if (json['redirect']) {
                        location = json['redirect'];
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                }
            });
        });
        <?php endif;?>
    });
</script>