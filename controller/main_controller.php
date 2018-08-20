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

use phpbb\auth\auth;
use phpbb\config\config;
use phpbb\controller\helper;
use phpbb\language\language;
use phpbb\request\request_interface;
use phpbb\template\template;
use phpbb\user;
use stevotvr\groupsub\operator\currency_interface;
use stevotvr\groupsub\operator\package_interface;
use stevotvr\groupsub\operator\subscription_interface;
use stevotvr\groupsub\operator\unit_helper_interface;

/**
 * Group Subscription controller for the main user-facing interface.
 */
class main_controller
{
	/**
	 * @var \phpbb\auth\auth
	 */
	protected $auth;

	/**
	 * @var \phpbb\config\config
	 */
	protected $config;

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
	 * @var \phpbb\request\request_interface
	 */
	protected $request;

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
	 * The root phpBB path.
	 *
	 * @var string
	 */
	protected $root_path;
	/**
	 * The script file extension.
	 *
	 * @var string
	 */
	protected $php_ext;

	/**
	 * @param \phpbb\auth\auth                                   $auth
	 * @param \phpbb\config\config                               $config
	 * @param \stevotvr\groupsub\operator\currency_interface     $currency
	 * @param \phpbb\controller\helper                           $helper
	 * @param \phpbb\language\language                           $language
	 * @param \stevotvr\groupsub\operator\package_interface      $pkg_operator
	 * @param \phpbb\request\request_interface                   $request
	 * @param \stevotvr\groupsub\operator\subscription_interface $sub_operator
	 * @param \phpbb\template\template                           $template
	 * @param \stevotvr\groupsub\operator\unit_helper_interface  $unit_helper
	 * @param \phpbb\user                                        $user
	 */
	public function __construct(auth $auth, config $config, currency_interface $currency, helper $helper, language $language, package_interface $pkg_operator, request_interface $request, subscription_interface $sub_operator, template $template, unit_helper_interface $unit_helper, user $user)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->currency = $currency;
		$this->helper = $helper;
		$this->language = $language;
		$this->pkg_operator = $pkg_operator;
		$this->request = $request;
		$this->sub_operator = $sub_operator;
		$this->template = $template;
		$this->unit_helper = $unit_helper;
		$this->user = $user;
	}

	/**
	 * Set the phpBB installation path information.
	 *
	 * @param string $root_path The root phpBB path
	 * @param string $php_ext   The script file extension
	 */
	public function set_path_info($root_path, $php_ext)
	{
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
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
		if (!$this->config['stevotvr_groupsub_active'] && !$this->auth->acl_get('a_'))
		{
			trigger_error('NOT_FOUND');
		}

		if ($this->user->data['user_id'] == ANONYMOUS)
		{
			$u_redirect = $this->helper->route('stevotvr_groupsub_main', array('name' => $name));
			redirect(append_sid($this->root_path . 'ucp.' . $this->php_ext, 'mode=login&amp;redirect=' . $u_redirect));
		}

		$term_id = $this->request->variable('term_id', 0);
		if ($term_id)
		{
			return $this->select_term($term_id, $name);
		}

		return $this->list_packages($name);
	}

	/**
	 * Show the list of available packages.
	 *
	 * @param string|null $name The unique identifier of a package
	 *
	 * @return \Symfony\Component\HttpFoundation\Response A Symfony Response object
	 */
	protected function list_packages($name)
	{
		$packages = $this->pkg_operator->get_packages($name);

		if (empty($packages))
		{
			trigger_error('GROUPSUB_NO_PACKAGES');
		}

		$subscriptions = $this->sub_operator->get_user_subscriptions($this->user->data['user_id']);
		$warn = $this->config['stevotvr_groupsub_warn_time'] * 86400;

		foreach ($packages as $package)
		{
			extract($package);
			$vars = array(
				'ID'	=> $package->get_id(),
				'NAME'	=> $package->get_name(),
				'DESC'	=> $package->get_desc_for_display(),
			);

			if (isset($subscriptions[$package->get_id()]))
			{
				$expires = $subscriptions[$package->get_id()]->get_expire();

				$vars = array_merge($vars, array(
					'S_ACTIVE'	=> true,
					'S_WARNING'	=> ($expires - time()) < $warn,

					'EXPIRES'	=> $this->user->format_date($expires),
				));
			}

			$this->template->assign_block_vars('package', $vars);

			foreach ($terms as $term)
			{
				$this->template->assign_block_vars('package.term', array(
					'ID'		=> $term->get_id(),
					'PRICE'		=> $this->currency->format_price($term->get_currency(), $term->get_price()),
					'LENGTH'	=> $this->unit_helper->get_formatted_timespan($term->get_length()),
				));
			}
		}

		return $this->helper->render('package_list.html', $this->language->lang('GROUPSUB_PACKAGE_LIST'));
	}

	/**
	 * Show the details of a package term.
	 *
	 * @param int         $term_id The term ID
	 * @param string|null $name    The unique identifier of a package
	 *
	 * @return \Symfony\Component\HttpFoundation\Response A Symfony Response object
	 */
	protected function select_term($term_id, $name)
	{
		$u_board = generate_board_url(true);
		$sandbox = $this->config['stevotvr_groupsub_pp_sandbox'];
		$business = $this->config[$sandbox ? 'stevotvr_groupsub_pp_sb_business' : 'stevotvr_groupsub_pp_business'];

		$term = $this->pkg_operator->get_package_term($term_id);
		if (!$term)
		{
			trigger_error('NOT_FOUND');
		}

		extract($term);

		$price = $term->get_price();
		$currency = $term->get_currency();

		$this->template->assign_vars(array(
			'S_PP_SANDBOX'	=> $sandbox,

			'USER_ID'		=> $this->user->data['user_id'],
			'PP_BUSINESS'	=> $business,

			'PKG_NAME'				=> $package->get_name(),
			'PKG_DESC'				=> $package->get_desc_for_display(),
			'TERM_ID'				=> $term->get_id(),
			'TERM_PRICE'			=> $this->currency->format_value($currency, $price),
			'TERM_CURRENCY'			=> $currency,
			'TERM_DISPLAY_PRICE'	=> $this->currency->format_price($currency, $price),
			'TERM_LENGTH'			=> $this->unit_helper->get_formatted_timespan($term->get_length()),

			'U_ACTION'			=> $this->helper->route('stevotvr_groupsub_main', array('name' => $name)),
			'U_NOTIFY'			=> $u_board . $this->helper->route('stevotvr_groupsub_ipn'),
			'U_RETURN'			=> $u_board . $this->helper->route('stevotvr_groupsub_return') . '?term_id=' . $term->get_id(),
			'U_CANCEL_RETURN'	=> $u_board . $this->helper->route('stevotvr_groupsub_main'),
		));

		return $this->helper->render('select_term.html', $package->get_name());
	}
}
