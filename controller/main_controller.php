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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
			return $this->helper->error('PAGE_NOT_FOUND', 404);
		}

		if ($this->user->data['user_id'] == ANONYMOUS)
		{
			$u_redirect = $this->helper->route('stevotvr_groupsub_main', array('name' => $name));
			redirect(append_sid($this->root_path . 'ucp.' . $this->php_ext, 'mode=login&amp;redirect=' . $u_redirect));
		}

		$this->template->assign_vars(array(
			'U_ACTION'	=> $this->helper->route('stevotvr_groupsub_main', array('name' => $name)),
		));

		$term_id = $this->request->variable('term_id', 0);
		if ($term_id)
		{
			return $this->select_term($term_id);
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
			return $this->helper->error('GROUPSUB_NO_PACKAGES', 404);
		}

		$subscriptions = $this->sub_operator->get_user_subscriptions($this->user->data['user_id']);
		$warn = $this->config['stevotvr_groupsub_warn_time'] * 86400;

		foreach ($packages as $package)
		{
			$vars = array(
				'ID'	=> $package['package']->get_id(),
				'NAME'	=> $package['package']->get_name(),
				'DESC'	=> $package['package']->get_desc_for_display(),
			);

			if (isset($subscriptions[$package['package']->get_id()]))
			{
				$expires = $subscriptions[$package['package']->get_id()]->get_expire();

				$vars = array_merge($vars, array(
					'S_ACTIVE'	=> true,
					'S_WARNING'	=> $expires && (($expires - time()) < $warn),

					'EXPIRES'	=> $expires ? $this->user->format_date($expires) : 0,
				));
			}

			$this->template->assign_block_vars('package', $vars);

			foreach ($package['terms'] as $term)
			{
				$this->template->assign_block_vars('package.term', array(
					'ID'		=> $term->get_id(),
					'PRICE'		=> $this->currency->format_price($term->get_currency(), $term->get_price()),
					'LENGTH'	=> $term->get_length() ? $this->unit_helper->get_formatted_timespan($term->get_length()) : 0,
				));
			}
		}

		return $this->helper->render('package_list.html', $this->language->lang('GROUPSUB_PACKAGE_LIST'));
	}

	/**
	 * Show the details of a package term.
	 *
	 * @param int $term_id The term ID
	 *
	 * @return \Symfony\Component\HttpFoundation\Response A Symfony Response object
	 */
	protected function select_term($term_id)
	{
		$u_board = generate_board_url(true);
		$sandbox = $this->config['stevotvr_groupsub_pp_sandbox'];
		$business = $this->config[$sandbox ? 'stevotvr_groupsub_pp_sb_business' : 'stevotvr_groupsub_pp_business'];

		$term = $this->pkg_operator->get_package_term($term_id);
		if (!$term)
		{
			return $this->helper->error('PAGE_NOT_FOUND', 404);
		}

		$price = $term['term']->get_price();
		$currency = $term['term']->get_currency();

		$u_ipn = $this->helper->route('stevotvr_groupsub_ipn', array(), true, false, UrlGeneratorInterface::ABSOLUTE_URL);
		$return_params = array('term_id' => $term['term']->get_id());
		$u_return = $this->helper->route('stevotvr_groupsub_return', $return_params, true, false, UrlGeneratorInterface::ABSOLUTE_URL);
		$u_main = $this->helper->route('stevotvr_groupsub_main', array(), true, false, UrlGeneratorInterface::ABSOLUTE_URL);

		$this->template->assign_vars(array(
			'S_PP_SANDBOX'	=> $sandbox,

			'USER_ID'		=> $this->user->data['user_id'],
			'PP_BUSINESS'	=> $business,

			'PKG_NAME'				=> $term['package']->get_name(),
			'PKG_DESC'				=> $term['package']->get_desc_for_display(),
			'TERM_ID'				=> $term['term']->get_id(),
			'TERM_PRICE'			=> $this->currency->format_value($currency, $price),
			'TERM_CURRENCY'			=> $currency,
			'TERM_DISPLAY_PRICE'	=> $this->currency->format_price($currency, $price),
			'TERM_LENGTH'			=> $term['term']->get_length() ? $this->unit_helper->get_formatted_timespan($term['term']->get_length()) : 0,

			'U_NOTIFY'			=> $u_ipn,
			'U_RETURN'			=> $u_return,
			'U_CANCEL_RETURN'	=> $u_main,
		));

		return $this->helper->render('select_term.html', $term['package']->get_name());
	}
}
