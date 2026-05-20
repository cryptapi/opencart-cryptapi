<?php

// Heading
$_['heading_title'] = 'CryptAPI';

$_['title'] = 'Titre';

$_['blockchain_fees'] = 'Ajouter les frais de la blockchain à la commande';
$_['fees'] = 'Frais de service à facturer';

$_['never'] = 'Jamais';

// Text
$_['text_extension'] = 'Extensions';
$_['text_success'] = 'Succès : vos paramètres CryptAPI ont été mis à jour !';
$_['text_edit'] = 'Modifier CryptAPI';
$_['text_cryptapi'] = '<a target="_BLANK" href="https://cryptapi.io/"><img src="' . HTTP_CATALOG . '/extension/cryptapi/admin/view/image/payment/cryptapi.png" alt="cryptapi" title="cryptapi" style="border: 1px solid #EEEEEE; height:37px" /></a>';
$_['text_connect_cryptapi'] = 'Ce module vous permet d\'accepter les paiements CryptAPI en toute sécurité.';
$_['text_cryptapi_image'] = '<a target="_BLANK" href="https://cryptapi.io/"><img src="' . HTTP_CATALOG . '/extension/cryptapi/admin/view/image/payment/cryptapi.png" alt="cryptapi" title="cryptapi" class="img-fluid" /></a>';
$_['text_cryptapi_suppport'] = 'Si vous avez besoin d\'aide ou avez une suggestion, contactez-nous via le chat en direct sur notre <a target="_blank" href="https://cryptapi.io">site</a>';
$_['text_blockchain_fees'] = 'Ajoute une estimation des frais de la blockchain à la valeur de la commande';
$_['text_fees'] = 'Définissez les frais de service CryptAPI à facturer au client. Remarque : frais destinés à couvrir totalement ou partiellement les frais de CryptAPI.';
$_['text_qrcode'] = 'Choisissez comment afficher le QR code à l\'utilisateur. Sélectionnez un affichage par défaut, ou masquez l\'un des deux.';
$_['text_btc'] = 'Bitcoin';
$_['text_refresh_values'] = 'Le système met automatiquement à jour la valeur de conversion des factures (avec des données en temps réel) toutes les X minutes. Utile lorsque le client met du temps à payer et que la cryptomonnaie choisie est volatile. Attention : régler sur "Jamais" peut causer des problèmes de conversion ; nous recommandons 5 minutes.';
$_['text_order_cancelation_timeout'] = 'Définit le délai dont dispose l\'utilisateur pour payer. Passé ce délai, la commande est marquée "Annulée" et toute valeur payée est ignorée. Attention : si l\'utilisateur continue à envoyer des fonds à l\'adresse générée, le montant continuera à vous être redirigé. Nous déconseillons plus de 1 heure.';

$_['text_tab_general'] = 'Général';
$_['text_tab_crypto'] = 'Cryptomonnaies';
$_['text_tab_advanced'] = 'Avancé';

// Entry
$_['entry_cryptocurrencies'] = 'Cryptomonnaies acceptées';
$_['entry_btc_address'] = $_['text_btc'] . ' Adresse';

$_['entry_order_status'] = 'Statut de la commande';
$_['warning_currency_unsupported'] = 'La devise de votre boutique (%s) n\'est pas dans la liste des devises supportées par CryptAPI. Les estimations de frais blockchain utiliseront USD comme repli. Consultez https://docs.cryptapi.io pour la liste actuelle.';
$_['entry_paid_order_statuses'] = 'Statuts considérés comme payés';
$_['text_paid_order_statuses']  = 'Sélectionnez les statuts de commande qui comptent comme "payé". Les commandes dans ces statuts ne seront pas re-traitées par les callbacks ni interrogées pour des paiements supplémentaires. Maintenez Ctrl/Cmd pour en sélectionner plusieurs.';
$_['entry_status'] = 'Statut';

$_['branding'] = 'Afficher le logo et les crédits CryptAPI sous le QR code';

$_['qrcode_default'] = 'Afficher le QR code';
$_['qrcode'] = 'QR code à afficher';
$_['qrcode_size'] = 'Taille du QR code';
$_['qrcode_without_ammount'] = 'Par défaut sans montant';
$_['qrcode_ammount'] = 'Par défaut avec montant';
$_['qrcode_hide_ammount'] = 'Masquer avec montant';
$_['qrcode_hide_without_ammount'] = 'Masquer sans montant';

$_['color_scheme'] = 'Thème de couleurs';
$_['scheme_light'] = 'Clair';
$_['scheme_dark'] = 'Sombre';
$_['scheme_auto'] = 'Automatique';

$_['refresh_values'] = 'Rafraîchir la valeur convertie';
$_['five_minutes'] = 'Toutes les 5 minutes';
$_['ten_minutes'] = 'Toutes les 10 minutes';
$_['fifteen_minutes'] = 'Toutes les 15 minutes';
$_['thirty_minutes'] = 'Toutes les 30 minutes';
$_['forty_five_minutes'] = 'Toutes les 45 minutes';
$_['sixty_minutes'] = 'Toutes les 60 minutes';

$_['order_cancelation_timeout'] = 'Délai d\'annulation de la commande';
$_['fifteen_minutes_cancellation'] = '15 minutes';
$_['thirty_minutes_cancellation'] = '30 minutes';
$_['forty_five_minutes_cancellation'] = '45 minutes';
$_['one_hour'] = '1 heure';
$_['six_hours'] = '6 heures';
$_['twelve_hours'] = '12 heures';
$_['eighteen_hours'] = '18 heures';
$_['twenty_four_hours'] = '24 heures';

$_['entry_geo_zone'] = 'Zone géographique';
$_['entry_sort_order'] = 'Ordre de tri';

// Error
$_['error_permission'] = 'Attention : vous n\'avez pas l\'autorisation de modifier le module de paiement CryptAPI';

// Help hints
$_['help_cryptocurrencies'] = 'Si vous utilisez BlockBee, vous pouvez choisir de définir les adresses de réception ici ou sur la page des paramètres BlockBee.<br/>Pour définir les adresses dans les paramètres du plugin, sélectionnez "Address Override" lors de la création de la clé API.<br/>Pour définir les adresses dans les paramètres BlockBee, NE sélectionnez PAS "Address Override" lors de la création de la clé API.';
$_['help_cryptocurrency'] = 'Cochez la case pour activer la cryptomonnaie';


// Order page - payment tab
$_['text_payment_info'] = 'Informations de paiement';

$_['disable_conversion'] = 'Désactiver la conversion';
$_['disable_conversion_warn_bold'] = 'Attention : cette option désactive la conversion de prix pour TOUTES les cryptomonnaies !';
$_['disable_conversion_warn'] = 'Si vous cochez ceci, le prix ne sera pas converti depuis la devise de votre boutique vers la cryptomonnaie choisie, et il sera demandé à l\'utilisateur de payer la même valeur que celle affichée sur votre boutique, quelle que soit la cryptomonnaie choisie';


$_['api_key'] = 'Clé API BlockBee';
$_['api_key_info'] = "Insérez ici votre clé API BlockBee. Vous pouvez en obtenir une sur BlockBee. Attention : si l'autorisation 'Address Override' n'est pas activée, vous devez définir l'adresse dans le tableau de bord, sinon les paiements peuvent échouer.";

$_['info_icon'] = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="ms-1 bi bi-info-circle" viewBox="0 0 16 16">
                                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                        <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533L8.93 6.588zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
                                    </svg>';
