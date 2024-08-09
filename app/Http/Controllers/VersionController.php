<?php

namespace App\Http\Controllers;

use App\Http\Resources\Root\VersionResource;
use Illuminate\Routing\Controller;
use Spatie\LaravelData\Data;

class VersionController extends Controller
{
	/**
	 * Retrieve the data about updates (so that it is not fully blocking).
	 *
	 * @return Data
	 */
	public function get(): Data
	{
		return new VersionResource();
	}
}
