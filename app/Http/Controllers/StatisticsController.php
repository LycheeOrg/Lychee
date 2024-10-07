<?php

namespace App\Http\Controllers;

use App\Actions\Statistics\GetSizes;
use App\Http\Resources\Statistics\All;
use Illuminate\Routing\Controller;

class StatisticsController extends Controller
{
	/**
	 * Update the Login information of the current user.
	 */
	public function all(GetSizes $getSizes): All
	{
		return All::fromDTO($getSizes->getFullSize());
	}
}
