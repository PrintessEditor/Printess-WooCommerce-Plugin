=== Printess Editor ===
Contributors: printess
Tags: personalization, mug, calendar, t-shirt, photo products, customization, web2print, photo books, canvas, avatar, photo tiles, personalized children book, greeting cards, graphic design, configurator
Requires at least: 5.6
Tested up to: 6.8.3
WC Tested up to: 10.3.0
Stable tag: 1.6.71
Requires PHP: 8.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Personalize anything! Friendship mugs, t-shirts, greeting cards. Limitless possibilities.

== Description ==

The Printess Editor makes creating personalized templates fast and easy.
It comes with its own layout editor and the fronted for your customers to customize the various options.
Sell photo mugs, t-shirts or other personalized content within minutes.

Head over to https://printess.com , register your account for free and start selling.

== Frequently Asked Questions ==

= How to get started? =

Check https://www.printess.com/plugins/WoocommerceInstall.html for the installation process and your first product.

= Where to get support? =

Check our manual at https://printess.com/kb/user-manual/ or
write an e-mail to support@printess.com , we'll answer asap :).

= How much does it cost? =

The account is free of charge.
Only pay when your customers order something.
10 orders are free.

Please check https://printess.com/pricing.html for more details.

== Screenshots ==


== Changelog ==

= 1.0 =
* Initial release

= 1.1.0 =
* Added: Possibility to use different templates for variations.
* Fixed: Deletion of variants when a Printess template is assigned.

= 1.4.6 =
* Added: Replaced table of personalized items in customer order detail view with CSS-Grid for easier styling.
* Fixed: Adding personalized items to basket failed for variants in case the item was added through the personalized items table inside the customers order detail view.

= 1.4.7 =
* Added: possibility to change saved design name on second and every consecutive "save" click.

= 1.4.8 =
* Fixed: Fixed broken printess settings after entering wrong api domain which resulted in a disappearing save button

= 1.4.9 =
* Fixed: broken saving of designs that created a new saved design with the same name on every second save click.
* Fixed: better table structure for saved designs in saved designs view and personalized products in order detail view
* Added: Option "Show original product in basket" that when unchecked does not display the original product inside the basket after editing a basket item.
* Added: Personalized items are now put upfront inside the shopping cart whereas the last edited one is on top.

= 1.5.0 =
* Added: Support for HPOS
* Added: Translation of "this item was edited. Consider removing it from your cart"
* Last version to support PHP 7.0 . New versions will need at least PHP 8.1 See php versions that still have security support: https://www.php.net/supported-versions.php

= 1.5.1 =
* Added: Moved personalized items table in order detail view on top of order detials
* Added: Better styling for saved designs table.

= 1.5.2 =
* Fixed: When using Ajax to add items to the cart, the editor did not close when adding items to the cart.

= 1.5.3 =
* Fixed: Broken Editor Overlay behavior in case the shop system is specifiying a maximum width for body and is moving the body into the browser center.

= 1.5.4 =
* Fixed: Fixed broken variant handling when editing cart item from within mini (overlay) cart. The Design variant (production pdf after the order) stayed the same, but the basket item and then later the ordered variant used the first available product variant.

= 1.5.5 =
* Fixed: Fixed issues with printess_adjust_add_to_cart being called with only 2 paramters instead of 3 due to customers theme handling things differently.

= 1.6.0 =
* Added: Reimplemented Printess Editor integration for better theme handling. The new implementation is only depending on one single woo commerce render callback now which leads to better theme support.
* Added: Order time values like orderId, lineItemId, itemQuantity etc. are know written to form fields at the time of production. List of new fields (A form field with the given name must exist):
  itemQuantity
  itemSku
  orderId
  orderName
  lineItemId
  productCategories
  productCategorySlugs
  productTags
  productTagSlugs

  ShippingFirstName
  ShippingLastName
  ShippingName
  ShippingAddress1
  ShippingAddress2
  ShippingCity
  ShippingCompany
  ShippingCountry
  ShippingCountryCode
  ShippingPhone
  ShippingProvince
  ShippingProvinceCode
  ShippingZip

  BillingFirstName
  BillingLastName
  BillingName
  BillingAddress1
  BillingAddress2
  BillingCity
  BillingCompany
  BillingCountry
  BillingCountryCode
  BillingPhone
  BillingProvince
  BillingProvinceCode
  BillingZip

  = 1.6.1 =
* Fixed: Fixed some stylings in the last version that caused some issues with some themes.

= 1.6.2 =
* The text "Saving your design" on your product page is now hidden from the beggining so that it is eeven not visible on slow loading pages.

= 1.6.3 =
* Fixed: Fixed the broken (ignored) template configuration on variant level.

= 1.6.4 =
* Fixed: Removed -webkit-fill-available from body css that prevented scrolling on some themes

= 1.6.5 =
* Fixed: All css changes to body and html tags are now only applied during the time the printess editor is open due to problems with some themes.

= 1.6.6 =
* Fixed: Fixed Design Now button position to be next to the quantity selector in case of product variants.

= 1.6.7 =
* Fixed: Fixed Invisible Save dialog when clicking on the save button inside the designer
* Fixed: Fixed missing production time form fields

= 1.6.9 =
* Fixed: Removed printess_DropshipNonce from admin order view
* Fixed: Hitting enter inside the product form should not be adding the item into the basket without opening the printerss designer for personalisation any more.
* Added: Admin Order View now always displays the order item id; There is a new option inside the printess settings to also display line item id on customer order view of personalized items.

= 1.6.10 =
* Added: Added support for page redirect after "add to cart event" even in case ajax is enabled for add to cart action.

= 1.6.11 =
* Fixed: Fixed issues with single products where not added to the cart anymore.

= 1.6.12 =
* Fixed: Removed URL modifications that canceled the submit while trying to add an item to the cart.

= 1.6.13 =
* Fixed: Fixed broken page redirects after adding item to cart for saved designs and edited cart items
* Added: Basic support for page redirects on printess products for ajax enabled add to basket actions.

= 1.6.14 =
* Added: Non variable products now write their attribute values to form fields at production time

= 1.6.15 =
* Fixed: Fixed broken approve buttons in admin order view for non variable products

= 1.6.16 =
* Fixed: Fixed broken "Edit order line item" Link in admin order detail view for variation products.

= 1.6.17 =
* Fixed: Fixed broken "Edit order line item" Link in admin order detail view that opened the base design without the customer changes.

= 1.6.18 =
* Fixed: Fixed broken "Edit order line item" Link in admin order detail view that opened the base design without the customer changes.

= 1.6.19 =
* Fixed: Fixed issues with editor lost startup settings that have been provided directly inside the editor embed url.

= 1.6.20 =
* Fixed: Now, editor does not open in case the customer did not select values for each variant relevant option.

= 1.6.21 =
* Fixed: Changing price relevant form fields inside the editor did not affect WooCommerce attribute values in case the label auto mapping was used and the attribute name contained non ascii characters

= 1.6.22 =
* Added: Added experimental support for the new panel UI

= 1.6.23 =
* Added: Added support for output file configuration

= 1.6.24 =
* Fixed: Fixed possible issues with some products where the global dropship product definition id override hasn't been applied correctly.
* Added: Added support for document thumbnail widths / heights of up to 1000 pixels.

= 1.6.25 =
* Fixed: 1.6.24 introduced issues with unparsable product definition id's that are specified directly inside the product configuration.

= 1.6.26 =
* Added: Added support for dropship variables without having to create template form fields

= 1.6.27 =
* Fixed: Fixed broken variant handling during saving of designs while not being logged in (variant settings got lost during the login process when saving a design)

= 1.6.28 =
* Added: Added support for the new theme blocks inside the shopping basket

= 1.6.29 =
* Fixed issues with keyboard inputs in case the keyboard input focus is required without having the user to click on input elements within the editor before.
* Added: Added support for item usage (Different pricing depending on numeric form field values)

= 1.6.30 =
* Changed: Removed experimental text from panel ui

= 1.6.31 =
* Fixed: Fixed mobile display issues of Panel UI under WooCommerce

= 1.6.32 =
* Added: Added Support for displaying form field values inside every order.

= 1.6.33 =
* Fixed: Fixed broken variant handling in item usage pricing

= 1.6.34 =
* Fixed: Fixed broken legal text
* Fixed: Fixed broken price display when using Panel UI

= 1.6.35 =
* Fixed: Fixed broken cart redirect back to the cart page after editing a cart item.
* Fixed: Fixed broken deletion of the original cart item after editing an item which resulted always in 2 cart items with each edit.

= 1.6.36 =
* Fixed: fixed timing issues when trying to add saved designs into the basket

= 1.6.37 =
* Added Price callback to manually control the prices displayed inside the editor.

= 1.6.38 =
* Added support for pushing in user values and user acf fields as form field values automatically

= 1.6.40 =
* Added support for auto importing image url form field values

= 1.6.41 =
* Fixed php warning with missing array keys in printessBlockIntegrations.php

= 1.6.42 =
* Fixed basket integration on "slow" rendering themes

= 1.6.43 =
* Added variant Support for controlling page count in books

= 1.6.44 =
* Added support to auto forward ACF fields and global product attributes into template form fields at the time the customer opens the buyer side.
* Added support for editing (replacing) save tokens in orders
* Shopping cart now blocks add to cart actions on products that are configured as printess products but are missing the printess specific settings like the save token to prevent adding items to the cart that were not personalized before.
* End- customers can now provide display names for line items while editing / adding designs to the cart
* Fixed bug in Save Dailog that sent an unpersonalized product to the shopping cart instead of saving the current design in case the enter key was hit inside the save dialog.

= 1.6.45 =
* Fixed issues with wrong parameter count on card item validation callback in some WooCommerce versions.
* Fixed wrong spelled variable name

= 1.6.46 =
- Fixed issues with replacing save tokens in Admin order view

= 1.6.47 =
- Added header block to save design and display name dialogs
- Added option to activate warning on page change / page reload while the editor is open

= 1.6.48 =
- Removed unused WooCommerce Cart Hook

= 1.6.49 =
- Fixed broken editing of order line items
- Fixed broken editing of save tokens in order line items

= 1.6.50 =
- Fixed issues with "Personalized product without personalization was added to the cart" warning that appeared on some products that have not been configured as printess items.

= 1.6.51 =
- Added option to order line items to reproduce single items

= 1.6.52 =
- Added option to printess settings to disable the basket warning about unpersonalized items added to the cart that should be personalized.

= 1.6.53 =
- Added support for merge templates inside variants

= 1.6.54 =
- Added option to use ACF field names instead of labels while assigning form field values
- Fixed variant option to use given template name as merge template for variants where not all variants use the template name as merge template

= 1.6.55 =
- fixed: broken thumbnail retrieval on save actions in PanelUi
- added: save token timeouts are automtaically extended after replacing the original save token in the admin order view
- added: admin order view / customer order view are now displaying the expiration date of the save tokens
- fixed: saved designs do now display the correct timeout date format that is configured inside the wordpress date / time settings
- added: it is now possible to specify form field mappings for ACF fields

= 1.6.56 =
- Added: support for showing a save reminder after a given amount of minutes
- Added: Order views now display the save token end date
- Fixed: Replaced save tokens will also now have the correct token life time configured inside the printess settings

= 1.6.57 =
- Fixed bug that came in 1.6.56 in saving that did not save the current save token to the list of saved designs.

= 1.6.58 =
- Fixed wrong BillingCity variable name that prevented billing city to be pushed into the buyer side of the editor.

= 1.6.59 =
- Fixed cart sorting for shopping cart items that are not of type object but of type array.

= 1.6.60 =
- Fixed wrong behaviour in PanelUi that did not hide the price when it was configured to be hidden.
- Saving dialog, saving reminder dialog and provide design name dialog are now accessible.

= 1.6.61 =
- Cancel button is now not red anymore in save dialog

= 1.6.62 =
- Fixed broken saving functionality while trying to trap focus events on save dialog

= 1.6.63 =
- Fixed parsing of serialized json configuration for merge templates in case a json string was provided as merge template name
- Fixed css for saving overlay thats displayed during saving a design to the customer account.

= 1.6.64 =
- Fixed improper handling of cart item sorting for some users that have cart items that are not printess cart items and have object type cart items instead of array type cart items

= 1.6.65 =
- Fixed: Fixed bug that saved a wrong variant in case the current user is not logged in and clicks on save. Before, the default variant was used instead of the current selected variant.

= 1.6.66 =
- Fixed bug that did not clear the exchange id cache when using panel Ui
- Fixed PHP warnings with referenced printess plugin files that could not be found by replacing include_once calls with require_once.
- Fixed broken add to cart when using specific product types or specific plugins that might chaneg the data type of product meta data
- PanelUi is now the standard for newly created products

= 1.6.67 =
 - Fixed error logging for failed template name retrieval, so that it is now called in case the template name could not be retrieved due to wrong data types
 
 = 1.6.68 =
 - Fixed broken file include with wrong file path

 = 1.6.69 =
 - Added basic tracking events to dom: "printess:editor_opened_new_design", "printess:editor_opened_save_token", "printess:editor_closed_without_add_to_cart","printess:design_saved","printess:add_to_cart_clicked" are now sent to DOM via window.dispatchEvent and can be received using document.addEventListener

 = 1.6.70 =
 - Changed save token live time extension from old deprecated api call to newer api's  (internal technical change).
 - Fixed user id forwarding to use strings instead of numbers (internal technical change).

 = 1.6.71 =
 - Added confirmation message before deletion of saved designs
 - Added WooCommerce logging for saving / replacing / deletion of saved designs
 - Added option to disable auto import of image urls inside form field values