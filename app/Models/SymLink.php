<?php

namespace App\Models;

use App\Casts\MustNotSetCast;
use App\Exceptions\Internal\FrameworkException;
use App\Models\Extensions\HasAttributesPatch;
use App\Models\Extensions\ThrowsConsistentExceptions;
use App\Models\Extensions\UTCBasedTimes;
use App\Observers\SymLinkObserver;
use Carbon\Exceptions\InvalidTimeZoneException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassAssignmentException;
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
 * @property string $full_path
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
	use ThrowsConsistentExceptions;

	protected string $friendlyModelName = 'symbolic link';

	const DISK_NAME = 'symbolic';

	/**
	 * @throws MassAssignmentException
	 * @throws FrameworkException
	 */
	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);
		try {
			$this->registerObserver(SymLinkObserver::class);
		} catch (\RuntimeException $e) {
			throw new FrameworkException('Laravel\'s observer component', $e);
		}
	}

	protected $casts = [
		'created_at' => 'datetime',
		'updated_at' => 'datetime',
		'url' => MustNotSetCast::class,
	];

	/**
	 * @var string[] The list of attributes which exist as columns of the DB
	 *               relation but shall not be serialized to JSON
	 */
	protected $hidden = [
		'size_variant', // see above and otherwise infinite loops will occur
		'size_variant_id', // see above
	];

	public function size_variant(): BelongsTo
	{
		return $this->belongsTo(SizeVariant::class);
	}

	/**
	 * Scopes the passed query to all outdated symlinks.
	 *
	 * @param Builder $query the unscoped query
	 *
	 * @return Builder the scoped query
	 *
	 * @throws InvalidTimeZoneException
	 */
	public function scopeExpired(Builder $query): Builder
	{
		$expiration = now()->subDays(intval(Configs::get_value('SL_life_time_days', '3')));

		return $query->where('created_at', '<', $this->fromDateTime($expiration));
	}

	/**
	 * Accessor for the "virtual" attribute {@link SymLink::$url}.
	 *
	 * Returns the URL to the symbolic link from the perspective of a
	 * web client.
	 * This is a convenient method and wraps {@link SymLink::$short_path}
	 * into {@link \Illuminate\Support\Facades\Storage::url()}.
	 *
	 * @return string the URL to the symbolic link
	 *
	 * @throws FrameworkException
	 */
	protected function getUrlAttribute(): string
	{
		try {
			return Storage::disk(self::DISK_NAME)->url($this->short_path);
		} catch (\RuntimeException $e) {
			throw new FrameworkException('Laravel\'s storage component', $e);
		}
	}

	/**
	 * Accessor for the "virtual" attribute {@link SymLink::$full_path}.
	 *
	 * Returns the full path of the symbolic link as it needs to be input into
	 * some low-level PHP functions like `unlink`.
	 * This is a convenient method and wraps {@link SymLink::$short_path}
	 * into {@link \Illuminate\Support\Facades\Storage::path()}.
	 *
	 * @return string the full path of the symbolic link
	 */
	protected function getFullPathAttribute(): string
	{
		return Storage::disk(self::DISK_NAME)->path($this->short_path);
	}
}
