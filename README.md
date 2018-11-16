# Multiple Layered Navigation for Magento 2

This extension gives you an ability to choose a few options of one filterable attribute

New beta features in 0.1.0 release (disabled in admin panel by default):

- Ajax page update
- Seo friendly URLs
- Price slider

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


## FAQ

#### How can I disable the slider for decimal attributes?

Currently you have to set `frontend_class` in `eav_attribute` to `no-slider` via database. 

