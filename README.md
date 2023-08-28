![CryptAPI](https://i.imgur.com/IfMAa7E.png)

# CryptAPI Payment Gateway for OpenCart
Accept cryptocurrency payments on your OpenCart store

### Requirements:

```
OpenCart >= 4.0
```

### Description

Accept payments in Bitcoin, Bitcoin Cash, Litecoin, Ethereum, USDT and Matic directly to your crypto wallet, without any sign-ups or lengthy processes.
All you need is to provide your crypto address.

#### Allow users to pay with crypto directly on your store

The CryptAPI extension enables your OpenCart store to get receive payments in cryptocurrency, with a simple setup and no sign-ups required.

= Accepted cryptocurrencies & tokens include: =

* (BTC) Bitcoin
* (ETH) Ethereum
* (BCH) Bitcoin Cash
* (LTC) Litecoin
* (MATIC) Polygon
* (TRX) Tron
* (BNB) Binance Coin
* (USDT) USDT
* (SHIB) Shiba Inu
* (DOGE) Dogecoin


among many others, for a full list of the supported cryptocurrencies and tokens, check [this page](https://cryptapi.io/cryptocurrencies/).

= Auto-value conversion =

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

#### Why choose CryptAPI?

CryptAPI has no setup fees, no monthly fees, no hidden costs, and you don't even need to sign-up!
Simply set your crypto addresses and you're ready to go. As soon as your customers pay we forward your earnings directly to your own wallet.

CryptAPI has a low 1% fee on the transactions processed. No hidden costs.
For more info on our fees [click here](https://cryptapi.io/fees)

### Installation

1. Open your OpenCart admin
2. Go to Extensions 
3. Upload the .zip file

### Configuration

1. Access your OpenCart Admin Panel
2. Go to Extensions -> Extensions
3. Select "Payments"
4. Scroll down to "CryptAPI"
5. Activate the payment method (if inactive)
6. Select which cryptocurrencies you wish to accept
7. Input your addresses to the cryptocurrencies you selected. This is where your funds will be sent to, so make sure the addresses are correct.
8. Save Changes
9. All done! 

### Cronjob

Some features require a cronjob to work. You need to create one in your hosting that runs every 1 minute. It should call this URL ``YOUR-DOMAIN/index.php?route=extension/cryptapi/payment/cryptapi|cron``.

### Frequently Asked Questions

#### Do I need an API key?

No. You just need to insert your crypto address of the cryptocurrencies you wish to accept. Whenever a customer pays, the money will be automatically and instantly forwarded to your address.

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
* Support CryptAPI Pro
* Minor fixes

#### 3.1.1
* Minor fixes

#### 3.2
* Support for OpenCart 4.0
* Support for BlockBee
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

### Upgrade Notice
* No breaking changes
