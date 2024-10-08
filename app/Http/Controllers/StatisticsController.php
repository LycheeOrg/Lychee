<?php

namespace App\Http\Controllers;

use App\Actions\Statistics\GetSizes;
use App\Http\Resources\Statistics\Statistics;
use Illuminate\Routing\Controller;

class StatisticsController extends Controller
{
	/**
	 * Update the Login information of the current user.
	 */
	public function all(GetSizes $getSizes): Statistics
	{
		return Statistics::fromDTO($getSizes->getFullSizeBreakdown(), $getSizes->getAlbumsSizes(), $getSizes->getTotalAlbumsSizes());
	}
}
