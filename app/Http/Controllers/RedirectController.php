<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Controllers;

class RedirectController extends Controller
{
	/**
	 * Trivial redirection.
	 */
	public function album($albumid)
	{
		return redirect('gallery#' . $albumid);
	}

	/**
	 * Trivial redirection.
	 */
	public function photo($albumid, $photoid)
	{
		return redirect('gallery#' . $albumid . '/' . $photoid);
	}
}
