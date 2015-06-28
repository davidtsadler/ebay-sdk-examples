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

/**
 * An instance of this class will be passed to the service object.
 * It will be responsible for sending the HTTP POST request.
 */
class HttpClient implements \DTS\eBaySDK\Interfaces\HttpClientInterface
{
    public function __construct() {}

    /**
     * This method will be called by the SDK to perform a POST HTTP request.
     *
     * @param string $url The API endpoint.
     * @param array $headers An associative array of HTTP headers.
     * @param string $body The request's body.
     *
     * @return string The response body.
     */
    public function post($url, $headers, $body)
    {
        /**
         * Convert the associative arary into an array that cURL can use.
         * $curlHeaders = array_map(function ($key, $value) { return "$key:$value"; }, array_keys($headers), array_values($headers));
         */
        $curlHeaders = array();
        foreach($headers as $key => $value) {
            $curlHeaders[] =  "$key:$value"; 
        }

        /**
         * For this example we will just use cURL
         */
		$connection = curl_init();

		curl_setopt($connection, CURLOPT_URL, $url);
		curl_setopt($connection, CURLOPT_HTTPHEADER, $curlHeaders);
		curl_setopt($connection, CURLOPT_POST, 1);
		curl_setopt($connection, CURLOPT_POSTFIELDS, $body);
		curl_setopt($connection, CURLOPT_RETURNTRANSFER, 1);
		
        /**
         * WARNING: You will not want to do this in production!
         */
		curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($connection, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($connection, CURLOPT_VERBOSE, 1);
		
		$response = curl_exec($connection);

		curl_close($connection);
		
		//return the response
		return $response;
    }
}

/**
 * Create the service object. 
 *
 * We pass in an instance of our HttpClient into the second parameter.
 *
 * For more information about creating a service object, see:
 * http://devbay.net/sdk/guides/getting-started/#service-object
 */
$service = new Services\TradingService(array(
    'apiVersion' => $config['tradingApiVersion'],
    'siteId' => Constants\SiteIds::US
), new HttpClient());

/**
 * Create the request object.
 *
 * For more information about creating a request object, see:
 * http://devbay.net/sdk/guides/getting-started/#request-object
 */
$request = new Types\GeteBayOfficialTimeRequestType();

/**
 * An user token is required when using the Trading service.
 *
 * For more information about getting your user tokens, see:
 * http://devbay.net/sdk/guides/application-keys/
 */
$request->RequesterCredentials = new Types\CustomSecurityHeaderType();
$request->RequesterCredentials->eBayAuthToken = $config['production']['userToken'];

/**
 * Send the request to the GeteBayOfficialTime service operation.
 *
 * For more information about calling a service operation, see:
 * http://devbay.net/sdk/guides/getting-started/#service-operation
 */
$response = $service->geteBayOfficialTime($request);

/**
 * Output the result of calling the service operation.
 *
 * For more information about working with the service response object, see:
 * http://devbay.net/sdk/guides/getting-started/#response-object
 */
if ($response->Ack !== 'Success') {
    if (isset($response->Errors)) {
        foreach ($response->Errors as $error) {
            printf("Error: %s\n", $error->ShortMessage);
        }
    }
} else {
    printf("The official eBay time is: %s\n", $response->Timestamp->format('H:i (\G\M\T) \o\n l jS F Y'));
}
