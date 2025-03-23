<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Controllers\Administration;

use App\Actions\Db\OptimizeDb;
use App\Actions\Db\OptimizeTables;
use App\Exceptions\Internal\QueryBuilderException;
use App\Legacy\V1\Requests\Settings\OptimizeRequest;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

final class OptimizeController extends Controller
{
	public function __construct(
		private OptimizeDb $optimize_db,
		private OptimizeTables $optimize_tables)
	{
	}

	/**
	 * display the Jobs.
	 *
	 * @return View
	 *
	 * @throws QueryBuilderException
	 */
	public function view(OptimizeRequest $request): View
	{
		$result = collect($this->optimize_db->do())->merge(collect($this->optimize_tables->do()))->all();

		return view('list', ['lines' => $result]);
	}
}
