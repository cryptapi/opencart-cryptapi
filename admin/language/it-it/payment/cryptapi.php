<?php

// Heading
$_['heading_title'] = 'CryptAPI';

$_['title'] = 'Titolo';

$_['blockchain_fees'] = 'Aggiungere la commissione della blockchain all\'ordine';
$_['fees'] = 'Commissione di servizio da applicare';

$_['never'] = 'Mai';

// Text
$_['text_extension'] = 'Estensioni';
$_['text_success'] = 'Successo: i tuoi dati CryptAPI sono stati aggiornati!';
$_['text_edit'] = 'Modifica CryptAPI';
$_['text_cryptapi'] = '<a target="_BLANK" href="https://cryptapi.io/"><img src="' . HTTP_CATALOG . '/extension/cryptapi/admin/view/image/payment/cryptapi.png" alt="cryptapi" title="cryptapi" style="border: 1px solid #EEEEEE; height:37px" /></a>';
$_['text_connect_cryptapi'] = 'Questo modulo ti permette di accettare pagamenti CryptAPI in sicurezza.';
$_['text_cryptapi_image'] = '<a target="_BLANK" href="https://cryptapi.io/"><img src="' . HTTP_CATALOG . '/extension/cryptapi/admin/view/image/payment/cryptapi.png" alt="cryptapi" title="cryptapi" class="img-fluid" /></a>';
$_['text_cryptapi_suppport'] = 'Se hai bisogno di aiuto o suggerimenti, contattaci tramite la live chat sul nostro <a target="_blank" href="https://cryptapi.io">sito</a>';
$_['text_blockchain_fees'] = 'Aggiunge una stima della commissione blockchain al valore dell\'ordine';
$_['text_fees'] = 'Imposta la commissione di servizio CryptAPI da addebitare al cliente. Nota: commissione per coprire totalmente o parzialmente le commissioni di CryptAPI.';
$_['text_qrcode'] = 'Scegli come mostrare il QR code all\'utente. Seleziona un\'opzione predefinita da mostrare per prima, o nascondi uno dei due.';
$_['text_btc'] = 'Bitcoin';
$_['text_refresh_values'] = 'Il sistema aggiorna automaticamente il valore di conversione delle fatture (con dati in tempo reale) ogni X minuti. Utile quando il cliente impiega tempo a pagare e la criptovaluta scelta è volatile. Attenzione: impostare "Mai" può causare problemi di conversione; consigliamo 5 minuti.';
$_['text_order_cancelation_timeout'] = 'Definisce il tempo a disposizione dell\'utente per pagare l\'ordine. Allo scadere, l\'ordine viene marcato come "Annullato" e qualsiasi importo pagato verrà ignorato. Attenzione: se l\'utente continua a inviare denaro all\'indirizzo generato, il valore continuerà a esserti reindirizzato. Sconsigliamo più di 1 ora.';

$_['text_tab_general'] = 'Generale';
$_['text_tab_crypto'] = 'Criptovalute';
$_['text_tab_advanced'] = 'Avanzato';

// Entry
$_['entry_cryptocurrencies'] = 'Criptovalute accettate';
$_['entry_btc_address'] = $_['text_btc'] . ' Indirizzo';

$_['entry_order_status'] = 'Stato dell\'ordine';
$_['warning_currency_unsupported'] = 'La valuta del tuo negozio (%s) non è nell\'elenco delle valute supportate da CryptAPI. Le stime delle commissioni blockchain useranno USD come ripiego. Consulta https://docs.cryptapi.io per l\'elenco aggiornato.';
$_['entry_paid_order_statuses'] = 'Stati considerati come pagati';
$_['text_paid_order_statuses']  = 'Seleziona gli stati dell\'ordine considerati come "pagato". Gli ordini in questi stati non verranno rielaborati dai callback né interrogati per ulteriori pagamenti. Tieni premuto Ctrl/Cmd per selezionarne più di uno.';
$_['entry_status'] = 'Stato';

$_['branding'] = 'Mostrare il logo e i crediti CryptAPI sotto il QR code';

$_['qrcode_default'] = 'Mostra QR code';
$_['qrcode'] = 'QR code da mostrare';
$_['qrcode_size'] = 'Dimensione del QR code';
$_['qrcode_without_ammount'] = 'Predefinito senza importo';
$_['qrcode_ammount'] = 'Predefinito con importo';
$_['qrcode_hide_ammount'] = 'Nascondi con importo';
$_['qrcode_hide_without_ammount'] = 'Nascondi senza importo';

$_['color_scheme'] = 'Schema colori';
$_['scheme_light'] = 'Chiaro';
$_['scheme_dark'] = 'Scuro';
$_['scheme_auto'] = 'Automatico';

$_['refresh_values'] = 'Aggiorna valore convertito';
$_['five_minutes'] = 'Ogni 5 minuti';
$_['ten_minutes'] = 'Ogni 10 minuti';
$_['fifteen_minutes'] = 'Ogni 15 minuti';
$_['thirty_minutes'] = 'Ogni 30 minuti';
$_['forty_five_minutes'] = 'Ogni 45 minuti';
$_['sixty_minutes'] = 'Ogni 60 minuti';

$_['order_cancelation_timeout'] = 'Tempo per annullare l\'ordine';
$_['fifteen_minutes_cancellation'] = '15 minuti';
$_['thirty_minutes_cancellation'] = '30 minuti';
$_['forty_five_minutes_cancellation'] = '45 minuti';
$_['one_hour'] = '1 ora';
$_['six_hours'] = '6 ore';
$_['twelve_hours'] = '12 ore';
$_['eighteen_hours'] = '18 ore';
$_['twenty_four_hours'] = '24 ore';

$_['entry_geo_zone'] = 'Zona geografica';
$_['entry_sort_order'] = 'Ordinamento';

// Error
$_['error_permission'] = 'Attenzione: non hai il permesso di modificare il modulo di pagamento CryptAPI';

// Help hints
$_['help_cryptocurrencies'] = 'Se stai usando BlockBee puoi scegliere se impostare gli indirizzi di ricezione qui sotto o nella pagina delle impostazioni di BlockBee.<br/>Per impostare gli indirizzi nelle impostazioni del plugin, seleziona "Address Override" durante la creazione della chiave API.<br/>Per impostare gli indirizzi nelle impostazioni di BlockBee, NON selezionare "Address Override" durante la creazione della chiave API.';
$_['help_cryptocurrency'] = 'Seleziona la casella per attivare la criptovaluta';


// Order page - payment tab
$_['text_payment_info'] = 'Informazioni di pagamento';

$_['disable_conversion'] = 'Disattivare conversione';
$_['disable_conversion_warn_bold'] = 'Attenzione: questa opzione disattiva la conversione del prezzo per TUTTE le criptovalute!';
$_['disable_conversion_warn'] = 'Se attivi questa opzione, il prezzo non verrà convertito dalla valuta del tuo negozio alla criptovaluta scelta dall\'utente, e all\'utente verrà richiesto di pagare lo stesso valore mostrato sul negozio, indipendentemente dalla criptovaluta scelta';


$_['api_key'] = 'Chiave API BlockBee';
$_['api_key_info'] = "Inserisci qui la tua chiave API BlockBee. Puoi ottenerne una su BlockBee. Attenzione: se il permesso 'Address Override' non è attivato, devi impostare l'indirizzo nel pannello, altrimenti i pagamenti potrebbero non andare a buon fine.";

$_['info_icon'] = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="ms-1 bi bi-info-circle" viewBox="0 0 16 16">
                                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                        <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533L8.93 6.588zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
                                    </svg>';
