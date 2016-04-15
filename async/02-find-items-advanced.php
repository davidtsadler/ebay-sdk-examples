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
use \DTS\eBaySDK\Constants;
use \DTS\eBaySDK\Finding\Services;
use \DTS\eBaySDK\Finding\Types;
use \DTS\eBaySDK\Finding\Enums;
use GuzzleHttp\Promise;

/**
 * Create the service object.
 */
$service = new Services\FindingService([
    'credentials' => $config['production']['credentials'],
    'globalId'    => Constants\GlobalIds::US
]);

/**
 * Create the request object.
 */
$request = new Types\FindItemsAdvancedRequest();
$request->keywords = 'Harry Potter';
$request->categoryId = ['617', '171228'];
$request->sortOrder = 'CurrentPriceHighest';
$request->paginationInput = new Types\PaginationInput();
$request->paginationInput->entriesPerPage = 10;
$request->paginationInput->pageNumber = 1;

/**
 * Send the request.
 * This is a synchronus request to obtain the total number of pages.
 */
$response = $service->findItemsAdvanced($request);

if (isset($response->errorMessage)) {
    foreach ($response->errorMessage->error as $error) {
        printf(
            "%s: %s\n\n",
            $error->severity=== Enums\ErrorSeverity::C_ERROR ? 'Error' : 'Warning',
            $error->message
        );
    }
}

/**
 * Output the result of the search.
 */
printf(
    "%s items found over %s pages.\n\n",
    $response->paginationOutput->totalEntries,
    $response->paginationOutput->totalPages
);

echo "==================\nResults for page 1\n==================\n";

if ($response->ack !== 'Failure') {
    foreach ($response->searchResult->item as $item) {
        printf(
            "(%s) %s: %s %.2f\n",
            $item->itemId,
            $item->title,
            $item->sellingStatus->currentPrice->currencyId,
            $item->sellingStatus->currentPrice->value
        );
    }
}

/**
 * Paginate through upto 20 more pages worth of results.
 * The requests for these pages will be made concurrently.
 */
$limit = min($response->paginationOutput->totalPages, 20);
$promises = [];
for ($pageNum = 2; $pageNum <= $limit; $pageNum++) {
    $request->paginationInput->pageNumber = $pageNum;
    $promises[$pageNum] = $service->findItemsAdvancedAsync($request);
}

/**
 * Wait on all promises to complete.
 */
$results = Promise\unwrap($promises);

foreach ($results as $pageNum => $response) {
    echo "==================\nResults for page $pageNum\n==================\n";

    if ($response->ack !== 'Failure') {
        foreach ($response->searchResult->item as $item) {
            printf(
                "(%s) %s: %s %.2f\n",
                $item->itemId,
                $item->title,
                $item->sellingStatus->currentPrice->currencyId,
                $item->sellingStatus->currentPrice->value
            );
        }
    }
}
