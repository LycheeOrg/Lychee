<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Models;

use App\Casts\MustNotSetCast;
use App\Constants\FileSystem;
use App\Exceptions\Internal\FrameworkException;
use App\Exceptions\MediaFileOperationException;
use App\Exceptions\ModelDBException;
use App\Image\Files\FlysystemFile;
use App\Models\Builders\SymLinkBuilder;
use App\Models\Extensions\ThrowsConsistentExceptions;
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
 * App\Models\SymLink.
 *
 * @property int         $id
 * @property int         $size_variant_id
 * @property SizeVariant $size_variant
 * @property string      $short_path
 * @property string      $url
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 *
 * @method static Builder                expired()
 * @method static SymLinkBuilder|SymLink addSelect($column)
 * @method static SymLinkBuilder|SymLink join(string $table, string $first, string $operator = null, string $second = null, string $type = 'inner', string $where = false)
 * @method static SymLinkBuilder|SymLink joinSub($query, $as, $first, $operator = null, $second = null, $type = 'inner', $where = false)
 * @method static SymLinkBuilder|SymLink leftJoin(string $table, string $first, string $operator = null, string $second = null)
 * @method static SymLinkBuilder|SymLink newModelQuery()
 * @method static SymLinkBuilder|SymLink newQuery()
 * @method static SymLinkBuilder|SymLink orderBy($column, $direction = 'asc')
 * @method static SymLinkBuilder|SymLink query()
 * @method static SymLinkBuilder|SymLink select($columns = [])
 * @method static SymLinkBuilder|SymLink whereCreatedAt($value)
 * @method static SymLinkBuilder|SymLink whereId($value)
 * @method static SymLinkBuilder|SymLink whereIn(string $column, string $values, string $boolean = 'and', string $not = false)
 * @method static SymLinkBuilder|SymLink whereNotIn(string $column, string $values, string $boolean = 'and')
 * @method static SymLinkBuilder|SymLink whereShortPath($value)
 * @method static SymLinkBuilder|SymLink whereSizeVariantId($value)
 * @method static SymLinkBuilder|SymLink whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class SymLink extends Model
{
	use UTCBasedTimes;
	use ThrowsConsistentExceptions {
		ThrowsConsistentExceptions::delete as private internalDelete;
	}

	protected $casts = [
		'id' => 'integer',
		'size_variant_id' => 'integer',
		'created_at' => 'datetime',
		'updated_at' => 'datetime',
		'url' => MustNotSetCast::class,
	];

	/**
	 * @var list<string> The list of attributes which exist as columns of the DB
	 *                   relation but shall not be serialized to JSON
	 */
	protected $hidden = [
		'size_variant', // see above and otherwise infinite loops will occur
		'size_variant_id', // see above
	];

	/**
	 * @param $query
	 *
	 * @return SymLinkBuilder
	 */
	public function newEloquentBuilder($query): SymLinkBuilder
	{
		return new SymLinkBuilder($query);
	}

	/**
	 * @return BelongsTo<SizeVariant,$this>
	 */
	public function size_variant(): BelongsTo
	{
		return $this->belongsTo(SizeVariant::class);
	}

	/**
	 * Scopes the passed query to all outdated symlinks.
	 *
	 * @param Builder<SizeVariant> $query the unscoped query
	 *
	 * @return Builder<SizeVariant> the scoped query
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
			/** @disregard P1013 */
			return Storage::disk(FileSystem::SYMLINK)->url($this->short_path);
			// @codeCoverageIgnoreStart
		} catch (\RuntimeException $e) {
			throw new FrameworkException('Laravel\'s storage component', $e);
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Performs the `INSERT` operation of the model and creates an actual
	 * symbolic link on disk.
	 *
	 * If this method cannot create the symbolic link, then this method
	 * cancels the insert operation.
	 *
	 * @param Builder<static> $query
	 *
	 * @return bool
	 *
	 * @throws MediaFileOperationException
	 */
	protected function performInsert(Builder $query): bool
	{
		$file = $this->size_variant->getFile()->toLocalFile();
		$orig_real_path = $file->getRealPath();
		$extension = $file->getExtension();
		$sym_short_path = hash('sha256', random_bytes(32) . '|' . $orig_real_path) . $extension;
		/** @disregard P1013 */
		$sym_absolute_path = Storage::disk(FileSystem::SYMLINK)->path($sym_short_path);
		try {
			if (is_link($sym_absolute_path)) {
				unlink($sym_absolute_path);
			}
			symlink($orig_real_path, $sym_absolute_path);
			// @codeCoverageIgnoreStart
		} catch (FilesystemException $e) {
			throw new MediaFileOperationException($e->getMessage(), $e);
		}
		// @codeCoverageIgnoreEnd
		$this->short_path = $sym_short_path;

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
		$fly_file = new FlysystemFile(Storage::disk(FileSystem::SYMLINK), $this->short_path);
		$sym_link = $fly_file->toLocalFile();
		$sym_link->delete();

		return $this->internalDelete();
	}
}
