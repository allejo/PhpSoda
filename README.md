# PhpSoda 

[![Stable Release](https://img.shields.io/packagist/v/allejo/php-soda.svg)](https://packagist.org/packages/allejo/php-soda) 
[![Build Status](https://img.shields.io/travis/allejo/PhpSoda.svg)](https://travis-ci.org/allejo/PhpSoda) 
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/allejo/PhpSoda.svg?maxAge=2592000)](https://scrutinizer-ci.com/g/allejo/PhpSoda/?branch=master) 
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/allejo/PhpSoda.svg?maxAge=2592000)](https://coveralls.io/r/allejo/PhpSoda?branch=master)

A PHP library for working with the [Socrata API](http://dev.socrata.com/docs/endpoints.html). Provided as an alternative to Socrata's official implementation, this library fills the short-comings of the official library by providing more functionality, a more object-oriented approach, documentation, and plenty of example code.

This library fully supports interacting with the API by getting datasets, handling tokens, handling basic authentication, and OAuth2.0 tokens in order to write or modify datasets.

## Requirements

- PHP 5.6+

## Installation

This library is available on Packagist as [`allejo/php-soda`](https://packagist.org/packages/allejo/php-soda), add it using [Composer](https://getcomposer.org/).

You're not using Composer? Don't worry, this library is also provided as a Phar archive for you include in your code. Get the latest Phar archive from our [Releases](https://github.com/allejo/PhpSoda/releases).

Check out our [wiki article](https://github.com/allejo/PhpSoda/wiki/Installation) if you require assistance with using this library.

## Sample Usage

Here are some quick examples on how PhpSoda works, but there's a lot more you can do. Check out our [wiki](https://github.com/allejo/PhpSoda/wiki) to see everything else.

**Get a dataset**

```php
// Create a client with information about the API to handle tokens and authentication
$sc = new SodaClient("opendata.socrata.com");

// Access a dataset based on the API end point
$ds = new SodaDataset($sc, "pkfj-5jsd");

// Create a SoqlQuery that will be used to filter out the results of a dataset
$soql = new SoqlQuery();

// Write a SoqlQuery naturally
$soql->select("date_posted", "state", "sample_type")
     ->where("state = 'AR'")
     ->limit(1);

// Fetch the dataset into an associative array
$results = $ds->getDataset($soql);
```

**Updating a dataset**

```php
// Create a client with information about the API to handle tokens and authentication
$sc = new SodaClient("opendata.socrata.com", "<token here>", "email@example.com", "password");

// The dataset to upload
$data = file_get_contents("dataset.json");

// Access a dataset based on the API end point
$ds = new SodaDataset($sc, "1234-abcd");

// To upsert a dataset
$ds->upsert($data);

// To replace a dataset
$ds->replace($data);
```

## Getting Help

To get help, see if our [wiki](https://github.com/allejo/PhpSoda/wiki) has any information regarding your question. If the wiki can't help you, you may either [create an issue](https://github.com/allejo/PhpSoda/issues) or stop by IRC; I'm available on IRC as "allejo" so feel free to ping me. I recommend creating an issue in case others have the same question but for quick help, IRC works just fine.

To report a bug or request a feature, please submit an issue.

### IRC

Channel: **#socrata-soda**  
Network: irc.freenode.net

## Thank You

- [Official Socrata PHP Library](https://github.com/socrata/soda-php)
- [C# Socrata Library](https://github.com/CityofSantaMonica/SODA.NET)

## License

[MIT](https://github.com/allejo/PhpSoda/blob/master/LICENSE.md)
