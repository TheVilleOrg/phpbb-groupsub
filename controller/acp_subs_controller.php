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
use phpbb\db\driver\driver_interface;
use phpbb\language\language;
use phpbb\request\request;
use phpbb\template\template;
use phpbb\user;
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
	 * @var \phpbb\db\driver\driver_interface
	 */
	protected $db;

	/**
	 * @var \phpbb\user
	 */
	protected $user;

	/**
	 * The root phpBB path.
	 *
	 * @var string
	 */
	protected $root_path;
	/**
	 * The script file extension.
	 *
	 * @var string
	 */
	protected $php_ext;

	/**
	 * @param \phpbb\config\config                               $config
	 * @param ContainerInterface                                 $container
	 * @param \phpbb\language\language                           $language
	 * @param \phpbb\request\request                             $request
	 * @param \phpbb\template\template                           $template
	 * @param array                                              $currencies    List of currencies
	 * @param \stevotvr\groupsub\operator\product_interface      $prod_operator
	 * @param \stevotvr\groupsub\operator\subscription_interface $sub_operator
	 * @param \phpbb\db\driver\driver_interface                  $db
	 * @param \phpbb\user                                        $user
	 * @param string                                             $root_path     The root phpBB path
	 * @param string                                             $php_ext       The script file
	 *                                                                          extension
	 */
	public function __construct(config $config, ContainerInterface $container, language $language, request $request, template $template, array $currencies, prod_operator $prod_operator, sub_operator $sub_operator, driver_interface $db, user $user, $root_path, $php_ext)
	{
		parent::__construct($config, $container, $language, $request, $template, $currencies);
		$this->prod_operator = $prod_operator;
		$this->sub_operator = $sub_operator;
		$this->db = $db;
		$this->user = $user;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
	}

	public function display()
	{
		$subscriptions = $this->sub_operator->get_subscriptions();

		foreach ($subscriptions as $subscription)
		{
			$entity = $subscription['entity'];
			$this->template->assign_block_vars('subscription', array(
				'SUB_USER'		=> $subscription['username'],
				'SUB_PRODUCT'	=> $subscription['product'],
				'SUB_EXPIRES'	=> $this->user->format_date($entity->get_expire()),

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

		$u_find_username = append_sid($this->root_path . 'memberlist.' . $this->php_ext,
			'mode=searchuser&amp;form=add_edit_sub&amp;field=sub_user&amp;select_single=true');
		$this->template->assign_vars(array(
			'S_ADD_SUB'	=> true,

			'U_ACTION'			=> $this->u_action . '&amp;action=add',
			'U_FIND_USERNAME'	=> $u_find_username,
		));
	}

	public function edit($id)
	{
		$subscription = $this->sub_operator->get_subscription($id);
		$this->add_edit_sub_data($subscription['entity']);
		$this->template->assign_vars(array(
			'S_EDIT_SUB'	=> true,

			'SUB_PRODUCT'	=> $subscription['product'],
			'SUB_USER'		=> $subscription['username'],

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

		$data = array();
		if ($submit)
		{
			$this->parse_expire($data, $errors);
		}

		if ($entity->get_id())
		{
			$this->template->assign_vars(array(
				'SUB_PRODUCT'	=> $entity->get_product(),
				'SUB_USER'		=> $entity->get_user(),
			));
		}
		else
		{
			if ($submit)
			{
				$this->parse_username($data, $errors);
			}
			$data['product'] = $this->request->variable('sub_product', 0);

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
		$expire = $entity->get_expire() ? $this->user->format_date($entity->get_expire(), 'Y-m-d') : '';

		$this->template->assign_vars(array(
			'S_ERROR'	=> (bool) count($errors),
			'ERROR_MSG'	=> count($errors) ? implode('<br />', $errors) : '',

			'SUB_EXPIRE'	=> $expire,

			'U_BACK'	=> $this->u_action,
		));
	}

	/**
	 * Parse the user field for creating a subscription.
	 *
	 * @param array &$data   The submitted data
	 * @param array &$errors The error array
	 */
	protected function parse_username(array &$data, array &$errors)
	{
		$username = $this->request->variable('sub_user', '', true);
		$sql = 'SELECT user_id
				FROM ' . USERS_TABLE . "
				WHERE username_clean = '" . $this->db->sql_escape(utf8_clean_string($username)) . "'";
		$result = $this->db->sql_query($sql);
		$userrow = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$userrow)
		{
			$errors[] = 'NO_USER';
			return;
		}

		$data['user'] = (int) $userrow['user_id'];
	}

	/**
	 * Parse the expiration date fields.
	 *
	 * @param array &$data   The submitted data
	 * @param array &$errors The error array
	 */
	protected function parse_expire(array &$data, array &$errors)
	{
		$date_parts = explode('-', $this->request->variable('sub_expire', ''));
		if (count($date_parts) == 3 && ((int) $date_parts[0] < 9999) &&
			(strlen($date_parts[0]) == 4) && (strlen($date_parts[1]) == 2) && (strlen($date_parts[2]) == 2))
		{
			$data['expire'] = $this->user->create_datetime()
										->setDate((int) $date_parts[0], (int) $date_parts[1], (int) $date_parts[2])
										->setTime(0, 0, 0)
										->getTimestamp();
			return;
		}

		$errors[] = 'ACP_GROUPSUB_ERROR_INVALID_DATE';
	}

	/**
	 * Load the list of available products into template block variables.
	 */
	protected function load_products()
	{
		$entities = $this->prod_operator->get_products();

		if (!count($entities))
		{
			trigger_error($this->language->lang('ACP_GROUPSUB_ERROR_NO_PRODS') . adm_back_link($this->u_action), E_USER_WARNING);
		}

		foreach ($entities as $entity)
		{
			$this->template->assign_block_vars('product', array(
				'PROD_ID'	=> $entity->get_id(),
				'PROD_NAME'	=> $entity->get_name(),
			));
		}
	}

	public function delete($id)
	{
		if (!confirm_box(true))
		{
			$hidden_fields = build_hidden_fields(array(
				'id'		=> $id,
				'mode'		=> 'subscriptions',
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
