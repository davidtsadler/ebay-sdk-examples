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
 * Include some utility functions.
 */
require __DIR__.'/utils.php';

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
use \DTS\eBaySDK\Sdk;
use \DTS\eBaySDK\Constants;
use \DTS\eBaySDK\FileTransfer;
use \DTS\eBaySDK\BulkDataExchange;
use \DTS\eBaySDK\MerchantData;


/**
 * Create the service objects.
 *
 * This example uses both the File Transfer and Bulk Data Exchange services.
 *
 * For more information about creating a service object, see:
 * http://devbay.net/sdk/guides/getting-started/#service-object
 */
$sdk = new Sdk([
    'credentials' => $config['sandbox']['credentials'],
    'authToken'   => $config['sandbox']['authToken'],
    'sandbox'     => true
]);
$exchangeService = $sdk->createBulkDataExchange();
$transferService = $sdk->createFileTransfer();
$merchantDataService = new MerchantData\Services\MerchantDataService();

/**
 * Before anything can be uploaded a request needs to be made to obtain a job ID and file reference ID.
 * eBay needs to know the job type and a way to identify it.
 */
$createUploadJobRequest = new BulkDataExchange\Types\CreateUploadJobRequest();
$createUploadJobRequest->uploadJobType = 'AddFixedPriceItem';
$createUploadJobRequest->UUID = uniqid();

/**
 * Send the request to the createUploadJob service operation.
 *
 * For more information about calling a service operation, see:
 * http://devbay.net/sdk/guides/getting-started/#service-operation
 */
print('Requesting job Id from eBay...');
$createUploadJobResponse = $exchangeService->createUploadJob($createUploadJobRequest);
print("Done\n");

/**
 * Output the result of calling the service operation.
 *
 * For more information about working with the service response object, see:
 * http://devbay.net/sdk/guides/getting-started/#response-object
 */
if (isset($createUploadJobResponse->errorMessage)) {
    foreach ($createUploadJobResponse->errorMessage->error as $error) {
        printf("%s: %s\n\n",
            $error->severity === BulkDataExchange\Enums\ErrorSeverity::C_ERROR ? 'Error' : 'Warning',
            $error->message
        );
    }
}

if(1) {
$createUploadJobResponse->jobId = '50013886268';
$createUploadJobResponse->fileReferenceId ='50014110938';
    printf("JobId [%s] FileReferenceId [%s]\n",
        $createUploadJobResponse->jobId,
        $createUploadJobResponse->fileReferenceId
    );

    /**
     * Pass the required values to the File Transfer service.
     */
    $uploadFileRequest = new FileTransfer\Types\UploadFileRequest();
    $uploadFileRequest->fileReferenceId = $createUploadJobResponse->fileReferenceId;
    $uploadFileRequest->taskReferenceId = $createUploadJobResponse->jobId;
    $uploadFileRequest->fileFormat = 'gzip';

    $payload = buildPayload();

    /**
     * Convert our payload to XML.
     */
    $payloadXml = $payload->toRequestXml();

    /**
     * GZip and attach the XML payload.
     */
    $uploadFileRequest->attachment(gzencode($payloadXml, 9));

    /**
     * Now upload the file.
     */
    print('Uploading fixed price item requests...');
    $uploadFileResponse = $transferService->uploadFile($uploadFileRequest);
    print("Done\n");

    if (isset($uploadFileResponse->errorMessage)) {
        foreach ($uploadFileResponse->errorMessage->error as $error) {
            printf("%s: %s\n\n",
                $error->severity === FileTransfer\Enums\ErrorSeverity::C_ERROR ? 'Error' : 'Warning',
                $error->message
            );
        }
    }

    if ($uploadFileResponse->ack !== 'Failure') {
        /**
         * Once the file has uploaded we can tell eBay to start processing it.
         */
        $startUploadJobRequest = new BulkDataExchange\Types\StartUploadJobRequest();
        $startUploadJobRequest->jobId = $createUploadJobResponse->jobId;

        print('Request processing of fixed price items...');
        $startUploadJobResponse = $exchangeService->startUploadJob($startUploadJobRequest);
        print("Done\n");

        if (isset($startUploadJobResponse->errorMessage)) {
            foreach ($startUploadJobResponse->errorMessage->error as $error) {
                printf("%s: %s\n\n",
                    $error->severity === BulkDataExchange\Enums\ErrorSeverity::C_ERROR ? 'Error' : 'Warning',
                    $error->message
                );
            }
        }

        if ($startUploadJobResponse->ack !== 'Failure') {
            /**
             * Now wait for the job to be processed.
             */
            $getJobStatusRequest = new BulkDataExchange\Types\GetJobStatusRequest();
            $getJobStatusRequest->jobId = $createUploadJobResponse->jobId;

            $done = false;

            while(!$done) {
                $getJobStatusResponse = $exchangeService->getJobStatus($getJobStatusRequest);

                if (isset($getJobStatusResponse->errorMessage)) {
                    foreach ($getJobStatusResponse->errorMessage->error as $error) {
                        printf("%s: %s\n\n",
                            $error->severity === BulkDataExchange\Enums\ErrorSeverity::C_ERROR ? 'Error' : 'Warning',
                            $error->message
                        );
                    }
                }

                if ($getJobStatusResponse->ack !== 'Failure') {
                    printf("Status is %s\n", $getJobStatusResponse->jobProfile[0]->jobStatus);

                    switch($getJobStatusResponse->jobProfile[0]->jobStatus)
                    {
                        case BulkDataExchange\Enums\JobStatus::C_COMPLETED:
                            $downloadFileReferenceId = $getJobStatusResponse->jobProfile[0]->fileReferenceId;
                            $done = true;
                            break;
                        case BulkDataExchange\Enums\JobStatus::C_ABORTED:
                        case BulkDataExchange\Enums\JobStatus::C_FAILED:
                            $done = true;
                            break;
                        default:
                            sleep(5);
                            break;
                    }
                } else {
                    $done = true;
                }
            }

            if (isset($downloadFileReferenceId)) {
                $downloadFileRequest = new FileTransfer\Types\DownloadFileRequest();
                $downloadFileRequest->fileReferenceId = $downloadFileReferenceId;
                $downloadFileRequest->taskReferenceId = $createUploadJobResponse->jobId;

                print('Downloading fixed price item responses...');
                $downloadFileResponse = $transferService->downloadFile($downloadFileRequest);
                print("Done\n");

                if (isset($downloadFileResponse->errorMessage)) {
                    foreach ($downloadFileResponse->errorMessage->error as $error) {
                        printf("%s: %s\n\n",
                            $error->severity === FileTransfer\Enums\ErrorSeverity::C_ERROR ? 'Error' : 'Warning',
                            $error->message
                        );
                    }
                }

                if ($downloadFileResponse->ack !== 'Failure') {
                    /**
                     * Check that the response has an attachment.
                     */
                    if ($downloadFileResponse->hasAttachment()) {
                        $attachment = $downloadFileResponse->attachment();

                        /**
                         * Save the attachment to file system's temporary directory.
                         */
                        $filename = saveAttachment($attachment['data']);
                        if ($filename !== false) {
                            $xml = unZipArchive($filename);
                            if ($xml !== false) {
                                $responses = $merchantDataService->addFixedPriceItem($xml);
                                foreach ($responses as $response) {
                                    if (isset($response->Errors)) {
                                        foreach ($response->Errors as $error) {
                                            printf("%s: %s\n%s\n\n",
                                                $error->SeverityCode === MerchantData\Enums\SeverityCodeType::C_ERROR ? 'Error' : 'Warning',
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
                                }
                            }
                        }
                    } else {
                        print("Unable to locate attachment\n\n");
                    }
                }
            }
        }
    }
}

function buildPayload()
{
global $config;

    $payload = new MerchantData\Types\BulkDataExchangeRequestsType();
    $payload->Header = new MerchantData\Types\MerchantDataRequestHeaderType();
    $payload->Header->SiteID = Constants\SiteIds::US;
    $payload->Header->Version = '951';

    $payload->AddFixedPriceItemRequest[] = buildAddFixedPriceItemRequest(array(
        'title' => 'Example one',
        'description' => 'Example one',
        'sku' => 'abc-001',
        'price' => 9.99,
        'quantity' => 1
    ));

    $payload->AddFixedPriceItemRequest[] = buildAddFixedPriceItemRequest(array(
        'title' => 'Example two',
        'description' => 'Example two',
        'sku' => 'abc-002',
        'price' => 10.99,
        'quantity' => 9
    ));

    return $payload;
}

function buildAddFixedPriceItemRequest($details)
{
global $config;

    $request = new MerchantData\Types\AddFixedPriceItemRequestType();
    $request->Version = '951'; 

    $item = new MerchantData\Types\ItemType();
    $item->ListingType = MerchantData\Enums\ListingTypeCodeType::C_FIXED_PRICE_ITEM;
    $item->Quantity = $details['quantity'];
    $item->ListingDuration = 'GTC';
    $item->StartPrice = new MerchantData\Types\AmountType(array('value' => $details['price']));
    $item->Title = $details['title'];
    $item->Description = $details['description'];
    $item->SKU = $details['sku'];
    $item->Country = 'US';
    $item->Location = 'Beverly Hills';
    $item->PostalCode = '90210';
    $item->Currency = 'USD';
    $item->PictureDetails = new MerchantData\Types\PictureDetailsType();
    $item->PictureDetails->GalleryType = MerchantData\Enums\GalleryTypeCodeType::C_GALLERY;
    $item->PictureDetails->PictureURL = array('http://lorempixel.com/1500/1024/abstract');
    $item->PrimaryCategory = new MerchantData\Types\CategoryType();
    $item->PrimaryCategory->CategoryID = '29792';
    $item->ConditionID = 1000;
    $item->PaymentMethods = array(
        'PayPal'
    );
    $item->PayPalEmailAddress = 'example@example.com';
    $item->DispatchTimeMax = 1;
    $item->ShippingDetails = new MerchantData\Types\ShippingDetailsType();
    $item->ShippingDetails->ShippingType = MerchantData\Enums\ShippingTypeCodeType::C_FLAT;
    $shippingService = new MerchantData\Types\ShippingServiceOptionsType();
    $shippingService->ShippingServicePriority = 1;
    $shippingService->ShippingService = 'Other';
    $shippingService->ShippingServiceCost = new MerchantData\Types\AmountType(array('value' => 2.00));
    $item->ShippingDetails->ShippingServiceOptions[] = $shippingService;
    $item->ReturnPolicy = new MerchantData\Types\ReturnPolicyType();
    $item->ReturnPolicy->ReturnsAcceptedOption = 'ReturnsAccepted';
    $item->ReturnPolicy->RefundOption = 'MoneyBack';
    $item->ReturnPolicy->ReturnsWithinOption = 'Days_14';
    $item->ReturnPolicy->ShippingCostPaidByOption = 'Buyer';

    $request->Item = $item;

    return $request;
}

