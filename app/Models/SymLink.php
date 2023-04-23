<?php

namespace App\Models;

use App\Casts\MustNotSetCast;
use App\Exceptions\Internal\FrameworkException;
use App\Exceptions\MediaFileOperationException;
use App\Exceptions\ModelDBException;
use App\Image\Files\FlysystemFile;
use App\Models\Extensions\HasAttributesPatch;
use App\Models\Extensions\ThrowsConsistentExceptions;
use App\Models\Extensions\UseFixedQueryBuilder;
use App\Models\Extensions\UTCBasedTimes;
use Carbon\Exceptions\InvalidTimeZoneException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Safe\Exceptions\FilesystemException;
use function Safe\symlink;
use function Safe\unlink;

/**
 * App\SymLink.
 *
 * @property int         $id
 * @property int         $size_variant_id
 * @property SizeVariant $size_variant
 * @property string      $short_path
 * @property string      $url
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 *
 * @method static Builder expired()
 */
class SymLink extends Model
{
	use UTCBasedTimes;
	use HasAttributesPatch;
	use ThrowsConsistentExceptions {
		ThrowsConsistentExceptions::delete as private internalDelete;
	}
	/** @phpstan-use UseFixedQueryBuilder<SymLink> */
	use UseFixedQueryBuilder;

	public const DISK_NAME = 'symbolic';

	protected $casts = [
		'id' => 'integer',
		'size_variant_id' => 'integer',
		'created_at' => 'datetime',
		'updated_at' => 'datetime',
		'url' => MustNotSetCast::class,
	];

	/**
	 * @var array<int,string> The list of attributes which exist as columns of the DB
	 *                        relation but shall not be serialized to JSON
	 */
	protected $hidden = [
		'size_variant', // see above and otherwise infinite loops will occur
		'size_variant_id', // see above
	];

	final protected function _toArray(): array
	{
		return parent::toArray();
	}

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
		$expiration = now()->subDays(Configs::getValueAsInt('SL_life_time_days'));

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
	 * Performs the `INSERT` operation of the model and creates an actual
	 * symbolic link on disk.
	 *
	 * If this method cannot create the symbolic link, then this method
	 * cancels the insert operation.
	 *
	 * @param Builder $query
	 *
	 * @return bool
	 *
	 * @throws MediaFileOperationException
	 */
	protected function performInsert(Builder $query): bool
	{
		$file = $this->size_variant->getFile()->toLocalFile();
		$origRealPath = $file->getRealPath();
		$extension = $file->getExtension();
		$symShortPath = hash('sha256', random_bytes(32) . '|' . $origRealPath) . $extension;
		$symAbsolutePath = Storage::disk(SymLink::DISK_NAME)->path($symShortPath);
		try {
			if (is_link($symAbsolutePath)) {
				unlink($symAbsolutePath);
			}
			symlink($origRealPath, $symAbsolutePath);
		} catch (FilesystemException $e) {
			throw new MediaFileOperationException($e->getMessage(), $e);
		}
		$this->short_path = $symShortPath;

		return parent::performInsert($query);
	}

	/**
	 * Deletes the model from the database and the symbolic link from storage.
	 *
	 * If this method cannot delete the symbolic link, then this method
	 * cancels the delete operation.
	 *
	 * @return bool always returns true
	 *
	 * @throws MediaFileOperationException
	 * @throws ModelDBException
	 */
	public function delete(): bool
	{
		// Laravel and Flysystem does not support symbolic links.
		// So we must convert it to a local file
		$flyFile = new FlysystemFile(Storage::disk(self::DISK_NAME), $this->short_path);
		$symLink = $flyFile->toLocalFile();
		$symLink->delete();

		return $this->internalDelete();
	}
}
