#DNS Request and Parser Class

This package contains a class that can perform dns requests, parse answer and return a json document. It can request data directly from Root Servers, and even from authoritative or recursive servers.

```php
$dns = new Dns_Request();

$dns->setRequestType("MX");

$ret =  $dns->DnsRequest("gogle.com");
```
 
## Installation

You can install the package via composer:

```bash
composer require ipok\dns
```

## Usage

The class can fetch these record types: `A`, `CNAME`, `NS`, `SOA`, `MX`, `TXT`.

``` php
$dns = new Dns_request();
$dns->setRequestType("MX");
$dns->DnsRequest("gogle.com");

$dns->setRequestType("CNAME"); //Set the record type

$dns->setServerRequest("8.8.8.8"); //Set server to send the request - Default: none

$dns->unsetRecursive(); //Unset the recursive feature - Default: enable

$dns->setTcp(); //Use tcp protocol (instead of udp) - Default: disable

$dns->unsetTrace(); //Unset Trace feature - Default: enable

Trace starts the search directly from Root Servers and follow the resolution path to the authoritative servers. This is useful when you want to avoid cached data in some server. You may need to disable it when you query a server directly.
 
```

## Contributing

Contributions to this project are welcome and will be credited.


## Credits

- [Fernando Bertasso Figaro](https://github.com/xxxx)


## License

The MIT License.


## Changelog

### 1.0.0 - 2019-11-15

- initial release

