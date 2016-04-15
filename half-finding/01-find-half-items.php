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
use \DTS\eBaySDK\HalfFinding\Services;
use \DTS\eBaySDK\HalfFinding\Types;
use \DTS\eBaySDK\HalfFinding\Enums;

/**
 * Create the service object.
 */
$service = new Services\HalfFindingService([
    'credentials' => $config['production']['credentials'],
    'globalId'    => Constants\GlobalIds::US
]);

/**
 * Create the request object.
 */
$request = new Types\FindItemsRequest();

/**
 * Assign the product ID that we want to search for.
 * This example will use an ISBN as a product ID.
 */
$request->productID = new Types\ProductIDType();
$request->productID->value = '0747532745';
$request->productID->type = 'ISBN';

/**
 * Filter results to only include fixed price items.
 */
$itemFilter = new Types\ItemFilter();
$itemFilter->name = 'ListingType';
$itemFilter->value[] = 'FixedPrice';
$request->itemFilter[] = $itemFilter;

/**
 * Add additional filter to only include items that are $50 or less in the price.
 *
 * Notice that we can take advantage of the fact that the SDK allows object properties to be assigned via the class constructor.
 */
$request->itemFilter[] = new Types\ItemFilter([
    'name' => 'MaxPrice',
    'value' => ['50.00']
]);

/**
 * Sort the results by fixed price.
 */
$request->sortBy = new Types\SortByType();
$request->sortBy->sortOn = 'FixedPrice';
$request->sortBy->sortOrder = 'DECREASING';

/**
 * Limit the results to 10 items per page and start at page 1.
 */
$request->paginationInput = new Types\PaginationInputType();
$request->paginationInput->entriesPerPage = 10;
$request->paginationInput->pageNumber = 1;

/**
 * Send the request.
 */
$response = $service->findHalfItems($request);

/**
 * Output the result of the search.
 */
if (isset($response->errorMessage)) {
    foreach ($response->errorMessage->error as $error) {
        printf(
            "%s: %s\n\n",
            $error->severity=== Enums\ErrorSeverity::C_ERROR ? 'Error' : 'Warning',
            $error->message
        );
    }
}

if (isset($response->product)) {
    printf(
        "%s items found over %s pages found for %s.\n\n",
        $response->paginationOutput->totalEntries,
        $response->paginationOutput->totalPages,
        $response->product->title
    );

    echo "==================\nResults for page 1\n==================\n";

    foreach ($response->product->item as $item) {
        printf(
            "%s: %s %.2f from %s\n",
            $item->itemID,
            $item->price->currencyId,
            $item->price->value,
            $item->seller->userID
        );
    }

    /**
     * Paginate through 2 more pages worth of results.
     */
    $limit = min($response->paginationOutput->totalPages, 3);
    for ($pageNum = 2; $pageNum <= $limit; $pageNum++) {
        $request->paginationInput->pageNumber = $pageNum;

        $response = $service->findHalfItems($request);

        if (isset($response->product)) {
            echo "==================\nResults for page $pageNum\n==================\n";

            foreach ($response->product->item as $item) {
                printf(
                    "%s: %s %.2f from %s\n",
                    $item->itemID,
                    $item->price->currencyId,
                    $item->price->value,
                    $item->seller->userID
                );
            }
        }
    }
}
