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

use phpbb\config\config;
use phpbb\language\language;
use phpbb\request\request;
use phpbb\template\template;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Group Subscription settings ACP controller.
 */
class acp_settings_controller extends acp_base_controller implements acp_settings_interface
{
	/**
	 * @var \phpbb\config\config
	 */
	protected $config;

	/**
	 * @param ContainerInterface       $container
	 * @param \phpbb\language\language $language
	 * @param \phpbb\request\request   $request
	 * @param \phpbb\template\template $template
	 * @param \phpbb\config\config     $config
	 */
	public function __construct(ContainerInterface $container, language $language, request $request, template $template, config $config)
	{
		parent::__construct($container, $language, $request, $template);
		$this->config = $config;
	}

	public function handle()
	{
		add_form_key('stevotvr_groupsub_settings');

		if ($this->request->is_set_post('submit'))
		{
			if (!check_form_key('stevotvr_groupsub_settings'))
			{
				trigger_error('FORM_INVALID');
			}

			trigger_error($this->language->lang('ACP_GROUPSUB_SETTINGS_SAVED') . adm_back_link($this->u_action));
		}

		$this->template->assign_vars(array(
			'U_ACTION'	=> $this->u_action,
		));
	}
}
