<?php

namespace App\Models;

use App\Enum\JobStatus;
use App\Exceptions\ConfigurationKeyMissingException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

/**
 * @property int         $id
 * @property int         $owner_id
 * @property User        $owner
 * @property string      $job
 * @property string|null $parent_id
 * @property Album|null  $parent
 * @property JobStatus   $status
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 */
class JobHistory extends Model
{
	protected $table = 'jobs_history';

	protected $hidden = [];

	/**
	 * @var array<string, string>
	 */
	protected $casts = [
		'status' => JobStatus::class,
		'created_at' => 'datetime',
		'updated_at' => 'datetime',
		'owner_id' => 'integer',
	];

	/**
	 * The relationships that should always be eagerly loaded by default.
	 */
	protected $with = ['owner'];

	/**
	 * Returns the relationship between an Job and its owner.
	 *
	 * @return BelongsTo
	 */
	public function owner(): BelongsTo
	{
		return $this->belongsTo('App\Models\User', 'owner_id', 'id');
	}

	/**
	 * Returns the relationship between an Job and its associated album.
	 *
	 * @return BelongsTo
	 */
	public function parent(): BelongsTo
	{
		return $this->belongsTo('App\Models\BaseAlbumImpl', 'parent_id', 'id');
	}

	/**
	 * @return Builder
	 *
	 * @throws \RuntimeException
	 * @throws \InvalidArgumentException
	 * @throws ConfigurationKeyMissingException
	 */
	public function scopeWithAlbumTitleOrNull(): Builder
	{
		$with = JobHistory::query()
		->join('base_albums', 'jobs_history.parent_id', '=', 'base_albums.id')
		->select(['jobs_history.*', 'base_albums.title']);

		return JobHistory::query()
		->doesntHave('parent')
		->select(['jobs_history.*', DB::raw('NULL as title')])
		->union($with);
	}
}
