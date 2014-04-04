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
use \DTS\eBaySDK\Constants;
use \DTS\eBaySDK\Finding\Services;
use \DTS\eBaySDK\Finding\Types;

/**
 * Create the service object.
 *
 * For more information about creating a service object, see:
 * http://devbay.net/sdk/guides/getting_started.html#service-object
 */
$service = new Services\FindingService(new HttpClient\HttpClient(), array(
    'appId' => $config['production']['appId'],
    'apiVersion' => $config['findingApiVersion'],
    'globalId' => Constants\GlobalIds::US
));

/**
 * Create the request object.
 *
 * For more information about creating a request object, see:
 * http://devbay.net/sdk/guides/getting_started.html#request-object
 */
$request = new Types\FindItemsByKeywordsRequest();

/**
 * Assign the keywords.
 */
$request->keywords = 'Harry Potter';

/**
 * Send the request to the findItemsByKeywords service operation.
 *
 * For more information about calling a service operation, see:
 * http://devbay.net/sdk/guides/getting_started.html#service-operation
 */
$response = $service->findItemsByKeywords($request);

/**
 * Output the result of the search.
 *
 * For more information about working with the service response object, see:
 * http://devbay.net/sdk/guides/getting_started.html#response-object
 */
if ($response->ack === 'Failure') {
    foreach ($response->errors as $error) {
        printf("Error: %s\n", $error->shortMessage);
    }
} else {
    foreach ($response->searchResult->item as $item) {
        printf("(%s) %s: %s %.2f\n",
            $item->itemId,
            $item->title,
            $item->sellingStatus->currentPrice->currencyId,
            $item->sellingStatus->currentPrice->value
        );
    }
}
