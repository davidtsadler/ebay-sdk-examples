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
use \DTS\eBaySDK\BulkDataExchange\Services;
use \DTS\eBaySDK\BulkDataExchange\Types;

/**
 * Create the service object.
 *
 * For more information about creating a service object, see:
 * http://devbay.net/sdk/guides/getting-started/#service-object
 */
$service = new Services\BulkDataExchangeService([
    'credentials' => $config['production']['credentials'],
    'authToken'   => $config['sandbox']['authToken'],
    'sandbox'     => true
]);

/**
 * Create the request object.
 *
 * For more information about creating a request object, see:
 * http://devbay.net/sdk/guides/getting-started/#request-object
 */
$request = new Types\GetJobsRequest();

/**
 * Send the request to the getJobs service operation.
 *
 * For more information about calling a service operation, see:
 * http://devbay.net/sdk/guides/getting-started/#service-operation
 */
$response = $service->getJobs($request);

/**
 * Output the result of calling the service operation.
 *
 * For more information about working with the service response object, see:
 * http://devbay.net/sdk/guides/getting-started/#response-object
 */
if (isset($response->errorMessage)) {
    foreach ($response->errorMessage->error as $error) {
        printf(
            "%s: %s\n\n",
            $error->severity === BulkDataExchange\Enums\ErrorSeverity::C_ERROR ? 'Error' : 'Warning',
            $error->message
        );
    }
}

if ($response->ack !== 'Failure') {
    /**
     * Just display the first 3 jobs from the response.
     */
    $upTo = min(count($response->jobProfile), 3);
    for ($x = 0; $x < $upTo; $x++) {
        $job = $response->jobProfile[$x];
        printf(
            "ID: %s\nType: %s\nStatus: %s\nInput File Reference ID: %s\nFile Reference ID: %s\nPercent Complete: %s\nCreated: %s\nCompleted: %s\n\n",
            $job->jobId,
            $job->jobType,
            $job->jobStatus,
            $job->inputFileReferenceId,
            $job->fileReferenceId,
            $job->percentComplete,
            $job->creationTime->format('H:i (\G\M\T) \o\n l jS F Y'),
            isset($job->completionTime) ? $job->completionTime->format('H:i (\G\M\T) \o\n l jS F Y') : ''
        );
    }
}
