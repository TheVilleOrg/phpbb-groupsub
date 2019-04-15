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

use phpbb\json_response;
use stevotvr\groupsub\entity\package_interface as pkg_entity;
use stevotvr\groupsub\exception\base;
use stevotvr\groupsub\operator\package_interface as pkg_operator;
use stevotvr\groupsub\operator\unit_helper_interface;

/**
 * Group Subscription package management ACP controller.
 */
class acp_pkgs_controller extends acp_base_controller implements acp_pkgs_interface
{
	/**
	 * @var \stevotvr\groupsub\operator\package_interface
	 */
	protected $pkg_operator;

	/**
	 * @var \stevotvr\groupsub\operator\unit_helper_interface
	 */
	protected $unit_helper;

	/**
	 * The name of the phpBB groups table.
	 *
	 * @var string
	 */
	protected $phpbb_groups_table;

	/**
	 * Set up the controller.
	 *
	 * @param \stevotvr\groupsub\operator\package_interface     $pkg_operator
	 * @param \stevotvr\groupsub\operator\unit_helper_interface $unit_helper
	 * @param string                                            $phpbb_groups_table The name of the phpBB groups table
	 */
	public function setup(pkg_operator $pkg_operator, unit_helper_interface $unit_helper, $phpbb_groups_table)
	{
		$this->pkg_operator = $pkg_operator;
		$this->unit_helper = $unit_helper;
		$this->phpbb_groups_table = $phpbb_groups_table;

		$this->language->add_lang('posting');
	}

	/**
	 * @inheritDoc
	 */
	public function display()
	{
		$packages = $this->pkg_operator->get_packages(false, false);

		foreach ($packages as $package)
		{
			$this->template->assign_block_vars('package', array(
				'IDENT'	=> $package['package']->get_ident(),
				'NAME'	=> $package['package']->get_name(),

				'U_MOVE_UP'		=> $this->u_action . '&amp;action=move_up&amp;id=' . $package['package']->get_id(),
				'U_MOVE_DOWN'	=> $this->u_action . '&amp;action=move_down&amp;id=' . $package['package']->get_id(),
				'U_EDIT'		=> $this->u_action . '&amp;action=edit&amp;id=' . $package['package']->get_id(),
				'U_DELETE'		=> $this->u_action . '&amp;action=delete&amp;id=' . $package['package']->get_id(),
			));

			foreach ($package['terms'] as $term)
			{
				$this->template->assign_block_vars('package.term', array(
					'PRICE'		=> $this->currency->format_price($term->get_currency(), $term->get_price()),
					'LENGTH'	=> $term->get_length() ? $this->unit_helper->get_formatted_timespan($term->get_length()) : 0,
				));
			}
		}

		$this->template->assign_vars(array(
			'U_ACTION'	=> $this->u_action,
			'U_ADD_PKG'	=> $this->u_action . '&amp;action=add',
		));
	}

	/**
	 * @inheritDoc
	 */
	public function add()
	{
		$entity = $this->container->get('stevotvr.groupsub.entity.package')
									->set_enabled(true);
		$this->add_edit_pkg_data($entity);
		$this->template->assign_vars(array(
			'S_ADD_PKG'	=> true,

			'U_ACTION'	=> $this->u_action . '&amp;action=add',
		));
	}

	/**
	 * @inheritDoc
	 */
	public function edit($id)
	{
		$entity = $this->container->get('stevotvr.groupsub.entity.package')->load($id);
		$this->add_edit_pkg_data($entity);
		$this->template->assign_vars(array(
			'S_EDIT_PKG'	=> true,

			'U_ACTION'		=> $this->u_action . '&amp;action=edit&amp;id=' . $id,
		));
	}

	/**
	 * Process data for the add/edit package form.
	 *
	 * @param \stevotvr\groupsub\entity\package_interface $entity The package
	 */
	protected function add_edit_pkg_data(pkg_entity $entity)
	{
		$errors = array();

		$submit = $this->request->is_set_post('submit');

		add_form_key('add_edit_pkg');

		$data = array(
			'name'				=> $this->request->variable('pkg_name', '', true),
			'desc'				=> $this->request->variable('pkg_desc', '', true),
			'bbcode_enabled'	=> $this->request->variable('parse_bbcode', false),
			'magic_url_enabled'	=> $this->request->variable('parse_magic_url', false),
			'smilies_enabled'	=> $this->request->variable('parse_smilies', false),
			'enabled'			=> $this->request->variable('pkg_enabled', false),
		);

		if (!$entity->get_id())
		{
			$data['ident'] = $this->request->variable('pkg_ident', '', true);
		}

		if ($submit)
		{
			if (!check_form_key('add_edit_pkg'))
			{
				$errors[] = 'FORM_INVALID';
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

			$terms = $this->parse_terms();
			if ($entity->is_enabled() && empty($terms))
			{
				$errors[] = 'ACP_GROUPSUB_ERROR_MISSING_TERMS';
			}

			if (empty($errors))
			{
				if ($entity->get_id())
				{
					$entity->save();
					$message = 'ACP_GROUPSUB_PKG_EDIT_SUCCESS';
				}
				else
				{
					$entity = $this->pkg_operator->add_package($entity);
					$message = 'ACP_GROUPSUB_PKG_ADD_SUCCESS';
				}

				$this->pkg_operator->set_terms($entity->get_id(), $terms);
				$this->parse_groups($entity->get_id());

				trigger_error($this->language->lang($message) . adm_back_link($this->u_action));
			}
		}

		if (!empty($errors))
		{
			$errors = array_map(array($this->language, 'lang'), $errors);
			$this->template->assign_vars(array(
				'ERROR_MSG'	=> implode('<br>', $errors),
			));
		}

		$this->load_groups($entity->get_id());
		$this->load_terms($entity->get_id());
		$this->assign_tpl_vars($entity, $data);
		$this->assign_currency_vars();
		$this->assign_length_vars();
	}

	/**
	 * Assign the main template variables.
	 *
	 * @param \stevotvr\groupsub\entity\package_interface $entity The package
	 * @param array                                       $post   The posted data
	 */
	protected function assign_tpl_vars(pkg_entity $entity, array $post)
	{
		$posted = $this->request->is_set_post('pkg_name');

		$ident = !$entity->get_id() ? $post['ident'] : $entity->get_ident();
		$name = $posted ? $post['name'] : $entity->get_name();
		$desc = $posted ? $post['desc'] : $entity->get_desc_for_edit();
		$bbcode = $posted ? $post['bbcode_enabled'] : $entity->is_bbcode_enabled();
		$magic_url = $posted ? $post['magic_url_enabled'] : $entity->is_magic_url_enabled();
		$smilies = $posted ? $post['smilies_enabled'] : $entity->is_smilies_enabled();
		$enabled = $posted ? $post['enabled'] : $entity->is_enabled();

		$this->template->assign_vars(array(
			'PKG_IDENT'		=> $ident,
			'PKG_NAME'		=> $name,
			'PKG_DESC'		=> $desc,
			'PKG_ENABLED'	=> $enabled,

			'S_PARSE_BBCODE_CHECKED'	=> $bbcode,
			'S_PARSE_SMILIES_CHECKED'	=> $smilies,
			'S_PARSE_MAGIC_URL_CHECKED'	=> $magic_url,

			'U_BACK'	=> $this->u_action,
		));
	}

	/**
	 * Load the list of groups into template block variables.
	 *
	 * @param int $package_id The package ID
	 */
	protected function load_groups($package_id)
	{
		$selected = $this->request->variable('pkg_groups', array(0));
		$default_group = $this->request->variable('pkg_default_group', 0);

		if ($package_id && empty($selected))
		{
			$selected = $this->pkg_operator->get_groups($package_id, $default_group);
		}

		$this->template->assign_var('PKG_DEFAULT_GROUP', $default_group);

		$sql = 'SELECT group_id, group_name
				FROM ' . $this->phpbb_groups_table . '
				WHERE ' . $this->db->sql_in_set('group_type', array(GROUP_OPEN, GROUP_CLOSED, GROUP_HIDDEN)) . '
				ORDER BY group_name ASC';
		$this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow())
		{
			$this->template->assign_block_vars('group', array(
				'ID'	=> (int) $row['group_id'],
				'NAME'	=> $row['group_name'],

				'S_SELECTED'	=> in_array((int) $row['group_id'], $selected),
			));
		}
		$this->db->sql_freeresult();
	}

	/**
	 * Parse the group list from the input.
	 *
	 * @param int $package_id The package ID
	 */
	protected function parse_groups($package_id)
	{
		if (!$package_id)
		{
			return;
		}

		$group_ids = $this->request->variable('pkg_groups', array(0));
		$default_group = $this->request->variable('pkg_default_group', 0);
		$this->pkg_operator->remove_groups($package_id);
		foreach ($group_ids as $group_id)
		{
			$this->pkg_operator->add_group($package_id, $group_id, $group_id === $default_group);
		}
	}

	/**
	 * Load the list of terms set for a package.
	 *
	 * @param int $package_id The package ID
	 */
	protected function load_terms($package_id)
	{
		if ($this->request->is_set_post('pkg_price'))
		{
			$prices = $this->request->variable('pkg_price', array(''));
			$currencies = $this->request->variable('pkg_currency', array(''));
			$lengths = $this->request->variable('pkg_length', array(''));
			$length_units = $this->request->variable('pkg_length_unit', array(''));

			$count = min(array_map('count', array($prices, $currencies, $lengths, $length_units)));
			for ($i = 0; $i < $count; $i++)
			{
				if (!is_numeric($lengths[$i]) || $lengths[$i] < 0 || !is_numeric($prices[$i]) || $prices[$i] < 0)
				{
					continue;
				}

				$this->template->assign_block_vars('term', array(
					'PRICE'			=> $prices[$i],
					'CURRENCY'		=> $currencies[$i],
					'LENGTH'		=> $lengths[$i],
					'LENGTH_UNIT'	=> $length_units[$i],
				));
			}

			return;
		}

		if (!$package_id)
		{
			return;
		}

		$terms = $this->pkg_operator->get_terms($package_id);

		if (!isset($terms[$package_id]))
		{
			return;
		}

		foreach ($terms[$package_id] as $term)
		{
			$length = $this->unit_helper->get_timespan_parts($term->get_length());
			$this->template->assign_block_vars('term', array(
				'PRICE'			=> $this->currency->format_value($term->get_currency(), $term->get_price()),
				'CURRENCY'		=> $term->get_currency(),
				'LENGTH'		=> $length['length'],
				'LENGTH_UNIT'	=> $length['unit'],
			));
		}
	}

	/**
	 * Parse the terms from the input.
	 *
	 * @return array An array of term entities
	 */
	protected function parse_terms()
	{
		$entities = array();

		$prices = $this->request->variable('pkg_price', array(''));
		$currencies = $this->request->variable('pkg_currency', array(''));
		$lengths = $this->request->variable('pkg_length', array(''));
		$length_units = $this->request->variable('pkg_length_unit', array(''));

		$count = min(array_map('count', array($prices, $currencies, $lengths, $length_units)));
		for ($i = 0; $i < $count; $i++)
		{
			if (!is_numeric($lengths[$i]) || $lengths[$i] < 0 || !is_numeric($prices[$i]) || $prices[$i] < 0)
			{
				continue;
			}

			$entity = $this->container->get('stevotvr.groupsub.entity.term')
				->set_price($this->currency->parse_value($currencies[$i], $prices[$i]))
				->set_currency($currencies[$i])
				->set_length($this->unit_helper->get_days($lengths[$i], $length_units[$i]));

			$entities[] = $entity;
		}

		return $entities;
	}

	/**
	 * Assign template variables for the length unit options.
	 */
	protected function assign_length_vars()
	{
		foreach (array('days', 'weeks', 'months', 'years') as $unit)
		{
			$this->template->assign_block_vars('time_unit', array(
				'ID'	=> $unit,
				'NAME'	=> $this->language->lang('GROUPSUB_' . strtoupper($unit)),
			));
		}
	}

	/**
	 * @inheritDoc
	 */
	public function delete($id)
	{
		if (!confirm_box(true))
		{
			$hidden_fields = build_hidden_fields(array(
				'id'		=> $id,
				'mode'		=> 'packages',
				'action'	=> 'delete',
			));
			confirm_box(false, $this->language->lang('ACP_GROUPSUB_PKG_DELETE_CONFIRM'), $hidden_fields);
			return;
		}

		$this->pkg_operator->delete_package($id);

		if ($this->request->is_ajax())
		{
			$json_response = new json_response();
			$json_response->send(array(
				'MESSAGE_TITLE'	=> $this->language->lang('INFORMATION'),
				'MESSAGE_TEXT'	=> $this->language->lang('ACP_GROUPSUB_PKG_DELETE_SUCCESS'),
				'REFRESH_DATA'	=> array(
					'time'	=> 3
				),
			));
		}

		trigger_error($this->language->lang('ACP_GROUPSUB_PKG_DELETE_SUCCESS') . adm_back_link($this->u_action));
	}

	/**
	 * @inheritDoc
	 */
	public function move($id, $offset)
	{
		$this->pkg_operator->move_package($id, $offset);

		if ($this->request->is_ajax())
		{
			$json_response = new json_response();
			$json_response->send(array('success' => true));
		}
	}
}
