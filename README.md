# PhpSoda [![Build Status](https://travis-ci.org/allejo/PhpSoda.svg?branch=master)](https://travis-ci.org/allejo/PhpSoda)

A PHP library for working with the [Socrata API](http://dev.socrata.com/docs/endpoints.html). Provided as an alternative to Socrata's official implementation, this library takes more of an object-oriented approach to working with the API instead of manually creating requests; autocomplete for IDEs will be more useful.

This library fully supports interacting with the API by getting datasets and handling tokens or authentication in order to write or modify datasets.

## Requires

- PHP 5.3+

## Sample Usage

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
     ->where("state = 'AR'");

// Finally, get the results
$results = $ds->getDataset($soql);
```

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
