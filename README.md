![CryptAPI](https://i.imgur.com/IfMAa7E.png)

# CryptAPI – Crypto Payment Gateway for OpenCart
Accept Bitcoin, Ethereum, USDT, USDC, Solana and other cryptocurrency payments in OpenCart. No sign-up: funds go straight to your wallet.

### Requirements:

```
OpenCart 4.0.0.0 - 4.1.0.4
PHP >= 8.0
```

### Description

Accept payments in Bitcoin, Bitcoin Cash, Litecoin, Ethereum, USDT and Polygon directly to your crypto wallet, without any sign-ups or lengthy processes.
All you need is to provide your crypto address and verify your domain once.

#### Allow users to pay with crypto directly on your store

The CryptAPI extension enables your OpenCart store to receive payments in cryptocurrency, with a simple setup and no sign-ups required.

#### Accepted cryptocurrencies & tokens include:

* (BTC) Bitcoin
* (ETH) Ethereum
* (BCH) Bitcoin Cash
* (LTC) Litecoin
* (TRX) Tron
* (BNB) Binance Coin
* (USDT) USDT
* (USDC) USDC
* (SHIB) Shiba Inu
* (DOGE) Dogecoin
* (POL) Polygon
* (SOL) Solana

among many others, for a full list of the supported cryptocurrencies and tokens, check [this page](https://cryptapi.io/cryptocurrencies/).

Stablecoins include USDT on TRC20 (Tron), ERC20, BEP20, Polygon, Solana, Base and TON, and USDC on ERC20, BEP20, Polygon, Solana, Base and Arbitrum.

#### Features

* Payment page with live payment progress
* QR codes with or without the amount
* Emails the customer the payment details and adds a "Pay now" button to their order page
* Handles partial payments and shows a payment history on the admin order page
* Refreshes the converted value on a schedule you choose
* Cancels unpaid orders after a time you choose
* Can add service and blockchain fees at checkout
* Signed callbacks, checked against CryptAPI's public key
* Light, dark and auto color schemes
* Translated into German, Spanish, French, Italian, Portuguese, Russian and Chinese

#### Auto-value conversion

CryptAPI will attempt to automatically convert the value you set on your store to the cryptocurrency your customer chose.

Exchange rates are fetched every 5 minutes from CoinGecko.

Supported currencies for automatic exchange rates are:

* (XAF) CFA Franc
* (RON) Romanian Leu
* (BGN) Bulgarian Lev
* (HUF) Hungarian Forint
* (CZK) Czech Koruna
* (PHP) Philippine Peso
* (PLN) Poland Zloti
* (UGX) Uganda Shillings
* (MXN) Mexican Peso
* (INR) Indian Rupee
* (HKD) Hong Kong Dollar
* (CNY) Chinese Yuan
* (BRL) Brazilian Real
* (DKK) Danish Krone
* (AED) UAE Dirham
* (JPY) Japanese Yen
* (CAD) Canadian Dollar
* (GBP) GB Pound
* (EUR) Euro
* (USD) US Dollar

If your OpenCart's currency is none of the above, the exchange rates will default to USD.
If you're using OpenCart in a different currency not listed here and need support, please [contact us](https://cryptapi.io) via our live chat.

**Note:** CryptAPI will not exchange your crypto for FIAT or other crypto, just convert the value.

#### Why choose CryptAPI?

CryptAPI has no setup fees, no monthly fees, no hidden costs, and you don't even need to sign-up!
Simply set your crypto addresses, verify your domain and you're ready to go. As soon as your customers pay we forward your earnings directly to your own wallet.

CryptAPI has a low 1% fee on the transactions processed. No hidden costs.
For more info on our fees [click here](https://cryptapi.io/fees/).

### Installation

1. Download `cryptapi.ocmod.zip` from the [Releases](https://github.com/cryptapi/opencart-cryptapi/releases) page. Keep that file name: OpenCart uses it to name the extension.
2. In your OpenCart admin, go to Extensions > Installer
3. Click 'Upload' and select `cryptapi.ocmod.zip` from your computer
4. Click 'Install' next to CryptAPI in the list of installed extensions

### Configuration

1. Access your OpenCart Admin Panel
2. Go to Extensions > Extensions
3. Select "Payments"
4. Scroll down to "CryptAPI"
5. Activate the payment method (if inactive)
6. Select which cryptocurrencies you wish to accept
7. Input your addresses to the cryptocurrencies you selected. This is where your funds will be sent to, so make sure the addresses are correct.
8. Save Changes
9. Verify your store's domain at [cryptapi.io/verify_domain](https://cryptapi.io/verify_domain/), with a DNS TXT record or an HTTPS file. You only need to do this once, but customers can't pay with CryptAPI until you do. When you're done, click "I've verified my domain, hide this notice" on the settings page. This also saves your settings.
10. All done! 

### Cronjob

Some features require a cronjob to work. You need to create one in your hosting that runs every 1 minute. It should call this URL:

```
https://YOUR-DOMAIN/index.php?route=extension/cryptapi/payment/cryptapi|cron&secret=YOUR_CRON_SECRET
```

Over HTTP from a non-loopback host (or from any host behind a reverse proxy) a matching `payment_cryptapi_cron_secret` (set in the extension settings) is required — append it as `&secret=YOUR_CRON_SECRET`. CLI (`php index.php ...`) and direct-loopback-without-proxy cron are exempt and need no secret.

### Frequently Asked Questions

#### Do I need an API key?

No. You just need to insert your crypto address of the cryptocurrencies you wish to accept and verify your domain once (see below). Whenever a customer pays, the money will be automatically and instantly forwarded to your address.

#### Do I need to verify my domain?

Yes. CryptAPI only creates payment addresses for verified domains, so customers can't pay until you verify the domain your store runs on. You only need to do it once, at [cryptapi.io/verify_domain](https://cryptapi.io/verify_domain/), with a DNS TXT record or an HTTPS file. You don't need an account.

A DNS record also covers your subdomains. An HTTPS file covers only the exact host, so `www.example.com` and `example.com` count as two domains. If you run more than one store, verify each store's domain. Payment addresses you already created keep working. Our [step-by-step guide](https://docs.cryptapi.io/kb/developers/how-to-verify-your-callback-domain) shows how.

#### How long do payments take before they're confirmed?

This depends on the cryptocurrency you're using. Bitcoin usually takes up to 11 minutes, Ethereum usually takes less than a minute.

#### Is there a minimum for a payment?

Yes, the minimums change according to the chosen cryptocurrency and can be checked [here](https://cryptapi.io/cryptocurrencies).
If the OpenCart order total is below the chosen cryptocurrency's minimum, an error is raised to the user.

#### Where can I find more documentation on your service?

You can find more documentation about our service on our [get started](https://cryptapi.io/get_started) page, our [technical documentation](https://docs.cryptapi.io/) page or our [resources](https://cryptapi.io/ecommerce/) page.
If there's anything else you need that is not covered on those pages, please get in touch with us, we're here to help you!

#### Where can I get support? 

The easiest and fastest way is via our live chat on our [website](https://cryptapi.io) or via our [contact form](https://cryptapi.io/contacts/).

### Changelog 

#### 1.0
* Initial release.

####  2.0
* New coins
* Updated codebase
* New API URL

#### 3.0 
* New settings and color schemes to fit dark mode
* New settings to add CryptAPI's services fees to the checkout
* New settings to add blockchain fees to the checkout
* Upgrade the settings
* Added a history of transactions to the order payment page
* Better handling of partial payments
* Disable QR Code with value in certain currencies due to some wallets not supporting it
* Minor fixes
* UI Improvements

#### 3.1
* Minor fixes

#### 3.1.1
* Minor fixes

#### 3.2
* Support for OpenCart 4.0
* New e-mail once a order is done with a link for payment
* New settings layout
* Minor fixes

#### 3.2.1
* Minor fixes

#### 3.2.2
* Minor fixes

#### 3.2.3
* Minor fixes

#### 3.2.4
* Minor fixes and improvements

#### 3.2.5
* Minor fixes

#### 3.2.6
* Minor fixes

#### 3.2.7
* Add new choices for order cancellation.
* Minor bugfixes

#### 3.3.0
* OpenCart 4.x compatibility fixes.
* Improved webhook security with signature verification.
* New admin warning for unsupported store currencies.
* Various bugfixes and improvements.
* Added translations

#### 3.4.0
* Bugfixes and security improvements

#### 3.5.0
* Bugfixes

#### 3.6.0
* Domain verification notice
* Button to hide the notice

### Upgrade Notice
* After upgrading, open the CryptAPI settings page once. This repairs the extension's events.
* From 3.6.0, the settings page shows a domain verification notice until you click "I've verified my domain, hide this notice". If you haven't verified your domain yet, see [Do I need to verify my domain?](#do-i-need-to-verify-my-domain)
