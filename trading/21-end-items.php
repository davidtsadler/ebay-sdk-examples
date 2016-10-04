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
use \DTS\eBaySDK\Trading\Services;
use \DTS\eBaySDK\Trading\Types;
use \DTS\eBaySDK\Trading\Enums;

/**
 * Create the service object.
 */
$service = new Services\TradingService([
    'credentials' => $config['sandbox']['credentials'],
    'sandbox'     => true,
    'siteId'      => Constants\SiteIds::US
]);

/**
 * Create the request object.
 */
$request = new Types\EndItemsRequestType();

/**
 * An user token is required when using the Trading service.
 */
$request->RequesterCredentials = new Types\CustomSecurityHeaderType();
$request->RequesterCredentials->eBayAuthToken = $config['sandbox']['authToken'];

/**
 * Tell eBay which items we are ending and why.
 */
$endItem = new Types\EndItemRequestContainerType();
$endItem->MessageID = '1ABC';
$endItem->ItemID = '1111111111';
$endItem->EndingReason = Enums\EndReasonCodeType::C_NOT_AVAILABLE;
$request->EndItemRequestContainer[] = $endItem;

$endItem = new Types\EndItemRequestContainerType();
$endItem->MessageID = '2DEF';
$endItem->ItemID = '2222222222';
$endItem->EndingReason = Enums\EndReasonCodeType::C_INCORRECT;
$request->EndItemRequestContainer[] = $endItem;

$endItem = new Types\EndItemRequestContainerType();
$endItem->MessageID = '3GHI';
$endItem->ItemID = '3333333333';
$endItem->EndingReason = Enums\EndReasonCodeType::C_LOST_OR_BROKEN;
$request->EndItemRequestContainer[] = $endItem;

/**
 * Send the request.
 */
$response = $service->endItems($request);

/**
 * Output the result of calling the service operation.
 */
if (isset($response->Errors)) {
    foreach ($response->Errors as $error) {
        printf(
            "%s: %s\n%s\n\n",
            $error->SeverityCode === Enums\SeverityCodeType::C_ERROR ? 'Error' : 'Warning',
            $error->ShortMessage,
            $error->LongMessage
        );
    }
}

foreach ($response->EndItemResponseContainer as $endItem) {
    if (isset($endItem->Errors)) {
        foreach ($endItem->Errors as $error) {
            printf(
                "[%s] %s: %s\n%s\n\n",
                $endItem->CorrelationID,
                $error->SeverityCode === Enums\SeverityCodeType::C_ERROR ? 'Error' : 'Warning',
                $error->ShortMessage,
                $error->LongMessage
            );
        }
    } else {
        printf("[%s] The item was ended at: %s\n", $endItem->EndTime->format('H:i (\G\M\T) \o\n l jS F Y'));
    }
}
