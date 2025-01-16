<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Models;

use App\Enum\JobStatus;
use App\Models\Builders\JobHistoryBuilder;
use App\Models\Extensions\ThrowsConsistentExceptions;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int       $id
 * @property int       $owner_id
 * @property User      $owner
 * @property string    $job
 * @property JobStatus $status
 * @property Carbon    $created_at
 * @property Carbon    $updated_at
 *
 * @method static JobHistoryBuilder|JobHistory addSelect($column)
 * @method static JobHistoryBuilder|JobHistory join(string $table, string $first, string $operator = null, string $second = null, string $type = 'inner', string $where = false)
 * @method static JobHistoryBuilder|JobHistory joinSub($query, $as, $first, $operator = null, $second = null, $type = 'inner', $where = false)
 * @method static JobHistoryBuilder|JobHistory leftJoin(string $table, string $first, string $operator = null, string $second = null)
 * @method static JobHistoryBuilder|JobHistory newModelQuery()
 * @method static JobHistoryBuilder|JobHistory newQuery()
 * @method static JobHistoryBuilder|JobHistory orderBy($column, $direction = 'asc')
 * @method static JobHistoryBuilder|JobHistory query()
 * @method static JobHistoryBuilder|JobHistory select($columns = [])
 * @method static JobHistoryBuilder|JobHistory whereCreatedAt($value)
 * @method static JobHistoryBuilder|JobHistory whereId($value)
 * @method static JobHistoryBuilder|JobHistory whereIn(string $column, string $values, string $boolean = 'and', string $not = false)
 * @method static JobHistoryBuilder|JobHistory whereJob($value)
 * @method static JobHistoryBuilder|JobHistory whereNotIn(string $column, string $values, string $boolean = 'and')
 * @method static JobHistoryBuilder|JobHistory whereOwnerId($value)
 * @method static JobHistoryBuilder|JobHistory whereStatus($value)
 * @method static JobHistoryBuilder|JobHistory whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class JobHistory extends Model
{
	use ThrowsConsistentExceptions;

	protected $table = 'jobs_history';

	protected $hidden = [];

	/**
	 * @param $query
	 *
	 * @return JobHistoryBuilder
	 */
	public function newEloquentBuilder($query): JobHistoryBuilder
	{
		return new JobHistoryBuilder($query);
	}

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
	 * @return BelongsTo<User,$this>
	 */
	public function owner(): BelongsTo
	{
		return $this->belongsTo(User::class, 'owner_id', 'id');
	}
}
