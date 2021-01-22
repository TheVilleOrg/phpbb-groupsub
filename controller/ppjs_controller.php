<?php
/**
 *
 * Group Subscription. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2021, Steve Guidetti, https://github.com/stevotvr
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace stevotvr\groupsub\controller;

use PayPalCheckoutSdk\Core\LiveEnvironment;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use phpbb\config\config;
use phpbb\controller\helper;
use phpbb\request\request_interface;
use phpbb\user;
use stevotvr\groupsub\operator\currency_interface;
use stevotvr\groupsub\operator\package_interface;
use stevotvr\groupsub\operator\transaction_interface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Group Subscription controller for the PayPal JavaScript backend
 */
class ppjs_controller
{
	/**
	 * @var config
	 */
	protected $config;

	/**
	 * @var currency_interface
	 */
	protected $currency;

	/**
	 * @var helper
	 */
	protected $helper;

	/**
	 * @var request_interface
	 */
	protected $request;

	/**
	 * @var package_interface
	 */
	protected $pkg_operator;

	/**
	 * @var transaction_interface
	 */
	protected $trans_operator;

	/**
	 * @var user
	 */
	protected $user;

	/**
	 * @param config                $config
	 * @param currency_interface    $currency
	 * @param helper                $helper
	 * @param request_interface     $request
	 * @param package_interface     $pkg_operator
	 * @param transaction_interface $trans_operator
	 * @param user                  $user
	 */
	public function __construct(config $config, currency_interface $currency, helper $helper, request_interface $request, package_interface $pkg_operator, transaction_interface $trans_operator, user $user)
	{
		$this->config = $config;
		$this->currency = $currency;
		$this->helper = $helper;
		$this->request = $request;
		$this->pkg_operator = $pkg_operator;
		$this->trans_operator = $trans_operator;
		$this->user = $user;
	}

	/**
	 * Handle the /ppjs/{action} route.
	 *
	 * @param string $action The action requested
	 *
	 * @return Response A Symfony Response object
	 */
	public function handle($action)
	{
		$sandbox = $this->config['stevotvr_groupsub_pp_sandbox'];
		$client_id = $this->config[$sandbox ? 'stevotvr_groupsub_sb_client' : 'stevotvr_groupsub_pp_client'];
		$client_secret = $this->config[$sandbox ? 'stevotvr_groupsub_sb_secret' : 'stevotvr_groupsub_pp_secret'];

		if (!$client_id || !$client_secret)
		{
			return new Response('', 404);
		}

		$environment = $sandbox ? new SandboxEnvironment($client_id, $client_secret) : new LiveEnvironment($client_id, $client_secret);
		$client = new PayPalHttpClient($environment);

		switch ($action)
		{
			case 'create':
				return $this->create($client);
			case 'capture':
				return $this->capture($client);
		}

		return new Response('', 404);
	}

	/**
	 * Create a new PayPal order and return the ID.
	 *
	 * @param PayPalHttpClient $client The PayPal HTTP client

	 * @return Response A Symfony Response object
	 */
	protected function create(PayPalHttpClient $client)
	{
		$term_id = $this->request->variable('term_id', '');
		$term = $this->pkg_operator->get_package_term($term_id);
		if (!$term)
		{
			return new Response('', 404);
		}

		$price = $term['term']->get_price();
		$currency = $term['term']->get_currency();

		$request = new OrdersCreateRequest();
		$request->body = array(
			'intent' => 'CAPTURE',
			'application_context' => array(
				'shipping_preference'	=> 'NO_SHIPPING',
			),
			'purchase_units' => array(
				array(
					'reference_id'	=> $term['term']->get_id(),
					'description'	=> $term['package']->get_name(),
					'custom_id'		=> $this->user->data['user_id'],
					'invoice_id'	=> strtoupper(substr(md5(mt_rand()), 0, 17)),
					'amount'		=> array(
						'currency_code'	=> $currency,
						'value'			=> $this->currency->format_value($currency, $price, false, false),
					),
				),
			),
		);

		$response = $client->execute($request);

		return new Response($response->result->id, $response->statusCode);
	}

	/**
	 * Capture a PayPal order and process the transaction.
	 *
	 * @param PayPalHttpClient $client The PayPal HTTP client

	 * @return Response A Symfony Response object
	 */
	protected function capture(PayPalHttpClient $client)
	{
		$request = new OrdersCaptureRequest($this->request->variable('order_id', ''));

		$response = $client->execute($request);
		$success = $this->trans_operator->process_transaction($response, $this->config['stevotvr_groupsub_pp_sandbox']);

		return new Response('', $success ? 200 : 400);
	}
}
