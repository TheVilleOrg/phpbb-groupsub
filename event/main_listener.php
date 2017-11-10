<?php
/**
 *
 * Group Subscription. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, Steve Guidetti, https://github.com/stevotvr
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace stevotvr\groupsub\event;

use phpbb\controller\helper;
use phpbb\event\data;
use phpbb\template\template;
use stevotvr\groupsub\operator\product_interface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Group Subscription event listener.
 */
class main_listener implements EventSubscriberInterface
{
	/**
	 * @var \phpbb\controller\helper
	 */
	protected $helper;

	/**
	 * @var \stevotvr\groupsub\operator\product_interface
	 */
	protected $prod_operator;

	/**
	 * @var \phpbb\template\template
	 */
	protected $template;

	/**
	 * @param \phpbb\controller\helper                      $helper
	 * @param \stevotvr\groupsub\operator\product_interface $prod_operator
	 * @param \phpbb\template\template                      $template
	 */
	public function __construct(helper $helper, product_interface $prod_operator, template $template)
	{
		$this->helper = $helper;
		$this->prod_operator = $prod_operator;
		$this->template = $template;
	}

	static public function getSubscribedEvents()
	{
		return array(
			'core.user_setup'	=> 'user_setup',
		);
	}

	/**
	 * Adds the extension language set and the controller link on user setup.
	 *
	 * @param \phpbb\event\data $event The event data
	 */
	public function user_setup(data $event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = array(
			'ext_name'	=> 'stevotvr/groupsub',
			'lang_set'	=> 'common',
		);
		$event['lang_set_ext'] = $lang_set_ext;

		if ($this->prod_operator->count_products())
		{
			$this->template->assign_var('U_GROUPSUB_SUBS', $this->helper->route('stevotvr_groupsub_main'));
		}
	}
}
