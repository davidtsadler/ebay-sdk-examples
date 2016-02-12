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
 * Overview
 *
 * Using the LMS to obtain an Active Inventory Report involves several steps using different services.
 *
 * STEP 1 - Obtain a download job ID from the API.
 * STEP 2 - Poll the API until it reports that the job has been completed.
 * STEP 3 - Download the job.
 * STEP 4 - Parse the results.
 */

/**
 * STEP 1 - Obtain a download job ID.
 *
 * Tell eBay which report you want to download.
 * Provide any information that is needed to control what is returned in the report.
 * Send the request to the startDownloadJob operation in the Bulk Data Exchange Service.
 * The response will include the ID of the job that has been assigned to generate the report.
 */
$activeInventoryReportFilter = new BulkDataExchange\Types\ActiveInventoryReportFilter();
$activeInventoryReportFilter->includeListingType = 'AuctionAndFixedPrice';
$activeInventoryReportFilter->fixedPriceItemDetails = new BulkDataExchange\Types\FixedPriceItemDetails();
$activeInventoryReportFilter->fixedPriceItemDetails->includeVariations = true;

$startDownloadJobRequest = new BulkDataExchange\Types\StartDownloadJobRequest();
$startDownloadJobRequest->downloadJobType = 'ActiveInventoryReport';
$startDownloadJobRequest->UUID = uniqid();
$startDownloadJobRequest->downloadRequestFilter = new BulkDataExchange\Types\DownloadRequestFilter();
$startDownloadJobRequest->downloadRequestFilter->activeInventoryReportFilter = $activeInventoryReportFilter;

print('Requesting job Id from eBay...');
$startDownloadJobResponse = $exchangeService->startDownloadJob($startDownloadJobRequest);
print("Done\n");

if (isset($startDownloadJobResponse->errorMessage)) {
    foreach ($startDownloadJobResponse->errorMessage->error as $error) {
        printf(
            "%s: %s\n\n",
            $error->severity === BulkDataExchange\Enums\ErrorSeverity::C_ERROR ? 'Error' : 'Warning',
            $error->message
        );
    }
}

if ($startDownloadJobResponse->ack !== 'Failure') {
    printf(
        "JobId [%s]\n",
        $startDownloadJobResponse->jobId
    );

    /**
     * STEP 2 - Poll the API until it reports that the job has been completed.
     *
     * Using the job ID returned from the previous step we repeatedly call getJobStatus until it reports that the job is complete.
     * The response will include a file reference ID that can be used to download the completed report.
     */
    $getJobStatusRequest = new BulkDataExchange\Types\GetJobStatusRequest();
    $getJobStatusRequest->jobId = $startDownloadJobResponse->jobId;

    $done = false;
    while (!$done) {
        $getJobStatusResponse = $exchangeService->getJobStatus($getJobStatusRequest);

        if (isset($getJobStatusResponse->errorMessage)) {
            foreach ($getJobStatusResponse->errorMessage->error as $error) {
                printf(
                    "%s: %s\n\n",
                    $error->severity === BulkDataExchange\Enums\ErrorSeverity::C_ERROR ? 'Error' : 'Warning',
                    $error->message
                );
            }
        }

        if ($getJobStatusResponse->ack !== 'Failure') {
            printf("Status is %s\n", $getJobStatusResponse->jobProfile[0]->jobStatus);

            switch ($getJobStatusResponse->jobProfile[0]->jobStatus) {
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
        /**
         * STEP 3 - Download the job.
         *
         * Using the file reference ID from the previous step we can download the report.
         */
        $downloadFileRequest = new FileTransfer\Types\DownloadFileRequest();
        $downloadFileRequest->fileReferenceId = $downloadFileReferenceId;
        $downloadFileRequest->taskReferenceId = $startDownloadJobResponse->jobId;

        print('Downloading the active inventory report...');
        $downloadFileResponse = $transferService->downloadFile($downloadFileRequest);
        print("Done\n");

        if (isset($downloadFileResponse->errorMessage)) {
            foreach ($downloadFileResponse->errorMessage->error as $error) {
                printf(
                    "%s: %s\n\n",
                    $error->severity === FileTransfer\Enums\ErrorSeverity::C_ERROR ? 'Error' : 'Warning',
                    $error->message
                );
            }
        }

        if ($downloadFileResponse->ack !== 'Failure') {
            /**
             * STEP 4 - Parse the results.
             *
             * The report is returned as a Zip archive attachment.
             * Save the attachment and then unzip it to get the report.
             */
            if ($downloadFileResponse->hasAttachment()) {
                $attachment = $downloadFileResponse->attachment();

                $filename = saveAttachment($attachment['data']);
                if ($filename !== false) {
                    $xml = unZipArchive($filename);
                    if ($xml !== false) {
                        $activeInventoryReport = $merchantDataService->activeInventoryReport($xml);
                        if (isset($activeInventoryReport->Errors)) {
                            foreach ($activeInventoryReport->Errors as $error) {
                                printf(
                                    "%s: %s\n%s\n\n",
                                    $error->SeverityCode === MerchantData\Enums\SeverityCodeType::C_ERROR ? 'Error' : 'Warning',
                                    $error->ShortMessage,
                                    $error->LongMessage
                                );
                            }
                        }

                        if ($activeInventoryReport->Ack !== 'Failure') {
                            foreach ($activeInventoryReport->SKUDetails as $skuDetails) {
                                printf("Item ID %s \n", $skuDetails->ItemID);
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
