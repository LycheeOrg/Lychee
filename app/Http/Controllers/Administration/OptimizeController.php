<?php

namespace App\Http\Controllers\Administration;

use App\Actions\Db\OptimizeDb;
use App\Actions\Db\OptimizeTables;
use App\Exceptions\Internal\QueryBuilderException;
use App\Http\Requests\Settings\OptimizeRequest;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class OptimizeController extends Controller
{
	protected OptimizeDb $optimizeDb;
	protected OptimizeTables $optimizeTables;

	public function __construct(OptimizeDb $optimizeDb, OptimizeTables $optimizeTables)
	{
		$this->optimizeDb = $optimizeDb;
		$this->optimizeTables = $optimizeTables;
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
		$result = collect($this->optimizeDb->do())->merge(collect($this->optimizeTables->do()))->all();

		return view('list', ['lines' => $result]);
	}
}
