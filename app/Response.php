<?php

namespace App;

/**
 * App/Response.
 */
class Response
{
	/**
	 * Return a json encoded string as a Warning.
	 * The Warning: is used by the front end to recognise the kind of message.
	 *
	 * @param $msg
	 *
	 * @return string
	 */
	public static function warning($msg)
	{
		return json_encode('Warning: ' . $msg);
	}

	/**
	 * Return a json encoded string as am Error.
	 * The Error: is used by the front end to recognise the kind of message.
	 *
	 * @param $msg
	 *
	 * @return string
	 */
	public static function error($msg)
	{
		return json_encode('Error: ' . $msg);
	}

	/**
	 * Just return a json encoded string.
	 * This is assumed to be a success.
	 *
	 * @param $str
	 * @param int $options
	 *
	 * @return string
	 */
	public static function json($str, $options = 0)
	{
		return json_encode($str, $options);
	}
}
