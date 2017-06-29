# OAuth Examples

These examples show how to use the eBay SDK for PHP to generate OAuth tokens.

1. [Get app token](https://github.com/davidtsadler/ebay-sdk-examples/blob/master/oauth-tokens/01-get-app-token.php)

   A basic example that generates an application token.

1. [Get user token](https://github.com/davidtsadler/ebay-sdk-examples/blob/master/oauth-tokens/02-get-user-token.php)

   An example that generates an user token.

1. [Refresh user token](https://github.com/davidtsadler/ebay-sdk-examples/blob/master/oauth-tokens/03-refresh-user-token.php)

   An example that generates an user token via a refresh token.

### Example Application

A [basic application](https://github.com/davidtsadler/ebay-sdk-examples/blob/master/oauth-tokens/slim-app.php) built with the Slim framework is available. It allows you to generate both application and user oauth tokens for the sandbox environment.

Before running the example you will need to configure your eBay developer account so that it can accept oauth tokens for the sandbox environment. This can be done by following the [eBay documentation](http://developer.ebay.com/devzone/rest/ebay-rest/content/oauth-gen-user-token.html#Getting4). When configuring the RuName you must use the following values for the URLs.

Privacy Policy URL https://127.0.0.1:8080/privacy
Auth Accepted URL https://127.0.0.1:8080/auth-accepted
Auth Declined URL https://127.0.0.1:8080/auth-declined

The SDK will need the value of the sandbox RuName. This value can be entered into the configuration.php file if you are using one. There is an [example configuration](https://github.com/davidtsadler/ebay-sdk-examples/blob/master/configuration.php.example) file if you need to create one.

It is possible to use PHP's built in server to run the application. Run the below command from the root directory of the example repository.

```
php -S 127.0.0.1:8080 -t oauth-tokens/
```

Open your browser to http://127.0.0.1:8080 and use the various links to generate your oauth tokens.

One import note is that eBay require you to use HTTPS endpoints. When you generate a user token you will be re-directed to the HTTPS end point on your machine. Your browser will display an error as PHP's server can not serve content over HTTPS. To resolve this simply change the URL in the address bar to HTTP and navigate to a URL that PHP can serve.
