# Trading Examples

These examples show how to use the eBay SDK for PHP with the Trading service.

1. [Get eBay Official Time](https://github.com/davidtsadler/ebay-sdk-examples/blob/master/trading/01-get-ebay-official-time.php)

   A basic example that retrieves the official eBay system time in GMT. This is a good way of testing your production eBay authorization tokens as the call does not modify any user data.

1. [Get Category Hierarchy](https://github.com/davidtsadler/ebay-sdk-examples/blob/master/trading/02-get-category-hierarchy.php)

   Shows how to retrieve the category hierarchy for a site. More information can be found in the [official eBay documentation](http://developer.ebay.com/DevZone/guides/ebayfeatures/Development/Categories-Hierarchy.html).

1. [Add Auction Item](https://github.com/davidtsadler/ebay-sdk-examples/blob/master/trading/03-add-auction-item.php)

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

1. [Add Fixed Price Item](https://github.com/davidtsadler/ebay-sdk-examples/blob/master/trading/04-add-fixed-price-item.php)

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

1. [Add An Item With Item Specifics](https://github.com/davidtsadler/ebay-sdk-examples/blob/master/trading/05-add-an-item-with-item-specifics.php)

   Shows how to specify item specifics when adding an item.
