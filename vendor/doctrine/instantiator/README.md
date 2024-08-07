# Doctrine Instantiator

This library provides a way of avoiding usage of constructors when instantiating PHP classes.

[![Build Status](https://travis-ci.org/doctrine/instantiator.svg?branch=master)](https://travis-ci.org/doctrine/instantiator)
[![Code Coverage](https://codecov.io/gh/doctrine/instantiator/branch/master/graph/badge.svg)](https://codecov.io/gh/doctrine/instantiator/branch/master)
[![Dependency Status](https://www.versioneye.com/package/php--doctrine--instantiator/badge.svg)](https://www.versioneye.com/package/php--doctrine--instantiator)

[![Latest Stable Version](https://poser.pugx.org/doctrine/instantiator/v/stable.png)](https://packagist.org/packages/doctrine/instantiator)
[![Latest Unstable Version](https://poser.pugx.org/doctrine/instantiator/v/unstable.png)](https://packagist.org/packages/doctrine/instantiator)

## Installation

The suggested installation method is via [composer](https://getcomposer.org/):

```sh
composer require doctrine/instantiator
```

## Usage

The instantiator is able to create new instances of any class without using the constructor or any API of the class
itself:

```php
$instantiator = new \Doctrine\Instantiator\Instantiator();

$instance = $instantiator->instantiate(\My\ClassName\Here::class);
```

## Contributing

Please read the [CONTRIBUTING.md](CONTRIBUTING.md) contents if you wish to help out!

## Credits

This library was migrated from [ocramius/instantiator](https://github.com/Ocramius/Instantiator), which
has been donated to the doctrine organization, and which is now deprecated in favour of this package.
