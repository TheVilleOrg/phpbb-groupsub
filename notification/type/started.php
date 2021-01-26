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

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Group Subscription start notification.
 */
class started extends base_type
{
	/**
	 * @inheritDoc
	 */
	static public $notification_option = array(
		'lang'	=> 'GROUPSUB_NOTIFICATION_TYPE_STARTED',
		'group'	=> 'GROUPSUB_NOTIFICATION_GROUP',
	);

	/**
	 * @inheritDoc
	 */
	public function get_type()
	{
		return 'stevotvr.groupsub.notification.type.started';
	}

	/**
	 * @inheritDoc
	 */
	public function get_title()
	{
		return $this->language->lang('GROUPSUB_NOTIFICATION_STARTED_TITLE');
	}

	/**
	 * @inheritDoc
	 */
	public function get_reference()
	{
		return $this->language->lang('GROUPSUB_NOTIFICATION_STARTED_REFERENCE', $this->get_data('pkg_name'));
	}

	/**
	 * @inheritDoc
	 */
	public function get_email_template()
	{
		return '@stevotvr_groupsub/subscription_started';
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
