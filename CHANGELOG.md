# Changelog

## 1.2.0 (12/26/20)

* No changes

## 1.2.0-beta (7/21/20)

* Replaced group associations with subscriptions start and end actions
* Added term selection when adding or editing subscriptions
* Added ability to reactivate a cancelled subscription
* Added notification for a subscription starting
* Added notification for a subscription being cancelled by an administrator
* Added more granular permissions
* Added subscriptions to the user administration module
* Added error logging for transaction errors
* Added option to change the number of terms listed as radio inputs
* Fixed localized prices causing PayPal buttons to fail
* Fixed out-of-bounds error when creating a subscription
* Fixed confirmation box logic
* Fixed user selection input field
* Fixed business names being case-sensitive

## 1.1.2 (2/6/20)

* Fixed unlimited subscriptions

## 1.1.1 (10/1/19)

* Fixed issue with language loading in the ACP
* Removed local certificate authority file

## 1.1.0 (8/25/19)

* Fixed expiration warning email errors

## 1.1.0-beta (8/11/19)

* Added support for the cURL extension
* Added local certificate authority file
* Added page header and footer options
* Added event `stevotvr.groupsub.payment_received`
* Fixed unlimited terms being shown as 0 years on the return page
* Fixed error when attempting to view inactive subscriptions

## 1.0.2 (6/5/19)

* Fixed notifications being sent about inactive subscriptions
* Use strong tags instead of b tags in language strings
* Optimize JavaScript event handling
* Update code for phpBB extension validation compliance

## 1.0.1 (4/22/19)

* Fixed some missing error messages
* Fixed incompatibility with PHP 5.4
* Cleaned up HTML and JavaScript output
* Fixed duplicate database key

## 1.0.0 (1/16/19)

* Initial stable release

## 0.2.1 (9/12/18)

* Fixed events being fired too early
* Fixed subscriptions not automatically expiring

## 0.2.0 (8/28/18)

* Fixed styling issues with the package list
* Fixed unlimited subscription terms
* Added package option to set the default group for subscribers

## 0.1.0 (8/23/18)

* Initial beta release
