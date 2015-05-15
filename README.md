# PhpSoda [![Build Status](https://magnum.travis-ci.com/allejo/PhpSoda.svg?token=N8pP5syRDREGy8yzpAqR&branch=master)](https://magnum.travis-ci.com/allejo/PhpSoda)

A PHP library for working with the [Socrata API](http://dev.socrata.com/docs/endpoints.html). Compared to Socrata's official implementation, this library takes more of an object-oriented approach to working with the API instead of manually creating requests to submit.

## Requires

- PHP 5.3+

## Examples

This library supports getting datasets, writing datasets, and handling tokens or authentication.

### Get a dataset

```php
// Create a client with information about the API to handle tokens and authentication
$sc = new SodaClient("opendata.socrata.com");

// Access a dataset based on the API end point
$ds = new SodaDataset($sc, "pkfj-5jsd");

// Create a SoqlQuery that will be used to filter out the results of a dataset
$soql = new SoqlQuery();

// Write a SoqlQuery naturally
$soql->select(array("date_posted", "state", "sample_type"))
     ->where("state = 'AR'");

// Finally, get the results
$results = $ds->getDataset($soql);
```

## Thanks To

- [Socrata PHP Wrapper](https://github.com/socrata/soda-php)
- [C# Socrata Library](https://github.com/CityofSantaMonica/SODA.NET)

## License

PhpSoda Copyright (C) 2015 Vladimir "allejo" Jimenez

This library is free software; you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License as published by the Free Software Foundation; either version 2.1 of the License, or (at your option) any later version.

This library is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public License along with this library; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
