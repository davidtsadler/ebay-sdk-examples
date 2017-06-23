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
use \DTS\eBaySDK\Feed\Services;
use \DTS\eBaySDK\Feed\Types;
use \DTS\eBaySDK\Feed\Enums;

/**
 * Create the service object.
 */
$service = new Services\FeedService([
    'authorization' => $config['production']['oauthUserToken'],
    'marketplaceId' => 'EBAY-US'
]);

/**
 * Create the request object.
 */
$request = new Types\GetItemFeedRestRequest();

/**
 * Note how URI parameters are just properties on the request object.
 */
$request->category_id = '15032';
$request->feed_type = 'DAILY';
$request->date = 'yyymmdd';

/**
 * Send the request.
 */
$response = $service->getItemFeed($request);

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

if ($response->getStatusCode() === 200) {
    foreach ($response->itemFeeds as $itemFeed) {
        printf(
            "%s\n%s\n",
            $itemFeed->itemId,
            $itemFeed->title
        );
    }
}
