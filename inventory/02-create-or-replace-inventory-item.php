<?php
/**
 * Copyright 2017 David T. Sadler
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
use \DTS\eBaySDK\Inventory\Services;
use \DTS\eBaySDK\Inventory\Types;
use \DTS\eBaySDK\Inventory\Enums;

/**
 * Create the service object.
 */
$service = new Services\InventoryService([
    'authorization'    => $config['sandbox']['oauthUserToken'],
    'requestLanguage'  => 'en-US',
    'responseLanguage' => 'en-US',
    'sandbox'          => true
]);

/**
 * Create the request object.
 */
$request = new Types\CreateOrReplaceInventoryItemRestRequest();

/**
 * Note how URI parameters are just properties on the request object.
 */
$request->sku = '123';

$request->availability = new Types\Availability();
$request->availability->shipToLocationAvailability = new Types\ShipToLocationAvailability();
$request->availability->shipToLocationAvailability->quantity = 50;

$request->condition = Enums\ConditionEnum::C_NEW_OTHER;

$request->product = new Types\Product();
$request->product->title = 'GoPro Hero4 Helmet Cam';
$request->product->description = 'New GoPro Hero4 Helmet Cam. Unopened box.';
/**
 * Aspects are specified as an associative array.
 */
$request->product->aspects = [
    'Brand'                => ['GoPro'],
    'Type'                 => ['Helmet/Action'],
    'Storage Type'         => ['Removable'],
    'Recording Definition' => ['High Definition'],
    'Media Format'         => ['Flash Drive (SSD)'],
    'Optical Zoom'         => ['10x', '8x', '4x']
];
$request->product->imageUrls = [
    'http://i.ebayimg.com/images/i/182196556219-0-1/s-l1000.jpg',
    'http://i.ebayimg.com/images/i/182196556219-0-1/s-l1001.jpg',
    'http://i.ebayimg.com/images/i/182196556219-0-1/s-l1002.jpg'
];

/**
 * Send the request.
 */
$response = $service->createOrReplaceInventoryItem($request);

/**
 * Output the result of calling the service operation.
 */
printf("\nStatus Code: %s\n\n", $response->getStatusCode());
if (isset($response->errors)) {
    foreach ($response->errors as $error) {
        printf(
            "%s: %s\n%s\n\n",
            $error->errorId,
            $error->message,
            $error->longMessage
        );
    }
}

if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 400) {
    echo "Success\n";
}
