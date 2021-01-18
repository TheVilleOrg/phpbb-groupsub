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
use phpbb\exception\http_exception;
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
	 * @var helper
	 */
	protected $helper;

	/**
	 * @var language
	 */
	protected $language;

	/**
	 * @var package_interface
	 */
	protected $pkg_operator;

	/**
	 * @var request_interface
	 */
	protected $request;

	/**
	 * @var template
	 */
	protected $template;

	/**
	 * @var unit_helper_interface
	 */
	protected $unit_helper;

	/**
	 * @param helper                $helper
	 * @param language              $language
	 * @param package_interface     $pkg_operator
	 * @param request_interface     $request
	 * @param template              $template
	 * @param unit_helper_interface $unit_helper
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
	 * @return Response A Symfony Response object
	 */
	public function handle()
	{
		$term_id = $this->request->variable('term_id', 0);
		if (!$term_id)
		{
			throw new http_exception(404, 'PAGE_NOT_FOUND');
		}

		$term = $this->pkg_operator->get_package_term($term_id);
		if (!$term)
		{
			throw new http_exception(404, 'PAGE_NOT_FOUND');
		}

		$length = $term['term']->get_length();
		$length = $length > 0 ? $this->unit_helper->get_formatted_timespan($length) : $this->language->lang('GROUPSUB_RETURN_UNLIMITED');

		$this->template->assign_vars(array(
			'PKG_NAME'		=> $term['package']->get_name(),
			'TERM_LENGTH'	=> $length,
		));

		return $this->helper->render('@stevotvr_groupsub/payment_return.html', $this->language->lang('GROUPSUB_RETURN_TITLE'));
	}
}
