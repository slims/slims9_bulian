# PHP Scanner

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE)
[![Build Status][ico-travis]][link-travis]
[![Total Downloads][ico-downloads]][link-downloads]

Created by Oscar Otero <http://oscarotero.com> <oom@oscarotero.com> (MIT License)

PHP code scanner to use with [gettext/gettext](https://github.com/php-gettext/Gettext)

## Installation

```
composer require gettext/php-scanner
```

## Usage example

```php
use Gettext\Scanner\PhpScanner;
use Gettext\Generator\PoGenerator;
use Gettext\Translations;

//Create a new scanner, adding a translation for each domain we want to get:
$phpScanner = new PhpScanner(
    Translations::create('domain1'),
    Translations::create('domain2'),
    Translations::create('domain3')
);

//Set a default domain, so any translations with no domain specified, will be added to that domain
$phpScanner->setDefaultDomain('domain1');

//Extract all comments starting with 'i18n:' and 'Translators:'
$phpScanner->extractCommentsStartingWith('i18n:', 'Translators:');

//Scan files
foreach (glob('*.php') as $file) {
    $phpScanner->scanFile($file);
}

//Save the translations in .po files
$generator = new PoGenerator();

foreach ($phpScanner->getTranslations() as $domain => $translations) {
    $generator->generateFile($translations, "locales/{$domain}.po");
}
```

---

Please see [CHANGELOG](CHANGELOG.md) for more information about recent changes and [CONTRIBUTING](CONTRIBUTING.md) for contributing details.

The MIT License (MIT). Please see [LICENSE](LICENSE) for more information.

[ico-version]: https://img.shields.io/packagist/v/gettext/php-scanner.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/php-gettext/PHP-Scanner/master.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/gettext/php-scanner.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/gettext/php-scanner
[link-travis]: https://travis-ci.org/php-gettext/PHP-Scanner
[link-downloads]: https://packagist.org/packages/gettext/php-scanner
