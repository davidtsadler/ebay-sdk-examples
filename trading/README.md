# Trading Examples

These examples show how to use the eBay SDK for PHP with the Trading service.

1. [Get eBay official time](https://github.com/davidtsadler/ebay-sdk-examples/blob/master/trading/01-get-ebay-official-time.php)

   A basic example that retrieves the official eBay system time in GMT. This is a good way of testing your production eBay authorization tokens as the call does not modify any user data.

1. [Get category hierarchy](https://github.com/davidtsadler/ebay-sdk-examples/blob/master/trading/02-get-category-hierarchy.php)

   Shows how to retrieve the category hierarchy for a site. More information can be found in the [official eBay documentation](http://developer.ebay.com/DevZone/guides/ebayfeatures/Development/Categories-Hierarchy.html).

1. [Add auction item](https://github.com/davidtsadler/ebay-sdk-examples/blob/master/trading/03-add-auction-item.php)

   An example that lists an auction item to the eBay sandbox. It shows how to do the following when listing an item.

   - Specify the correct listing type.
   - Set the starting price.
   - Describe the item.
   - Include a picture that will appear in the eBay gallery.
   - Specify what eBay category the item will be listed in.
   - State what payment methods are accepted.
   - Specify both domestic and international shipping options.
   - State what the return policy is.

   This example does not show all the features that are available to sellers when listing. Other examples will instead focus on particular features.

1. [Add fixed price item](https://github.com/davidtsadler/ebay-sdk-examples/blob/master/trading/04-add-fixed-price-item.php)

   An example that lists an auction item to the eBay sandbox. It shows how to do the following when listing an item.

   - Specify the correct listing type.
   - Set the item price.
   - Accept best offers.
   - Describe the item.
   - Include a picture that will appear in the eBay gallery.
   - Specify what eBay category the item will be listed in.
   - State what payment methods are accepted.
   - Specify both domestic and international shipping options.
   - State what the return policy is.

   This example does not show all the features that are available to sellers when listing. Other examples will instead focus on particular features.

1. [Add an item with item specifics](https://github.com/davidtsadler/ebay-sdk-examples/blob/master/trading/05-add-an-item-with-item-specifics.php)

   Shows how to specify item specifics when adding an item.

1. [Add an item with multiple variations](https://github.com/davidtsadler/ebay-sdk-examples/blob/master/trading/06-add-an-item-with-multiple-variations.php)

   Shows how to specify multiple variations when adding an item.

1. [Add an item with parts compatibility](https://github.com/davidtsadler/ebay-sdk-examples/blob/master/trading/07-add-an-item-with-parts-compatibility.php)

   Shows how to specify parts compatibility when adding an item.

1. [Upload picture to eBay picture service](https://github.com/davidtsadler/ebay-sdk-examples/blob/master/trading/08-upload-picture-to-ebay-picture-service.php)

   A simple example that shows how to upload a picture to the eBay picture service sandbox.

1. [Download category item specifics](https://github.com/davidtsadler/ebay-sdk-examples/blob/master/trading/09-download-category-item-specifics.php)

   An example that downloads all the category item specifics for a site. What is particularly interesting about this example is that it requires the use of the [File Transfer SDK](https://github.com/davidtsadler/ebay-sdk-file-transfer).
