# PhpSoda [![Stable Release](https://img.shields.io/packagist/v/allejo/php-soda.svg)](https://packagist.org/packages/allejo/php-soda) [![Build Status](https://travis-ci.org/allejo/PhpSoda.svg?branch=master)](https://travis-ci.org/allejo/PhpSoda) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/allejo/PhpSoda/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/allejo/PhpSoda/?branch=master) [![Coverage Status](https://coveralls.io/repos/allejo/PhpSoda/badge.svg?branch=master)](https://coveralls.io/r/allejo/PhpSoda?branch=master)

A PHP library for working with the [Socrata API](http://dev.socrata.com/docs/endpoints.html). Provided as an alternative to Socrata's official implementation, this library takes more of an object-oriented approach to working with the API instead of manually creating requests and aims to fill some of the short-comings of the official library.

This library fully supports interacting with the API by getting datasets and handling tokens/authentication in order to write or modify datasets.

## Requirements

- PHP 5.3+

## Installation

This library is on Packagist as [`allejo/php-soda`](https://packagist.org/packages/allejo/php-soda), add it using [Composer](https://getcomposer.org/).

Check out our [wiki article](https://github.com/allejo/PhpSoda/wiki/Installation) if you require assistance.

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
$sc = new SodaClient("opendata.socrata.com", "<token here>", "email@example.com", "muffin button");

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

PhpSoda - A PHP library for the Socrata API  
Copyright (C) 2015 Vladimir "allejo" Jimenez

This library is free software; you can redistribute it and/or
modify it under the terms of the GNU Lesser General Public
License as published by the Free Software Foundation; either
version 2.1 of the License, or (at your option) any later version.

This library is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public
License along with this library; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
