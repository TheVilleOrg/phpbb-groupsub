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
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Group Subscription ACP controller base class.
 */
abstract class acp_base_controller implements acp_base_interface
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
	 * @var \phpbb\db\driver\driver_interface
	 */
	protected $db;

	/**
	 * @var \phpbb\language\language
	 */
	protected $language;

	/**
	 * @var \phpbb\request\request_interface
	 */
	protected $request;

	/**
	 * @var \phpbb\template\template
	 */
	protected $template;

	/**
	 * Array of currencies
	 *
	 * @var array
	 */
	protected $currencies;

	/**
	 * The URL for the current page.
	 *
	 * @var string
	 */
	protected $u_action;

	/**
	 * @param \phpbb\config\config              $config
	 * @param ContainerInterface                $container
	 * @param \phpbb\db\driver\driver_interface $db
	 * @param \phpbb\language\language          $language
	 * @param \phpbb\request\request_interface  $request
	 * @param \phpbb\template\template          $template
	 * @param array                             $currencies List of currencies
	 */
	public function __construct(config $config, ContainerInterface $container, driver_interface $db, language $language, request_interface $request, template $template, array $currencies)
	{
		$this->config = $config;
		$this->container = $container;
		$this->db = $db;
		$this->language = $language;
		$this->request = $request;
		$this->template = $template;
		$this->currencies = $currencies;

		$language->add_lang('common', 'stevotvr/groupsub');
	}

	public function set_page_url($page_url)
	{
		$this->u_action = $page_url;
	}

	/**
	 * Assign template block variables for the currency select box.
	 *
	 * @param string|null $selected The selected currency
	 */
	protected function assign_currency_vars($selected = null)
	{
		$selected = $selected ? $selected : $this->config['stevotvr_groupsub_currency'];
		foreach ($this->currencies as $code => $symbol)
		{
			$this->template->assign_block_vars('currency', array(
				'CURRENCY'	=> $code,
				'SYMBOL'	=> $symbol,

				'S_SELECTED'	=> ($code === $selected),
			));
		}
	}
}
