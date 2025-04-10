<?php

namespace App\Models;

use App\Enum\MetricsAction;
use App\Models\Builders\LiveMetricsBuilder;
use App\Models\Extensions\ThrowsConsistentExceptions;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\LiveMetrics.
 *
 * @property int    $id
 * @property Carbon $created_at
 * @property string $visitor_id
 * @property string $action
 * @property string $album_id
 * @property string $photo_id
 *
 * @method static LiveMetricsBuilder|LiveMetrics addSelect($column)
 * @method static LiveMetricsBuilder|LiveMetrics join(string $table, string $first, string $operator = null, string $second = null, string $type = 'inner', string $where = false)
 * @method static LiveMetricsBuilder|LiveMetrics joinSub($query, $as, $first, $operator = null, $second = null, $type = 'inner', $where = false)
 * @method static LiveMetricsBuilder|LiveMetrics leftJoin(string $table, string $first, string $operator = null, string $second = null)
 * @method static LiveMetricsBuilder|LiveMetrics newModelQuery()
 * @method static LiveMetricsBuilder|LiveMetrics newQuery()
 * @method static LiveMetricsBuilder|LiveMetrics orderBy($column, $direction = 'asc')
 * @method static LiveMetricsBuilder|LiveMetrics query()
 * @method static LiveMetricsBuilder|LiveMetrics select($columns = [])
 * @method static LiveMetricsBuilder|LiveMetrics whereCreatedAt($value)
 * @method static LiveMetricsBuilder|LiveMetrics whereId($value)
 * @method static LiveMetricsBuilder|LiveMetrics whereIn(string $column, string $values, string $boolean = 'and', string $not = false)
 * @method static LiveMetricsBuilder|LiveMetrics whereNotIn(string $column, string $values, string $boolean = 'and')
 *
 * @mixin \Eloquent
 */
class LiveMetrics extends Model
{
	use ThrowsConsistentExceptions;

	/**
	 * @param $query
	 *
	 * @return LiveMetricsBuilder
	 */
	public function newEloquentBuilder($query): LiveMetricsBuilder
	{
		return new LiveMetricsBuilder($query);
	}

	/**
	 * @var array<string, string>
	 */
	protected $casts = [
		'action' => MetricsAction::class,
		'created_at' => 'datetime',
	];

	protected $fillable = [
		'created_at',
		'visitor_id',
		'action',
		'album_id',
		'photo_id',
	];
}
