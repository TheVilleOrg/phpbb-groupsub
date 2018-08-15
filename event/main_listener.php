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

use phpbb\config\config;
use phpbb\controller\helper;
use phpbb\event\data;
use phpbb\template\template;
use stevotvr\groupsub\operator\package_interface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Group Subscription event listener.
 */
class main_listener implements EventSubscriberInterface
{
	/**
	 * @var \phpbb\config\config
	 */
	protected $config;

	/**
	 * @var \phpbb\controller\helper
	 */
	protected $helper;

	/**
	 * @var \stevotvr\groupsub\operator\package_interface
	 */
	protected $pkg_operator;

	/**
	 * @var \phpbb\template\template
	 */
	protected $template;

	/**
	 * @param \phpbb\config\config                          $config
	 * @param \phpbb\controller\helper                      $helper
	 * @param \stevotvr\groupsub\operator\package_interface $pkg_operator
	 * @param \phpbb\template\template                      $template
	 */
	public function __construct(config $config, helper $helper, package_interface $pkg_operator, template $template)
	{
		$this->config = $config;
		$this->helper = $helper;
		$this->pkg_operator = $pkg_operator;
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

		if ($this->groupsub_active() && $this->pkg_operator->count_packages())
		{
			$this->template->assign_var('U_GROUPSUB_SUBS', $this->helper->route('stevotvr_groupsub_main'));
		}
	}

	/**
	 * Check if the extension is configured.
	 *
	 * @return boolean The extension is ready to use
	 */
	protected function groupsub_active()
	{
		$pp_sandbox = $this->config['stevotvr_groupsub_pp_sandbox'];
		$sb = $pp_sandbox && !empty($this->config['stevotvr_groupsub_pp_sb_business']);
		$live = !$pp_sandbox && !empty($this->config['stevotvr_groupsub_pp_business']);
		return $sb || $live;
	}
}
