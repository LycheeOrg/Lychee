<?php

namespace App\Models;

use App\Casts\MustNotSetCast;
use App\Models\Extensions\HasAttributesPatch;
use App\Models\Extensions\UTCBasedTimes;
use App\Observers\SymLinkObserver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * App\SymLink.
 *
 * @property int $id
 * @property int $size_variant_id
 * @property SizeVariant size_variant
 * @property string $short_path
 * @property string $url
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @method static Builder expired()
 */
class SymLink extends Model
{
	use Notifiable;
	use UTCBasedTimes;
	use HasAttributesPatch;

	const DISK_NAME = 'symbolic';

	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);
		$this->registerObserver(SymLinkObserver::class);
	}

	protected $casts = [
		'created_at' => 'datetime',
		'updated_at' => 'datetime',
		'size_variant_id' => MustNotSetCast::class,
		'short_path' => MustNotSetCast::class,
		'url' => MustNotSetCast::class,
	];

	public function sizeVariant(): BelongsTo
	{
		return $this->belongsTo(SizeVariant::class);
	}

	/**
	 * Scopes the passed query to all outdated symlinks.
	 *
	 * @param Builder $query the unscoped query
	 *
	 * @return Builder the scoped query
	 */
	public function scopeExpired(Builder $query): Builder
	{
		$expiration = now()->subDays(intval(Configs::get_value('SL_life_time_days', '3')));

		return $query->where('created_at', '<', $this->fromDateTime($expiration));
	}

	protected function getUrlAttribute(): string
	{
		return Storage::disk(self::DISK_NAME)->url($this->short_path);
	}
}
