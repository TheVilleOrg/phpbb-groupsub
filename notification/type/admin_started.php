<?php
/**
 *
 * Group Subscription. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2020, Steve Guidetti, https://github.com/stevotvr
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace stevotvr\groupsub\notification\type;

/**
 * Group Subscription start notification.
 */
class admin_started extends admin_base_type
{
	/**
	 * @inheritDoc
	 */
	static public $notification_option = array(
		'lang'	=> 'GROUPSUB_NOTIFICATION_TYPE_ADMIN_STARTED',
		'group'	=> 'GROUPSUB_NOTIFICATION_GROUP',
	);

	/**
	 * @inheritDoc
	 */
	public function get_type()
	{
		return 'stevotvr.groupsub.notification.type.admin_started';
	}

	/**
	 * @inheritDoc
	 */
	public function get_title()
	{
		return $this->language->lang('GROUPSUB_NOTIFICATION_ADMIN_STARTED_TITLE');
	}

	/**
	 * @inheritDoc
	 */
	public function get_reference()
	{
		$username = $this->user_loader->get_username($this->get_data('sub_user'), 'username', false, false, true);
		return $this->language->lang('GROUPSUB_NOTIFICATION_ADMIN_STARTED_REFERENCE', $username, $this->get_data('pkg_name'));
	}

	/**
	 * @inheritDoc
	 */
	public function get_email_template()
	{
		return '@stevotvr_groupsub/admin_subscription_started';
	}

	/**
	 * @inheritDoc
	 */
	public function get_email_template_variables()
	{
		$params = array('name' => $this->get_data('pkg_ident'));

		return array(
			'SUB_NAME'	=> $this->get_data('pkg_name'),
			'SUB_USER'	=> htmlspecialchars_decode($this->user_loader->get_username($this->get_data('sub_user'), 'username')),
		);
	}
}
