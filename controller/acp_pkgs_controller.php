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

use phpbb\event\dispatcher_interface;
use phpbb\group\helper;
use phpbb\json_response;
use stevotvr\groupsub\entity\package_interface as pkg_entity;
use stevotvr\groupsub\exception\base;
use stevotvr\groupsub\operator\package_interface as pkg_operator;

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
	 * @var \phpbb\event\dispatcher_interface
	 */
	protected $phpbb_dispatcher;

	/**
	 * @var \phpbb\group\helper
	 */
	protected $group_helper;

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
	 * @param \phpbb\event\dispatcher_interface                 $phpbb_dispatcher
	 * @param \phpbb\group\helper                               $group_helper
	 * @param string                                            $phpbb_groups_table The name of the phpBB groups table
	 */
	public function setup(pkg_operator $pkg_operator, dispatcher_interface $phpbb_dispatcher, helper $group_helper, $phpbb_groups_table)
	{
		$this->pkg_operator = $pkg_operator;
		$this->phpbb_dispatcher = $phpbb_dispatcher;
		$this->group_helper = $group_helper;
		$this->phpbb_groups_table = $phpbb_groups_table;
	}

	/**
	 * @inheritDoc
	 */
	public function add_lang()
	{
		parent::add_lang();

		$this->language->add_lang('posting');
		$this->language->add_lang('acp_packages', 'stevotvr/groupsub');
	}

	/**
	 * @inheritDoc
	 */
	public function display()
	{
		$this->add_lang();

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
		$this->add_lang();

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
				$this->parse_actions($entity->get_id());

				trigger_error($this->language->lang($message) . adm_back_link($this->u_action));
			}
		}

		if (!empty($errors))
		{
			$errors = array_map(array($this->language, 'lang'), $errors);
			$this->template->assign_vars(array(
				'ERROR_MESSAGE'	=> implode('<br>', $errors),
			));
		}

		$this->load_actions($entity->get_id(), $submit);
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
	 * Load the lists of groups into template block variables.
	 *
	 * @param int  $package_id The package ID
	 * @param bool $submit     True if the form was submitted, false otherwise
	 */
	protected function load_actions($package_id, $submit)
	{
		$start_actions = array();
		$end_actions = array();

		if ($package_id)
		{
			$start_actions = $this->pkg_operator->get_start_actions($package_id);
			$end_actions = $this->pkg_operator->get_end_actions($package_id);
		}

		/**
		 * Event triggered when the actions are loaded into the package edit form.
		 *
		 * @event stevotvr.groupsub.acp_view_actions
		 * @var int   package_id    The package ID
		 * @var array start_actions The subscription start actions assigned to the package
		 * @var array end_actions   The subscription end actions assigned to the package
		 * @var bool  submit        True if the form was submitted, false otherwise
		 * @since 1.2.0
		 */
		$vars = array('package_id', 'start_actions', 'end_actions', 'submit');
		extract($this->phpbb_dispatcher->trigger_event('stevotvr.groupsub.acp_view_actions', compact($vars)));

		$groups_start_add = $this->request->variable('pkg_groups_start_add', array(0));
		$groups_start_remove = $this->request->variable('pkg_groups_start_remove', array(0));
		$groups_end_add = $this->request->variable('pkg_groups_end_add', array(0));
		$groups_end_remove = $this->request->variable('pkg_groups_end_remove', array(0));
		$default_group_start = $this->request->variable('pkg_default_group_start', 0);
		$default_group_end = $this->request->variable('pkg_default_group_end', 0);

		if (!$submit)
		{
			foreach ($start_actions as $action)
			{
				if ($action['name'] === 'gs_add_group')
				{
					$groups_start_add[] = (int) $action['param'];
				}
				else if ($action['name'] === 'gs_remove_group')
				{
					$groups_start_remove[] = (int) $action['param'];
				}
				else if ($action['name'] === 'gs_default_group')
				{
					$default_group_start = (int) $action['param'];
				}
			}

			foreach ($end_actions as $action)
			{
				if ($action['name'] === 'gs_add_group')
				{
					$groups_end_add[] = (int) $action['param'];
				}
				else if ($action['name'] === 'gs_remove_group')
				{
					$groups_end_remove[] = (int) $action['param'];
				}
				else if ($action['name'] === 'gs_default_group')
				{
					$default_group_end = (int) $action['param'];
				}
			}
		}

		$sql = 'SELECT group_id, group_name
				FROM ' . $this->phpbb_groups_table . '
				ORDER BY group_name ASC';
		$this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow())
		{
			$this->template->assign_block_vars('group', array(
				'ID'	=> (int) $row['group_id'],
				'NAME'	=> $this->group_helper->get_name($row['group_name']),

				'S_SELECTED_START_ADD'		=> in_array((int) $row['group_id'], $groups_start_add),
				'S_SELECTED_START_REMOVE'	=> in_array((int) $row['group_id'], $groups_start_remove),
				'S_SELECTED_START_DEFAULT'	=> (int) $row['group_id'] == $default_group_start,
				'S_SELECTED_END_ADD'		=> in_array((int) $row['group_id'], $groups_end_add),
				'S_SELECTED_END_REMOVE'		=> in_array((int) $row['group_id'], $groups_end_remove),
				'S_SELECTED_END_DEFAULT'	=> (int) $row['group_id'] == $default_group_end,
			));
		}
		$this->db->sql_freeresult();
	}

	/**
	 * Parse the group lists from the input.
	 *
	 * @param int $package_id The package ID
	 */
	protected function parse_actions($package_id)
	{
		if (!$package_id)
		{
			return;
		}

		$groups_start_add = $this->request->variable('pkg_groups_start_add', array(0));
		$groups_start_remove = $this->request->variable('pkg_groups_start_remove', array(0));
		$groups_end_add = $this->request->variable('pkg_groups_end_add', array(0));
		$groups_end_remove = $this->request->variable('pkg_groups_end_remove', array(0));
		$default_group_start = $this->request->variable('pkg_default_group_start', 0);
		$default_group_end = $this->request->variable('pkg_default_group_end', 0);

		$this->pkg_operator->remove_actions($package_id);

		$start_actions = array();
		$end_actions = array();

		foreach ($groups_start_add as $group_id)
		{
			$start_actions[] = array(
				'action'	=> 'gs_add_group',
				'param'		=> $group_id,
			);
		}

		foreach ($groups_start_remove as $group_id)
		{
			$start_actions[] = array(
				'action'	=> 'gs_remove_group',
				'param'		=> $group_id,
			);
		}

		foreach ($groups_end_add as $group_id)
		{
			$end_actions[] = array(
				'action'	=> 'gs_add_group',
				'param'		=> $group_id,
			);
		}

		foreach ($groups_end_remove as $group_id)
		{
			$end_actions[] = array(
				'action'	=> 'gs_remove_group',
				'param'		=> $group_id,
			);
		}

		if ($default_group_start)
		{
			$start_actions[] = array(
				'action'	=> 'gs_default_group',
				'param'		=> $default_group_start,
			);
		}

		if ($default_group_end)
		{
			$end_actions[] = array(
				'action'	=> 'gs_default_group',
				'param'		=> $default_group_end,
			);
		}

		/**
		 * Event triggered when the actions are parsed from the package edit form.
		 *
		 * @event stevotvr.groupsub.acp_modify_actions
		 * @var int   package_id    The package ID
		 * @var array start_actions The subscription start actions to assign to the package
		 * @var array end_actions   The subscription end actions to assign to the package
		 * @since 1.2.0
		 */
		$vars = array('package_id', 'start_actions', 'end_actions');
		extract($this->phpbb_dispatcher->trigger_event('stevotvr.groupsub.acp_modify_actions', compact($vars)));

		foreach ($start_actions as $action)
		{
			$this->pkg_operator->add_start_action($package_id, $action['action'], $action['param']);
		}

		foreach ($end_actions as $action)
		{
			$this->pkg_operator->add_end_action($package_id, $action['action'], $action['param']);
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
		$this->add_lang();

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
