# Phai - Application Insights PHP middleware

Phai is PSR-15 compliant middleware for [Application Insights](https://docs.microsoft.com/ja-jp/azure/azure-monitor/app/app-insights-overview).

## Installation

Install the latest version with

```
$ composer require kaz29/phai:dev-master
```

## Basic Usage

## Framework Integrations

Frameworks or libraries using PSR-15 can be used very easily with this middleware.

### Slim3

- [Slim3 example](https://github.com/kaz29/phai/tree/master/examples/slim3)

#### How to setup

```
$ cd examples/slim3
$ composer install
$ env APPLICATION_INSIGHTS_KEY='Your application insights "Instrumentation Key"' php -S localhost:8080 -t public public/index.php
```

### CakePHP

- [CakePHP example](https://github.com/kaz29/phai/tree/master/examples/cakephp)

#### Hot to setup

```
$ cd examples/cakephp
$ composer install
$ env APPLICATION_INSIGHTS_KEY='Your application insights "Instrumentation Key"' ./bin/cake server 
```

### Laravel

TBD

## Author

Kazuhiro Watanabe - cyo [at] mac.com - [https://twitter.com/kaz_29](https://twitter.com/kaz_29)

## License

Phai is licensed under the MIT License - see the LICENSE file for details

