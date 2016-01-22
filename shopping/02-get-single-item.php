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
use \DTS\eBaySDK\Shopping\Services;
use \DTS\eBaySDK\Shopping\Types;
use \DTS\eBaySDK\Shopping\Enums;

/**
 * Create the service object.
 *
 * For more information about creating a service object, see:
 * http://devbay.net/sdk/guides/getting-started/#service-object
 */
$service = new Services\ShoppingService([
    'credentials' => $config['production']['credentials']
]);

/**
 * Create the request object.
 *
 * For more information about creating a request object, see:
 * http://devbay.net/sdk/guides/getting-started/#request-object
 */
$request = new Types\GetSingleItemRequestType();

/**
 * Specify the item ID of the listing.
 */
$request->ItemID = '111111111111';

/**
 * Specify that additional fields need to be returned in the response.
 */
$request->IncludeSelector = 'ItemSpecifics,Variations,Compatibility,Details';

/**
 * Send the request to the GetSingleItem service operation.
 *
 * For more information about calling a service operation, see:
 * http://devbay.net/sdk/guides/getting-started/#service-operation
 */
$response = $service->getSingleItem($request);

/**
 * Output the result of calling the service operation.
 *
 * For more information about working with the service response object, see:
 * http://devbay.net/sdk/guides/getting-started/#response-object
 */
if (isset($response->Errors)) {
    foreach ($response->Errors as $error) {
        printf("%s: %s\n%s\n\n",
            $error->SeverityCode === Enums\SeverityCodeType::C_ERROR ? 'Error' : 'Warning',
            $error->ShortMessage,
            $error->LongMessage
        );
    }
}

if ($response->Ack !== 'Failure') {
    $item = $response->Item;

    print("$item->Title\n");

    printf("Quantity sold %s, quantiy available %s\n",
        $item->QuantitySold,
        $item->Quantity - $item->QuantitySold
    );

    if (isset($item->ItemSpecifics)) {
        print("\nThis item has the following item specifics:\n\n");

        foreach($item->ItemSpecifics->NameValueList as $nameValues) {
            printf("%s: %s\n",
                $nameValues->Name,
                implode(', ', iterator_to_array($nameValues->Value))
            );
        }
    }

    if (isset($item->Variations)) {
        print("\nThis item has the following variations:\n");

        foreach($item->Variations->Variation as $variation) {
            printf("\nSKU: %s\nStart Price: %s\n",
                $variation->SKU,
                $variation->StartPrice->value
            );

            printf("Quantity sold %s, quantiy available %s\n",
                $variation->SellingStatus->QuantitySold,
                $variation->Quantity - $variation->SellingStatus->QuantitySold
            );

            foreach($variation->VariationSpecifics as $specific) {
                foreach($specific->NameValueList as $nameValues) {
                    printf("%s: %s\n",
                        $nameValues->Name,
                        implode(', ', iterator_to_array($nameValues->Value))
                    );
                }
            }
        }
    }

    if (isset($item->ItemCompatibilityCount)) {
        printf("\nThis item is compatible with %s vehicles:\n\n", $item->ItemCompatibilityCount);

        // Only show the first 3.
        $limit = min($item->ItemCompatibilityCount, 3);
        for ($x = 0; $x < $limit; $x++) {
            $compatibility = $item->ItemCompatibilityList->Compatibility[$x];
            foreach($compatibility->NameValueList as $nameValues) {
                printf("%s: %s\n",
                    $nameValues->Name,
                    implode(', ', iterator_to_array($nameValues->Value))
                );
            }
            printf("Notes: %s \n", $compatibility->CompatibilityNotes);
        }
    }
}

