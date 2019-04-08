<?php
/**
 *
 * Group Subscription. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, Steve Guidetti, https://github.com/stevotvr
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace stevotvr\groupsub\cron\task;

use phpbb\config\config;
use phpbb\cron\task\base;
use stevotvr\groupsub\operator\subscription_interface;

/**
 * Group Subscription main cron task.
 */
class main extends base
{
	/* The interval of the cron task in seconds */
	const INTERVAL = 3600;

	/**
	 * @var \phpbb\config\config
	 */
	protected $config;

	/**
	 * @var \stevotvr\groupsub\operator\subscription_interface
	 */
	protected $sub_operator;

	/**
	 * @param \phpbb\config\config                               $config
	 * @param \stevotvr\groupsub\operator\subscription_interface $sub_operator
	 */
	public function __construct(config $config, subscription_interface $sub_operator)
	{
		$this->config = $config;
		$this->sub_operator = $sub_operator;
	}

	/**
	 * @inheritDoc
	 */
	public function run()
	{
		$this->sub_operator->process_expiring();
		$this->sub_operator->notify_subscribers();

		$this->config->set('stevotvr_groupsub_cron_last_run', time());
	}

	/**
	 * @inheritDoc
	 */
	public function should_run()
	{
		return (time() - (int) $this->config['stevotvr_groupsub_cron_last_run']) > self::INTERVAL;
	}
}
