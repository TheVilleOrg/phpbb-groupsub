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

use phpbb\language\language;
use phpbb\request\request;
use phpbb\template\template;
use stevotvr\groupsub\entity\subscription_interface as sub_entity;
use stevotvr\groupsub\exception\base;
use stevotvr\groupsub\operator\product_interface as prod_operator;
use stevotvr\groupsub\operator\subscription_interface as sub_operator;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Group Subscription subscription management ACP controller.
 */
class acp_subs_controller extends acp_base_controller implements acp_subs_interface
{
	/**
	 * @var \stevotvr\groupsub\operator\product_interface
	 */
	protected $prod_operator;

	/**
	 * @var \stevotvr\groupsub\operator\subscription_interface
	 */
	protected $sub_operator;

	/**
	 * @param ContainerInterface                                 $container
	 * @param \phpbb\language\language                           $language
	 * @param \phpbb\request\request                             $request
	 * @param \phpbb\template\template                           $template
	 * @param \stevotvr\groupsub\operator\product_interface      $prod_operator
	 * @param \stevotvr\groupsub\operator\subscription_interface $sub_operator
	 */
	public function __construct(ContainerInterface $container, language $language, request $request, template $template, prod_operator $prod_operator, sub_operator $sub_operator)
	{
		parent::__construct($container, $language, $request, $template);
		$this->prod_operator = $prod_operator;
		$this->sub_operator = $sub_operator;
	}

	public function display()
	{
		$entities = $this->sub_operator->get_subscriptions();

		foreach ($entities as $entity)
		{
			$this->template->assign_block_vars('subscription', array(
				'SUB_ID'	=> $entity->get_id(),

				'U_MOVE_UP'		=> $this->u_action . '&amp;action=move_up&amp;id=' . $entity->get_id(),
				'U_MOVE_DOWN'	=> $this->u_action . '&amp;action=move_down&amp;id=' . $entity->get_id(),
				'U_EDIT'		=> $this->u_action . '&amp;action=edit&amp;id=' . $entity->get_id(),
				'U_DELETE'		=> $this->u_action . '&amp;action=delete&amp;id=' . $entity->get_id(),
			));
		}

		$this->template->assign_vars(array(
			'U_ACTION'	=> $this->u_action,
			'U_ADD_SUB'	=> $this->u_action . '&amp;action=add',
		));
	}

	public function add()
	{
		$entity = $this->container->get('stevotvr.groupsub.entity.subscription');
		$this->add_edit_sub_data($entity);
		$this->template->assign_vars(array(
			'S_ADD_SUB'	=> true,

			'U_ACTION'	=> $this->u_action . '&amp;action=add',
		));
	}

	public function edit($id)
	{
		$entity = $this->container->get('stevotvr.groupsub.entity.subscription')->load($id);
		$this->add_edit_sub_data($entity);
		$this->template->assign_vars(array(
			'S_EDIT_SUB'	=> true,

			'U_ACTION'		=> $this->u_action . '&amp;action=edit&amp;id=' . $id,
		));
	}

	/**
	 * Process data for the add/edit subscription form.
	 *
	 * @param \stevotvr\groupsub\entity\subscription_interface $entity The subscription
	 */
	protected function add_edit_sub_data(sub_entity $entity)
	{
		$errors = array();

		$submit = $this->request->is_set_post('submit');

		add_form_key('add_edit_sub');

		$data = array(
			'expire'	=> $this->parse_expire(),
		);

		if ($entity->get_id())
		{
			$this->template->assign_vars(array(
				'SUB_PRODUCT'	=> $entity->get_product(),
				'SUB_USER'		=> $entity->get_user(),
			));
		}
		else
		{
			$data['product'] = $this->request->variable('sub_product', 0);
			$data['user'] = $this->request->variable('sub_user', 0);

			$this->load_products();
		}

		if ($submit)
		{
			if (!check_form_key('add_edit_sub'))
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
					$message = 'ACP_GROUPSUB_SUB_EDIT_SUCCESS';
				}
				else
				{
					$entity = $this->sub_operator->add_subscription($entity);
					$message = 'ACP_GROUPSUB_SUB_ADD_SUCCESS';
				}

				trigger_error($this->language->lang($message) . adm_back_link($this->u_action));
			}
		}

		$errors = array_map(array($this->language, 'lang'), $errors);

		$this->template->assign_vars(array(
			'S_ERROR'	=> (bool) count($errors),
			'ERROR_MSG'	=> count($errors) ? implode('<br />', $errors) : '',

			'SUB_EXPIRE'	=> $entity->get_expire(),

			'U_BACK'	=> $this->u_action,
		));
	}

	protected function parse_expire()
	{
		return time() + 3600;
	}

	protected function load_products()
	{
		foreach ($this->prod_operator->get_products() as $entity)
		{
			$this->template->assign_block_vars('product', array(
				'PROD_ID'	=> $entity->get_id(),
				'PROD_NAME'	=> $entity->get_name(),
			));
		}
	}

	public function delete($id)
	{
		$entity = $this->container->get('stevotvr.groupsub.entity.subscription')->load($id);

		if (!confirm_box(true))
		{
			$hidden_fields = build_hidden_fields(array(
				'id'		=> $id,
				'mode'		=> 'subscription',
				'action'	=> 'delete',
			));
			confirm_box(false, $this->language->lang('ACP_GROUPSUB_SUB_DELETE_CONFIRM'), $hidden_fields);
			return;
		}

		$this->sub_operator->delete_subscription($id);

		if ($this->request->is_ajax())
		{
			$json_response = new json_response();
			$json_response->send(array(
				'MESSAGE_TITLE'	=> $this->language->lang('INFORMATION'),
				'MESSAGE_TEXT'	=> $this->language->lang('ACP_GROUPSUB_SUB_DELETE_SUCCESS'),
				'REFRESH_DATA'	=> array(
					'time'	=> 3
				),
			));
		}

		trigger_error($this->language->lang('ACP_GROUPSUB_SUB_DELETE_SUCCESS') . adm_back_link($this->u_action));
	}
}
