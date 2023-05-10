<?php

namespace App\Models\Builders;

use App\Eloquent\FixedQueryBuilder;
use App\Exceptions\Internal\QueryBuilderException;
use App\Models\JobHistory;
use Illuminate\Support\Facades\DB;

/**
 * Specialized query builder for {@link \App\Models\JobHistory}.
 *
 * @extends FixedQueryBuilder<\App\Models\JobHistory>
 */
class JobHistoryBuilder extends FixedQueryBuilder
{
	/**
	 * Get the hydrated models without eager loading.
	 *
	 * @param array<string>|string $columns
	 *
	 * @return JobHistory[]
	 *
	 * @throws QueryBuilderException
	 */
	public function getModels($columns = ['*']): array
	{
		$baseQuery = $this->getQuery();

		if ($baseQuery->columns === null || count($baseQuery->columns) === 0) {
			$this->select([$baseQuery->from . '.*']);
		}

		if (
			($columns === ['*'] || $columns === ['jobs_history.*']) &&
			($baseQuery->columns === ['*'] || $baseQuery->columns === ['jobs_history.*'])
		) {
			$title = DB::table('base_albums', 'ba')
				->selectRaw('ba.title')
				->whereColumn('ba.id', '=', 'jobs_history.parent_id');

			$this->addSelect(['title' => $title]);
		}

		return parent::getModels($columns);
	}
}