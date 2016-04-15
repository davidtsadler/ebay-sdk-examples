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
use \DTS\eBaySDK\BusinessPoliciesManagement\Services;
use \DTS\eBaySDK\BusinessPoliciesManagement\Types;

/**
 * Create the service object.
 */
$service = new Services\BusinessPoliciesManagementService([
    'credentials' => $config['production']['credentials'],
    'authToken'   => $config['production']['authToken'],
    'globalId'    => Constants\GlobalIds::US
]);

/**
 * Create the request object.
 */
$request = new Types\GetSellerProfilesRequest();

/**
 * Send the request.
 */
$response = $service->getSellerProfiles($request);

/**
 * Output the result of calling the service operation.
 */
if ($response->ack !== 'Success') {
    if (isset($response->errorMessage)) {
        foreach ($response->errorMessage->error as $error) {
            printf("Error: %s\n", $error->message);
        }
    }
} else {
    /**
     *  Have to take into account that a seller may not have any business policies.
     *  When no policies exist the API does not return the appropriate field.
     *  Using isset ensures that we don't try and access properties that haven't been set.
     */
    if (isset($response->paymentProfileList)) {
        echo "================\nPayment Profiles\n================\n";
        foreach ($response->paymentProfileList->PaymentProfile as $profile) {
            printf(
                "(%s) %s: %s\n",
                $profile->profileId,
                $profile->profileName,
                $profile->profileDesc
            );
        }
    }

    if (isset($response->returnPolicyProfileList)) {
        echo "======================\nReturn Policy Profiles\n======================\n";
        foreach ($response->returnPolicyProfileList->ReturnPolicyProfile as $profile) {
            printf(
                "(%s) %s: %s\n",
                $profile->profileId,
                $profile->profileName,
                $profile->profileDesc
            );
        }
    }

    if (isset($response->shippingPolicyProfile)) {
        echo "========================\nShipping Policy Profiles\n========================\n";
        foreach ($response->shippingPolicyProfile->ShippingPolicyProfile as $profile) {
            printf(
                "(%s) %s: %s\n",
                $profile->profileId,
                $profile->profileName,
                $profile->profileDesc
            );
        }
    }
}
