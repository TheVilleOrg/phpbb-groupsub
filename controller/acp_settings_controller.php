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
	/**
	 * @inheritDoc
	 */
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
				'pp_sandbox'		=> $this->request->variable('pp_sandbox', true),
				'pp_sb_business'	=> $this->request->variable('pp_sb_business', ''),
				'pp_business'		=> $this->request->variable('pp_business', ''),
				'currency'			=> $this->request->variable('currency', ''),
				'warn_time'			=> max(0, $this->request->variable('warn_time', 0)),
				'grace'				=> max(0, $this->request->variable('grace', 0)),
			);

			if (!$this->currency->is_valid($data['currency']))
			{
				$errors[] = 'ACP_GROUPSUB_ERROR_CURRENCY';
			}

			if (!count($errors))
			{
				foreach ($data as $key => $value)
				{
					$this->config->set('stevotvr_groupsub_' . $key, $value);
				}

				$this->config->set('stevotvr_groupsub_active', !$data['pp_sandbox'] && $data['pp_business']);

				trigger_error($this->language->lang('ACP_GROUPSUB_SETTINGS_SAVED') . adm_back_link($this->u_action));
			}
		}

		$errors = array_map(array($this->language, 'lang'), $errors);
		$this->template->assign_vars(array(
			'ERROR_MSG'	=> implode('<br>', $errors),

			'PP_SANDBOX'		=> $this->config['stevotvr_groupsub_pp_sandbox'],
			'PP_SB_BUSINESS'	=> $this->config['stevotvr_groupsub_pp_sb_business'],
			'PP_BUSINESS'		=> $this->config['stevotvr_groupsub_pp_business'],
			'CURRENCY'			=> $this->config['stevotvr_groupsub_currency'],
			'WARN_TIME'			=> $this->config['stevotvr_groupsub_warn_time'],
			'GRACE'				=> $this->config['stevotvr_groupsub_grace'],

			'U_ACTION'	=> $this->u_action,
		));

		$this->assign_currency_vars();
	}
}
