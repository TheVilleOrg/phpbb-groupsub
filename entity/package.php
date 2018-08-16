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

use phpbb\config\config;
use stevotvr\groupsub\exception\missing_field;
use stevotvr\groupsub\exception\out_of_bounds;
use stevotvr\groupsub\exception\unexpected_value;

/**
 * Group Subscription package entity.
 */
class package extends entity implements package_interface
{
	protected $columns = array(
		'pkg_id'					=> 'integer',
		'pkg_ident'					=> 'string',
		'pkg_name'					=> 'set_name',
		'pkg_desc'					=> 'string',
		'pkg_desc_bbcode_uid'		=> 'string',
		'pkg_desc_bbcode_bitfield'	=> 'string',
		'pkg_desc_bbcode_options'	=> 'integer',
		'pkg_warn_time'				=> 'set_warn_time',
		'pkg_grace'					=> 'set_grace',
		'pkg_order'					=> 'set_order',
	);

	protected $id_column = 'pkg_id';

	/**
	 * @var \phpbb\config\config
	 */
	protected $config;

	/**
	 * Set up the entity with the configuration.
	 *
	 * @param \phpbb\config\config $config
	 */
	public function setup(config $config)
	{
		$this->config = $config;
	}

	public function get_ident()
	{
		return isset($this->data['pkg_ident']) ? (string) $this->data['pkg_ident'] : '';
	}

	public function set_ident($ident)
	{
		$ident = strtolower(utf8_clean_string((string) $ident));
		$ident = preg_replace('/\s+/', '_', $ident);

		if ($ident === '')
		{
			throw new missing_field('pkg_ident');
		}

		if (truncate_string($ident, 30) !== $ident)
		{
			throw new unexpected_value('pkg_ident', 'TOO_LONG');
		}

		if (!preg_match('/^[a-z][a-z\d_]*$/', $ident))
		{
			throw new unexpected_value('pkg_ident', 'INVALID_IDENT');
		}

		$sql = 'SELECT pkg_id
				FROM ' . $this->table_name . "
				WHERE pkg_ident = '" . $this->db->sql_escape($ident) . "'";
		$this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow();
		$this->db->sql_freeresult();
		if ($row !== false)
		{
			throw new unexpected_value('pkg_ident', 'NOT_UNIQUE');
		}

		$this->data['pkg_ident'] = $ident;

		return $this;
	}

	public function get_name()
	{
		return isset($this->data['pkg_name']) ? (string) $this->data['pkg_name'] : '';
	}

	public function set_name($name)
	{
		$name = (string) $name;

		if ($name === '')
		{
			throw new missing_field('pkg_name');
		}

		if (truncate_string($name, 255) !== $name)
		{
			throw new unexpected_value('pkg_name', 'TOO_LONG');
		}

		$this->data['pkg_name'] = $name;

		return $this;
	}

	public function get_desc_for_edit()
	{
		$content = isset($this->data['pkg_desc']) ? $this->data['pkg_desc'] : '';
		$uid = isset($this->data['pkg_desc_bbcode_uid']) ? $this->data['pkg_desc_bbcode_uid'] : '';
		$options = isset($this->data['pkg_desc_bbcode_options']) ? (int) $this->data['pkg_desc_bbcode_options'] : 0;

		$content_data = generate_text_for_edit($content, $uid, $options);

		return $content_data['text'];
	}

	public function get_desc_for_display()
	{
		$content = isset($this->data['pkg_desc']) ? $this->data['pkg_desc'] : '';
		$uid = isset($this->data['pkg_desc_bbcode_uid']) ? $this->data['pkg_desc_bbcode_uid'] : '';
		$bitfield = isset($this->data['pkg_desc_bbcode_bitfield']) ? $this->data['pkg_desc_bbcode_bitfield'] : '';
		$options = isset($this->data['pkg_desc_bbcode_options']) ? (int) $this->data['pkg_desc_bbcode_options'] : 0;

		return generate_text_for_display($content, $uid, $bitfield, $options);
	}

	public function set_desc($desc)
	{
		$this->config['max_post_chars'] = 0;

		$uid = $bitfield = $flags = '';
		generate_text_for_storage($desc, $uid, $bitfield, $flags, $this->is_bbcode_enabled(), $this->is_magic_url_enabled(), $this->is_smilies_enabled());

		$this->data['pkg_desc'] = $desc;
		$this->data['pkg_desc_bbcode_uid'] = $uid;
		$this->data['pkg_desc_bbcode_bitfield'] = $bitfield;

		return $this;
	}

	public function is_bbcode_enabled()
	{
		if (!isset($this->data['pkg_desc_bbcode_options']))
		{
			return true;
		}
		return ($this->data['pkg_desc_bbcode_options'] & OPTION_FLAG_BBCODE);
	}

	public function set_bbcode_enabled($enable)
	{
		$this->set_desc_option(OPTION_FLAG_BBCODE, $enable);

		return $this;
	}

	public function is_magic_url_enabled()
	{
		if (!isset($this->data['pkg_desc_bbcode_options']))
		{
			return true;
		}
		return ($this->data['pkg_desc_bbcode_options'] & OPTION_FLAG_LINKS);
	}

	public function set_magic_url_enabled($enable)
	{
		$this->set_desc_option(OPTION_FLAG_LINKS, $enable);

		return $this;
	}

	public function is_smilies_enabled()
	{
		if (!isset($this->data['pkg_desc_bbcode_options']))
		{
			return true;
		}
		return ($this->data['pkg_desc_bbcode_options'] & OPTION_FLAG_SMILIES);
	}

	public function set_smilies_enabled($enable)
	{
		$this->set_desc_option(OPTION_FLAG_SMILIES, $enable);

		return $this;
	}

	public function get_warn_time()
	{
		return isset($this->data['pkg_warn_time']) ? (int) $this->data['pkg_warn_time'] : null;
	}

	public function set_warn_time($warn_time)
	{
		$warn_time = (int) $warn_time;

		if ($warn_time < 0 || $warn_time > 16777215)
		{
			throw new out_of_bounds('pkg_warn_time');
		}

		$this->data['pkg_warn_time'] = $warn_time;

		return $this;
	}

	public function get_grace()
	{
		return isset($this->data['pkg_grace']) ? (int) $this->data['pkg_grace'] : null;
	}

	public function set_grace($grace)
	{
		$grace = (int) $grace;

		if ($grace < 0 || $grace > 16777215)
		{
			throw new out_of_bounds('pkg_grace');
		}

		$this->data['pkg_grace'] = $grace;

		return $this;
	}

	public function get_order()
	{
		return isset($this->data['pkg_order']) ? (int) $this->data['pkg_order'] : 0;
	}

	public function set_order($order)
	{
		$order = (int) $order;

		if ($order < 0 || $order > 16777215)
		{
			throw new out_of_bounds('pkg_order');
		}

		$this->data['pkg_order'] = $order;

		return $this;
	}

	/**
	 * Set a parsing option on the description text.
	 *
	 * @param int     $option The option to set
	 * @param boolean $value  The value of the option
	 */
	protected function set_desc_option($option, $value)
	{
		$this->data['pkg_desc_bbcode_options'] = isset($this->data['pkg_desc_bbcode_options']) ? $this->data['pkg_desc_bbcode_options'] : 0;

		if ($value && !($this->data['pkg_desc_bbcode_options'] & $option))
		{
			$this->data['pkg_desc_bbcode_options'] += $option;
		}

		if (!$value && $this->data['pkg_desc_bbcode_options'] & $option)
		{
			$this->data['pkg_desc_bbcode_options'] -= $option;
		}

		if (!empty($this->data['pkg_desc']))
		{
			$content = $this->data['pkg_desc'];

			decode_message($content, $this->data['pkg_desc_bbcode_uid']);

			$this->set_desc($content);
		}
	}
}
