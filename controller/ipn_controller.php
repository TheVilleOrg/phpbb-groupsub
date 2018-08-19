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
use phpbb\request\request_interface;
use stevotvr\groupsub\operator\transaction_interface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Group Subscription controller for listening for PayPal IPN messages.
 */
class ipn_controller
{
	/**
	 * Postback URLs
	 */
	const VERIFY_URI = 'https://ipnpb.paypal.com/cgi-bin/webscr';
	const SANDBOX_VERIFY_URI = 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr';

	/**
	 * Response from PayPal indicating validation was successful
	 */
	const VALID = 'VERIFIED';

	/**
	 * @var \phpbb\config\config
	 */
	protected $config;

	/**
	 * @var \phpbb\request\request_interface
	 */
	protected $request;

	/**
	 * @var \stevotvr\groupsub\operator\transaction_interface
	 */
	protected $trans_operator;

	/**
	 * @param \phpbb\config\config                               $config
	 * @param \phpbb\request\request_interface                   $request
	 * @param \stevotvr\groupsub\operator\transaction_interface  $trans_operator
	 */
	public function __construct(config $config, request_interface $request, transaction_interface $trans_operator)
	{
		$this->config = $config;
		$this->request = $request;
		$this->trans_operator = $trans_operator;
	}

	/**
	 * Handle the /groupsub/ipn route.
	 *
	 * @return \Symfony\Component\HttpFoundation\Response A Symfony Response object
	 */
	public function handle()
	{
		if ($this->request->is_set_post('txn_id'))
		{
			$sandbox = (bool) $this->config['stevotvr_groupsub_pp_sandbox'];
			if (self::verify($sandbox))
			{
				return new Response('', $this->trans_operator->process_transaction() ? 200 : 400);
			}

			return new Response('', 400);
		}

		return new Response('', 200);
	}

	/**
	 * Verify a PayPal IPN request.
	 *
	 * @param boolean $sandbox Sandbox mode is enabled
	 *
	 * @return boolean The request was verified
	 */
	static protected function verify($sandbox)
	{
		$raw_post = file_get_contents('php://input');
		$raw_post_ary = explode('&', $raw_post);

		$return = array();
		foreach ($raw_post_ary as $kv)
		{
			$kv = explode('=', $kv);
			if (count($kv) === 2)
			{
				if ($kv[0] === 'payment_date' && substr_count($kv[1], '+') === 1)
				{
					$kv[1] = str_replace('+', '%2B', $kv[1]);
				}

				$return[$kv[0]] = urldecode($kv[1]);
			}
		}

		$req = 'cmd=_notify-validate';
		foreach ($return as $key => $value)
		{
			$req .= sprintf('&%s=%s', $key, urlencode($value));
		}

		$url = $sandbox ? self::SANDBOX_VERIFY_URI : self::VERIFY_URI;
		$ctx = stream_context_create(array(
			'http' => array(
				'method'			=> 'POST',
				'header'			=> 'Connection: Close',
				'content'			=> $req,
				'protocol_version'	=> 1.1,
				'timeout'			=> 30.0,
				'ignore_errors'		=> true,
			),
		));
		$fp = fopen($url, 'r', false, $ctx);

		if ($fp === false)
		{
			return false;
		}

		try
		{
			$meta = stream_get_meta_data($fp);
			$http_response = $meta['wrapper_data'][0];
			$http_code = (int) substr($http_response, strpos($http_response, ' ') + 1, 3);
			if ($http_code !== 200)
			{
				return false;
			}

			$response = fread($fp, 16);

			return $response === self::VALID;
		}
		finally
		{
			fclose($fp);
		}
	}
}
