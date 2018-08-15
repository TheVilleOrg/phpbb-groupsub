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
	 * @var \phpbb\group\helper
	 */
	protected $group_helper;

	/**
	 * @var \stevotvr\groupsub\operator\package_interface
	 */
	protected $pkg_operator;

	/**
	 * @var \stevotvr\groupsub\operator\unit_helper_interface
	 */
	protected $unit_helper;

	/**
	 * Set up the controller.
	 *
	 * @param \phpbb\group\helper                               $group_helper
	 * @param \stevotvr\groupsub\operator\package_interface     $pkg_operator
	 * @param \stevotvr\groupsub\operator\unit_helper_interface $unit_helper
	 */
	public function setup(helper $group_helper, pkg_operator $pkg_operator, unit_helper_interface $unit_helper)
	{
		$this->group_helper = $group_helper;
		$this->pkg_operator = $pkg_operator;
		$this->unit_helper = $unit_helper;

		$this->language->add_lang('posting');
	}

	public function display()
	{
		$entities = $this->pkg_operator->get_packages();
		$terms = $this->pkg_operator->get_terms();

		foreach ($entities as $entity)
		{
			$this->template->assign_block_vars('package', array(
				'PKG_IDENT'	=> $entity->get_ident(),
				'PKG_NAME'	=> $entity->get_name(),

				'U_MOVE_UP'		=> $this->u_action . '&amp;action=move_up&amp;id=' . $entity->get_id(),
				'U_MOVE_DOWN'	=> $this->u_action . '&amp;action=move_down&amp;id=' . $entity->get_id(),
				'U_EDIT'		=> $this->u_action . '&amp;action=edit&amp;id=' . $entity->get_id(),
				'U_DELETE'		=> $this->u_action . '&amp;action=delete&amp;id=' . $entity->get_id(),
			));

			if (isset($terms[$entity->get_id()]))
			{
				foreach ($terms[$entity->get_id()] as $term)
				{
					$this->template->assign_block_vars('package.term', array(
						'PKG_AMOUNT'	=> $this->currency->format_price($term->get_currency(), $term->get_amount()),
						'PKG_LENGTH'	=> $this->unit_helper->get_formatted_timespan($term->get_length()),
					));
				}
			}
		}

		$this->template->assign_vars(array(
			'U_ACTION'		=> $this->u_action,
			'U_ADD_PKG'	=> $this->u_action . '&amp;action=add',
		));
	}

	public function add()
	{
		$entity = $this->container->get('stevotvr.groupsub.entity.package');
		$this->add_edit_pkg_data($entity);
		$this->template->assign_vars(array(
			'S_ADD_PKG'	=> true,

			'U_ACTION'	=> $this->u_action . '&amp;action=add',
		));
	}

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
			'warn_time'			=> max(0, $this->request->variable('pkg_warn_time', 0)),
			'grace'				=> max(0, $this->request->variable('pkg_grace', 0)),
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

				$this->parse_groups($entity->get_id());
				$this->parse_terms($entity->get_id());

				if (empty($errors))
				{
					trigger_error($this->language->lang($message) . adm_back_link($this->u_action));
				}
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
		$warn_time = $posted ? $post['warn_time'] : $entity->get_warn_time();
		$grace = $posted ? $post['grace'] : $entity->get_grace();

		$this->template->assign_vars(array(
			'PKG_IDENT'		=> $ident,
			'PKG_NAME'		=> $name,
			'PKG_DESC'		=> $desc,
			'PKG_WARN_TIME'	=> is_int($warn_time) ? $warn_time : $this->config['stevotvr_groupsub_warn_time'],
			'PKG_GRACE'		=> is_int($grace) ? $grace : $this->config['stevotvr_groupsub_grace'],

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
		$groups = array();

		$selected = $this->request->variable('pkg_groups', array(0));

		if ($package_id && empty($selected))
		{
			$selected = $this->pkg_operator->get_groups($package_id);
		}

		$sql = 'SELECT group_id, group_name
				FROM ' . GROUPS_TABLE . '
				WHERE group_type < ' . GROUP_SPECIAL;
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
	 * @param int $package_id The package ID
	 */
	protected function parse_groups($package_id)
	{
		if (!$package_id)
		{
			return;
		}

		$group_ids = $this->request->variable('pkg_groups', array(0));
		$this->pkg_operator->remove_groups($package_id);
		foreach ($group_ids as $group_id)
		{
			$this->pkg_operator->add_group($package_id, $group_id);
		}
	}

	/**
	 * Load the list of terms set for a package.
	 *
	 * @param int $package_id The package ID
	 */
	protected function load_terms($package_id)
	{
		if ($this->request->is_set_post('pkg_amount'))
		{
			$amounts = $this->request->variable('pkg_amount', array(''));
			$currencies = $this->request->variable('pkg_currency', array(''));
			$lengths = $this->request->variable('pkg_length', array(0));
			$length_units = $this->request->variable('pkg_length_unit', array(''));

			$count = min(array_map('count', array($amounts, $currencies, $lengths, $length_units)));
			for ($i = 0; $i < $count; $i++)
			{
				if ($lengths[$i] <= 0 || $amounts[$i] === '')
				{
					continue;
				}

				$this->template->assign_block_vars('term', array(
					'PKG_AMOUNT'		=> $amounts[$i],
					'PKG_CURRENCY'		=> $currencies[$i],
					'PKG_LENGTH'		=> $lengths[$i],
					'PKG_LENGTH_UNIT'	=> $length_units[$i],
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
				'PKG_AMOUNT'		=> $this->currency->format_value($term->get_currency(), $term->get_amount()),
				'PKG_CURRENCY'		=> $term->get_currency(),
				'PKG_LENGTH'		=> $length['length'],
				'PKG_LENGTH_UNIT'	=> $length['unit'],
			));
		}
	}

	/**
	 * Parse the terms from the input.
	 *
	 * @param int $package_id The package ID
	 */
	protected function parse_terms($package_id)
	{
		if (!$package_id)
		{
			return;
		}

		$entities = array();

		$amounts = $this->request->variable('pkg_amount', array(''));
		$currencies = $this->request->variable('pkg_currency', array(''));
		$lengths = $this->request->variable('pkg_length', array(0));
		$length_units = $this->request->variable('pkg_length_unit', array(''));

		$count = min(array_map('count', array($amounts, $currencies, $lengths, $length_units)));
		for ($i = 0; $i < $count; $i++)
		{
			if ($lengths[$i] <= 0 || $amounts[$i] === '')
			{
				continue;
			}

			$entity = $this->container->get('stevotvr.groupsub.entity.term')
				->set_amount($this->currency->parse_value($currencies[$i], $amounts[$i]))
				->set_currency($currencies[$i])
				->set_length($this->unit_helper->get_days($lengths[$i], $length_units[$i]));

			$entities[] = $entity;
		}

		$this->pkg_operator->set_terms($package_id, $entities);
	}

	/**
	 * Assign template variables for the length unit options.
	 */
	protected function assign_length_vars()
	{
		foreach (array('days', 'weeks', 'months', 'years') as $unit)
		{
			$this->template->assign_block_vars('time_unit', array(
				'UNIT_ID'	=> $unit,
				'UNIT_NAME'	=> $this->language->lang('GROUPSUB_' . strtoupper($unit)),
			));
		}
	}

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
