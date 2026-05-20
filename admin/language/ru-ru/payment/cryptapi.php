<?php
// NOTE: Russian translation — auto-generated, recommend native-speaker review before public release.

// Heading
$_['heading_title'] = 'CryptAPI';

$_['title'] = 'Название';

$_['blockchain_fees'] = 'Добавить комиссию блокчейна к заказу';
$_['fees'] = 'Сервисная комиссия';

$_['never'] = 'Никогда';

// Text
$_['text_extension'] = 'Расширения';
$_['text_success'] = 'Успех: ваши настройки CryptAPI обновлены!';
$_['text_edit'] = 'Редактировать CryptAPI';
$_['text_cryptapi'] = '<a target="_BLANK" href="https://cryptapi.io/"><img src="' . HTTP_CATALOG . '/extension/cryptapi/admin/view/image/payment/cryptapi.png" alt="cryptapi" title="cryptapi" style="border: 1px solid #EEEEEE; height:37px" /></a>';
$_['text_connect_cryptapi'] = 'Этот модуль позволяет вам безопасно принимать платежи CryptAPI.';
$_['text_cryptapi_image'] = '<a target="_BLANK" href="https://cryptapi.io/"><img src="' . HTTP_CATALOG . '/extension/cryptapi/admin/view/image/payment/cryptapi.png" alt="cryptapi" title="cryptapi" class="img-fluid" /></a>';
$_['text_cryptapi_suppport'] = 'Если вам нужна помощь или у вас есть предложения, свяжитесь с нами через онлайн-чат на нашем <a target="_blank" href="https://cryptapi.io">сайте</a>';
$_['text_blockchain_fees'] = 'Добавляет оценку комиссии блокчейна к сумме заказа';
$_['text_fees'] = 'Установите сервисную комиссию CryptAPI, которую хотите взимать с клиента. Примечание: комиссия для полного или частичного покрытия комиссий CryptAPI.';
$_['text_qrcode'] = 'Выберите, как показывать QR-код пользователю. Выберите вариант по умолчанию или скройте один из них.';
$_['text_btc'] = 'Bitcoin';
$_['text_refresh_values'] = 'Система автоматически обновляет курс конвертации счетов (в реальном времени) каждые X минут. Полезно, когда клиент долго платит, а выбранная криптовалюта волатильна. Внимание: установка "Никогда" может вызвать проблемы с конвертацией; рекомендуем 5 минут.';
$_['text_order_cancelation_timeout'] = 'Определяет время, которое есть у пользователя для оплаты заказа. После истечения этого времени заказ помечается как "Отменён" и любая уплаченная сумма игнорируется. Внимание: если пользователь продолжит отправлять средства на сгенерированный адрес, сумма всё равно будет перенаправляться вам. Не рекомендуется больше 1 часа.';

$_['text_tab_general'] = 'Общие';
$_['text_tab_crypto'] = 'Криптовалюты';
$_['text_tab_advanced'] = 'Дополнительно';

// Entry
$_['entry_cryptocurrencies'] = 'Принимаемые криптовалюты';
$_['entry_btc_address'] = $_['text_btc'] . ' Адрес';

$_['entry_order_status'] = 'Статус заказа';
$_['warning_currency_unsupported'] = 'Валюта вашего магазина (%s) не входит в список поддерживаемых CryptAPI фиатных валют. Оценки комиссии блокчейна будут использовать USD в качестве запасного варианта. См. https://docs.cryptapi.io для текущего списка.';
$_['entry_paid_order_statuses'] = 'Статусы, считающиеся оплаченными';
$_['text_paid_order_statuses']  = 'Выберите, какие статусы заказа считать "оплачено". Заказы в этих статусах не будут переобработаны коллбэками и не будут опрашиваться для дальнейших платежей. Удерживайте Ctrl/Cmd для выбора нескольких.';
$_['entry_status'] = 'Статус';

$_['branding'] = 'Показывать логотип и кредиты CryptAPI под QR-кодом';

$_['qrcode_default'] = 'Показать QR-код';
$_['qrcode'] = 'Какой QR-код показывать';
$_['qrcode_size'] = 'Размер QR-кода';
$_['qrcode_without_ammount'] = 'По умолчанию без суммы';
$_['qrcode_ammount'] = 'По умолчанию с суммой';
$_['qrcode_hide_ammount'] = 'Скрыть с суммой';
$_['qrcode_hide_without_ammount'] = 'Скрыть без суммы';

$_['color_scheme'] = 'Цветовая схема';
$_['scheme_light'] = 'Светлая';
$_['scheme_dark'] = 'Тёмная';
$_['scheme_auto'] = 'Авто';

$_['refresh_values'] = 'Обновлять конвертированное значение';
$_['five_minutes'] = 'Каждые 5 минут';
$_['ten_minutes'] = 'Каждые 10 минут';
$_['fifteen_minutes'] = 'Каждые 15 минут';
$_['thirty_minutes'] = 'Каждые 30 минут';
$_['forty_five_minutes'] = 'Каждые 45 минут';
$_['sixty_minutes'] = 'Каждые 60 минут';

$_['order_cancelation_timeout'] = 'Тайм-аут отмены заказа';
$_['fifteen_minutes_cancellation'] = '15 минут';
$_['thirty_minutes_cancellation'] = '30 минут';
$_['forty_five_minutes_cancellation'] = '45 минут';
$_['one_hour'] = '1 час';
$_['six_hours'] = '6 часов';
$_['twelve_hours'] = '12 часов';
$_['eighteen_hours'] = '18 часов';
$_['twenty_four_hours'] = '24 часа';

$_['entry_geo_zone'] = 'Гео-зона';
$_['entry_sort_order'] = 'Порядок сортировки';

// Error
$_['error_permission'] = 'Внимание: у вас нет прав на изменение платёжного модуля CryptAPI';

// Help hints
$_['help_cryptocurrencies'] = 'Если вы используете BlockBee, вы можете выбрать, задавать ли адреса получения здесь или на странице настроек BlockBee.<br/>Чтобы задать адреса в настройках плагина, выберите "Address Override" при создании API-ключа.<br/>Чтобы задать адреса в настройках BlockBee, НЕ выбирайте "Address Override" при создании API-ключа.';
$_['help_cryptocurrency'] = 'Установите флажок, чтобы включить криптовалюту';


// Order page - payment tab
$_['text_payment_info'] = 'Информация о платеже';

$_['disable_conversion'] = 'Отключить конвертацию';
$_['disable_conversion_warn_bold'] = 'Внимание: эта опция отключает конвертацию цен для ВСЕХ криптовалют!';
$_['disable_conversion_warn'] = 'Если вы включите это, цена не будет конвертироваться из валюты магазина в выбранную пользователем криптовалюту, и пользователю будет предложено оплатить то же значение, что показано в магазине, независимо от выбранной криптовалюты';


$_['api_key'] = 'API-ключ BlockBee';
$_['api_key_info'] = "Вставьте сюда ваш API-ключ BlockBee. Получить его можно в BlockBee. Внимание: если разрешение 'Address Override' не активировано, вы должны задать адрес в панели, иначе платежи могут не пройти.";

$_['info_icon'] = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="ms-1 bi bi-info-circle" viewBox="0 0 16 16">
                                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                        <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533L8.93 6.588zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
                                    </svg>';
