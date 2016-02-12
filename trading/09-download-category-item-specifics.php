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
use \DTS\eBaySDK\Sdk;
use \DTS\eBaySDK\Constants;
use \DTS\eBaySDK\Trading;
use \DTS\eBaySDK\FileTransfer;

/**
 * Downloading the category specifics is a two step process.
 *
 * The first step is to use the Trading service to request the FileReferenceID and TaskReferenceID from eBay.
 * This is done with the GetCategorySpecifics operation.
 *
 * The second step is to use the File Transfer service to download the file that contains the specifics.
 * This is done with the downloadFile operation using the FileReferenceID and TaskReferenceID values.
 *
 * For more information, see:
 * http://developer.ebay.com/DevZone/XML/docs/Reference/eBay/GetCategorySpecifics.html#downloadFile
 */

/**
 * Specify the numerical site id that we to download the category specifics for.
 * Note that each site will have its own category structure and specifics.
 */
$siteId = Constants\SiteIds::US;

$sdk = new Sdk([
    'credentials' => $config['production']['credentials'],
    'authToken'   => $config['production']['authToken'],
    'siteId'      => $siteId
]);

/**
 * Create the service object.
 *
 * For more information about creating a service object, see:
 * http://devbay.net/sdk/guides/getting-started/#service-object
 */
$service = $sdk->createTrading();

/**
 * Create the request object.
 *
 * For more information about creating a request object, see:
 * http://devbay.net/sdk/guides/getting-started/#request-object
 */
$request = new Trading\Types\GetCategorySpecificsRequestType();

/**
 * Request the FileReferenceID and TaskReferenceID from eBay.
 */
$request->CategorySpecificsFileInfo = true;

/**
 * Send the request to the GetCategorySpecifics service operation.
 *
 * For more information about calling a service operation, see:
 * http://devbay.net/sdk/guides/getting-started/#service-operation
 */
$response = $service->getCategorySpecifics($request);

/**
 * Output the result of calling the service operation.
 *
 * For more information about working with the service response object, see:
 * http://devbay.net/sdk/guides/getting-started/#response-object
 */
if (isset($response->Errors)) {
    foreach ($response->Errors as $error) {
        printf(
            "%s: %s\n%s\n\n",
            $error->SeverityCode === Trading\Enums\SeverityCodeType::C_ERROR ? 'Error' : 'Warning',
            $error->ShortMessage,
            $error->LongMessage
        );
    }
}

if ($response->Ack !== 'Failure') {
    /**
     * Get the values that will be passed to the File Transfer service.
     */
    $fileReferenceId = $response->FileReferenceID;
    $taskReferenceId = $response->TaskReferenceID;

    printf(
        "FileReferenceID [%s] TaskReferenceID [%s]\n",
        $fileReferenceId,
        $taskReferenceId
    );

    print("Downloading file...\n");

    $service = $sdk->createFileTransfer();

    $request = new FileTransfer\Types\DownloadFileRequest();

    $request->fileReferenceId = $fileReferenceId;
    $request->taskReferenceId = $taskReferenceId;

    $response = $service->downloadFile($request);

    if (isset($response->errorMessage)) {
        foreach ($response->errorMessage->error as $error) {
            printf(
                "%s: %s\n\n",
                $error->severity === FileTransfer\Enums\ErrorSeverity::C_ERROR ? 'Error' : 'Warning',
                $error->message
            );
        }
    }

    if ($response->ack !== 'Failure') {
        /**
         * Check that the response has an attachment.
         */
        if ($response->hasAttachment()) {
            $attachment = $response->attachment();

            /**
             * Save the attachment to file system's temporary directory.
             */
            $tempFilename = tempnam(sys_get_temp_dir(), 'category-specifics-').'.zip';
            $fp = fopen($tempFilename, 'wb');
            if (!$fp) {
                printf("Failed. Cannot open %s to write!\n", $tempFilename);
            } else {
                fwrite($fp, $attachment['data']);
                fclose($fp);

                printf("File downloaded to %s\nUnzip this file to obtain the category item specifics.\n\n", $tempFilename);
            }
        } else {
            print("Unable to locate attachment\n\n");
        }
    }
}
