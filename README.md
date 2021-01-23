# phpBB Group Subscription Extension

This is a extension for phpBB 3.2 that allows you to create paid subscriptions for members to gain access to usergroups.

[![Build Status](https://github.com/stevotvr/phpbb-groupsub/workflows/Tests/badge.svg?branch=master)](https://github.com/stevotvr/phpbb-groupsub/actions)
[![Code Climate](https://codeclimate.com/github/stevotvr/phpbb-groupsub/badges/gpa.svg)](https://codeclimate.com/github/stevotvr/phpbb-groupsub)

## Features

* Accept payments via PayPal in any currency supported by PayPal
* Supports multiple packages each with multiple price/length options
* Subscription packages can be attached to one or more usergroups
* Users are automatically added/removed from groups as their subscription starts/ends
* Optionally set subscribers' default group
* Notifications when a subscription is about to expire and has expired
* Configurable warning time and grace period
* Sandbox mode for testing

## Requirements

* PHP 5.6 or newer
* TLS 1.2 support
* A PayPal Business account

## Install

1. [Download the latest validated release](https://www.phpbb.com/customise/db/extension/groupsub/).
2. Unzip the downloaded release and copy it to the `ext` directory of your phpBB board.
3. Navigate in the ACP to `Customise -> Manage extensions`.
4. Look for `Group Subscription` under the Disabled Extensions list, and click its `Enable` link.
5. Set up and configure Group Subscription by navigating in the ACP to `Extensions` -> `Group Subscription`.

## Uninstall

1. Navigate in the ACP to `Customise -> Extension Management -> Extensions`.
2. Look for `Group Subscription` under the Enabled Extensions list, and click its `Disable` link.
3. To permanently uninstall, click `Delete Data` and then delete the `/ext/stevotvr/groupsub` directory.

## Support

* **Important: Only official release versions validated by the phpBB Extensions Team should be installed on a live forum. Pre-release (beta, RC) versions downloaded from this repository are only to be used for testing on offline/development forums and are not officially supported.**
* Report bugs and other issues to our [Issue Tracker](https://github.com/stevotvr/phpbb-groupsub/issues).
* Support requests should be posted and discussed in the [Group Subscription topic at phpBB.com](https://www.phpbb.com/customise/db/extension/groupsub/support).

## Translations

* Translations should be posted to the [Group Subscription topic at phpBB.com](https://www.phpbb.com/customise/db/extension/groupsub/support/topic/207181).

## License
[GNU General Public License v2](http://opensource.org/licenses/GPL-2.0)
