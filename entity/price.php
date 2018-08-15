<?php
/**
 *
 * Group Subscription. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, Steve Guidetti, https://github.com/stevotvr
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace stevotvr\groupsub\entity;

use stevotvr\groupsub\exception\out_of_bounds;
use stevotvr\groupsub\exception\unexpected_value;

/**
 * Group Subscription price option entity.
 */
class price extends entity implements price_interface
{
	protected $columns = array(
		'price_id'			=> 'integer',
		'pkg_id'			=> 'integer',
		'price_amount'		=> 'set_amount',
		'price_currency'	=> 'set_currency',
		'price_length'		=> 'set_length',
		'price_order'		=> 'set_order',
	);

	protected $id_column = 'price_id';

	/**
	 * Array of currencies
	 *
	 * @var array
	 */
	protected $currencies;

	/**
	 * Set up the entity with the list of currencies.
	 *
	 * @param array $currencies List of currencies
	 */
	public function setup(array $currencies)
	{
		$this->currencies = $currencies;
	}

	public function get_package()
	{
		return isset($this->data['pkg_id']) ? (int) $this->data['pkg_id'] : 0;
	}

	public function set_package($package_id)
	{
		$package_id = (int) $package_id;

		if ($package_id < 0)
		{
			throw new out_of_bounds('pkg_id');
		}

		$this->data['pkg_id'] = $package_id;

		return $this;
	}

	public function get_amount()
	{
		return isset($this->data['price_amount']) ? (int) $this->data['price_amount'] : null;
	}

	public function set_amount($amount)
	{
		$amount = (int) $amount;

		if ($amount < 0 || $amount > 16777215)
		{
			throw new out_of_bounds('price_amount');
		}

		$this->data['price_amount'] = $amount;

		return $this;
	}

	public function get_currency()
	{
		return isset($this->data['price_currency']) ? (string) $this->data['price_currency'] : '';
	}

	public function set_currency($currency)
	{
		$currency = strtoupper((string) $currency);

		if (!isset($this->currencies[$currency]))
		{
			throw new unexpected_value('price_currency', 'INVALID_CURRENCY');
		}

		$this->data['price_currency'] = $currency;

		return $this;
	}

	public function get_length()
	{
		return isset($this->data['price_length']) ? (int) $this->data['price_length'] : null;
	}

	public function set_length($length)
	{
		$length = (int) $length;

		if ($length < 0 || $length > 16777215)
		{
			throw new out_of_bounds('price_length');
		}

		$this->data['price_length'] = $length;

		return $this;
	}

	public function get_order()
	{
		return isset($this->data['price_order']) ? (int) $this->data['price_order'] : 0;
	}

	public function set_order($order)
	{
		$order = (int) $order;

		if ($order < 0 || $order > 16777215)
		{
			throw new out_of_bounds('price_order');
		}

		$this->data['price_order'] = $order;

		return $this;
	}
}
