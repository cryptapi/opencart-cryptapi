<?php

// Heading
$_['heading_title'] = 'CryptAPI';

$_['title'] = 'Título';

$_['blockchain_fees'] = 'Añadir la tarifa de la blockchain al pedido';
$_['fees'] = 'Tarifa de servicio a cobrar';

$_['never'] = 'Nunca';

// Text
$_['text_extension'] = 'Extensiones';
$_['text_success'] = '¡Éxito: tus datos de CryptAPI han sido actualizados!';
$_['text_edit'] = 'Editar CryptAPI';
$_['text_cryptapi'] = '<a target="_BLANK" href="https://cryptapi.io/"><img src="' . HTTP_CATALOG . '/extension/cryptapi/admin/view/image/payment/cryptapi.png" alt="cryptapi" title="cryptapi" style="border: 1px solid #EEEEEE; height:37px" /></a>';
$_['text_connect_cryptapi'] = 'Este módulo te permite aceptar pagos CryptAPI de forma segura.';
$_['text_cryptapi_image'] = '<a target="_BLANK" href="https://cryptapi.io/"><img src="' . HTTP_CATALOG . '/extension/cryptapi/admin/view/image/payment/cryptapi.png" alt="cryptapi" title="cryptapi" class="img-fluid" /></a>';
$_['text_cryptapi_suppport'] = 'Si necesitas ayuda o tienes alguna sugerencia, contáctanos a través del chat en vivo de nuestro <a target="_blank" href="https://cryptapi.io">sitio web</a>';
$_['text_blockchain_fees'] = 'Añade una estimación de la tarifa de la blockchain al valor del pedido';
$_['text_fees'] = 'Establece la tarifa de servicio CryptAPI que deseas cobrar al cliente. Nota: tarifa para cubrir total o parcialmente las tarifas de CryptAPI.';
$_['text_qrcode'] = 'Elige cómo mostrar el código QR al usuario. Selecciona uno por defecto para mostrar primero u oculta uno de ellos.';
$_['text_btc'] = 'Bitcoin';
$_['text_refresh_values'] = 'El sistema actualiza automáticamente el valor de conversión de las facturas (con datos en tiempo real) cada X minutos. Útil cuando el cliente tarda en pagar y la criptomoneda elegida es volátil. Aviso: poner "Nunca" puede causar problemas de conversión; recomendamos mantener 5 minutos.';
$_['text_order_cancelation_timeout'] = 'Define el tiempo que el usuario tiene para pagar el pedido. Cuando termina, el pedido se marca como "Cancelado" y cualquier valor pagado se ignora. Aviso: si el usuario sigue enviando pagos a la dirección generada, el valor seguirá siendo redirigido a ti. No recomendamos más de 1 hora.';

$_['text_tab_general'] = 'General';
$_['text_tab_crypto'] = 'Criptomonedas';
$_['text_tab_advanced'] = 'Avanzado';

// Entry
$_['entry_cryptocurrencies'] = 'Criptomonedas aceptadas';
$_['entry_btc_address'] = $_['text_btc'] . ' Dirección';

$_['entry_order_status'] = 'Estado del pedido';
$_['warning_currency_unsupported'] = 'La moneda de tu tienda (%s) no está en la lista de monedas soportadas por CryptAPI. Las estimaciones de tarifa de blockchain usarán USD como alternativa. Consulta https://docs.cryptapi.io para la lista actual.';
$_['entry_paid_order_statuses'] = 'Estados que cuentan como pagados';
$_['text_paid_order_statuses']  = 'Selecciona qué estados del pedido cuentan como "pagado". Los pedidos en estos estados no se reprocesarán por callbacks ni se sondearán para más pagos. Mantén Ctrl/Cmd para seleccionar varios.';
$_['entry_status'] = 'Estado';

$_['branding'] = 'Mostrar el logo y créditos de CryptAPI debajo del código QR';

$_['qrcode_default'] = 'Mostrar código QR';
$_['qrcode'] = 'Código QR a mostrar';
$_['qrcode_size'] = 'Tamaño del código QR';
$_['qrcode_without_ammount'] = 'Por defecto sin importe';
$_['qrcode_ammount'] = 'Por defecto con importe';
$_['qrcode_hide_ammount'] = 'Ocultar con importe';
$_['qrcode_hide_without_ammount'] = 'Ocultar sin importe';

$_['color_scheme'] = 'Esquema de colores';
$_['scheme_light'] = 'Claro';
$_['scheme_dark'] = 'Oscuro';
$_['scheme_auto'] = 'Automático';

$_['refresh_values'] = 'Actualizar valor convertido';
$_['five_minutes'] = 'Cada 5 minutos';
$_['ten_minutes'] = 'Cada 10 minutos';
$_['fifteen_minutes'] = 'Cada 15 minutos';
$_['thirty_minutes'] = 'Cada 30 minutos';
$_['forty_five_minutes'] = 'Cada 45 minutos';
$_['sixty_minutes'] = 'Cada 60 minutos';

$_['order_cancelation_timeout'] = 'Tiempo para cancelar el pedido';
$_['fifteen_minutes_cancellation'] = '15 minutos';
$_['thirty_minutes_cancellation'] = '30 minutos';
$_['forty_five_minutes_cancellation'] = '45 minutos';
$_['one_hour'] = '1 hora';
$_['six_hours'] = '6 horas';
$_['twelve_hours'] = '12 horas';
$_['eighteen_hours'] = '18 horas';
$_['twenty_four_hours'] = '24 horas';

$_['entry_geo_zone'] = 'Zona geográfica';
$_['entry_sort_order'] = 'Orden';

// Error
$_['error_permission'] = 'Aviso: no tienes permiso para modificar el módulo de pago CryptAPI';

// Help hints
$_['help_cryptocurrencies'] = 'Si estás usando BlockBee puedes elegir si configuras las direcciones de recepción aquí o en la página de ajustes de BlockBee.<br/>Para configurar las direcciones en los ajustes del plugin, debes seleccionar "Address Override" al crear la clave API.<br/>Para configurar las direcciones en los ajustes de BlockBee, NO debes seleccionar "Address Override" al crear la clave API.';
$_['help_cryptocurrency'] = 'Marca la casilla para activar la criptomoneda';


// Order page - payment tab
$_['text_payment_info'] = 'Información del pago';

$_['disable_conversion'] = 'Desactivar conversión';
$_['disable_conversion_warn_bold'] = '¡Atención: esta opción desactivará la conversión de precios para TODAS las criptomonedas!';
$_['disable_conversion_warn'] = 'Si marcas esto, el precio no se convertirá de la moneda de la tienda a la criptomoneda elegida por el usuario, y se le pedirá que pague el mismo valor mostrado en la tienda, independientemente de la criptomoneda elegida';


$_['api_key'] = 'Clave API BlockBee';
$_['api_key_info'] = "Inserta aquí tu clave API BlockBee. Puedes obtener una con BlockBee. Aviso: si el permiso 'Address Override' no está activado, debes establecer la dirección en el panel, de lo contrario los pagos pueden fallar.";

$_['info_icon'] = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="ms-1 bi bi-info-circle" viewBox="0 0 16 16">
                                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                        <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533L8.93 6.588zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
                                    </svg>';
