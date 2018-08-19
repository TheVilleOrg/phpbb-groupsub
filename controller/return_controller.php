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

use phpbb\controller\helper;
use phpbb\language\language;
use phpbb\request\request_interface;
use phpbb\template\template;
use stevotvr\groupsub\operator\package_interface;
use stevotvr\groupsub\operator\unit_helper_interface;

/**
 * Group Subscription controller for the payment return page.
 */
class return_controller
{
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
	 * @var \phpbb\template\template
	 */
	protected $template;

	/**
	 * @var \stevotvr\groupsub\operator\unit_helper_interface
	 */
	protected $unit_helper;

	/**
	 * @param \phpbb\controller\helper                          $helper
	 * @param \phpbb\language\language                          $language
	 * @param \stevotvr\groupsub\operator\package_interface     $pkg_operator
	 * @param \phpbb\request\request_interface                  $request
	 * @param \phpbb\template\template                          $template
	 * @param \stevotvr\groupsub\operator\unit_helper_interface $unit_helper
	 */
	public function __construct(helper $helper, language $language, package_interface $pkg_operator, request_interface $request, template $template, unit_helper_interface $unit_helper)
	{
		$this->helper = $helper;
		$this->language = $language;
		$this->pkg_operator = $pkg_operator;
		$this->request = $request;
		$this->template = $template;
		$this->unit_helper = $unit_helper;
	}

	/**
	 * Handle the /groupsub/return route.
	 *
	 * @return \Symfony\Component\HttpFoundation\Response A Symfony Response object
	 */
	public function handle()
	{
		$term_id = $this->request->variable('term_id', 0);
		if (!$term_id)
		{
			trigger_error('NOT_FOUND');
		}

		$term = $this->pkg_operator->get_package_term($term_id);
		if (!$term)
		{
			trigger_error('NOT_FOUND');
		}

		extract($term);
		$this->template->assign_vars(array(
			'PKG_NAME'		=> $package->get_name(),
			'TERM_LENGTH'	=> $this->unit_helper->get_formatted_timespan($term->get_length()),
		));

		return $this->helper->render('payment_return.html', $this->language->lang('GROUPSUB_RETURN_TITLE'));
	}
}
