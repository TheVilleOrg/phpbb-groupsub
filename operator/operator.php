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

use Symfony\Component\DependencyInjection\ContainerInterface;
use phpbb\db\driver\driver_interface;

/**
 * Group Subscription subscription operator base class.
 */
class operator
{
	/**
	 * @var \Symfony\Component\DependencyInjection\ContainerInterface
	 */
	protected $container;

	/**
	 * @var \phpbb\db\driver\driver_interface
	 */
	protected $db;

	/**
	 * The name of the groupsub table.
	 *
	 * @var string
	 */
	protected $product_table;

	/**
	 * The name of the groupsub_groups table.
	 *
	 * @var string
	 */
	protected $group_table;

	/**
	 * The name of the groupsub_subs table.
	 *
	 * @var string
	 */
	protected $sub_table;

	/**
	 * @param ContainerInterface                $container
	 * @param \phpbb\db\driver\driver_interface $db
	 * @param string                            $product_table The name of the groupsub table
	 * @param string                            $group_table   The name of the groupsub_groups table
	 * @param string                            $sub_table     The name of the groupsub_subs table
	 */
	public function __construct(ContainerInterface $container, driver_interface $db, $product_table, $group_table, $sub_table)
	{
		$this->container = $container;
		$this->db = $db;
		$this->product_table = $product_table;
		$this->group_table = $group_table;
		$this->sub_table = $sub_table;
	}
}
