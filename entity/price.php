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
		'p_id'						=> 'integer',
		'gs_id'						=> 'integer',
		'p_price'					=> 'set_price',
		'p_currency'				=> 'set_currency',
		'p_length'					=> 'set_length',
		'p_order'					=> 'set_order',
	);

	protected $id_column = 'p_id';

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

	public function get_product()
	{
		return isset($this->data['gs_id']) ? (int) $this->data['gs_id'] : 0;
	}

	public function set_product($product_id)
	{
		$product_id = (int) $product_id;

		if ($product_id < 0)
		{
			throw new out_of_bounds('gs_id');
		}

		$this->data['gs_id'] = $product_id;

		return $this;
	}

	public function get_price()
	{
		return isset($this->data['p_price']) ? (int) $this->data['p_price'] : null;
	}

	public function set_price($price)
	{
		$price = (int) $price;

		if ($price < 0 || $price > 16777215)
		{
			throw new out_of_bounds('p_price');
		}

		$this->data['p_price'] = $price;

		return $this;
	}

	public function get_currency()
	{
		return isset($this->data['p_currency']) ? (string) $this->data['p_currency'] : '';
	}

	public function set_currency($currency)
	{
		$currency = strtoupper((string) $currency);

		if (!isset($this->currencies[$currency]))
		{
			throw new unexpected_value('p_currency', 'INVALID_CURRENCY');
		}

		$this->data['p_currency'] = $currency;

		return $this;
	}

	public function get_length()
	{
		return isset($this->data['p_length']) ? (int) $this->data['p_length'] : null;
	}

	public function set_length($length)
	{
		$length = (int) $length;

		if ($length < 0 || $length > 16777215)
		{
			throw new out_of_bounds('p_length');
		}

		$this->data['p_length'] = $length;

		return $this;
	}

	public function get_order()
	{
		return isset($this->data['p_order']) ? (int) $this->data['p_order'] : 0;
	}

	public function set_order($order)
	{
		$order = (int) $order;

		if ($order < 0 || $order > 16777215)
		{
			throw new out_of_bounds('p_order');
		}

		$this->data['p_order'] = $order;

		return $this;
	}
}
