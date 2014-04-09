# EBAY-SDK-EXAMPLES

This project contains serveral examples of using the [eBay SDK for PHP](https://github.com/davidtsadler/ebay-sdk).

## Requirements

  - PHP 5.3.3 or greater with the following extensions:
    - cURL
    - libxml
  - SSL enabled on the cURL extension so that https requests can be made.

## Installation

1. Download the project.

   ```
   git clone https://github.com/davidtsadler/ebay-sdk-examples.git
   ```

1. From the `ebay-sdk-examples` directory install Composer with:

   ```
   curl -sS https://getcomposer.org/installer | php
   ```

1. Install the dependencies.

   ```
   php composer.phar install
   ```

## Configuration

All the examples load configuration settings from a `configuration.php` file located in the root of the project directory. This file can be created by running the following command inside the `ebay-sdk-examples` directory:

   ```
   cp configuration.php.example configuration.php
   ```

Edit the resulting `configuration.php` file and specify your eBay application keys. Ensure that you enter values for both the sandbox and production enviroments. A guide is available to [help get your application keys](http://devbay.net/sdk/guides/application_keys.html).

**Be careful not to commit the `configuration.php` file into an SCM repository as you risk exposing your eBay application keys to more people than intended.**

## Examples

There are several examples for each service that the SDK supports and they are listed in the `README` file for each service.

1. [Finding](https://github.com/davidtsadler/ebay-sdk-examples/blob/master/finding/README.md)

1. [Trading](https://github.com/davidtsadler/ebay-sdk-examples/blob/master/trading/README.md)

## License

Copyright 2014 [David T. Sadler](http://twitter.com/davidtsadler)

Licensed under the [Apache Public License 2.0](http://www.apache.org/licenses/LICENSE-2.0.html).
