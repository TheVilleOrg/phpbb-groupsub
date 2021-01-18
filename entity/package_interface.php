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
use stevotvr\groupsub\exception\unexpected_value;

/**
 * Group Subscription package entity interface.
 */
interface package_interface extends entity_interface
{
	/**
	 * @return string The unique identifier string for this package
	 */
	public function get_ident();

	/**
	 * @param string $ident The unique identifier string for this package
	 *
	 * @return package_interface This object for chaining
	 *
	 * @throws unexpected_value
	 */
	public function set_ident($ident);

	/**
	 * @return string The name of this package
	 */
	public function get_name();

	/**
	 * @param string $name The name of this package
	 *
	 * @return package_interface This object for chaining
	 *
	 * @throws unexpected_value
	 */
	public function set_name($name);

	/**
	 * @return string The description of this package for editing
	 */
	public function get_desc_for_edit();

	/**
	 * @return string The description of this package for display
	 */
	public function get_desc_for_display();

	/**
	 * @param string $desc The description of this package
	 *
	 * @return package_interface This object for chaining
	 *
	 * @throws unexpected_value
	 */
	public function set_desc($desc);

	/**
	 * @return boolean BBCode is enabled on the description
	 */
	public function is_bbcode_enabled();

	/**
	 * @param boolean $enable Enable BBCode on the description.
	 *
	 * @return package_interface This object for chaining
	 */
	public function set_bbcode_enabled($enable);

	/**
	 * @return boolean URL parsing is enabled on the description
	 */
	public function is_magic_url_enabled();

	/**
	 * @param boolean $enable Enable URL parsing on the description.
	 *
	 * @return package_interface This object for chaining
	 */
	public function set_magic_url_enabled($enable);

	/**
	 * @return boolean Smilies are enabled on the description
	 */
	public function is_smilies_enabled();

	/**
	 * @param boolean $enable Enable smilies on the description.
	 *
	 * @return package_interface This object for chaining
	 */
	public function set_smilies_enabled($enable);

	/**
	 * @return int The order of this package
	 */
	public function get_order();

	/**
	 * @param int $order The order of this package
	 *
	 * @return package_interface This object for chaining
	 *
	 * @throws out_of_bounds
	 */
	public function set_order($order);

	/**
	 * @return boolean The package is enabled
	 */
	public function is_enabled();

	/**
	 * @param boolean $enabled The package is enabled
	 *
	 * @return package_interface This object for chaining
	 */
	public function set_enabled($enabled);
}
