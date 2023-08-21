<?php

namespace App\Http\Controllers\Administration;

use App\Exceptions\Internal\QueryBuilderException;
use App\Http\Requests\Logs\ShowJobsRequest;
use App\Models\Configs;
use App\Models\JobHistory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class JobController extends Controller
{
	/**
	 * @param string $order
	 *
	 * @return Collection<JobHistory>
	 *
	 * @throws QueryBuilderException
	 */
	public function list(ShowJobsRequest $request, string $order = 'desc'): Collection
	{
		// PHPStan does not understand that `get` returns `Collection<Logs>`, but assumes that it returns `Collection<Model>`
		// @phpstan-ignore-next-line
		return JobHistory::query()
		->orderBy('id', $order)
		->limit(Configs::getValueAsInt('log_max_num_line'))
		->get();
	}

	/**
	 * display the Jobs.
	 *
	 * @return View
	 *
	 * @throws QueryBuilderException
	 */
	public function view(ShowJobsRequest $request): View
	{
		return view('jobs', ['jobs' => $this->list($request)]);
	}
}
