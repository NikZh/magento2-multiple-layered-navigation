# Multiple Layered Navigation for Magento 2

This extension gives you an ability to choose a few options of one filterable attribute

## Installation:

First add repository to composer configuration:
```bash
composer config repositories.niks-multiple-layered-navigation vcs git@github.com:NikZh/magento2-multiple-layered-navigation.git
```

Require new package with composer:
```bash
composer require niks/multiple-layered-navigation
```

Enable module
```bash
php bin/magento module:enable Niks_LayeredNavigation
```

Upgrade setup:
```bash
php bin/magento setup:upgrade
```
