<?php
/**
 *
 * Group Subscription. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, Steve Guidetti, https://github.com/stevotvr
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace stevotvr\groupsub\operator;

use phpbb\config\config;
use phpbb\db\driver\driver_interface;
use phpbb\language\language;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Group Subscription subscription operator base class.
 */
abstract class operator
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
	 * @var driver_interface
	 */
	protected $db;

	/**
	 * @var language
	 */
	protected $language;

	/**
	 * Array of currencies
	 *
	 * @var array
	 */
	protected $currencies;

	/**
	 * The name of the groupsub_packages table.
	 *
	 * @var string
	 */
	protected $package_table;

	/**
	 * The name of the groupsub_actions table.
	 *
	 * @var string
	 */
	protected $action_table;

	/**
	 * The name of the groupsub_terms table.
	 *
	 * @var string
	 */
	protected $term_table;

	/**
	 * The name of the groupsub_subs table.
	 *
	 * @var string
	 */
	protected $sub_table;

	/**
	 * @param config             $config
	 * @param ContainerInterface $container
	 * @param driver_interface   $db
	 * @param language           $language
	 * @param array              $currencies    List of currencies
	 * @param string             $package_table The name of the groupsub_packages table
	 * @param string             $action_table  The name of the groupsub_actions table
	 * @param string             $term_table    The name of the groupsub_terms table
	 * @param string             $sub_table     The name of the groupsub_subs table
	 */
	public function __construct(config $config, ContainerInterface $container, driver_interface $db, language $language, array $currencies, $package_table, $action_table, $term_table, $sub_table)
	{
		$this->config = $config;
		$this->container = $container;
		$this->db = $db;
		$this->language = $language;
		$this->currencies = $currencies;
		$this->package_table = $package_table;
		$this->action_table = $action_table;
		$this->term_table = $term_table;
		$this->sub_table = $sub_table;
	}
}
