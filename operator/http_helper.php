<?php
/**
 *
 * Group Subscription. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2019, Steve Guidetti, https://github.com/stevotvr
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace stevotvr\groupsub\operator;

/**
 * Group Subscription HTTP helper operator.
 */
class http_helper extends operator implements http_helper_interface
{
	/**
	 * @inheritDoc
	 */
	public function post($url, $body)
	{
		return function_exists('curl_init') ? $this->post_curl($url, $body) : $this->post_fopen($url, $body);
	}

	/**
	 * Make an HTTPS POST request to a remote URL using fopen.
	 *
	 * @param string $url  The URL for the request
	 * @param string $body The body of the request
	 *
	 * @return string|boolean The response body, or FALSE on failure
	 */
	protected function post_fopen($url, $body)
	{
		$ctx = stream_context_create(array(
			'http' => array(
				'method'			=> 'POST',
				'header'			=> 'Connection: Close',
				'content'			=> $body,
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

		$meta = stream_get_meta_data($fp);
		$http_response = $meta['wrapper_data'][0];
		$http_code = (int) substr($http_response, strpos($http_response, ' ') + 1, 3);
		if ($http_code !== 200)
		{
			return false;
		}

		$response = stream_get_contents($fp);

		fclose($fp);

		return $response;
	}

	/**
	 * Make an HTTPS POST request to a remote URL using cURL.
	 *
	 * @param string $url  The URL for the request
	 * @param string $body The body of the request
	 *
	 * @return string|boolean The response body, or FALSE on failure
	 */
	protected function post_curl($url, $body)
	{
		$ch = curl_init($url);

		if ($ch === false)
		{
			return false;
		}

		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));

		$response = curl_exec($ch);

		curl_close($ch);

		return $response;
	}
}
