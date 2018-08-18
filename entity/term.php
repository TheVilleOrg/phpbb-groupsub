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
 * Group Subscription term entity.
 */
class term extends entity implements term_interface
{
	protected $columns = array(
		'term_id'		=> 'integer',
		'pkg_id'		=> 'integer',
		'term_price'	=> 'set_price',
		'term_currency'	=> 'set_currency',
		'term_length'	=> 'set_length',
		'term_order'	=> 'set_order',
	);

	protected $id_column = 'term_id';

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

		if ($package_id <= 0)
		{
			throw new out_of_bounds('pkg_id');
		}

		$this->data['pkg_id'] = $package_id;

		return $this;
	}

	public function get_price()
	{
		return isset($this->data['term_price']) ? (int) $this->data['term_price'] : null;
	}

	public function set_price($price)
	{
		$price = (int) $price;

		if ($price < 0 || $price > 16777215)
		{
			throw new out_of_bounds('term_price');
		}

		$this->data['term_price'] = $price;

		return $this;
	}

	public function get_currency()
	{
		return isset($this->data['term_currency']) ? (string) $this->data['term_currency'] : '';
	}

	public function set_currency($currency)
	{
		$currency = strtoupper((string) $currency);

		if (!isset($this->currencies[$currency]))
		{
			throw new unexpected_value('term_currency', 'INVALID_CURRENCY');
		}

		$this->data['term_currency'] = $currency;

		return $this;
	}

	public function get_length()
	{
		return isset($this->data['term_length']) ? (int) $this->data['term_length'] : null;
	}

	public function set_length($length)
	{
		$length = (int) $length;

		if ($length < 0 || $length > 16777215)
		{
			throw new out_of_bounds('term_length');
		}

		$this->data['term_length'] = $length;

		return $this;
	}

	public function get_order()
	{
		return isset($this->data['term_order']) ? (int) $this->data['term_order'] : 0;
	}

	public function set_order($order)
	{
		$order = (int) $order;

		if ($order < 0 || $order > 16777215)
		{
			throw new out_of_bounds('term_order');
		}

		$this->data['term_order'] = $order;

		return $this;
	}
}
