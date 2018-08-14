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
 * Group Subscription product entity.
 */
class product extends entity implements product_interface
{
	protected $columns = array(
		'gs_id'						=> 'integer',
		'gs_ident'					=> 'string',
		'gs_name'					=> 'set_name',
		'gs_desc'					=> 'string',
		'gs_desc_bbcode_uid'		=> 'string',
		'gs_desc_bbcode_bitfield'	=> 'string',
		'gs_desc_bbcode_options'	=> 'integer',
		'gs_warn_time'				=> 'set_warn_time',
		'gs_grace'					=> 'set_grace',
		'gs_order'					=> 'set_order',
	);

	protected $id_column = 'gs_id';

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
		return isset($this->data['gs_ident']) ? (string) $this->data['gs_ident'] : '';
	}

	public function set_ident($ident)
	{
		$ident = strtolower(utf8_clean_string((string) $ident));
		$ident = preg_replace('/\s+/', '_', $ident);

		if ($ident === '')
		{
			throw new missing_field('gs_ident');
		}

		if (truncate_string($ident, 30) !== $ident)
		{
			throw new unexpected_value('gs_ident', 'TOO_LONG');
		}

		if (!preg_match('/^[a-z][a-z\d_]*$/', $ident))
		{
			throw new unexpected_value('gs_ident', 'INVALID_IDENT');
		}

		$sql = 'SELECT gs_id
				FROM ' . $this->table_name . "
				WHERE gs_ident = '" . $this->db->sql_escape($ident) . "'";
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		if ($row !== false)
		{
			throw new unexpected_value('gs_ident', 'NOT_UNIQUE');
		}

		$this->data['gs_ident'] = $ident;

		return $this;
	}

	public function get_name()
	{
		return isset($this->data['gs_name']) ? (string) $this->data['gs_name'] : '';
	}

	public function set_name($name)
	{
		$name = (string) $name;

		if ($name === '')
		{
			throw new missing_field('gs_name');
		}

		if (truncate_string($name, 255) !== $name)
		{
			throw new unexpected_value('gs_name', 'TOO_LONG');
		}

		$this->data['gs_name'] = $name;

		return $this;
	}

	public function get_desc_for_edit()
	{
		$content = isset($this->data['gs_desc']) ? $this->data['gs_desc'] : '';
		$uid = isset($this->data['gs_desc_bbcode_uid']) ? $this->data['gs_desc_bbcode_uid'] : '';
		$options = isset($this->data['gs_desc_bbcode_options']) ? (int) $this->data['gs_desc_bbcode_options'] : 0;

		$content_data = generate_text_for_edit($content, $uid, $options);

		return $content_data['text'];
	}

	public function get_desc_for_display()
	{
		$content = isset($this->data['gs_desc']) ? $this->data['gs_desc'] : '';
		$uid = isset($this->data['gs_desc_bbcode_uid']) ? $this->data['gs_desc_bbcode_uid'] : '';
		$bitfield = isset($this->data['gs_desc_bbcode_bitfield']) ? $this->data['gs_desc_bbcode_bitfield'] : '';
		$options = isset($this->data['gs_desc_bbcode_options']) ? (int) $this->data['gs_desc_bbcode_options'] : 0;

		return generate_text_for_display($content, $uid, $bitfield, $options);
	}

	public function set_desc($desc)
	{
		$this->config['max_post_chars'] = 0;

		$uid = $bitfield = $flags = '';
		generate_text_for_storage($desc, $uid, $bitfield, $flags, $this->is_bbcode_enabled(), $this->is_magic_url_enabled(), $this->is_smilies_enabled());

		$this->data['gs_desc'] = $desc;
		$this->data['gs_desc_bbcode_uid'] = $uid;
		$this->data['gs_desc_bbcode_bitfield'] = $bitfield;

		return $this;
	}

	public function is_bbcode_enabled()
	{
		if (!isset($this->data['gs_desc_bbcode_options']))
		{
			return true;
		}
		return ($this->data['gs_desc_bbcode_options'] & OPTION_FLAG_BBCODE);
	}

	public function set_bbcode_enabled($enable)
	{
		$this->set_desc_option(OPTION_FLAG_BBCODE, $enable);

		return $this;
	}

	public function is_magic_url_enabled()
	{
		if (!isset($this->data['gs_desc_bbcode_options']))
		{
			return true;
		}
		return ($this->data['gs_desc_bbcode_options'] & OPTION_FLAG_LINKS);
	}

	public function set_magic_url_enabled($enable)
	{
		$this->set_desc_option(OPTION_FLAG_LINKS, $enable);

		return $this;
	}

	public function is_smilies_enabled()
	{
		if (!isset($this->data['gs_desc_bbcode_options']))
		{
			return true;
		}
		return ($this->data['gs_desc_bbcode_options'] & OPTION_FLAG_SMILIES);
	}

	public function set_smilies_enabled($enable)
	{
		$this->set_desc_option(OPTION_FLAG_SMILIES, $enable);

		return $this;
	}

	public function get_warn_time()
	{
		return isset($this->data['gs_warn_time']) ? (int) $this->data['gs_warn_time'] : null;
	}

	public function set_warn_time($warn_time)
	{
		$warn_time = (int) $warn_time;

		if ($warn_time < 0 || $warn_time > 16777215)
		{
			throw new out_of_bounds('gs_warn_time');
		}

		$this->data['gs_warn_time'] = $warn_time;

		return $this;
	}

	public function get_grace()
	{
		return isset($this->data['gs_grace']) ? (int) $this->data['gs_grace'] : null;
	}

	public function set_grace($grace)
	{
		$grace = (int) $grace;

		if ($grace < 0 || $grace > 16777215)
		{
			throw new out_of_bounds('gs_grace');
		}

		$this->data['gs_grace'] = $grace;

		return $this;
	}

	public function get_order()
	{
		return isset($this->data['gs_order']) ? (int) $this->data['gs_order'] : 0;
	}

	public function set_order($order)
	{
		$order = (int) $order;

		if ($order < 0 || $order > 16777215)
		{
			throw new out_of_bounds('gs_order');
		}

		$this->data['gs_order'] = $order;

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
		$this->data['gs_desc_bbcode_options'] = isset($this->data['gs_desc_bbcode_options']) ? $this->data['gs_desc_bbcode_options'] : 0;

		if ($value && !($this->data['gs_desc_bbcode_options'] & $option))
		{
			$this->data['gs_desc_bbcode_options'] += $option;
		}

		if (!$value && $this->data['gs_desc_bbcode_options'] & $option)
		{
			$this->data['gs_desc_bbcode_options'] -= $option;
		}

		if (!empty($this->data['gs_desc']))
		{
			$content = $this->data['gs_desc'];

			decode_message($content, $this->data['gs_desc_bbcode_uid']);

			$this->set_desc($content);
		}
	}
}
