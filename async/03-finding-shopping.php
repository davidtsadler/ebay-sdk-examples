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
 */
$config = require __DIR__.'/../configuration.php';

/**
 * The namespaces provided by the SDK.
 */
use \DTS\eBaySDK\Sdk;
use \DTS\eBaySDK\Constants;
use \DTS\eBaySDK\Finding;
use \DTS\eBaySDK\Shopping;
use GuzzleHttp\Promise;

/**
 * Create the service objects.
 */
$sdk = new Sdk([
    'credentials' => $config['production']['credentials'],
    'globalId'    => Constants\GlobalIds::US
]);
$findingService = $sdk->createFinding();
$shoppingService = $sdk->createShopping();

/**
 * Create the shopping request object.
 */
$shoppingRequest = new Shopping\Types\GetSingleItemRequestType();
$shoppingRequest->IncludeSelector = 'Details';

/**
 * Create the finding request object.
 */
$findingRequest = new Finding\Types\FindItemsAdvancedRequest();
$findingRequest->keywords = 'Harry Potter';
$findingRequest->categoryId = ['617', '171228'];
$findingRequest->sortOrder = 'CurrentPriceHighest';
$findingRequest->paginationInput = new Finding\Types\PaginationInput();
$findingRequest->paginationInput->entriesPerPage = 10;
$findingRequest->paginationInput->pageNumber = 1;

/**
 * Send the request to the findItemsByAdvanced service operation.
 * This will a synchronus request in order to obtain the total number of pages.
 */
$response = $findingService->findItemsAdvanced($findingRequest);
$limit = min($response->paginationOutput->totalPages, 20);

$findingRequests = function () use ($findingService, $findingRequest, $limit) {
    for ($pageNum = 1; $pageNum <= $limit; $pageNum++) {
        $findingRequest->paginationInput->pageNumber = $pageNum;
        yield $findingService->findItemsAdvancedAsync($findingRequest);
    }
};

$shoppingRequests = function ($findingResponse) use ($shoppingService, $shoppingRequest) {
    foreach ($findingResponse->searchResult->item as $item) {
        $shoppingRequest->ItemID = $item->itemId;
        yield $shoppingService->getSingleItemAsync($shoppingRequest);
    }
};

$displayItem = function ($response) {
    if ($response->Ack !== 'Failure') {
        $item = $response->Item;
        printf(
            "%s: %s [%s]\n",
            $item->ItemID,
            $item->Title,
            $item->SKU
        );
        return $item->ItemID;
    }
};

$handleResponses = function ($response) use ($shoppingRequests, $displayItem) {
    Promise\each_limit($shoppingRequests($response), 10, $displayItem)->wait();
};

Promise\each_limit($findingRequests(), 20, $handleResponses)->wait();
