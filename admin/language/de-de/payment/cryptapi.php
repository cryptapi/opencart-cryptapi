<?php

// Heading
$_['heading_title'] = 'CryptAPI';

$_['title'] = 'Titel';

$_['blockchain_fees'] = 'Blockchain-Gebühr zur Bestellung hinzufügen';
$_['fees'] = 'Servicegebühr';

$_['never'] = 'Nie';

// Text
$_['text_extension'] = 'Erweiterungen';
$_['text_success'] = 'Erfolg: Ihre CryptAPI-Daten wurden aktualisiert!';
$_['text_edit'] = 'CryptAPI bearbeiten';
$_['text_cryptapi'] = '<a target="_BLANK" href="https://cryptapi.io/"><img src="' . HTTP_CATALOG . '/extension/cryptapi/admin/view/image/payment/cryptapi.png" alt="cryptapi" title="cryptapi" style="border: 1px solid #EEEEEE; height:37px" /></a>';
$_['text_connect_cryptapi'] = 'Dieses Modul ermöglicht es Ihnen, CryptAPI-Zahlungen sicher anzunehmen.';
$_['text_cryptapi_image'] = '<a target="_BLANK" href="https://cryptapi.io/"><img src="' . HTTP_CATALOG . '/extension/cryptapi/admin/view/image/payment/cryptapi.png" alt="cryptapi" title="cryptapi" class="img-fluid" /></a>';
$_['text_cryptapi_suppport'] = 'Wenn Sie Hilfe benötigen oder einen Vorschlag haben, kontaktieren Sie uns über den Live-Chat auf unserer <a target="_blank" href="https://cryptapi.io">Website</a>';
$_['text_blockchain_fees'] = 'Fügt eine Schätzung der Blockchain-Gebühr zum Bestellwert hinzu';
$_['text_fees'] = 'Legen Sie die CryptAPI-Servicegebühr fest, die Sie dem Kunden berechnen möchten. Hinweis: Gebühr, um die CryptAPI-Gebühren ganz oder teilweise zu decken.';
$_['text_qrcode'] = 'Wählen Sie, wie der QR-Code dem Nutzer angezeigt werden soll. Wählen Sie eine Standardansicht oder verbergen Sie eine der beiden.';
$_['text_btc'] = 'Bitcoin';
$_['text_refresh_values'] = 'Das System aktualisiert den Umrechnungswert der Rechnungen (mit Echtzeitdaten) alle X Minuten automatisch. Nützlich, wenn der Kunde lange braucht, um zu zahlen, und die gewählte Kryptowährung volatil ist. Achtung: "Nie" kann zu Umrechnungsproblemen führen; wir empfehlen 5 Minuten.';
$_['text_order_cancelation_timeout'] = 'Legt die Zeit fest, die der Nutzer für die Zahlung hat. Danach wird die Bestellung als "Storniert" markiert und jeder bezahlte Betrag wird ignoriert. Achtung: Wenn der Nutzer weiterhin an die generierte Adresse zahlt, wird der Betrag weiterhin an Sie weitergeleitet. Wir raten von mehr als 1 Stunde ab.';

$_['text_tab_general'] = 'Allgemein';
$_['text_tab_crypto'] = 'Kryptowährungen';
$_['text_tab_advanced'] = 'Erweitert';

// Entry
$_['entry_cryptocurrencies'] = 'Akzeptierte Kryptowährungen';
$_['entry_btc_address'] = $_['text_btc'] . ' Adresse';

$_['entry_order_status'] = 'Bestellstatus';
$_['warning_currency_unsupported'] = 'Die Währung Ihres Shops (%s) ist nicht in der CryptAPI-Liste der unterstützten Fiat-Währungen enthalten. Blockchain-Gebührenschätzungen verwenden USD als Fallback. Siehe https://docs.cryptapi.io für die aktuelle Liste.';
$_['entry_paid_order_statuses'] = 'Status, die als bezahlt gelten';
$_['text_paid_order_statuses']  = 'Wählen Sie, welche Bestellstatus als "bezahlt" gelten. Bestellungen in diesen Status werden weder von Callbacks weiterverarbeitet noch nach weiteren Zahlungen abgefragt. Strg/Cmd halten für Mehrfachauswahl.';
$_['entry_status'] = 'Status';

$_['branding'] = 'Logo und Credits von CryptAPI unter dem QR-Code anzeigen';

$_['qrcode_default'] = 'QR-Code anzeigen';
$_['qrcode'] = 'Anzuzeigender QR-Code';
$_['qrcode_size'] = 'QR-Code-Größe';
$_['qrcode_without_ammount'] = 'Standard ohne Betrag';
$_['qrcode_ammount'] = 'Standard mit Betrag';
$_['qrcode_hide_ammount'] = 'Mit Betrag ausblenden';
$_['qrcode_hide_without_ammount'] = 'Ohne Betrag ausblenden';

$_['color_scheme'] = 'Farbschema';
$_['scheme_light'] = 'Hell';
$_['scheme_dark'] = 'Dunkel';
$_['scheme_auto'] = 'Automatisch';

$_['refresh_values'] = 'Umrechnungswert aktualisieren';
$_['five_minutes'] = 'Alle 5 Minuten';
$_['ten_minutes'] = 'Alle 10 Minuten';
$_['fifteen_minutes'] = 'Alle 15 Minuten';
$_['thirty_minutes'] = 'Alle 30 Minuten';
$_['forty_five_minutes'] = 'Alle 45 Minuten';
$_['sixty_minutes'] = 'Alle 60 Minuten';

$_['order_cancelation_timeout'] = 'Bestellabbruch-Frist';
$_['fifteen_minutes_cancellation'] = '15 Minuten';
$_['thirty_minutes_cancellation'] = '30 Minuten';
$_['forty_five_minutes_cancellation'] = '45 Minuten';
$_['one_hour'] = '1 Stunde';
$_['six_hours'] = '6 Stunden';
$_['twelve_hours'] = '12 Stunden';
$_['eighteen_hours'] = '18 Stunden';
$_['twenty_four_hours'] = '24 Stunden';

$_['entry_geo_zone'] = 'Geozone';
$_['entry_sort_order'] = 'Sortierreihenfolge';

// Error
$_['error_permission'] = 'Achtung: Sie haben keine Berechtigung, das CryptAPI-Zahlungsmodul zu ändern';

// Help hints
$_['help_cryptocurrencies'] = 'Wenn Sie BlockBee verwenden, können Sie wählen, ob Sie die Empfangsadressen hier oder auf der BlockBee-Einstellungsseite festlegen.<br/>Um die Adressen in den Plugin-Einstellungen festzulegen, wählen Sie "Address Override" beim Erstellen des API-Schlüssels.<br/>Um die Adressen in den BlockBee-Einstellungen festzulegen, wählen Sie "Address Override" NICHT beim Erstellen des API-Schlüssels.';
$_['help_cryptocurrency'] = 'Klicken Sie auf das Kontrollkästchen, um die Kryptowährung zu aktivieren';


// Order page - payment tab
$_['text_payment_info'] = 'Zahlungsinformationen';

$_['disable_conversion'] = 'Umrechnung deaktivieren';
$_['disable_conversion_warn_bold'] = 'Achtung: Diese Option deaktiviert die Preisumrechnung für ALLE Kryptowährungen!';
$_['disable_conversion_warn'] = 'Wenn Sie dies aktivieren, wird der Preis nicht von der Shop-Währung in die vom Nutzer gewählte Kryptowährung umgerechnet, und der Nutzer wird aufgefordert, denselben Wert zu zahlen, der im Shop angezeigt wird, unabhängig von der gewählten Kryptowährung';


$_['api_key'] = 'BlockBee API-Schlüssel';
$_['api_key_info'] = "Geben Sie hier Ihren BlockBee API-Schlüssel ein. Sie können einen bei BlockBee erhalten. Hinweis: Wenn die Berechtigung 'Address Override' nicht aktiviert ist, müssen Sie die Adresse im Dashboard festlegen, sonst können Zahlungen fehlschlagen.";

$_['info_icon'] = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="ms-1 bi bi-info-circle" viewBox="0 0 16 16">
                                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                        <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533L8.93 6.588zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
                                    </svg>';
