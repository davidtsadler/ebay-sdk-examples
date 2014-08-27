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
 * http://devbay.net/sdk/guides/application-keys/
 */
$config = require __DIR__.'/../configuration.php';

/**
 * The namespaces provided by the SDK.
 */
use \DTS\eBaySDK\Constants;
use \DTS\eBaySDK\Finding\Services;
use \DTS\eBaySDK\Finding\Types;
use \DTS\eBaySDK\Finding\Enums;

/**
 * Create the service object.
 *
 * For more information about creating a service object, see:
 * http://devbay.net/sdk/guides/getting-started/#service-object
 */
$service = new Services\FindingService(array(
    'appId' => $config['production']['appId'],
    'apiVersion' => $config['findingApiVersion'],
    'globalId' => Constants\GlobalIds::US
));

/**
 * Create the request object.
 *
 * For more information about creating a request object, see:
 * http://devbay.net/sdk/guides/getting-started/#request-object
 */
$request = new Types\FindItemsByProductRequest();

/**
 * Assign the product ID that we want to search for.
 * This example will use a UPC as a product ID.
 * NOTE: eBay only allow a UPC value for products in the Music (e.g. CDs), DVD and Movie, and Video Game categories.
 * Using a UPC value for any other product will result in no items been returned.
 */
$productId = new Types\ProductId();
$productId->value = '085392246724';
$productId->type = 'UPC';
$request->productId = $productId;

/**
 * Filter results to only include auction items or auctions with buy it now.
 */
$itemFilter = new Types\ItemFilter();
$itemFilter->name = 'ListingType';
$itemFilter->value[] = 'Auction';
$itemFilter->value[] = 'AuctionWithBIN';
$request->itemFilter[] = $itemFilter;

/**
 * Add additional filters to only include items that fall in the price range of $1 to $10.
 *
 * Notice that we can take advantage of the fact that the SDK allows object properties to be assigned via the class constructor.
 */
$request->itemFilter[] = new Types\ItemFilter(array(
    'name' => 'MinPrice',
    'value' => array('1.00')
));

$request->itemFilter[] = new Types\ItemFilter(array(
    'name' => 'MaxPrice',
    'value' => array('10.00')
));

/**
 * Sort the results by current price.
 */
$request->sortOrder = 'CurrentPriceHighest';

/**
 * Limit the results to 10 items per page and start at page 1.
 */
$request->paginationInput = new Types\PaginationInput();
$request->paginationInput->entriesPerPage = 10;
$request->paginationInput->pageNumber = 1;

/**
 * Send the request to the findItemsByProduct service operation.
 *
 * For more information about calling a service operation, see:
 * http://devbay.net/sdk/guides/getting-started/#service-operation
 */
$response = $service->findItemsByProduct($request);

if (isset($response->errorMessage)) {
    foreach ($response->errorMessage->error as $error) {
        printf("%s: %s\n\n",
            $error->severity=== Enums\ErrorSeverity::C_ERROR ? 'Error' : 'Warning',
            $error->message
        );
    }
}

/**
 * Output the result of the search.
 *
 * For more information about working with the service response object, see:
 * http://devbay.net/sdk/guides/getting-started/#response-object
 */
printf("%s items found over %s pages.\n\n",
    $response->paginationOutput->totalEntries,
    $response->paginationOutput->totalPages
);

echo "==================\nResults for page 1\n==================\n";

if ($response->ack !== 'Failure') {
    foreach ($response->searchResult->item as $item) {
        printf("(%s) %s: %s %.2f\n",
            $item->itemId,
            $item->title,
            $item->sellingStatus->currentPrice->currencyId,
            $item->sellingStatus->currentPrice->value
        );
    }
}

/**
 * Paginate through 2 more pages worth of results.
 */
$limit = min($response->paginationOutput->totalPages, 3);
for ($pageNum = 2; $pageNum <= $limit; $pageNum++ ) {
    $request->paginationInput->pageNumber = $pageNum;

    $response = $service->findItemsByProduct($request);

    echo "==================\nResults for page $pageNum\n==================\n";

    if ($response->ack !== 'Failure') {
        foreach ($response->searchResult->item as $item) {
            printf("(%s) %s: %s %.2f\n",
                $item->itemId,
                $item->title,
                $item->sellingStatus->currentPrice->currencyId,
                $item->sellingStatus->currentPrice->value
            );
        }
    }
}
