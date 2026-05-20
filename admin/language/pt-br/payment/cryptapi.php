<?php

// Heading
$_['heading_title'] = 'CryptAPI';

$_['title'] = 'Título';

$_['blockchain_fees'] = 'Adicionar a taxa da blockchain ao pedido';
$_['fees'] = 'Taxa de serviço a cobrar';

$_['never'] = 'Nunca';

// Text
$_['text_extension'] = 'Extensões';
$_['text_success'] = 'Sucesso: Seus dados do CryptAPI foram atualizados!';
$_['text_edit'] = 'Editar CryptAPI';
$_['text_cryptapi'] = '<a target="_BLANK" href="https://cryptapi.io/"><img src="' . HTTP_CATALOG . '/extension/cryptapi/admin/view/image/payment/cryptapi.png" alt="cryptapi" title="cryptapi" style="border: 1px solid #EEEEEE; height:37px" /></a>';
$_['text_connect_cryptapi'] = 'Este módulo permite que você aceite pagamentos CryptAPI com segurança.';
$_['text_cryptapi_image'] = '<a target="_BLANK" href="https://cryptapi.io/"><img src="' . HTTP_CATALOG . '/extension/cryptapi/admin/view/image/payment/cryptapi.png" alt="cryptapi" title="cryptapi" class="img-fluid" /></a>';
$_['text_cryptapi_suppport'] = 'Se precisar de ajuda ou tiver alguma sugestão, contate-nos pelo chat ao vivo no nosso <a target="_blank" href="https://cryptapi.io">site</a>';
$_['text_blockchain_fees'] = 'Adiciona uma estimativa da taxa da blockchain ao valor do pedido';
$_['text_fees'] = 'Defina a taxa de serviço CryptAPI que deseja cobrar do cliente. Observação: taxa para cobrir total ou parcialmente as taxas do CryptAPI.';
$_['text_qrcode'] = 'Escolha como mostrar o QR Code ao usuário. Selecione um padrão para mostrar primeiro ou oculte um dos códigos.';
$_['text_btc'] = 'Bitcoin';
$_['text_refresh_values'] = 'O sistema atualiza automaticamente o valor de conversão das faturas (com dados em tempo real) a cada X minutos. Útil quando o cliente demora para pagar e a criptomoeda escolhida é volátil. Aviso: definir como "Nunca" pode causar problemas de conversão; recomendamos manter 5 minutos.';
$_['text_order_cancelation_timeout'] = 'Define o tempo que o usuário tem para pagar o pedido. Após esse tempo, o pedido é marcado como "Cancelado" e qualquer valor pago é ignorado. Aviso: se o usuário continuar enviando pagamento para o endereço gerado, o valor continua sendo redirecionado para você. Não recomendamos mais de 1 hora.';

$_['text_tab_general'] = 'Geral';
$_['text_tab_crypto'] = 'Criptomoedas';
$_['text_tab_advanced'] = 'Avançado';

// Entry
$_['entry_cryptocurrencies'] = 'Criptomoedas aceitas';
$_['entry_btc_address'] = $_['text_btc'] . ' Endereço';

$_['entry_order_status'] = 'Status do pedido';
$_['warning_currency_unsupported'] = 'A moeda da sua loja (%s) não está na lista de moedas suportadas pelo CryptAPI. As estimativas de taxa da blockchain usarão USD como alternativa. Consulte https://docs.cryptapi.io para a lista atual.';
$_['entry_paid_order_statuses'] = 'Status que contam como pagos';
$_['text_paid_order_statuses']  = 'Selecione quais status do pedido contam como "pago". Pedidos nesses status não serão reprocessados por callbacks nem sondados para mais pagamentos. Mantenha Ctrl/Cmd pressionado para selecionar vários.';
$_['entry_status'] = 'Status';

$_['branding'] = 'Mostrar o logotipo e créditos CryptAPI abaixo do QR Code';

$_['qrcode_default'] = 'Mostrar QR Code';
$_['qrcode'] = 'QR Code a mostrar';
$_['qrcode_size'] = 'Tamanho do QR Code';
$_['qrcode_without_ammount'] = 'Padrão sem valor';
$_['qrcode_ammount'] = 'Padrão com valor';
$_['qrcode_hide_ammount'] = 'Ocultar com valor';
$_['qrcode_hide_without_ammount'] = 'Ocultar sem valor';

$_['color_scheme'] = 'Esquema de cores';
$_['scheme_light'] = 'Claro';
$_['scheme_dark'] = 'Escuro';
$_['scheme_auto'] = 'Automático';

$_['refresh_values'] = 'Atualizar valor convertido';
$_['five_minutes'] = 'A cada 5 minutos';
$_['ten_minutes'] = 'A cada 10 minutos';
$_['fifteen_minutes'] = 'A cada 15 minutos';
$_['thirty_minutes'] = 'A cada 30 minutos';
$_['forty_five_minutes'] = 'A cada 45 minutos';
$_['sixty_minutes'] = 'A cada 60 minutos';

$_['order_cancelation_timeout'] = 'Tempo para cancelar o pedido';
$_['fifteen_minutes_cancellation'] = '15 minutos';
$_['thirty_minutes_cancellation'] = '30 minutos';
$_['forty_five_minutes_cancellation'] = '45 minutos';
$_['one_hour'] = '1 hora';
$_['six_hours'] = '6 horas';
$_['twelve_hours'] = '12 horas';
$_['eighteen_hours'] = '18 horas';
$_['twenty_four_hours'] = '24 horas';

$_['entry_geo_zone'] = 'Zona geográfica';
$_['entry_sort_order'] = 'Ordem';

// Error
$_['error_permission'] = 'Aviso: você não tem permissão para modificar o módulo de pagamento CryptAPI';

// Help hints
$_['help_cryptocurrencies'] = 'Se estiver usando o BlockBee você pode escolher se define os endereços de recebimento aqui ou na página de configurações do BlockBee.<br/>Para definir os endereços nas configurações do plugin, você deve selecionar "Address Override" ao criar a chave de API.<br/>Para definir os endereços nas configurações do BlockBee, você NÃO deve selecionar "Address Override" ao criar a chave de API.';
$_['help_cryptocurrency'] = 'Marque a caixa para ativar a criptomoeda';


// Order page - payment tab
$_['text_payment_info'] = 'Informações do pagamento';

$_['disable_conversion'] = 'Desativar conversão';
$_['disable_conversion_warn_bold'] = 'Atenção: esta opção desativa a conversão de preço para TODAS as criptomoedas!';
$_['disable_conversion_warn'] = 'Se ativar isto, o preço não será convertido da moeda da sua loja para a criptomoeda escolhida pelo usuário, e será solicitado pagar o mesmo valor mostrado na loja, independentemente da criptomoeda escolhida';


$_['api_key'] = 'Chave de API BlockBee';
$_['api_key_info'] = "Insira aqui sua chave de API BlockBee. Você pode obter uma no BlockBee. Aviso: se a permissão 'Address Override' não estiver ativada, você deve definir o endereço no painel, caso contrário os pagamentos podem falhar.";

$_['info_icon'] = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="ms-1 bi bi-info-circle" viewBox="0 0 16 16">
                                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                        <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533L8.93 6.588zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
                                    </svg>';
