<?php
/**
 *
 * Group Subscription. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, Steve Guidetti, https://github.com/stevotvr
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace stevotvr\groupsub\entity;

use stevotvr\groupsub\exception\out_of_bounds;

/**
 * Group Subscription subscription entity.
 */
class subscription extends entity implements subscription_interface
{
	protected $columns = array(
		'sub_id'			=> 'integer',
		'pkg_id'			=> 'integer',
		'user_id'			=> 'integer',
		'sub_active'		=> 'boolean',
		'sub_start'			=> 'set_start',
		'sub_expires'		=> 'set_expire',
		'sub_paypal_id'		=> 'set_paypal_id',
	);

	protected $id_column = 'sub_id';

	/**
	 * @inheritDoc
	 */
	public function is_active()
	{
		return (bool) $this->data['sub_active'];
	}

	/**
	 * @inheritDoc
	 */
	public function get_package()
	{
		return isset($this->data['pkg_id']) ? (int) $this->data['pkg_id'] : 0;
	}

	/**
	 * @inheritDoc
	 */
	public function set_package($package_id)
	{
		$package_id = (int) $package_id;

		if ($package_id <= 0)
		{
			throw new out_of_bounds('pkg_id');
		}

		$this->data['pkg_id'] = $package_id;

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function get_user()
	{
		return isset($this->data['user_id']) ? (int) $this->data['user_id'] : 0;
	}

	/**
	 * @inheritDoc
	 */
	public function set_user($user_id)
	{
		$user_id = (int) $user_id;

		if ($user_id <= 0)
		{
			throw new out_of_bounds('user_id');
		}

		$this->data['user_id'] = $user_id;

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function get_start()
	{
		return isset($this->data['sub_start']) ? (int) $this->data['sub_start'] : 0;
	}

	/**
	 * @inheritDoc
	 */
	public function set_start($start)
	{
		$start = (int) $start;

		if ($start < 0)
		{
			throw new out_of_bounds('sub_start');
		}

		$this->data['sub_start'] = $start;

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function get_expire()
	{
		return isset($this->data['sub_expires']) ? (int) $this->data['sub_expires'] : 0;
	}

	/**
	 * @inheritDoc
	 */
	public function set_expire($expire)
	{
		$expire = (int) $expire;

		if ($expire < 0)
		{
			throw new out_of_bounds('sub_expires');
		}

		$this->data['sub_expires'] = $expire;

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function get_paypal_id()
	{
		return isset($this->data['sub_paypal_id']) ? (string) $this->data['sub_paypal_id'] : null;
	}

	/**
	 * @inheritDoc
	 */
	public function set_paypal_id($paypal_id)
	{
		$this->data['sub_paypal_id'] = isset($paypal_id) ? (string) $paypal_id : null;

		return $this;
	}
}
