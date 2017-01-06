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
use \DTS\eBaySDK\Account\Services;
use \DTS\eBaySDK\Account\Types;
use \DTS\eBaySDK\Account\Enums;

/**
 * Create the service object.
 */
$service = new Services\AccountService([
    'authorization' => $config['production']['oauthUserToken']
]);

/**
 * Create the request object.
 */
$request = new Types\GetFulfillmentPoliciesByMarketplaceRestRequest();

/**
 * Note how URI parameters are just properties on the request object.
 */
$request->marketplace_id = Enums\MarketplaceIdEnum::C_EBAY_US;

/**
 * Send the request.
 */
$response = $service->getFulfillmentPoliciesByMarketPlace($request);

/**
 * Output the result of calling the service operation.
 */
echo "====================\nFulfillment Policies\n====================\n";
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
    foreach ($response->fulfillmentPolicies as $policy) {
        printf(
            "(%s) %s: %s\n",
            $policy->fulfillmentPolicyId,
            $policy->name,
            $policy->description
        );
    }
}

/**
 * Create the request object.
 */
$request = new Types\GetPaymentPoliciesByMarketplaceRestRequest();

/**
 * Note how URI parameters are properties on the request object.
 */
$request->marketplace_id = Enums\MarketplaceIdEnum::C_EBAY_US;

/**
 * Send the request.
 */
$response = $service->getPaymentPoliciesByMarketPlace($request);

/**
 * Output the result of calling the service operation.
 */
echo "\n================\nPayment Policies\n================\n";
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
    foreach ($response->paymentPolicies as $policy) {
        printf(
            "(%s) %s: %s\n",
            $policy->paymentPolicyId,
            $policy->name,
            $policy->description
        );
    }
}

/**
 * Create the request object.
 */
$request = new Types\GetReturnPoliciesByMarketplaceRestRequest();

/**
 * Note how URI parameters are properties on the request object.
 */
$request->marketplace_id = Enums\MarketplaceIdEnum::C_EBAY_US;

/**
 * Send the request.
 */
$response = $service->getReturnPoliciesByMarketPlace($request);

/**
 * Output the result of calling the service operation.
 */
echo "\n===============\nReturn Policies\n===============\n";
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
    foreach ($response->returnPolicies as $policy) {
        printf(
            "(%s) %s: %s\n",
            $policy->returnPolicyId,
            $policy->name,
            $policy->description
        );
    }
}
