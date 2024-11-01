=== Wafi Payment for WooCommerce ===
Contributors: Wafi, Oluebube
Tags: Wafi, payment gateway, bank payment, woocommerce, cashback, united states, dollar
Requires at least: 5.8
Tested up to: 6.4.1
Stable tag: 1.1.0
Requires PHP: 8.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Lower cost of payment processing with Wafi - a secure way to accept payments from your customers.
== Description ==

Wafi lets customers pay securely with their bank account and earn cash back every time. With Wafi you can lower your payment processing fees by half, increase checkout rate, reduce chargebacks and boost repeat purchases. 

* Bank payment
* Many more coming soon


= Why Choose Wafi? =

*Lower processing fees: Every time your customers pay with Wafi, you lower your payment processing fees by at least half compared to card payments.
*One click payment: Once customers connect their bank account to Wafi, they can pay securely with a single click every time and everywhere.
*Loyalty cash back: Wafi gives customers a share of our earnings from payment processing fees. Wafi also enables you to create targeted cash back promotion to boost customer loyalty and increase Average Order Value.
*Enhanced security: Wafi comes built-in with best in class smart security features including 2FA technology and 256 bit encryption ensuring that customers can pay securely every time.

= Plugin Features =
*   __Seamless payment processing__ allows customers pay securely using their bank account with one click.
*   __Easy refunds__ from the WooCommerce order details page. Refund an order directly from the order details page



= Note =

This plugin is meant for woocommerce merchants accepting payments in USD.
In order for us to deliver our services, this plugin will transfer data between your woocommerce website and our servers at wafi.cash. When a customer clicks on 'Pay with Wafi' button, Wafi begins the checkout process by redirecting users to the [Wafi](https://wafi.cash) payment screen where they can authorize the payment using this endpoint 'https://sandbox-api.wafi.cash/v1/checkout-ui' for a test transaction or 'https://api.wafi.cash/v1/checkout-ui for a live transaction. [View Wafi terms of service](https://www.wafi.cash/terms-of-service)
Wafi provides custom checkout buttons, using our custom checkout script 'https://checkoutscript.wafi.cash/checkout.min.js' for a better and more appealing checkout experience.
When the refund button is clicked, [Wafi](https://wafi.cash) makes a call to its refund endpoint 'https://api.wafi.cash/v1/checkout/{{transaction_id}}/refund' to initialize and process the refund. Wafi only processes refunds for live and completed transactions


== Installation ==

*   Go to __WordPress Admin__ > __Plugins__ > __Add New Plugin__ from the left-hand menu
*   In the search box type __Wafi Payment for WooCommerce__
*   Click on Install now when you see __Wafi Payment for WooCommerce__ to install the plugin
*   After installation, __activate__ the plugin.


= Setup and Configuration =
*   Go to __WooCommerce > Settings__ and click on the __Payments__ tab
*   You'll see Wafi listed along with your other payment methods. Click __Set Up__
*   You will see a form like input boxes on the next screen, Provide the information. Below is what each of them is for.

1. __Enable/Disable__ - Check this checkbox to Enable Wafi as a payment method on your store.
2. __Logging__ - Check this checkbox to enable logging to trace and resolve any transaction issues or for general troubleshooting. It’s especially useful to enable it when encountering unexpected issues, and sharing these logs can aid the support team in addressing your queries more effectively. If everything operates smoothly, you may disable logging. 
3. __Test Mode__ - Check this to enable test mode. When selected, this enables you to perform test transaction. Notice also the API Key field changes from "Live" to "Test" as your test API Key not Live API Key is required in this case.
4. __API Key__ - This is an input field where you can paste/type in your API Key which is found on your Wafi Client dashboard. This can be Live API Key or Test API Key dpending on the whether test mode was activated on the step above or not.
7. __Client ID__ - This is an input field where you provide us with your Client ID, which is also available on your Wafi Client dashboard.
8. __Client Name__ - Type in your business name here.
9. Click on __Save Changes__ to update the settings.

Wafi uses webhook to communicate events with and from Wafi. 
This is especially useful to avoid situations where a bad network makes it impossible to verify transactions. 

If you do not find Wafi on the Payment method options, please go through the settings again and ensure that:

*   You have set your store to accept payment in USD, as Wafi only processes USD payments
*   You've checked the __"Enable/Disable"__ checkbox
*   You've entered your __API Key__ in the appropriate field
*   You've clicked on __Save Changes__ during setup

== Frequently Asked Questions ==
= Why should a customer choose Wafi? =
Wafi lets customers pay securely with their bank account, enables them to earn cash back on every payment and gives them the ability to pay with one click everywhere Wafi is accepted.
= How do customers get their cash back? =
Once a customer’s payment is successful, they will see any cash back earned in their Wafi wallet and after 30 business days they are able to spend or withdraw cash backs. Cash backs never expire.
= How do I get paid after my customer makes a successful payment? =
Once a payment is approved, Wafi credits your Wafi wallet immediately and automatically pays out the money to your connected bank account four business days later.

= What Do I Need To Use The Plugin =

*   An active [WooCommerce installation](https://docs.woocommerce.com/document/installing-uninstalling-woocommerce)
*   A Wafi Client account, by signing up on [Wafi Client Dashboard](https://dashboard.wafi.cash)

== Screenshots ==

1. Wafi on installation page

2. Wafi displayed as a payment method on the WooCommerce payment methods page

3. Wafi WooCommerce payment gateway settings page

4. Wafi on WooCommerce Checkout

== Changelog ==
= v1.0.0 =
* First release

= v1.0.1 - March 20, 2024
* Fix: Settings not showing on admin page
* Misc: Updated screenshots

= v1.1.0 - March 25, 2024
* New: Added tested/supported WC version range to the header
