# Async Examples

These examples show how to use the SDK to make asynchronous requests.

1. [Get eBay offical time](https://github.com/davidtsadler/ebay-sdk-examples/blob/master/async/01-get-ebay-official-time.php)

   A basic example that sends two concurrent requests to two difference services.

1. [Find items advanced](https://github.com/davidtsadler/ebay-sdk-examples/blob/master/async/02-find-items-advanced.php)

   Shows how to make concurrent pagination requests to the findItemsAdvanced operation. 

1. [Finding and shoppinh](https://github.com/davidtsadler/ebay-sdk-examples/blob/master/async/03-finding-shopping.php)

   Advanced example that show how to make concurrent requests to both the Finding and Shopping services. The Finding service is used to obtain various Item IDs that are passed onto the Shopping service. The Shopping service requests are made concurrently while the Finding requests are been handled. 
