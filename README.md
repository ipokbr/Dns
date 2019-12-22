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
composer require ipokbr\dns
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

$dns->setTrace(); //Set Trace feature - Default: disable

 
```

## Contributing

Contributions to this project are welcome and will be credited.


## Credits

- [Fernando Bertasso Figaro](https://github.com/ipokbr)


## License

The MIT License.


## Changelog

### 1.0.0 - 2019-11-15

- initial release

