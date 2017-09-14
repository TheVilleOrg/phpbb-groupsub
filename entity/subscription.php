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
		'sub_id'		=> 'integer',
		'gs_id'			=> 'integer',
		'user_id'		=> 'integer',
		'sub_expires'	=> 'set_expire',
	);

	protected $id_column = 'sub_id';

	public function get_product()
	{
		return isset($this->data['gs_id']) ? (int) $this->data['gs_id'] : 0;
	}

	public function set_product($product)
	{
		$product = (int) $product;

		if ($product < 0)
		{
			throw new out_of_bounds('gs_id');
		}

		$this->data['gs_id'] = $product;

		return $this;
	}

	public function get_user()
	{
		return isset($this->data['user_id']) ? (int) $this->data['user_id'] : 0;
	}

	public function set_user($user)
	{
		$user = (int) $user;

		if ($user < 0)
		{
			throw new out_of_bounds('user_id');
		}

		$this->data['user_id'] = $user;

		return $this;
	}

	public function get_expire()
	{
		return isset($this->data['sub_expires']) ? (int) $this->data['sub_expires'] : 0;
	}

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
}
