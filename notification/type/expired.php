<?php
/**
 *
 * Group Subscription. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, Steve Guidetti, https://github.com/stevotvr
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace stevotvr\groupsub\notification\type;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Group Subscription expiration notification.
 */
class expired extends base_type
{
	/**
	 * @inheritDoc
	 */
	static public $notification_option = array(
		'lang'	=> 'GROUPSUB_NOTIFICATION_TYPE_EXPIRED',
		'group'	=> 'GROUPSUB_NOTIFICATION_GROUP',
	);

	/**
	 * @inheritDoc
	 */
	public function get_type()
	{
		return 'stevotvr.groupsub.notification.type.expired';
	}

	/**
	 * @inheritDoc
	 */
	public function get_title()
	{
		return $this->language->lang('GROUPSUB_NOTIFICATION_EXPIRED_TITLE');
	}

	/**
	 * @inheritDoc
	 */
	public function get_reference()
	{
		return $this->language->lang('GROUPSUB_NOTIFICATION_EXPIRED_REFERENCE', $this->get_data('pkg_name'));
	}

	/**
	 * @inheritDoc
	 */
	public function get_email_template()
	{
		return '@stevotvr_groupsub/subscription_expired';
	}

	/**
	 * @inheritDoc
	 */
	public function get_email_template_variables()
	{
		$params = array('name' => $this->get_data('pkg_ident'));
		$u_view_sub = $this->helper->route('stevotvr_groupsub_main', $params, false, false, UrlGeneratorInterface::ABSOLUTE_URL);

		return array(
			'SUB_NAME'		=> $this->get_data('pkg_name'),

			'U_VIEW_SUB'	=> $u_view_sub,
		);
	}
}
