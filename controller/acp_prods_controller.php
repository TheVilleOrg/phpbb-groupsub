<?php
/**
 *
 * Group Subscription. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, Steve Guidetti, https://github.com/stevotvr
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace stevotvr\groupsub\controller;

use phpbb\group\helper;
use stevotvr\groupsub\entity\product_interface as prod_entity;
use stevotvr\groupsub\exception\base;
use stevotvr\groupsub\operator\product_interface as prod_operator;
use stevotvr\groupsub\operator\unit_helper_interface;

/**
 * Group Subscription product management ACP controller.
 */
class acp_prods_controller extends acp_base_controller implements acp_prods_interface
{
	/**
	 * @var \phpbb\group\helper
	 */
	protected $group_helper;

	/**
	 * @var \stevotvr\groupsub\operator\product_interface
	 */
	protected $prod_operator;

	/**
	 * @var \stevotvr\groupsub\operator\unit_helper_interface
	 */
	protected $unit_helper;

	/**
	 * Set up the controller.
	 *
	 * @param \phpbb\group\helper                               $group_helper
	 * @param \stevotvr\groupsub\operator\product_interface     $prod_operator
	 * @param \stevotvr\groupsub\operator\unit_helper_interface $unit_helper
	 */
	public function setup(helper $group_helper, prod_operator $prod_operator, unit_helper_interface $unit_helper)
	{
		$this->group_helper = $group_helper;
		$this->prod_operator = $prod_operator;
		$this->unit_helper = $unit_helper;

		$this->language->add_lang('posting');
	}

	public function display()
	{
		$entities = $this->prod_operator->get_products();

		foreach ($entities as $entity)
		{
			$price = sprintf('%s%d %s', $this->currencies[$entity->get_currency()], $entity->get_price(), $entity->get_currency());

			$this->template->assign_block_vars('product', array(
				'PROD_IDENT'	=> $entity->get_ident(),
				'PROD_NAME'		=> $entity->get_name(),
				'PROD_PRICE'	=> $price,
				'PROD_LENGTH'	=> $this->unit_helper->get_formatted_timespan($entity->get_length()),

				'U_MOVE_UP'		=> $this->u_action . '&amp;action=move_up&amp;id=' . $entity->get_id(),
				'U_MOVE_DOWN'	=> $this->u_action . '&amp;action=move_down&amp;id=' . $entity->get_id(),
				'U_EDIT'		=> $this->u_action . '&amp;action=edit&amp;id=' . $entity->get_id(),
				'U_DELETE'		=> $this->u_action . '&amp;action=delete&amp;id=' . $entity->get_id(),
			));
		}

		$this->template->assign_vars(array(
			'U_ACTION'		=> $this->u_action,
			'U_ADD_PROD'	=> $this->u_action . '&amp;action=add',
		));
	}

	public function add()
	{
		$entity = $this->container->get('stevotvr.groupsub.entity.product');
		$this->add_edit_prod_data($entity);
		$this->template->assign_vars(array(
			'S_ADD_PROD'	=> true,

			'U_ACTION'	=> $this->u_action . '&amp;action=add',
		));
	}

	public function edit($id)
	{
		$entity = $this->container->get('stevotvr.groupsub.entity.product')->load($id);
		$this->add_edit_prod_data($entity);
		$this->template->assign_vars(array(
			'S_EDIT_PROD'	=> true,

			'U_ACTION'		=> $this->u_action . '&amp;action=edit&amp;id=' . $id,
		));
	}

	/**
	 * Process data for the add/edit product form.
	 *
	 * @param \stevotvr\groupsub\entity\product_interface $entity The product
	 */
	protected function add_edit_prod_data(prod_entity $entity)
	{
		$errors = array();

		$submit = $this->request->is_set_post('submit');

		add_form_key('add_edit_prod');

		$data = array(
			'name'		=> $this->request->variable('prod_name', '', true),
			'desc'		=> $this->request->variable('prod_desc', '', true),
			'price'		=> $this->request->variable('prod_price', 0),
			'currency'	=> $this->request->variable('prod_currency', ''),
			'length'	=> $this->parse_length(),
			'warn_time'	=> max(0, $this->request->variable('prod_warn_time', 0)),
			'grace'		=> max(0, $this->request->variable('prod_grace', 0)),
		);

		if (!$entity->get_id())
		{
			$data['ident'] = $this->request->variable('prod_ident', '', true);
		}

		$data['warn_time'] = min($data['warn_time'], $data['length']);

		$this->set_parse_options($entity, $submit);

		if ($submit)
		{
			if (!check_form_key('add_edit_prod'))
			{
				$errors[] = 'FORM_INVALID';
			}

			if ($data['price'] < 1)
			{
				$errors[] = 'ACP_GROUPSUB_ERROR_INVALID_PRICE';
			}

			foreach ($data as $name => $value)
			{
				try
				{
					$entity->{'set_' . $name}($value);
				}
				catch (base $e)
				{
					$errors[] = $e->get_message($this->language);
				}
			}

			if (empty($errors))
			{
				if ($entity->get_id())
				{
					$entity->save();
					$message = 'ACP_GROUPSUB_PROD_EDIT_SUCCESS';
				}
				else
				{
					$entity = $this->prod_operator->add_product($entity);
					$message = 'ACP_GROUPSUB_PROD_ADD_SUCCESS';
				}

				$this->parse_groups($entity->get_id());

				trigger_error($this->language->lang($message) . adm_back_link($this->u_action));
			}
		}

		$errors = array_map(array($this->language, 'lang'), $errors);

		$this->template->assign_vars(array(
			'S_ERROR'	=> (bool) count($errors),
			'ERROR_MSG'	=> count($errors) ? implode('<br />', $errors) : '',

			'PROD_IDENT'		=> $entity->get_ident(),
			'PROD_NAME'			=> $entity->get_name(),
			'PROD_DESC'			=> $entity->get_desc_for_edit(),
			'PROD_PRICE'		=> is_int($entity->get_price()) ? $entity->get_price() : '',
			'PROD_WARN_TIME'	=> is_int($entity->get_warn_time()) ? $entity->get_warn_time() : $this->config['stevotvr_groupsub_warn_time'],
			'PROD_GRACE'		=> is_int($entity->get_grace()) ? $entity->get_grace() : $this->config['stevotvr_groupsub_grace'],

			'S_PARSE_BBCODE_CHECKED'	=> $entity->is_bbcode_enabled(),
			'S_PARSE_SMILIES_CHECKED'	=> $entity->is_smilies_enabled(),
			'S_PARSE_MAGIC_URL_CHECKED'	=> $entity->is_magic_url_enabled(),

			'U_BACK'	=> $this->u_action,
		));

		$this->load_groups($entity->get_id());
		$this->load_length($entity);
		$this->assign_currency_vars($entity->get_currency());
	}

	/**
	 * Load the list of groups into template block variables.
	 *
	 * @param int $prod_id The product ID
	 */
	protected function load_groups($prod_id)
	{
		$groups = array();

		$selected = $prod_id ? $this->prod_operator->get_groups($prod_id) : array();

		$sql = 'SELECT group_id, group_name
				FROM ' . GROUPS_TABLE;
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$groups[] = array(
				'GROUP_ID'		=> (int) $row['group_id'],
				'GROUP_NAME'	=> $this->group_helper->get_name($row['group_name']),

				'S_SELECTED'	=> in_array((int) $row['group_id'], $selected),
			);
		}
		$this->db->sql_freeresult($result);

		$names = array_map('strtolower', array_column($groups, 'GROUP_NAME'));
		array_multisort($names, SORT_ASC, SORT_STRING, $groups);
		foreach ($groups as $group)
		{
			$this->template->assign_block_vars('group', $group);
		}
	}

	/**
	 * Parse the group list from the input.
	 *
	 * @param int $prod_id The product ID
	 */
	protected function parse_groups($prod_id)
	{
		if (!$prod_id)
		{
			return;
		}

		$group_ids = $this->request->variable('prod_groups', array(0));
		$this->prod_operator->remove_groups($prod_id);
		foreach ($group_ids as $group_id)
		{
			$this->prod_operator->add_group($prod_id, $group_id);
		}
	}

	/**
	 * Load the length and length unit options into template variables.
	 *
	 * @param \stevotvr\groupsub\entity\product_interface $entity The product
	 */
	protected function load_length(prod_entity $entity)
	{
		$selected = null;
		$length = $entity->get_length();
		if (is_int($length))
		{
			if ($length > 0)
			{
				extract($this->unit_helper->get_timespan_parts($length));
				$selected = $unit;
			}

			$this->template->assign_var('PROD_LENGTH', $length);
		}

		foreach (array('days', 'weeks', 'months', 'years') as $unit)
		{
			$this->template->assign_block_vars('time_unit', array(
				'UNIT_ID'	=> $unit,
				'UNIT_NAME'	=> $this->language->lang('GROUPSUB_' . strtoupper($unit)),

				'S_SELECTED'	=> ($unit === $selected),
			));
		}
	}

	/**
	 * Parse the length fields.
	 *
	 * @return int The length in days
	 */
	protected function parse_length()
	{
		$value = $this->request->variable('prod_length', 0);
		if ($value <= 0)
		{
			return 0;
		}

		$unit = $this->request->variable('prod_length_unit', '');
		try
		{
			return $this->unit_helper->get_days($value, $unit);
		}
		catch (base $e)
		{
		}

		return $value;
	}

	/**
	 * Process parsing options for the product description field.
	 *
	 * @param \stevotvr\groupsub\entity\product_interface $entity The product
	 * @param boolean                                     $submit The form has been submitted
	 */
	protected function set_parse_options(prod_entity $entity, $submit)
	{
		$bbcode = $this->request->variable('parse_bbcode', false);
		$magic_url = $this->request->variable('parse_magic_url', false);
		$smilies = $this->request->variable('parse_smilies', false);
		$parse_options = array(
			'bbcode'	=> $submit ? $bbcode : ($entity->get_id() ? $entity->is_bbcode_enabled() : 1),
			'magic_url'	=> $submit ? $magic_url : ($entity->get_id() ? $entity->is_magic_url_enabled() : 1),
			'smilies'	=> $submit ? $smilies : ($entity->get_id() ? $entity->is_smilies_enabled() : 1),
		);
		foreach ($parse_options as $function => $enabled)
		{
			$entity->{'set_' . $function . '_enabled'}($enabled);
		}
	}

	public function delete($id)
	{
		if (!confirm_box(true))
		{
			$hidden_fields = build_hidden_fields(array(
				'id'		=> $id,
				'mode'		=> 'products',
				'action'	=> 'delete',
			));
			confirm_box(false, $this->language->lang('ACP_GROUPSUB_PROD_DELETE_CONFIRM'), $hidden_fields);
			return;
		}

		$this->prod_operator->delete_product($id);

		if ($this->request->is_ajax())
		{
			$json_response = new json_response();
			$json_response->send(array(
				'MESSAGE_TITLE'	=> $this->language->lang('INFORMATION'),
				'MESSAGE_TEXT'	=> $this->language->lang('ACP_GROUPSUB_PROD_DELETE_SUCCESS'),
				'REFRESH_DATA'	=> array(
					'time'	=> 3
				),
			));
		}

		trigger_error($this->language->lang('ACP_GROUPSUB_PROD_DELETE_SUCCESS') . adm_back_link($this->u_action));
	}

	public function move($id, $offset)
	{
		$this->prod_operator->move_product($id, $offset);

		if ($this->request->is_ajax())
		{
			$json_response = new json_response();
			$json_response->send(array('success' => true));
		}
	}
}
