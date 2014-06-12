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
use \DTS\eBaySDK\BusinessPoliciesManagement\Services;
use \DTS\eBaySDK\BusinessPoliciesManagement\Types;

/**
 * Create the service object.
 *
 * For more information about creating a service object, see:
 * http://devbay.net/sdk/guides/getting-started/#service-object
 *
 * Note that an user token is required when using the Business Policies Management service. 
 *
 * For more information about getting your user tokens, see:
 * http://devbay.net/sdk/guides/application-keys/
 */
$service = new Services\BusinessPoliciesManagementService(array(
    'authToken' => $config['production']['userToken'],
    'globalId' => Constants\GlobalIds::US
));

/**
 * Create the request object.
 *
 * For more information about creating a request object, see:
 * http://devbay.net/sdk/guides/getting-started/#request-object
 */
$request = new Types\GetSellerProfilesRequest();

/**
 * Send the request to the getSellerProfiles service operation.
 *
 * For more information about calling a service operation, see:
 * http://devbay.net/sdk/guides/getting-started/#service-operation
 */
$response = $service->getSellerProfiles($request);

/**
 * Output the result of calling the service operation.
 *
 * For more information about working with the service response object, see:
 * http://devbay.net/sdk/guides/getting-started/#response-object
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
            printf("(%s) %s: %s\n",
                $profile->profileId,
                $profile->profileName,
                $profile->profileDesc
            );
        }
    }

    if (isset($response->returnPolicyProfileList)) {
        echo "======================\nReturn Policy Profiles\n======================\n";
        foreach ($response->returnPolicyProfileList->ReturnPolicyProfile as $profile) {
            printf("(%s) %s: %s\n",
                $profile->profileId,
                $profile->profileName,
                $profile->profileDesc
            );
        }
    }

    if (isset($response->shippingPolicyProfile)) {
        echo "========================\nShipping Policy Profiles\n========================\n";
        foreach ($response->shippingPolicyProfile->ShippingPolicyProfile as $profile) {
            printf("(%s) %s: %s\n",
                $profile->profileId,
                $profile->profileName,
                $profile->profileDesc
            );
        }
    }
}
