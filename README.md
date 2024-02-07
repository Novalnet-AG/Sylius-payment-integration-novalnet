<p align="center">
    <a href="https://www.novalnet.com/" target="_blank">
        <img src="https://demo.sylius.com/assets/shop/img/logo.png" />
    </a>
</p>

# Novalnet Payment Plugin for Sylius
The [Sylius](https://www.novalnet.com/modul/) Payment Gateway by Novalnet enables secure integration of payments and payment services for all Sylius shops. The full-service platform allow merchant to [automate payment processing](https://www.novalnet.de/produkte), enrich customer experiences and improve conversion rate through one interface and one contract partner.

## Integration requirements for Sylius
Novalnet [merchant account](https://www.novalnet.de/) is required for processing all international and local payments through this Sylius Payment Gateway. The module is available for in the following languages: EN & DE

## Installation

1. Require with composer

```bash
composer require novalnet/sylius-novalnet-payment-plugin --no-scripts
```
2. When using Symfony flex the proper bundle class will be automatically registered in your bundles.php file. Otherwise, add it to your `config/bundles.php` file:

```php
return [
    // ...
    Novalnet\SyliusNovalnetPaymentPlugin\NovalnetSyliusNovalnetPaymentPlugin::class => ['all' => true],
];
```

3. Import required config in your `config/packages/_sylius.yaml` file:

```yaml
# config/packages/_sylius.yaml

imports:
    ...
    - { resource: "@NovalnetSyliusNovalnetPaymentPlugin/Resources/config/config.yaml" }
```

4. Import the routing in your `config/routes.yaml` file:

```yaml
# config/routes.yaml

novalnet_sylius_plugin:
    resource: "@NovalnetSyliusNovalnetPaymentPlugin/Resources/config/routing.yaml"
```
5. Execute migrations

```
bin/console doctrine:migrations:migrate --env=prod --no-debug
```

6. Install assets

```
bin/console assets:install --env=prod --no-debug
```

7. Clear cache

```
bin/console cache:clear --env=prod --no-debug
```
## Key features of Sylius payment integration
- Easy configuration of all international & local payment methods
- One PCI DSS certified payment platform for all payment services from checkout to collection
- Complete automation of all payment processes
- 60+ risk & payment fraud detection modules to prevent defaults in real time
- Clear overview of payment status from checkout to receivables
- Multilevel claims management with integrated handover to collection and various export functions for the accounting
- Comprehensive fraud prevention solution with more than 60 modules (Machine learning)
- Reporting & analytics dashboards with multiple export options
- Automated e-mail notifications for staying up to date on the payment status
- Automated bookkeeping report in XML, SOAP, CSV, MT940
- Simple seamless integration of the payment module
- Secure SSL-encoded gateways
- Seamless checkout Iframe integration
- Easy confirmation/cancellation of on-hold transactions for selected payment types
- Responsive templates

For detailed documentation and other technical inquiries, please send us an email at [sales@novalnet.de](mailto:sales@novalnet.de)

## Integrated payment methods
- Direct Debit SEPA
- Credit/Debit Cards
- Apple Pay
- Google Pay
- Invoice
- Prepayment
- Invoice with payment guarantee
- Direct Debit SEPA with payment guarantee
- iDEAL
- Sofort
- giropay
- Barzahlen/viacash
- Przelewy24
- eps
- PayPal
- PostFinance Card
- PostFinance E-Finance
- Bancontact
- Multibanco
- Online bank transfer
- Alipay
- WeChat Pay
- Trustly
- Blik

## License
See our License Agreement at: https://www.novalnet.com/payment-plugins-free-license/

## Documentation & Support
For more information about the Sylius Payment Integration by Novalnet, please get in touch with us: sales@novalnet.de or +49 89 9230683-20

Novalnet AG<br>
Zahlungsinstitut (ZAG)<br>
Feringastr. 4<br>
85774 Unterföhring<br>
Deutschland<br>
Website: www.novalnet.de

## Who is Novalnet AG?
Novalnet AG is a [leading financial service institution](https://www.novalnet.de/zahlungsinstitut) offering payment gateways for processing online payments. Operating in the market as a full payment service provider Novalnet AG provides online merchants user-friendly payment integration with all major shop systems and self-programmed sites.

Accept, manage and monitor payments all on one platform with one single contract!

Our SaaS engine is [PIC DSS](https://www.novalnet.de/pci-dss-zertifizierung) certified and designed to enable real-time risk management, secured payments via escrow accounts, efficient receivables management, dynamic member and subscription management, customized payment solutions for various business