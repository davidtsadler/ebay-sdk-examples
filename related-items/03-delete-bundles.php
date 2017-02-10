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
use \DTS\eBaySDK\Constants;
use \DTS\eBaySDK\RelatedItemsManagement\Services;
use \DTS\eBaySDK\RelatedItemsManagement\Types;
use \DTS\eBaySDK\RelatedItemsManagement\Enums;

/**
 * Create the service object.
 */
$service = new Services\RelatedItemsManagementService([
    'credentials' => $config['sandbox']['credentials'],
    'authToken'   => $config['sandbox']['authToken'],
    'globalId'    => Constants\GlobalIds::US,
    'sandbox'     => true
]);

/**
 * Create the request object.
 */
$request = new Types\DeleteBundlesRequest();
/**
 * Replace with your actual bundle IDs.
 */
$request->bundleID = [111, 222];

/**
 * Send the request.
 */
$response = $service->deleteBundles($request);

/**
 * Output the result of the operation.
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

foreach ($response->bundleStatus as $bundleStatus) {
    if (isset($bundleStatus->errorMessage)) {
        foreach ($bundleStatus->errorMessage->error as $error) {
            printf(
                "Bundle ID (%s): Error %s: %s\n\n",
                $bundleStatus->bundleID,
                $error->severity=== Enums\ErrorSeverity::C_ERROR ? 'Error' : 'Warning',
                $error->message
            );
        }
    }

    if ($bundleStatus->ack !== 'Failure') {
        printf(
            "Bundle Deleted (%s) %s\n",
            $bundleStatus->bundleID,
            $bundleStatus->bundleName
        );
    }
}
