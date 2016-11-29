# Changelog

## 1.0.1

**Changes**

- Enforce TLS 1.2, per [Socrata's announcement](https://github.com/allejo/PhpSoda/commit/63d01d48b51a6a20b0e29fcf1c5f739d78c21aa3)

## 1.0.0

**Fixes**

- SoQL queries with aggregate functions no longer fail

**Changes**

- A standard **SoqlQuery** no longer automatically selects `:id` and sorts in ascending order
- Dropped support for PHP 5.3, 5.4, 5.5 due to [Socrata's new SNI requirement](https://dev.socrata.com/changelog/2016/08/24/sni-now-required-for-https-connections.html)
- Add support for `SoqlQuery::having()`
- phpDoc links in SoQL have been updated to Socrata's new URL patterns

## 0.1.3

**Changes**

- Remove limit of 50,000 in the SoqlQuery::limit(); older datasets will simply throw an exception with an error message stating the limit.
- API 2.0 and 2.1 datasets can now be differentiated with SodaDataset::getApiVersion()
    - This change also changes the return type of this function from int to double. Unless you explicitly used is_int in your code, this won't break any code.

## 0.1.2

**New**

- Introduced SodaDataset::getRow()
- Introduced SodaDataset::deleteRow()
- The **SodaException** now gives access to the JSON object returned with all of the errors

**Fixes**

- Use OAuth2.0 token when fetching a dataset's metadata

**Changes**

- Visibility of some functions in the **UrlQuery** class have changed
- More documentation has been added
- This library is now licensed as [MIT](https://github.com/allejo/PhpSoda/blob/master/LICENSE.md)

## 0.1.1

**New**

- Introduced SodaClient:: setOAuth2Token() function

**Fixes**

- URL encode the needle in a fullTextSearch

**Changes**

- Follow URL encoding spec according to RFC 3986
- Move features from CsvConverter to parent class

## 0.1.0

Initial release of PhpSoda

**Featuers**

- Tokens & basic authentication
- Simple filters
- SoQL queries
- Dataset metadata
