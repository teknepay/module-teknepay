# Teknepay Payment plugin for Magento2
Use Teknepay's plugin for Magento 2 to offer frictionless payments online, in-app, and in-store.

# Teknepay Supported Payment Types
At this moment we support payments by Credit Card, Check 21 and EFT.

## Requirements
This plugin supports Magento2 version 2.1 and higher

## Collaboration
We commit all our new features directly into our GitHub repository.
But you can also request or suggest new features or code changes yourself!

## Installation
You can install our plugin through Composer:
```
composer require teknepay/module-teknepay
bin/magento module:enable Teknepay_Teknepay
bin/magento setup:upgrade
```
If you have issues in enabling the module try to recompile the magento code:
```
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy -f
php bin/magento cache:clean
php bin/magento cache:flush
```

## Support
You can create issues on our Magento Repository. In case of specific problems with your account, please contact  <a href="mailto:support@teknepay.com">support@teknepay.com</a>.

## License
GNU GENERAL PUBLIC LICENSE, Version 3 license. For more information, see the LICENSE file.
