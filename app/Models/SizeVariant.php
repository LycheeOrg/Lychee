<?php

namespace App\Models;

use App\Actions\SizeVariant\Delete;
use App\Casts\MustNotSetCast;
use App\Enum\SizeVariantType;
use App\Enum\StorageDiskType;
use App\Exceptions\ConfigurationException;
use App\Exceptions\MediaFileOperationException;
use App\Exceptions\ModelDBException;
use App\Image\Files\FlysystemFile;
use App\Models\Builders\SizeVariantBuilder;
use App\Models\Extensions\HasAttributesPatch;
use App\Models\Extensions\HasBidirectionalRelationships;
use App\Models\Extensions\ThrowsConsistentExceptions;
use App\Models\Extensions\ToArrayThrowsNotImplemented;
use App\Models\Extensions\UTCBasedTimes;
use App\Relations\HasManyBidirectionally;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\Local\LocalFilesystemAdapter;

/**
 * Class SizeVariant.
 *
 * Describes a size variant of a photo.
 *
 * @property int                  $id
 * @property string               $photo_id
 * @property Photo                $photo
 * @property SizeVariantType      $type
 * @property string               $short_path
 * @property string               $url
 * @property string               $full_path
 * @property int                  $width
 * @property int                  $height
 * @property float                $ratio
 * @property StorageDiskType|null $storage_disk
 * @property int                  $filesize
 * @property Collection<SymLink>  $sym_links
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
	use HasAttributesPatch;
	use HasBidirectionalRelationships;
	use ThrowsConsistentExceptions;
	use ToArrayThrowsNotImplemented;
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
		'full_path' => MustNotSetCast::class . ':short_path',
		'url' => MustNotSetCast::class . ':short_path',
		'width' => 'integer',
		'height' => 'integer',
		'filesize' => 'integer',
		'ratio' => 'float',
		'storage_disk' => StorageDiskType::class,
	];

	/**
	 * @var array<int,string> The list of attributes which exist as columns of the DB
	 *                        relation but shall not be serialized to JSON
	 */
	protected $hidden = [
		'id', // irrelevant, because a size variant is always serialized as an embedded object of its photo
		'photo', // see above and otherwise infinite loops will occur
		'photo_id', // see above
		'short_path',  // serialize url instead
		'sym_links', // don't serialize relation of symlinks
	];

	/**
	 * @var array<int,string> The list of "virtual" attributes which do not exist as
	 *                        columns of the DB relation but which shall be appended to
	 *                        JSON from accessors
	 */
	protected $appends = [
		'url',
	];

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int,string>
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
	 * @return array<string,mixed>
	 */
	protected function _toArray(): array
	{
		return parent::toArray();
	}

	/**
	 * Returns the association to the photo which this size variant belongs
	 * to.
	 *
	 * @return BelongsTo<Photo,SizeVariant>
	 */
	public function photo(): BelongsTo
	{
		return $this->belongsTo(Photo::class);
	}

	/**
	 * Returns the association to the symbolics links which point to this
	 * size variant.
	 *
	 * @return HasManyBidirectionally<SymLink,SizeVariant>
	 */
	public function sym_links(): HasManyBidirectionally
	{
		return $this->hasManyBidirectionally(SymLink::class);
	}

	/**
	 * Accessor for the "virtual" attribute {@link SizeVariant::$url}.
	 *
	 * This is more than a simple convenient method which wraps
	 * {@link SizeVariant::$short_path} into
	 * {@link \Illuminate\Support\Facades\Storage::url()}.
	 * Based on the current application settings and the authenticated user,
	 * this method returns a URL to a short-living symbolic link instead of a
	 * direct URL to the actual size variant, if the underlying storage
	 * provides symbolic links.
	 *
	 * @return string the url of the size variant
	 *
	 * @throws ConfigurationException
	 */
	public function getUrlAttribute(): string
	{
		$imageDisk = Storage::disk($this->storage_disk->value);

		if (
			!Configs::getValueAsBool('SL_enable') ||
			(!Configs::getValueAsBool('SL_for_admin') && Auth::user()?->may_administrate === true)
		) {
			return $imageDisk->url($this->short_path);
		}

		$storageAdapter = $imageDisk->getAdapter();
		if ($storageAdapter instanceof AwsS3V3Adapter) {
			return $this->getAwsUrl();
		}

		if ($storageAdapter instanceof LocalFilesystemAdapter) {
			return $this->getSymLinkUrl();
		}

		throw new ConfigurationException('the chosen storage adapter "' . get_class($storageAdapter) . '" does not support the symbolic linking feature');
	}

	/**
	 * Retrieve the tempary url from AWS if possible.
	 *
	 * @return string
	 */
	private function getAwsUrl(): string
	{
		// In order to allow a grace period, we create a new symbolic link,
		$maxLifetime = Configs::getValueAsInt('SL_life_time_days') * 24 * 60 * 60;
		$imageDisk = Storage::disk($this->storage_disk->value);

		// Return the public URL in case the S3 bucket is set to public, otherwise generate a temporary URL
		$visibility = config('filesystems.disks.s3.visibility', 'private');
		if ($visibility === 'public') {
			return $imageDisk->url($this->short_path);
		}

		return $imageDisk->temporaryUrl($this->short_path, now()->addSeconds($maxLifetime));
	}

	/**
	 * Get the symlink url if possible.
	 *
	 * @return string
	 */
	private function getSymLinkUrl(): string
	{
		// In order to allow a grace period, we create a new symbolic link,
		// if the most recent existing link has reached 2/3 of its lifetime
		$maxLifetime = Configs::getValueAsInt('SL_life_time_days') * 24 * 60 * 60;
		$gracePeriod = $maxLifetime / 3;

		/** @var ?SymLink $symLink */
		$symLink = $this->sym_links()->latest()->first();
		if ($symLink === null || $symLink->created_at->isBefore(now()->subSeconds($gracePeriod))) {
			/** @var SymLink $symLink */
			$symLink = $this->sym_links()->create();
		}

		return $symLink->url;
	}

	/**
	 * Accessor for the "virtual" attribute {@link SizeVariant::$full_path}.
	 *
	 * Returns the full path of the size variant as it needs to be input into
	 * some low-level PHP functions like `unlink`.
	 * This is a convenient method and wraps {@link SizeVariant::$short_path}
	 * into {@link \Illuminate\Support\Facades\Storage::path()}.
	 *
	 * TODO: Remove this method eventually, we must not use paths.
	 *
	 * @return string the full path of the file
	 */
	public function getFullPathAttribute(): string
	{
		return Storage::disk($this->storage_disk->value)->path($this->short_path);
	}

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
		$fileDeleter = (new Delete())->do([$this->id]);
		$this->exists = false;
		$fileDeleter->do();
	}
}
