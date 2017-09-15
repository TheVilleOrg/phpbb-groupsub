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

/**
 * Group Subscription settings ACP controller.
 */
class acp_settings_controller extends acp_base_controller implements acp_settings_interface
{
	public function handle()
	{
		$errors = array();

		add_form_key('stevotvr_groupsub_settings');

		if ($this->request->is_set_post('submit'))
		{
			if (!check_form_key('stevotvr_groupsub_settings'))
			{
				trigger_error('FORM_INVALID');
			}

			$data = array(
				'currency'	=> $this->request->variable('groupsub_currency', ''),
				'warn_time'	=> $this->request->variable('groupsub_warn_time', 0),
				'grace'		=> $this->request->variable('groupsub_grace', 0),
			);

			if (!isset($this->currencies[$data['currency']]))
			{
				$errors[] = 'ACP_GROUPSUB_ERROR_CURRENCY';
			}

			if (!count($errors))
			{
				foreach ($data as $key => $value)
				{
					$this->config->set('stevotvr_groupsub_' . $key, $value);
				}

				trigger_error($this->language->lang('ACP_GROUPSUB_SETTINGS_SAVED') . adm_back_link($this->u_action));
			}
		}

		$errors = array_map(array($this->language, 'lang'), $errors);
		$this->template->assign_vars(array(
			'S_ERROR'	=> (bool) count($errors),
			'ERROR_MSG'	=> count($errors) ? implode('<br />', $errors) : '',

			'WARN_TIME'	=> $this->config['stevotvr_groupsub_warn_time'],
			'GRACE'		=> $this->config['stevotvr_groupsub_grace'],

			'U_ACTION'	=> $this->u_action,
		));

		$this->assign_currency_vars();
	}
}
