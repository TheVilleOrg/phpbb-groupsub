<?php
/**
 *
 * Group Subscription. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, Steve Guidetti, https://github.com/stevotvr
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace stevotvr\groupsub\controller;

use phpbb\config\config;
use phpbb\controller\helper;
use phpbb\language\language;
use phpbb\template\template;
use phpbb\user;
use stevotvr\groupsub\operator\currency_interface;
use stevotvr\groupsub\operator\package_interface;
use stevotvr\groupsub\operator\subscription_interface;
use stevotvr\groupsub\operator\unit_helper_interface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Group Subscription controller for the main user-facing interface.
 */
class main_controller
{
	/**
	 * @var \phpbb\config\config
	 */
	protected $config;

	/**
	 * @var \Symfony\Component\DependencyInjection\ContainerInterface
	 */
	protected $container;

	/**
	 * @var \stevotvr\groupsub\operator\currency_interface
	 */
	protected $currency;

	/**
	 * @var \phpbb\controller\helper
	 */
	protected $helper;

	/**
	 * @var \phpbb\language\language
	 */
	protected $language;

	/**
	 * @var \stevotvr\groupsub\operator\package_interface
	 */
	protected $pkg_operator;

	/**
	 * @var \stevotvr\groupsub\operator\subscription_interface
	 */
	protected $sub_operator;

	/**
	 * @var \phpbb\template\template
	 */
	protected $template;

	/**
	 * @var \stevotvr\groupsub\operator\unit_helper_interface
	 */
	protected $unit_helper;

	/**
	 * @var \phpbb\user
	 */
	protected $user;

	/**
	 * @param \phpbb\config\config                               $config
	 * @param ContainerInterface                                 $container
	 * @param \stevotvr\groupsub\operator\currency_interface     $currency
	 * @param \phpbb\controller\helper                           $helper
	 * @param \phpbb\language\language                           $language
	 * @param \stevotvr\groupsub\operator\package_interface      $pkg_operator
	 * @param \stevotvr\groupsub\operator\subscription_interface $sub_operator
	 * @param \phpbb\template\template                           $template
	 * @param \stevotvr\groupsub\operator\unit_helper_interface  $unit_helper
	 * @param \phpbb\user                                        $user
	 */
	public function __construct(config $config, ContainerInterface $container, currency_interface $currency, helper $helper, language $language, package_interface $pkg_operator, subscription_interface $sub_operator, template $template, unit_helper_interface $unit_helper, user $user)
	{
		$this->config = $config;
		$this->container = $container;
		$this->currency = $currency;
		$this->helper = $helper;
		$this->language = $language;
		$this->pkg_operator = $pkg_operator;
		$this->sub_operator = $sub_operator;
		$this->template = $template;
		$this->unit_helper = $unit_helper;
		$this->user = $user;
	}

	/**
	 * Handle the /groupsub/{name} route.
	 *
	 * @param string|null $name The unique identifier of a package
	 *
	 * @return \Symfony\Component\HttpFoundation\Response A Symfony Response object
	 */
	public function handle($name)
	{
		$u_board = generate_board_url(true);
		$sandbox = $this->config['stevotvr_groupsub_pp_sandbox'];
		$business = $this->config[$sandbox ? 'stevotvr_groupsub_pp_sb_business' : 'stevotvr_groupsub_pp_business'];

		if (empty($business))
		{
			trigger_error('GROUPSUB_NO_PACKAGES');
		}

		$this->template->assign_vars(array(
			'S_PP_SANDBOX'	=> $sandbox,

			'USER_ID'		=> $this->user->data['user_id'],
			'PP_BUSINESS'	=> $business,

			'U_NOTIFY'			=> $u_board . $this->helper->route('stevotvr_groupsub_ipn'),
			'U_CANCEL_RETURN'	=> $u_board . $this->helper->route('stevotvr_groupsub_main'),
		));

		$packages = $this->pkg_operator->get_packages($name);
		$package_groups = $this->pkg_operator->get_all_groups();
		foreach ($packages as $package)
		{
			$id = $package->get_id();
			$price = $package->get_price();
			$currency = $package->get_currency();
			$this->template->assign_block_vars('package', array(
				'PKG_ID'			=> $id,
				'PKG_NAME'			=> $package->get_name(),
				'PKG_DESC'			=> $package->get_desc_for_display(),
				'PKG_PRICE'			=> $this->currency->format_value($currency, $price),
				'PKG_CURRENCY'		=> $currency,
				'PKG_DISPLAY_PRICE'	=> $this->currency->format_price($currency, $price),
				'PKG_LENGTH'		=> $this->unit_helper->get_formatted_timespan($package->get_length()),

				'U_RETURN'	=> $u_board . $this->helper->route('stevotvr_groupsub_main', array('name' => $package->get_ident())),
			));

			if (isset($package_groups[$id]))
			{
				foreach ($package_groups[$id] as $group)
				{
					$this->template->assign_block_vars('package.group', array(
						'GROUP_NAME'	=> $group['name'],
					));
				}
			}
		}

		return $this->helper->render('package_list.html', $this->language->lang('GROUPSUB_PACKAGE_LIST'));
	}
}
