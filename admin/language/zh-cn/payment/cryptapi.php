<?php
// NOTE: Simplified Chinese translation — auto-generated, recommend native-speaker review before public release.

// Heading
$_['heading_title'] = 'CryptAPI';

$_['title'] = '标题';

$_['blockchain_fees'] = '将区块链手续费添加到订单中';
$_['fees'] = '服务费';

$_['never'] = '从不';

// Text
$_['text_extension'] = '扩展';
$_['text_success'] = '成功:您的 CryptAPI 设置已更新!';
$_['text_edit'] = '编辑 CryptAPI';
$_['text_cryptapi'] = '<a target="_BLANK" href="https://cryptapi.io/"><img src="' . HTTP_CATALOG . '/extension/cryptapi/admin/view/image/payment/cryptapi.png" alt="cryptapi" title="cryptapi" style="border: 1px solid #EEEEEE; height:37px" /></a>';
$_['text_connect_cryptapi'] = '此模块允许您安全地接受 CryptAPI 支付。';
$_['text_cryptapi_image'] = '<a target="_BLANK" href="https://cryptapi.io/"><img src="' . HTTP_CATALOG . '/extension/cryptapi/admin/view/image/payment/cryptapi.png" alt="cryptapi" title="cryptapi" class="img-fluid" /></a>';
$_['text_cryptapi_suppport'] = '如需帮助或有建议,请通过我们<a target="_blank" href="https://cryptapi.io">网站</a>上的在线聊天联系我们';
$_['text_blockchain_fees'] = '将区块链手续费的估算值加到订单金额上';
$_['text_fees'] = '设置您要向客户收取的 CryptAPI 服务费。说明:用于全额或部分覆盖 CryptAPI 手续费。';
$_['text_qrcode'] = '选择如何向用户显示二维码。可以选择默认显示哪一个,或隐藏其中一个。';
$_['text_btc'] = 'Bitcoin';
$_['text_refresh_values'] = '系统每 X 分钟自动更新发票的转换金额(实时数据)。当客户支付较慢且所选加密货币波动较大时很有用。警告:设置为"从不"可能导致转换问题;建议保持 5 分钟。';
$_['text_order_cancelation_timeout'] = '用户支付订单的时长。超时后订单将被标记为"已取消",所有已支付金额将被忽略。注意:如果用户继续向生成的地址发送资金,金额仍会转给您。不建议超过 1 小时。';

$_['text_tab_general'] = '常规';
$_['text_tab_crypto'] = '加密货币';
$_['text_tab_advanced'] = '高级';

// Entry
$_['entry_cryptocurrencies'] = '接受的加密货币';
$_['entry_btc_address'] = $_['text_btc'] . ' 地址';

$_['entry_order_status'] = '订单状态';
$_['warning_currency_unsupported'] = '您的店铺货币(%s)不在 CryptAPI 支持的法币列表中。区块链手续费估算将回退到美元。请参阅 https://docs.cryptapi.io 查看当前支持列表。';
$_['entry_paid_order_statuses'] = '视为已支付的状态';
$_['text_paid_order_statuses']  = '选择哪些订单状态视为"已支付"。这些状态的订单不会被回调重新处理,也不会再被轮询查询更多支付。按住 Ctrl/Cmd 进行多选。';
$_['entry_status'] = '状态';

$_['branding'] = '在二维码下方显示 CryptAPI 标志和署名';

$_['qrcode_default'] = '显示二维码';
$_['qrcode'] = '要显示的二维码';
$_['qrcode_size'] = '二维码大小';
$_['qrcode_without_ammount'] = '默认 - 不含金额';
$_['qrcode_ammount'] = '默认 - 含金额';
$_['qrcode_hide_ammount'] = '隐藏 - 含金额';
$_['qrcode_hide_without_ammount'] = '隐藏 - 不含金额';

$_['color_scheme'] = '配色方案';
$_['scheme_light'] = '浅色';
$_['scheme_dark'] = '深色';
$_['scheme_auto'] = '自动';

$_['refresh_values'] = '刷新转换值';
$_['five_minutes'] = '每 5 分钟';
$_['ten_minutes'] = '每 10 分钟';
$_['fifteen_minutes'] = '每 15 分钟';
$_['thirty_minutes'] = '每 30 分钟';
$_['forty_five_minutes'] = '每 45 分钟';
$_['sixty_minutes'] = '每 60 分钟';

$_['order_cancelation_timeout'] = '订单取消超时';
$_['fifteen_minutes_cancellation'] = '15 分钟';
$_['thirty_minutes_cancellation'] = '30 分钟';
$_['forty_five_minutes_cancellation'] = '45 分钟';
$_['one_hour'] = '1 小时';
$_['six_hours'] = '6 小时';
$_['twelve_hours'] = '12 小时';
$_['eighteen_hours'] = '18 小时';
$_['twenty_four_hours'] = '24 小时';

$_['entry_geo_zone'] = '地理区域';
$_['entry_sort_order'] = '排序';

// Error
$_['error_permission'] = '警告:您没有修改 CryptAPI 支付模块的权限';

// Help hints
$_['help_cryptocurrencies'] = '如果您使用 BlockBee,可以选择在此处或在 BlockBee 设置页面中设置收款地址。<br/>要在插件设置中设置地址,请在创建 API 密钥时选择 "Address Override"。<br/>要在 BlockBee 设置中设置地址,请在创建 API 密钥时不要选择 "Address Override"。';
$_['help_cryptocurrency'] = '勾选复选框以启用该加密货币';


// Order page - payment tab
$_['text_payment_info'] = '支付信息';

$_['disable_conversion'] = '禁用转换';
$_['disable_conversion_warn_bold'] = '注意:此选项将禁用所有加密货币的价格转换!';
$_['disable_conversion_warn'] = '如果勾选,价格将不会从店铺货币转换为用户选择的加密货币,系统将要求用户支付店铺显示的相同数值,无论选择哪种加密货币';


$_['api_key'] = 'BlockBee API 密钥';
$_['api_key_info'] = "在此输入您的 BlockBee API 密钥。可在 BlockBee 获取。注意:如果未启用 'Address Override' 权限,您必须在面板中设置地址,否则支付可能失败。";

$_['info_icon'] = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="ms-1 bi bi-info-circle" viewBox="0 0 16 16">
                                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                        <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533L8.93 6.588zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
                                    </svg>';
