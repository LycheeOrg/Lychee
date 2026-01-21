<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Models;

use App\Actions\SizeVariant\Delete;
use App\Casts\MustNotSetCast;
use App\Enum\SizeVariantType;
use App\Enum\StorageDiskType;
use App\Exceptions\MediaFileOperationException;
use App\Exceptions\ModelDBException;
use App\Http\Resources\Models\SizeVariantResource;
use App\Image\Files\FlysystemFile;
use App\Image\Watermarker;
use App\Models\Builders\SizeVariantBuilder;
use App\Models\Extensions\HasBidirectionalRelationships;
use App\Models\Extensions\ThrowsConsistentExceptions;
use App\Models\Extensions\ToArrayThrowsNotImplemented;
use App\Models\Extensions\UTCBasedTimes;
use App\Services\UrlGenerator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * App\Models\SizeVariant.
 *
 * Describes a size variant of a photo.
 *
 * @property int                  $id
 * @property string|null          $photo_id
 * @property Photo|null           $photo
 * @property SizeVariantType      $type
 * @property string               $short_path
 * @property string|null          $short_path_watermarked
 * @property string               $url
 * @property int                  $width
 * @property int                  $height
 * @property float                $ratio
 * @property StorageDiskType|null $storage_disk
 * @property int                  $filesize
 * @property bool                 $is_watermarked
 *
 * @method static SizeVariantBuilder|SizeVariant addSelect($column)
 * @method static SizeVariantBuilder|SizeVariant join(string $table, string $first, string $operator = null, string $second = null, string $type = 'inner', string $where = false)
 * @method static SizeVariantBuilder|SizeVariant joinSub($query, $as, $first, $operator = null, $second = null, $type = 'inner', $where = false)
 * @method static SizeVariantBuilder|SizeVariant leftJoin(string $table, string $first, string $operator = null, string $second = null)
 * @method static SizeVariantBuilder|SizeVariant newModelQuery()
 * @method static SizeVariantBuilder|SizeVariant newQuery()
 * @method static SizeVariantBuilder|SizeVariant orderBy($column, $direction = 'asc')
 * @method static SizeVariantBuilder|SizeVariant query()
 * @method static SizeVariantBuilder|SizeVariant select($columns = [])
 * @method static SizeVariantBuilder|SizeVariant whereFilesize($value)
 * @method static SizeVariantBuilder|SizeVariant whereHeight($value)
 * @method static SizeVariantBuilder|SizeVariant whereId($value)
 * @method static SizeVariantBuilder|SizeVariant whereIn(string $column, string $values, string $boolean = 'and', string $not = false)
 * @method static SizeVariantBuilder|SizeVariant whereNotIn(string $column, string $values, string $boolean = 'and')
 * @method static SizeVariantBuilder|SizeVariant wherePhotoId($value)
 * @method static SizeVariantBuilder|SizeVariant whereShortPath($value)
 * @method static SizeVariantBuilder|SizeVariant whereType($value)
 * @method static SizeVariantBuilder|SizeVariant whereWidth($value)
 *
 * @mixin \Eloquent
 */
class SizeVariant extends Model
{
	use UTCBasedTimes;
	use HasBidirectionalRelationships;
	use ThrowsConsistentExceptions;
	use ToArrayThrowsNotImplemented;
	/** @phpstan-use HasFactory<\Database\Factories\SizeVariantFactory> */
	use HasFactory;

	/**
	 * This model has no own timestamps as it is inseparably bound to its
	 * parent {@link \App\Models\Photo} and uses the same timestamps.
	 *
	 * @var bool
	 */
	public $timestamps = false;

	/**
	 * @var array<string,string>
	 */
	protected $casts = [
		'id' => 'integer',
		'type' => SizeVariantType::class,
		'url' => MustNotSetCast::class . ':short_path',
		'width' => 'integer',
		'height' => 'integer',
		'filesize' => 'integer',
		'ratio' => 'float',
		'storage_disk' => StorageDiskType::class,
	];

	/**
	 * @var list<string> The list of attributes which exist as columns of the DB
	 *                   relation but shall not be serialized to JSON
	 */
	protected $hidden = [
		'id', // irrelevant, because a size variant is always serialized as an embedded object of its photo
		'photo', // see above and otherwise infinite loops will occur
		'photo_id', // see above
		'short_path',  // serialize url instead
		'short_path_watermarked', // serialize url instead
		'storage_disk', // serialize url instead
	];

	/**
	 * @var list<string> The list of "virtual" attributes which do not exist as
	 *                   columns of the DB relation but which shall be appended to
	 *                   JSON from accessors
	 */
	protected $appends = [
		'url',
	];

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var list<string>
	 */
	protected $fillable = ['photo_id', 'storage_disk', 'type', 'short_path', 'width', 'height', 'filesize', 'ratio'];

	/**
	 * @param $query
	 *
	 * @return SizeVariantBuilder
	 */
	public function newEloquentBuilder($query): SizeVariantBuilder
	{
		return new SizeVariantBuilder($query);
	}

	/**
	 * Returns the association to the photo which this size variant belongs
	 * to.
	 *
	 * @return BelongsTo<Photo,$this>
	 */
	public function photo(): BelongsTo
	{
		return $this->belongsTo(Photo::class);
	}

	/**
	 * Accessor for the "virtual" attribute {@link SizeVariant::$url}.
	 *
	 * This is more than a simple convenient method which wraps
	 * {@link SizeVariant::$short_path} into
	 * {@link \Illuminate\Support\Facades\Storage::url()}.
	 * Based on the current application settings and the authenticated user,
	 * this method returns a URL to a short-living link instead of a
	 * direct URL to the actual size variant.
	 *
	 * @return string the url of the size variant
	 */
	public function getUrlAttribute(): string
	{
		if ($this->type === SizeVariantType::PLACEHOLDER) {
			return 'data:image/webp;base64,' . $this->short_path;
		}

		$watermarker = resolve(Watermarker::class);
		$path = $watermarker->get_path($this);

		$url_generator = resolve(UrlGenerator::class);

		return $url_generator->pathToUrl(
			$path,
			$this->storage_disk->value,
			$this->type,
		);
	}

	/**
	 * Return the downloadable URL for this size variant.
	 * This is not watermark aware.
	 *
	 * @return string
	 */
	public function getDownloadUrlAttribute(): string
	{
		$url_generator = resolve(UrlGenerator::class);

		return $url_generator->pathToUrl(
			$this->short_path,
			$this->storage_disk->value,
			$this->type,
		);
	}

	/**
	 * Return the file associated to the size variant.
	 *
	 * @return FlysystemFile
	 */
	public function getFile(): FlysystemFile
	{
		return new FlysystemFile(
			Storage::disk($this->storage_disk->value),
			$this->short_path
		);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws ModelDBException
	 * @throws MediaFileOperationException
	 */
	protected function performDeleteOnModel(): void
	{
		(new Delete())->do([$this->id]);
		$this->exists = false;
	}

	public function toDataResource(bool $no_url = false): SizeVariantResource
	{
		return new SizeVariantResource($this, no_url: $no_url);
	}

	/**
	 * Accessor for the is_watermarked attribute.
	 *
	 * @return bool
	 */
	public function getIsWatermarkedAttribute(): bool
	{
		return $this->short_path_watermarked !== null && $this->short_path_watermarked !== '';
	}
}