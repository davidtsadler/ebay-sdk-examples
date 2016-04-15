<?php
/**
 * Copyright 2016 David T. Sadler
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * Include the SDK by using the autoloader from Composer.
 */
require __DIR__.'/../vendor/autoload.php';

/**
 * Include the configuration values.
 *
 * Ensure that you have edited the configuration.php file
 * to include your application keys.
 */
$config = require __DIR__.'/../configuration.php';

/**
 * The namespaces provided by the SDK.
 */
use \DTS\eBaySDK\Sdk;
use \DTS\eBaySDK\Constants;
use \DTS\eBaySDK\Trading;
use \DTS\eBaySDK\Shopping;
use GuzzleHttp\Promise;

/**
 * Create the service objects.
 */
$sdk = new Sdk([
    'credentials' => $config['production']['credentials'],
    'authToken'   => $config['production']['authToken'],
    'siteId'      => Constants\SiteIds::US
]);
$tradingService = $sdk->createTrading();
$shoppingService = $sdk->createShopping();

/**
 * Create the request objects.
 */
$tradingRequest = new Trading\Types\GeteBayOfficialTimeRequestType();
$shoppingRequest = new Shopping\Types\GeteBayTimeRequestType();

/**
 * Create the promises.
 */
$promises = [
    'trading' => $tradingService->geteBayOfficialTimeAsync($tradingRequest),
    'shopping' => $shoppingService->geteBayTimeAsync($shoppingRequest)
];

/**
 * Wait on both promises to complete.
 */
$results = Promise\unwrap($promises);

printf("The official eBay time is: %s\n", $results['trading']->Timestamp->format('H:i (\G\M\T) \o\n l jS F Y'));
printf("The official eBay time is: %s\n", $results['shopping']->Timestamp->format('H:i (\G\M\T) \o\n l jS F Y'));
