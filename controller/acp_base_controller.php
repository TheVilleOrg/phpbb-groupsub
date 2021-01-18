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
use phpbb\db\driver\driver_interface;
use phpbb\language\language;
use phpbb\request\request_interface;
use phpbb\template\template;
use stevotvr\groupsub\operator\currency_interface;
use stevotvr\groupsub\operator\unit_helper_interface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Group Subscription ACP controller base class.
 */
abstract class acp_base_controller implements acp_base_interface
{
	/**
	 * @var config
	 */
	protected $config;

	/**
	 * @var ContainerInterface
	 */
	protected $container;

	/**
	 * @var currency_interface
	 */
	protected $currency;

	/**
	 * @var driver_interface
	 */
	protected $db;

	/**
	 * @var language
	 */
	protected $language;

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
	 * The URL for the current page.
	 *
	 * @var string
	 */
	protected $u_action;

	/**
	 * @param config                $config
	 * @param ContainerInterface    $container
	 * @param currency_interface    $currency
	 * @param driver_interface      $db
	 * @param language              $language
	 * @param request_interface     $request
	 * @param template              $template
	 * @param unit_helper_interface $unit_helper
	 */
	public function __construct(config $config, ContainerInterface $container, currency_interface $currency, driver_interface $db, language $language, request_interface $request, template $template, unit_helper_interface $unit_helper)
	{
		$this->config = $config;
		$this->container = $container;
		$this->currency = $currency;
		$this->db = $db;
		$this->language = $language;
		$this->request = $request;
		$this->template = $template;
		$this->unit_helper = $unit_helper;
	}

	/**
	 * @inheritDoc
	 */
	public function set_page_url($page_url)
	{
		$this->u_action = $page_url;
	}

	/**
	 * @inheritDoc
	 */
	public function add_lang()
	{
		$this->language->add_lang('acp_common', 'stevotvr/groupsub');
	}

	/**
	 * Assign template block variables for the currency select box.
	 */
	protected function assign_currency_vars()
	{
		foreach ($this->currency->get_currencies() as $code => $currency)
		{
			$this->template->assign_block_vars('currency', array(
				'CURRENCY'	=> $code,
				'SYMBOL'	=> $currency['symbol'],

				'S_DEFAULT'	=> $code === $this->config['stevotvr_groupsub_currency'],
			));
		}
	}
}
