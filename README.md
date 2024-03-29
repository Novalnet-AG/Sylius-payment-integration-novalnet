# Novalnet payment plugin for Sylius

Novalnet payment plugin for Sylius simplifies your daily work by automating the entire payment process, from checkout till collection. The Sylius payment plugin is designed to help you increase your sales by offering various international and local payment methods.

## Why Sylius with Novalnet? 

Sylius aim to shape the future of eCommerce, leveraging the strength of open source and the power of community-driven development. Our team ensures that Sylius as a framework stays up-to-date, innovative, and tailored to the ever-changing needs of modern eCommerce, making it a leading choice for those seeking a customizable and powerful solution. 

## Advantages of Sylius Payment plugin
- Easy configuration for all payment methods - international and local
- One platform for all payment types and related services
- Complete automation of all payment processes
- More than 50 fraud prevention modules integrated to prevent risk in real-time
- Effortless configuration of risk management with fraud prevention
- Comprehensive affiliate system with automated split conversion of transaction on revenue sharing
- No PCI DSS certification required when using our payment module
- Real-time monitoring of the payment methods & transaction flows 
- Multilevel claims management with integrated handover to collection and various export functions for the accounting
- Automated e-mail notification function concerning payment status reports
- Clear real-time overview and monitoring of payment status
- Automated bookkeeping report in XML, SOAP, CSV, MT940
- Seamless and fast integration of the payment plugin
- Secure SSL- encoded gateways
- Responsive templates

## Supported payment methods

- Direct Debit SEPA
- Direct Debit ACH
- Credit/Debit Cards
- Apple Pay
- Google Pay
- Invoice
- Prepayment
- Invoice with payment guarantee
- Direct Debit SEPA with payment guarantee
- Instalment by Invoice
- Instalment by Direct Debit SEPA
- iDEAL
- Sofort
- giropay
- Barzahlen/viacash
- Przelewy24
- eps
- PayPal
- MB Way
- PostFinance Card
- PostFinance E-Finance
- Bancontact
- Multibanco
- Online bank transfer
- Alipay
- WeChat Pay
- Trustly
- Blik
- Payconiq

## Installation via Composer

#### Follow the below steps and run each command from the shop root directory
 ##### 1. Run the below command to install the payment module
 ```
 composer require novalnet/sylius-novalnet-payment-plugin --no-scripts
 ```
##### 2. When using Symfony flex the proper bundle class will be automatically registered in your bundles.php file. If you're not using Symfony Flex, you'll need to manually add the bundle class to your `config/bundles.php` file
 ```
 return [
// ...
Novalnet\SyliusNovalnetPaymentPlugin\NovalnetSyliusNovalnetPaymentPlugin::class => ['all' => true],
];
 ```
##### 3. Import required configuration settings into your `config/packages/_sylius.yaml` file:
 ```
 # config/packages/_sylius.yaml
imports:
...
- { resource: "@NovalnetSyliusNovalnetPaymentPlugin/Resources/config/config.yaml" }
 ```
##### 4. To import routing configuration into your `config/routes.yaml` file:
 ```
 # config/routes.yaml
novalnet_sylius_plugin:
resource: "@NovalnetSyliusNovalnetPaymentPlugin/Resources/config/routing.yaml"
 ```
##### 5. Install assets
 ```
bin/console assets:install
 ```
##### 6. Clear cache
 ```
bin/console cache:clear
 ```
##### 7. To execute migrations
 ```
bin/console doctrine:migrations:migrate
 ```

## Documentation & Support
For more information about the integration, please get in touch with us at sales@novalnet.de or +49 89 9230683-20 or by contacting us <a href="https://www.novalnet.de/kontakt/sales"> here.</a>

Novalnet AG<br>
Zahlungsinstitut (ZAG)<br>
Feringastr. 4<br>
85774 Unterföhring<br>
Deutschland<br>
Website: <a href= "https://www.novalnet.de/"> www.novalnet.de </a>

## Licenses

As a European Payment institution, Novalnet holds all necessary payment licenses to accept and process payments worldwide. We also comply with European data protection regulations to guarantee advanced data protection worldwide.  

See here for [Freeware License Agreement](https://www.novalnet.com/payment-plugins-free-license/).
