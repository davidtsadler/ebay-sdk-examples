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
 *
 * For more information about creating a service object, see:
 * http://devbay.net/sdk/guides/getting-started/#service-object
 */
$service = new Services\TradingService([
    'credentials' => $config['sandbox']['credentials'],
    'sandbox'     => true,
    'siteId'      => $siteId
]);

/**
 * Create the request object.
 *
 * For more information about creating a request object, see:
 * http://devbay.net/sdk/guides/getting-started/#request-object
 */
$request = new Types\AddFixedPriceItemRequestType();

/**
 * An user token is required when using the Trading service.
 *
 * For more information about getting your user tokens, see:
 * http://devbay.net/sdk/guides/application-keys/
 */
$request->RequesterCredentials = new Types\CustomSecurityHeaderType();
$request->RequesterCredentials->eBayAuthToken = $config['sandbox']['authToken'];

/**
 * Begin creating the fixed price item.
 */
$item = new Types\ItemType();

/**
 * The item that will be listed is the audiobook of a well known novel.
 */
$item->Title = "Harry Potter and the Philosopher's Stone";
$item->Description = 'Audiobook of the wizard novel';

/**
 * Since the item is an audio book list in the Books > Audiobooks (29792) category.
 */
$item->PrimaryCategory = new Types\CategoryType();
$item->PrimaryCategory->CategoryID = '29792';

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
 * Subject=Fiction & Literature
 * Topic=Fantasy
 * Format=MP3 CD
 * Length=Unabridged
 * Language=English
 *
 * In addition to the names and values that eBay has recommended this item will list with 
 * its own custom item specifics.
 *
 * Bit rate=320 kbit/s
 * Narrated by=Stephen Fry
 *
 * Note that some categories allow multiple values to be specified for each name.
 * This example will only use one value per name.
 */
$item->ItemSpecifics = new Types\NameValueListArrayType();

$specific = new Types\NameValueListType();
$specific->Name = 'Subject';
$specific->Value[] = 'Fiction &amp; Literature';
$item->ItemSpecifics->NameValueList[] = $specific;

/**
 * This shows an alternative way of adding a specific.
 */
$item->ItemSpecifics->NameValueList[] = new Types\NameValueListType(array(
    'Name' => 'Topic',
    'Value' => array('Fantasy')
));

$specific = new Types\NameValueListType();
$specific->Name = 'Format';
$specific->Value[] = 'MP3 CD';
$item->ItemSpecifics->NameValueList[] = $specific;

$specific = new Types\NameValueListType();
$specific->Name = 'Length';
$specific->Value[] = 'Unabrided';
$item->ItemSpecifics->NameValueList[] = $specific;

$specific = new Types\NameValueListType();
$specific->Name = 'Language';
$specific->Value[] = 'English';
$item->ItemSpecifics->NameValueList[] = $specific;

/** 
 * Add the two custom item specifics.
 * Notice they are no different to eBay recommended item specifics.
 */
$specific = new Types\NameValueListType();
$specific->Name = 'Bit rate';
$specific->Value[] = '320 kbit/s';
$item->ItemSpecifics->NameValueList[] = $specific;

$specific = new Types\NameValueListType();
$specific->Name = 'Narrated by';
$specific->Value[] = 'Stephen Fry';
$item->ItemSpecifics->NameValueList[] = $specific;

/**
 * Provide enough information so that the item is listed.
 * It is beyond the scope of this example to go into any detail.
 */
$item->ListingType = Enums\ListingTypeCodeType::C_FIXED_PRICE_ITEM;
$item->Quantity = 99;
$item->ListingDuration = Enums\ListingDurationCodeType::C_GTC;
$item->StartPrice = new Types\AmountType(array('value' => 19.99));
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
 * Send the request to the AddFixedPriceItem service operation.
 *
 * For more information about calling a service operation, see:
 * http://devbay.net/sdk/guides/getting-started/#service-operation
 */
$response = $service->addFixedPriceItem($request);

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
    printf("The item was listed to the eBay Sandbox with the Item number %s\n",
        $response->ItemID
    );
}

