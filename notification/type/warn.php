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

use phpbb\datetime;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Group Subscription expiration warning notification.
 */
class warn extends base_type
{
	/**
	 * @inheritDoc
	 */
	static public $notification_option = array(
		'lang'	=> 'GROUPSUB_NOTIFICATION_TYPE_WARN',
		'group'	=> 'GROUPSUB_NOTIFICATION_GROUP',
	);

	/**
	 * @inheritDoc
	 */
	public function get_type()
	{
		return 'stevotvr.groupsub.notification.type.warn';
	}

	/**
	 * @inheritDoc
	 */
	public function get_title()
	{
		return $this->language->lang('GROUPSUB_NOTIFICATION_WARN_TITLE');
	}

	/**
	 * @inheritDoc
	 */
	public function get_reference()
	{
		$date = $this->user->format_date($this->get_data('sub_expires'), '|M d|');

		return $this->language->lang('GROUPSUB_NOTIFICATION_WARN_REFERENCE', $this->get_data('pkg_name'), $date);
	}

	/**
	 * @inheritDoc
	 */
	public function get_email_template()
	{
		return '@stevotvr_groupsub/subscription_warn';
	}

	/**
	 * @inheritDoc
	 */
	public function get_email_template_variables()
	{
		$this->language->add_lang('common', 'stevotvr/groupsub');

		$user_info = $this->user_loader->get_user($this->user_id);
		$tz = new \DateTimeZone($user_info['user_timezone']);
		$time = date('c', $this->get_data('sub_expires'));
		$datetime = new datetime($this->user, $time, $tz);

		$params = array('name' => $this->get_data('pkg_ident'));
		$u_view_sub = $this->helper->route('stevotvr_groupsub_main', $params, false, false, UrlGeneratorInterface::RELATIVE_PATH);

		$days_left = floor(((int) $this->get_data('sub_expires') - time()) / 86400);

		return array(
			'DAYS_LEFT'		=> $days_left,
			'DAYS'			=> $this->language->lang('GROUPSUB_DAYS', $days_left),
			'EXPIRE_DATE'	=> $datetime->format('|M d, Y|'),
			'SUB_NAME'		=> $this->get_data('pkg_name'),

			'U_VIEW_SUB'	=> generate_board_url() . '/' . $u_view_sub,
		);
	}

	/**
	 * @inheritDoc
	 */
	public function users_to_query()
	{
		return array($this->user_id);
	}

	/**
	 * @inheritDoc
	 */
	public function create_insert_array($data, $pre_create_data = array())
	{
		$this->set_data('sub_expires', (int) $data['sub_expires']);

		parent::create_insert_array($data, $pre_create_data);
	}
}
