<?php
/**
 * Copyright 2017 David T. Sadler
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
 * Include a very basic web server.
 */
require __DIR__.'/Server.php';
require __DIR__.'/gen-pem.php';

/**
 * Include the configuration values.
 *
 * Ensure that you have edited the configuration.php file
 * to include your application keys.
 */
$config = require __DIR__.'/../configuration.php';

/**
 * The namespaces provided by the SDK and the Slim framework.
 */
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \DTS\eBaySDK\OAuth\Services;
use \DTS\eBaySDK\OAuth\Types;

$options = getopt('h:p:');

$host = isset($options['h']) ? $options['h'] : '127.0.0.1';
$port = isset($options['p']) ? $options['p'] : 8080;

genSelfSignedCertificate();

$server = new Server($host, $port);

$server->listen(function ($environment) use ($config) {
    $container = new \Slim\Container();

    $container['environment'] = $environment;

    $container['view'] = function ($container) {
        $view = new \Slim\Views\Twig(__DIR__.'/templates', [
            'cache' => false
        ]);

        $basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
        $view->addExtension(new Slim\Views\TwigExtension($container['router'], $basePath));

        return $view;
    };

    $container['sdk-config'] = $config;

    $container['oAuthService'] = function ($container) {
        $config = $container['sdk-config'];

        $service = new Services\OAuthService([
            'credentials' => $config['sandbox']['credentials'],
            'ruName'      => $config['sandbox']['ruName'],
            'sandbox'     => true
        ]);

        return $service;
    };

    $app = new \Slim\App($container);

    $app->get('/', function (Request $request, Response $response) {
        return $this->view->render($response, 'index.html');
    });

    $app->get('/get-app-token', function (Request $request, Response $response) {
        $api= $this->oAuthService->getAppToken();

        return $this->view->render($response, 'get-app-token.html', [
            'statusCode'        => $api->getStatusCode(),
            'accessToken'       => $api->access_token,
            'tokenType'         => $api->token_type,
            'expiresIn'         => $api->expires_in,
            'refreshToken'      => $api->refresh_token,
            'error'             => $api->error,
            'errorDescription'  => $api->error_description
        ]);
    });

    $app->get('/get-user-token', function (Request $request, Response $response) {
        $state = uniqid();
        $url =  $this->oAuthService->redirectUrlForUser([
            'state' => $state,
            'scope' => [
                'https://api.ebay.com/oauth/api_scope/sell.account',
                'https://api.ebay.com/oauth/api_scope/sell.inventory'
            ]
        ]);

        return $this->view->render($response, 'get-user-token.html', [
            'url'   => $url,
            'state' => $state
        ]);
    });

    $app->get('/refresh-user-token', function (Request $request, Response $response) {
        $paramRefreshToken = $request->getQueryParam('refresh-token');

        $api = $this->oAuthService->refreshUserToken(new Types\RefreshUserTokenRestRequest([
            'refresh_token' => $paramRefreshToken,
            'scope' => [
                'https://api.ebay.com/oauth/api_scope/sell.account',
                'https://api.ebay.com/oauth/api_scope/sell.inventory'
            ]
        ]));

        return $this->view->render($response, 'refresh-user-token.html', [
            'paramRefreshToken' => $paramRefreshToken,
            'statusCode'        => $api->getStatusCode(),
            'accessToken'       => $api->access_token,
            'tokenType'         => $api->token_type,
            'expiresIn'         => $api->expires_in,
            'refreshToken'      => $api->refresh_token,
            'error'             => $api->error,
            'errorDescription'  => $api->error_description
        ]);
    });


    $app->get('/auth-accepted', function (Request $request, Response $response) {
        $api = $this->oAuthService->getUserToken(new Types\GetUserTokenRestRequest([
            'code' => $request->getQueryParam('code')
        ]));

        return $this->view->render($response, 'auth-accepted.html', [
            'state'             => $request->getQueryParam('state'),
            'code'              => $request->getQueryParam('code'),
            'statusCode'        => $api->getStatusCode(),
            'accessToken'       => $api->access_token,
            'tokenType'         => $api->token_type,
            'expiresIn'         => $api->expires_in,
            'refreshToken'      => $api->refresh_token,
            'error'             => $api->error,
            'errorDescription'  => $api->error_description
        ]);
    });

    $app->get('/auth-declined', function (Request $request, Response $response) {
        return $this->view->render($response, 'auth-declined.html', [
            'state' => $request->getQueryParam('state'),
            'error' => $request->getQueryParam('error'),
            'errorDescription' => $request->getQueryParam('error_description')
        ]);
    });

    return $app->run(true);
});
