<?php
/**
 * Copyright 2014 David T. Sadler
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
 * 
 * For more information about getting your application keys, see:
 * http://devbay.net/sdk/guides/application_keys.html
 */
$config = require __DIR__.'/../configuration.php';

/**
 * The namespaces provided by the SDK.
 */
use \DTS\eBaySDK\HttpClient;
use \DTS\eBaySDK\Shopping\Services;
use \DTS\eBaySDK\Shopping\Types;

/**
 * Create the service object.
 *
 * For more information about creating a service object, see:
 * http://devbay.net/sdk/guides/getting_started.html#service-object
 */
$service = new Services\ShoppingService(new HttpClient\HttpClient(), array(
    'apiVersion' => $config['shoppingApiVersion'],
    'appId' => $config['production']['appId']
));

/**
 * Create the request object.
 *
 * For more information about creating a request object, see:
 * http://devbay.net/sdk/guides/getting_started.html#request-object
 */
$request = new Types\GeteBayTimeRequestType();

/**
 * Send the request to the GeteBayTime service operation.
 *
 * For more information about calling a service operation, see:
 * http://devbay.net/sdk/guides/getting_started.html#service-operation
 */
$response = $service->geteBayTime($request);

/**
 * Output the result of calling the service operation.
 *
 * For more information about working with the service response object, see:
 * http://devbay.net/sdk/guides/getting_started.html#response-object
 */
if ($response->ack === 'Failure') {
    foreach ($response->errors as $error) {
        printf("Error: %s\n", $error->shortMessage);
    }
} else {
    printf("The official eBay time is: %s\n", $response->timestamp->format('H:i (\G\M\T) \o\n l jS Y'));
}
