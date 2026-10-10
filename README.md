# Central Payments Engine for phpBB

Central Payments is a powerful and robust payments engine for your phpBB 3.3+ community. It acts as a centralized hub for API keys and communication logic with various banks and crypto gateways. 

If you are building (or using) an extension that needs to charge users (e.g., Donations, Marketplaces, VIP Systems), this extension handles all the heavy lifting of generating checkout links and verifying payments!

## Main Features

- **Centralized API Management**: Configure all your payment gateway keys securely in one place via the ACP.
- **Developer Friendly (API)**: Other extensions don't need to implement gateway logic from scratch. They just send a data payload to this extension, and it returns a ready-to-use checkout link.
- **Unified Payment Verification**: Easily verify if a transaction was paid, is pending, or failed with a single standardized method call.
- **Fiat & Crypto Support**: Fully supports standard fiat currencies (USD, EUR, BRL) and automatically handles conversion for cryptocurrency gateways.
- **Modern Checkout Experience**: Designed to work perfectly with standard redirects or modern HTML modals/iframes.

## Supported Gateways

- **Stripe** (Credit Card, Apple Pay, Google Pay)
- **PayPal** (Account Balance, Credit Card)
- **Mercado Pago** (Pix, Boleto, Credit Card)
- **Coinbase Commerce** (Crypto - USDC/Base)
- **BTCPay Server** (Bitcoin Natively)
- **CoinPayments** (Altcoins)

## Requirements

- phpBB 3.3.0 or higher.
- PHP 7.4 or higher.

## Installation

1. Download the extension.
2. Create the following directory path: `ext/eunaumtenhoid/payments`.
3. Copy the extension files into that directory.
4. Go to your phpBB Administration Control Panel (ACP) > **Customise** > **Manage extensions**.
5. Locate **Central Payments** under the disabled extensions list and click **Enable**.
6. Once enabled, go to the **Extensions** tab to configure your gateways API keys.

## For Developers

Central Payments is designed to be extensible and used by other extensions. Check the `DEVELOPERS.en.md` file in the extension folder to learn how to integrate this payment engine into your own phpBB extensions!

## License

[GNU General Public License v2](license.txt)

---

Did you like this extension? **Buy me a coffee** >> [https://ko-fi.com/eunaumtenhoid](https://ko-fi.com/eunaumtenhoid)
