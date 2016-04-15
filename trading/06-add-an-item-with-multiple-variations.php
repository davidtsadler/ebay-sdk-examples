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
 * Specify the numerical site id that we want the listing to appear on.
 *
 * This determines the validation rules that eBay will apply to the request.
 * For example, it will determine what categories can be specified, the values
 * allowed as shipping services, the visibility of the item in some searches and other
 * information.
 *
 * Note that due to the risk of listing fees been raised this example will list the item
 * to the sandbox site.
 */
$siteId = Constants\SiteIds::US;

/**
 * Create the service object.
 */
$service = new Services\TradingService([
    'credentials' => $config['sandbox']['credentials'],
    'sandbox'     => true,
    'siteId'      => $siteId
]);

/**
 * Create the request object.
 */
$request = new Types\AddFixedPriceItemRequestType();

/**
 * An user token is required when using the Trading service.
 */
$request->RequesterCredentials = new Types\CustomSecurityHeaderType();
$request->RequesterCredentials->eBayAuthToken = $config['sandbox']['authToken'];

/**
 * Begin creating the fixed price item.
 */
$item = new Types\ItemType();

/**
 * The item is T-Shirts in various color and sizes.
 */
$item->Title = "T-Shirt";
$item->Description = 'A plain T-Shirt';

/**
 * List in the Clothing, Shoes & Accessories > Men's Clothing > T-Shirts (15687) category.
 */
$item->PrimaryCategory = new Types\CategoryType();
$item->PrimaryCategory->CategoryID = '15687';

/**
 * The item will be T-Shirts in different colors that are available in several sizes.
 *
 * | SKU    | Color | Size (Men's) | Quantity | Price |
 * |--------------------------------------------------|
 * | TS-R-S | Red   | S            | 10       | 9.99  |
 * | TS-R-M | Red   | M            | 10       | 9.99  |
 * | TS-R-L | Red   | L            | 10       | 9.99  |
 * | TS-W-S | White | S            | 5        | 10.99 |
 * | TS-W-M | White | M            | 5        | 10.99 |
 * | TS-B-L | Blue  | L            | 10       | 9.99  |
 */

$item->Variations = new Types\VariationsType();

/**
 * Before we specify the variations we need to inform eBay all the possible
 * names and values that the listing could use over its life time.
 */
$variationSpecificsSet = new Types\NameValueListArrayType();

$nameValue = new Types\NameValueListType();
$nameValue->Name = 'Color';
$nameValue->Value = ['Red', 'White', 'Blue'];
$variationSpecificsSet->NameValueList[] = $nameValue;

$nameValue = new Types\NameValueListType();
$nameValue->Name = "Size (Men's)";
$nameValue->Value = ['S', 'M', 'L'];
$variationSpecificsSet->NameValueList[] = $nameValue;

$item->Variations->VariationSpecificsSet = $variationSpecificsSet;

/**
 * Variation
 * SKU          - TS-R-S
 * Color        - Red
 * Size (Men's) - S
 * Quantity     - 10
 * Price        - 9.99
 */
$variation = new Types\VariationType();
$variation->SKU = 'TS-R-S';
$variation->Quantity = 10;
$variation->StartPrice = new Types\AmountType(['value' => 9.99]);
$variationSpecifics = new Types\NameValueListArrayType();
$nameValue = new Types\NameValueListType();
$nameValue->Name = 'Color';
$nameValue->Value = ['Red'];
$variationSpecifics->NameValueList[] = $nameValue;
$nameValue = new Types\NameValueListType();
$nameValue->Name = "Size (Men's)";
$nameValue->Value = ['S'];
$variationSpecifics->NameValueList[] = $nameValue;
$variation->VariationSpecifics[] = $variationSpecifics;
$item->Variations->Variation[] = $variation;

/**
 * Variation
 * SKU          - TS-R-M
 * Color        - Red
 * Size (Men's) - M
 * Quantity     - 10
 * Price        - 9.99
 */
$variation = new Types\VariationType();
$variation->SKU = 'TS-R-M';
$variation->Quantity = 10;
$variation->StartPrice = new Types\AmountType(['value' => 9.99]);
$variationSpecifics = new Types\NameValueListArrayType();
$nameValue = new Types\NameValueListType();
$nameValue->Name = 'Color';
$nameValue->Value = ['Red'];
$variationSpecifics->NameValueList[] = $nameValue;
$nameValue = new Types\NameValueListType();
$nameValue->Name = "Size (Men's)";
$nameValue->Value = ['M'];
$variationSpecifics->NameValueList[] = $nameValue;
$variation->VariationSpecifics[] = $variationSpecifics;
$item->Variations->Variation[] = $variation;

/**
 * Variation
 * SKU          - TS-R-L
 * Color        - Red
 * Size (Men's) - L
 * Quantity     - 10
 * Price        - 9.99
 */
$variation = new Types\VariationType();
$variation->SKU = 'TS-R-L';
$variation->Quantity = 10;
$variation->StartPrice = new Types\AmountType(['value' => 9.99]);
$variationSpecifics = new Types\NameValueListArrayType();
$nameValue = new Types\NameValueListType();
$nameValue->Name = 'Color';
$nameValue->Value = ['Red'];
$variationSpecifics->NameValueList[] = $nameValue;
$nameValue = new Types\NameValueListType();
$nameValue->Name = "Size (Men's)";
$nameValue->Value = ['L'];
$variationSpecifics->NameValueList[] = $nameValue;
$variation->VariationSpecifics[] = $variationSpecifics;
$item->Variations->Variation[] = $variation;

/**
 * Variation
 * SKU          - TS-W-S
 * Color        - White
 * Size (Men's) - S
 * Quantity     - 5
 * Price        - 10.99
 *
 * The SDK allows properties to be specified when constructing new objects.
 * By taking advantage of this feature we can add a variation as follows.
 */
$item->Variations->Variation[] = new Types\VariationType([
    'SKU' => 'TS-W-S',
    'Quantity' => 5,
    'StartPrice' => new Types\AmountType(['value' => 10.99]),
    'VariationSpecifics' => [new Types\NameValueListArrayType([
        'NameValueList' => [
            new Types\NameValueListType(['Name' => 'Color', 'Value' => ['White']]),
            new Types\NameValueListType(['Name' => "Size (Men's)", 'Value' => ['S']])
        ]
    ])]
]);

/**
 * Variation
 * SKU          - TS-W-M
 * Color        - White
 * Size (Men's) - M
 * Quantity     - 5
 * Price        - 10.99
 */
$item->Variations->Variation[] = new Types\VariationType([
    'SKU' => 'TS-W-M',
    'Quantity' => 5,
    'StartPrice' => new Types\AmountType(['value' => 10.99]),
    'VariationSpecifics' => [new Types\NameValueListArrayType([
        'NameValueList' => [
            new Types\NameValueListType(['Name' => 'Color', 'Value' => ['White']]),
            new Types\NameValueListType(['Name' => "Size (Men's)", 'Value' => ['M']])
        ]
    ])]
]);

/**
 * Variation
 * SKU          - TS-B-L
 * Color        - Blue
 * Size (Men's) - L
 * Quantity     - 10
 * Price        - 9.99
 */
$item->Variations->Variation[] = new Types\VariationType([
    'SKU' => 'TS-B-L',
    'Quantity' => 10,
    'StartPrice' => new Types\AmountType(['value' => 9.99]),
    'VariationSpecifics' => [new Types\NameValueListArrayType([
        'NameValueList' => [
            new Types\NameValueListType(['Name' => 'Color', 'Value' => ['Blue']]),
            new Types\NameValueListType(['Name' => "Size (Men's)", 'Value' => ['L']])
        ]
    ])]
]);

$pictures = new Types\PicturesType();
$pictures->VariationSpecificName = 'Color';

$pictureSet = new Types\VariationSpecificPictureSetType();
$pictureSet->VariationSpecificValue = 'Red';
$pictureSet->PictureURL[] = 'http://lorempixel.com/1500/1024/fashion';
$pictureSet->PictureURL[] = 'http://lorempixel.com/1500/1024/abstract';
$pictures->VariationSpecificPictureSet[] = $pictureSet;

$pictureSet = new Types\VariationSpecificPictureSetType();
$pictureSet->VariationSpecificValue = 'White';
$pictureSet->PictureURL[] = 'http://lorempixel.com/1500/1024/cats';
$pictureSet->PictureURL[] = 'http://lorempixel.com/1500/1024/animals';
$pictures->VariationSpecificPictureSet[] = $pictureSet;

$pictureSet = new Types\VariationSpecificPictureSetType();
$pictureSet->VariationSpecificValue = 'Blue';
$pictureSet->PictureURL[] = 'http://lorempixel.com/1500/1024/city';
$pictureSet->PictureURL[] = 'http://lorempixel.com/1500/1024/transport';
$pictures->VariationSpecificPictureSet[] = $pictureSet;

$item->Variations->Pictures[] = $pictures;

/**
 * Item specifics describe the aspects of the item and are specified using a name-value pair system.
 * For example:
 *
 *  Color=Red
 *  Size=Small
 *  Gemstone=Amber
 *
 * The names and values that are available will depend upon the category the item is listed in.
 * Before specifying your item specifics you would normally call GetCategorySpecifics to get
 * a list of names and values that are recommended by eBay.
 * Showing how to do this is beyond the scope of this example but it can be assumed that
 * a call has previously been made and the following names and values were returned.
 *
 * Brand="Handmade"
 * Style=Basic Tee
 * Size Type=Regular
 * Material=100% Cotton
 *
 * It is important to note that item specifics Style and Size Type are required for the
 * category that we are listing in.
 */
$item->ItemSpecifics = new Types\NameValueListArrayType();

$specific = new Types\NameValueListType();
$specific->Name = 'Brand';
$specific->Value[] = '"Handmade"';
$item->ItemSpecifics->NameValueList[] = $specific;

/**
 * This shows an alternative way of adding a specific.
 */
$item->ItemSpecifics->NameValueList[] = new Types\NameValueListType([
    'Name' => 'Style',
    'Value' => ['Basic Tee']
]);

$specific = new Types\NameValueListType();
$specific->Name = 'Size Type';
$specific->Value[] = 'Regular';
$item->ItemSpecifics->NameValueList[] = $specific;

$specific = new Types\NameValueListType();
$specific->Name = 'Material';
$specific->Value[] = '100% Cotton';
$item->ItemSpecifics->NameValueList[] = $specific;

/**
 * Provide enough information so that the item is listed.
 * It is beyond the scope of this example to go into any detail.
 */
$item->ListingType = Enums\ListingTypeCodeType::C_FIXED_PRICE_ITEM;
$item->ListingDuration = Enums\ListingDurationCodeType::C_GTC;
$item->Country = 'US';
$item->Location = 'Beverly Hills';
$item->Currency = 'USD';
$item->ConditionID = 1000;
$item->PaymentMethods[] = 'PayPal';
$item->PayPalEmailAddress = 'example@example.com';
$item->DispatchTimeMax = 1;
$item->ShipToLocations[] = 'None';
$item->ReturnPolicy = new Types\ReturnPolicyType();
$item->ReturnPolicy->ReturnsAcceptedOption = 'ReturnsNotAccepted';

/**
 * Finish the request object.
 */
$request->Item = $item;

/**
 * Send the request.
 */
$response = $service->addFixedPriceItem($request);

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

if ($response->Ack !== 'Failure') {
    printf(
        "The item was listed to the eBay Sandbox with the Item number %s\n",
        $response->ItemID
    );
}
