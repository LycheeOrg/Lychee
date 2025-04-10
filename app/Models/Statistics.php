<?php

namespace App\Models;

use App\Models\Builders\StatisticsBuilder;
use App\Models\Extensions\ThrowsConsistentExceptions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Statistics.
 *
 * @property int         $id
 * @property string|null $album_id
 * @property string|null $photo_id
 * @property int         $visit_count
 * @property int         $download_count
 * @property int         $favourite_count
 * @property int         $shared_count
 *
 * @method static StatisticsBuilder|Statistics addSelect($column)
 * @method static StatisticsBuilder|Statistics join(string $table, string $first, string $operator = null, string $second = null, string $type = 'inner', string $where = false)
 * @method static StatisticsBuilder|Statistics joinSub($query, $as, $first, $operator = null, $second = null, $type = 'inner', $where = false)
 * @method static StatisticsBuilder|Statistics leftJoin(string $table, string $first, string $operator = null, string $second = null)
 * @method static StatisticsBuilder|Statistics newModelQuery()
 * @method static StatisticsBuilder|Statistics newQuery()
 * @method static StatisticsBuilder|Statistics orderBy($column, $direction = 'asc')
 * @method static StatisticsBuilder|Statistics query()
 * @method static StatisticsBuilder|Statistics select($columns = [])
 * @method static StatisticsBuilder|Statistics whereCreatedAt($value)
 * @method static StatisticsBuilder|Statistics whereId($value)
 * @method static StatisticsBuilder|Statistics whereIn(string $column, string $values, string $boolean = 'and', string $not = false)
 * @method static StatisticsBuilder|Statistics whereNotIn(string $column, string $values, string $boolean = 'and')
 *
 * @mixin \Eloquent
 */
class Statistics extends Model
{
	use ThrowsConsistentExceptions;
	/** @phpstan-use HasFactory<\Database\Factories\StatisticsFactory> */
	use HasFactory;

	public $timestamps = false;

	/**
	 * @param $query
	 *
	 * @return StatisticsBuilder
	 */
	public function newEloquentBuilder($query): StatisticsBuilder
	{
		return new StatisticsBuilder($query);
	}

	protected $fillable = [
		'album_id',
		'photo_id',
		'visit_count',
		'download_count',
		'favourite_count',
		'shared_count',
	];
}
