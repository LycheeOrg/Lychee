<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Models;

use App\Actions\Photo\Delete;
use App\Casts\DateTimeWithTimezoneCast;
use App\Casts\MustNotSetCast;
use App\Constants\PhotoAlbum as PA;
use App\Contracts\Models\HasUTCBasedTimes;
use App\Enum\LicenseType;
use App\Enum\SmartAlbumType;
use App\Enum\StorageDiskType;
use App\Exceptions\Internal\IllegalOrderOfOperationException;
use App\Exceptions\Internal\LycheeAssertionError;
use App\Exceptions\Internal\ZeroModuloException;
use App\Exceptions\MediaFileOperationException;
use App\Exceptions\ModelDBException;
use App\Facades\Helpers;
use App\Models\Builders\PhotoBuilder;
use App\Models\Extensions\HasBidirectionalRelationships;
use App\Models\Extensions\HasRandomIDAndLegacyTimeBasedID;
use App\Models\Extensions\SizeVariants;
use App\Models\Extensions\ThrowsConsistentExceptions;
use App\Models\Extensions\ToArrayThrowsNotImplemented;
use App\Models\Extensions\UTCBasedTimes;
use App\Relations\HasManySizeVariants;
use App\Repositories\ConfigManager;
use App\Services\Image\FileExtensionService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use function Safe\preg_match;

/**
 * App\Models\Photo.
 *
 * @property string                $id
 * @property string                $title
 * @property string|null           $description
 * @property Collection<int,Tag>   $tags
 * @property int                   $owner_id
 * @property string|null           $type
 * @property string|null           $iso
 * @property string|null           $aperture
 * @property string|null           $make
 * @property string|null           $model
 * @property string|null           $lens
 * @property string|null           $shutter
 * @property string|null           $focal
 * @property float|null            $latitude
 * @property float|null            $longitude
 * @property float|null            $altitude
 * @property float|null            $img_direction
 * @property string|null           $location
 * @property Carbon|null           $taken_at
 * @property string|null           $taken_at_orig_tz
 * @property Carbon|null           $initial_taken_at
 * @property string|null           $initial_taken_at_orig_tz
 * @property bool                  $is_highlighted
 * @property string|null           $rating_avg
 * @property string|null           $live_photo_short_path
 * @property string|null           $live_photo_url
 * @property string                $checksum
 * @property string                $original_checksum
 * @property LicenseType           $license
 * @property Carbon                $created_at
 * @property Carbon                $updated_at
 * @property string|null           $live_photo_content_id
 * @property string|null           $live_photo_checksum
 * @property Collection<int,Album> $albums
 * @property User                  $owner
 * @property SizeVariants          $size_variants
 * @property int                   $filesize
 * @property Palette|null          $palette
 *
 * @method static PhotoBuilder|Photo addSelect($column)
 * @method static PhotoBuilder|Photo join(string $table, string $first, string $operator = null, string $second = null, string $type = 'inner', string $where = false)
 * @method static PhotoBuilder|Photo joinSub($query, $as, $first, $operator = null, $second = null, $type = 'inner', $where = false)
 * @method static PhotoBuilder|Photo leftJoin(string $table, string $first, string $operator = null, string $second = null)
 * @method static PhotoBuilder|Photo newModelQuery()
 * @method static PhotoBuilder|Photo newQuery()
 * @method static PhotoBuilder|Photo orderBy($column, $direction = 'asc')
 * @method static PhotoBuilder|Photo query()
 * @method static PhotoBuilder|Photo with(array|string $relations)
 * @method static PhotoBuilder|Photo select($columns = [])
 * @method static PhotoBuilder|Photo whereAlbumId($value)
 * @method static PhotoBuilder|Photo whereAltitude($value)
 * @method static PhotoBuilder|Photo whereAperture($value)
 * @method static PhotoBuilder|Photo whereChecksum($value)
 * @method static PhotoBuilder|Photo whereCreatedAt($value)
 * @method static PhotoBuilder|Photo whereDescription($value)
 * @method static PhotoBuilder|Photo whereFilesize($value)
 * @method static PhotoBuilder|Photo whereFocal($value)
 * @method static PhotoBuilder|Photo whereId($value)
 * @method static PhotoBuilder|Photo whereImgDirection($value)
 * @method static PhotoBuilder|Photo whereIn(string $column, string $values, string $boolean = 'and', string $not = false)
 * @method static PhotoBuilder|Photo whereIsHighlighted($value)
 * @method static PhotoBuilder|Photo whereIso($value)
 * @method static PhotoBuilder|Photo whereLatitude($value)
 * @method static PhotoBuilder|Photo whereLegacyId($value)
 * @method static PhotoBuilder|Photo whereLens($value)
 * @method static PhotoBuilder|Photo whereLicense($value)
 * @method static PhotoBuilder|Photo whereLivePhotoChecksum($value)
 * @method static PhotoBuilder|Photo whereLivePhotoContentId($value)
 * @method static PhotoBuilder|Photo whereLivePhotoShortPath($value)
 * @method static PhotoBuilder|Photo whereLocation($value)
 * @method static PhotoBuilder|Photo whereLongitude($value)
 * @method static PhotoBuilder|Photo whereMake($value)
 * @method static PhotoBuilder|Photo whereModel($value)
 * @method static PhotoBuilder|Photo whereNotIn(string $column, string $values, string $boolean = 'and')
 * @method static PhotoBuilder|Photo whereOriginalChecksum($value)
 * @method static PhotoBuilder|Photo whereOwnerId($value)
 * @method static PhotoBuilder|Photo whereShutter($value)
 * @method static PhotoBuilder|Photo whereTags($value)
 * @method static PhotoBuilder|Photo whereTakenAt($value)
 * @method static PhotoBuilder|Photo whereTakenAtOrigTz($value)
 * @method static PhotoBuilder|Photo whereTitle($value)
 * @method static PhotoBuilder|Photo whereType($value)
 * @method static PhotoBuilder|Photo whereUpdatedAt($value)
 */
class Photo extends Model implements HasUTCBasedTimes
{
	/** @phpstan-use HasFactory<\Database\Factories\PhotoFactory> */
	use HasFactory;
	use UTCBasedTimes;
	/** @phpstan-use HasRandomIDAndLegacyTimeBasedID<Photo> */
	use HasRandomIDAndLegacyTimeBasedID;
	use ThrowsConsistentExceptions;
	// @phpstan-use HasBidirectionalRelationships
	use HasBidirectionalRelationships;
	use ToArrayThrowsNotImplemented;

	/**
	 * @var string The type of the primary key
	 */
	protected $keyType = 'string';

	/**
	 * Indicates if the model's primary key is auto-incrementing.
	 *
	 * @var bool
	 */
	public $incrementing = false;

	protected $casts = [
		'created_at' => 'datetime',
		'updated_at' => 'datetime',
		'taken_at' => DateTimeWithTimezoneCast::class,
		'initial_taken_at' => DateTimeWithTimezoneCast::class,
		'live_photo_url' => MustNotSetCast::class . ':live_photo_short_path',
		'taken_at_mod' => 'datetime',
		'owner_id' => 'integer',
		'is_highlighted' => 'boolean',
		'latitude' => 'float',
		'longitude' => 'float',
		'altitude' => 'float',
		'img_direction' => 'float',
		'rating_avg' => 'decimal:4',
	];

	/**
	 * @var list<string> The list of attributes which exist as columns of the DB
	 *                   relation but shall not be serialized to JSON
	 */
	protected $hidden = [
		'album',  // do not serialize relation in order to avoid infinite loops
		'owner',  // do not serialize relation
		'owner_id',
		'live_photo_short_path', // serialize live_photo_url instead
	];

	public function newEloquentBuilder($query): PhotoBuilder
	{
		return new PhotoBuilder($query);
	}

	/**
	 * Return the relationship between a Photo and its Album.
	 *
	 * @return BelongsToMany<Album,$this>
	 */
	public function albums(): BelongsToMany
	{
		return $this->belongsToMany(Album::class, PA::PHOTO_ALBUM, 'photo_id', 'album_id', 'id', 'id');
	}

	/**
	 * Return the relationship between a Photo and its Owner.
	 *
	 * @return BelongsTo<User,$this>
	 */
	public function owner(): BelongsTo
	{
		return $this->belongsTo(User::class, 'owner_id', 'id');
	}

	public function size_variants(): HasManySizeVariants
	{
		return new HasManySizeVariants($this);
	}

	/**
	 * Returns the relationship between a photo and its associated statistics.
	 *
	 * @return HasOne<Statistics,$this>
	 */
	public function statistics(): HasOne
	{
		return $this->hasOne(Statistics::class, 'photo_id', 'id');
	}

	/**
	 * Get the purchasable settings for this photo.
	 *
	 * @return HasMany<Purchasable,$this>
	 */
	public function purchasable(): HasMany
	{
		return $this->hasMany(Purchasable::class, 'photo_id', 'id');
	}

	/**
	 * Get all ratings for this photo.
	 *
	 * @return HasMany<PhotoRating,$this>
	 *
	 * @codeCoverageIgnore Just a simple relationship - Not used yet.
	 */
	public function ratings(): HasMany
	{
		return $this->hasMany(PhotoRating::class, 'photo_id', 'id');
	}

	/**
	 * Get all ratings for this photo.
	 *
	 * @return HasOne<PhotoRating,$this>
	 */
	public function rating(): HasOne
	{
		/** @phpstan-ignore return.type (because of when() method used in the return statement) */
		return $this->hasOne(PhotoRating::class)
			->when(Auth::check(), fn ($query) => $query->where('user_id', '=', Auth::id()))
			->when(!Auth::check(), fn ($query) => $query->whereNull('user_id'));
	}

	/**
	 * Returns the relationship between a photo and its associated color palette.
	 *
	 * This is a one-to-one relationship where each photo can have one palette
	 * associated with it, which contains color information derived from the
	 * photo.
	 *
	 * @return HasOne<Palette,$this>
	 */
	public function palette(): HasOne
	{
		return $this->hasOne(Palette::class, 'photo_id', 'id');
	}

	/**
	 * Returns the relationship between a tag and all photos with whom
	 * this tag is attached.
	 *
	 * @return BelongsToMany<Tag,$this>
	 */
	public function tags(): BelongsToMany
	{
		return $this->belongsToMany(
			Tag::class,
			'photos_tags',
			'photo_id',
			'tag_id',
		);
	}

	/**
	 * Accessor for attribute {@link Photo::$shutter}.
	 *
	 * This accessor ensures that the returned string is either formatted as
	 * a unit fraction or a decimal number irrespective of what is stored
	 * in the database.
	 *
	 * Actually it would be much more efficient to write a mutator which
	 * ensures that the string is stored correctly formatted at the DB right
	 * from the beginning and then simply return the stored string instead of
	 * re-format the string on every fetch.
	 * TODO: Refactor this.
	 *
	 * @param string|null $shutter the value from the database passed in by
	 *                             the Eloquent framework
	 *
	 * @return ?string A properly formatted shutter value
	 */
	protected function getShutterAttribute(?string $shutter): ?string
	{
		try {
			if ($shutter === null || $shutter === '') {
				return null;
			}
			// shutter speed needs to be processed. It is stored as a string `a/b s`
			if (!str_starts_with($shutter, '1/')) {
				preg_match('/(\d+)\/(\d+) s/', $shutter, $matches);
				/** @disregard pass by reference from above. */
				if (count($matches) > 0) {
					$a = intval($matches[1]);
					$b = intval($matches[2]);
					if ($b !== 0) {
						$gcd = Helpers::gcd($a, $b);
						$a /= $gcd;
						$b /= $gcd;
						if ($a === 1) {
							$shutter = '1/' . $b . ' s';
						} else {
							$shutter = ($a / $b) . ' s';
						}
					}
				}
			}

			if ($shutter === '1/1 s') {
				$shutter = '1 s';
			}

			return $shutter;
			// @codeCoverageIgnoreStart
		} catch (ZeroModuloException $e) {
			// this should not happen as we covered the case $b = 0;
			throw LycheeAssertionError::createFromUnexpectedException($e);
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Accessor for attribute `license`.
	 *
	 * If the photo has an explicitly set license, that license is returned.
	 * Else, either the licence of the album is returned (if the photo is
	 * part of an album) or the default license of the application-wide
	 * setting is returned.
	 *
	 * @param ?string $license the value from the database passed in by
	 *                         the Eloquent framework
	 */
	protected function getLicenseAttribute(?string $license): LicenseType
	{
		$config_manager = app(ConfigManager::class);
		if ($license === null) {
			return $config_manager->getValueAsEnum('default_license', LicenseType::class);
		}

		if (LicenseType::tryFrom($license) !== null && LicenseType::tryFrom($license) !== LicenseType::NONE) {
			return LicenseType::from($license);
		}

		// if ($this->album_id !== null && $this->relationLoaded('album')) {
		// 	return $this->album->license;
		// }

		return $config_manager->getValueAsEnum('default_license', LicenseType::class);
	}

	/**
	 * Accessor for attribute `focal`.
	 *
	 * In case the photo is a video (why it is called a photo then, btw?), the
	 * attribute `focal` is exploited to store the framerate and rounded
	 * to two decimal digits.
	 *
	 * Again, we probably should do that when the value is set and stored,
	 * not every time when it is read from the database.
	 * TODO: Refactor this.
	 *
	 * @param string|null $focal the value from the database passed in by the
	 *                           Eloquent framework
	 *
	 * @throws IllegalOrderOfOperationException
	 */
	protected function getFocalAttribute(?string $focal): ?string
	{
		if ($focal === null || $focal === '') {
			return null;
		}

		// We need to format the framerate (stored as focal) -> max 2 decimal digits
		return $this->isVideo() ? (string) round(floatval($focal), 2) : $focal;
	}

	/**
	 * Accessor for the "virtual" attribute {@see Photo::$live_photo_url}.
	 *
	 * Returns the URL of the live photo as it is seen from a client's
	 * point of view.
	 * This is a convenient method and wraps
	 * {@link Photo::$live_photo_short_path} into
	 * {@link \Illuminate\Support\Facades\Storage::url()}.
	 *
	 * @return ?string the url of the file
	 */
	protected function getLivePhotoUrlAttribute(): ?string
	{
		$path = $this->live_photo_short_path;
		$disk_name = $this->size_variants->getOriginal()?->storage_disk?->value ?? StorageDiskType::LOCAL->value;

		/** @disregard P1013 */
		return ($path === null || $path === '') ? null : Storage::disk($disk_name)->url($path);
	}

	/**
	 * Accessor for the virtual attribute $aspect_ratio.
	 *
	 * Returns the correct aspect ratio for
	 * - photos
	 * - and videos where small or medium exists
	 * Otherwise returns 1 (square)
	 *
	 * @return float aspect ratio to use in display mode
	 */
	protected function getAspectRatioAttribute(): float
	{
		if ($this->isVideo() &&
			$this->size_variants->getSmall() === null &&
			$this->size_variants->getMedium() === null) {
			return 1;
		}

		return $this->size_variants->getOriginal()?->ratio ??
			$this->size_variants->getMedium()?->ratio ??
			$this->size_variants->getSmall()?->ratio ?? 1;
	}

	/**
	 * Checks if the photo represents a (real) photo (as opposed to video or raw).
	 *
	 * @throws IllegalOrderOfOperationException
	 */
	public function isPhoto(): bool
	{
		if ($this->type === null || $this->type === '') {
			// @codeCoverageIgnoreStart
			throw new IllegalOrderOfOperationException('Photo::isPhoto() must not be called before Photo::$type has been set');
			// @codeCoverageIgnoreEnd
		}

		$file_extension_service = resolve(FileExtensionService::class);

		return $file_extension_service->isSupportedImageMimeType($this->type);
	}

	/**
	 * Checks if the photo represents a video.
	 *
	 * @throws IllegalOrderOfOperationException
	 */
	public function isVideo(): bool
	{
		if ($this->type === null || $this->type === '') {
			// @codeCoverageIgnoreStart
			throw new IllegalOrderOfOperationException('Photo::isVideo() must not be called before Photo::$type has been set');
			// @codeCoverageIgnoreEnd
		}

		$file_extension_service = resolve(FileExtensionService::class);

		return $file_extension_service->isSupportedVideoMimeType($this->type);
	}

	/**
	 * Checks if the photo represents a raw media.
	 *
	 * The media record is "raw" if it is neither of a supported photo nor
	 * video type.
	 *
	 * @throws IllegalOrderOfOperationException
	 */
	public function isRaw(): bool
	{
		return !$this->isPhoto() && !$this->isVideo();
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws ModelDBException
	 * @throws MediaFileOperationException
	 */
	protected function performDeleteOnModel(): void
	{
		// Delete all the links to the photo.
		DB::table(PA::PHOTO_ALBUM)->where('photo_id', $this->id)->delete();
		// Clean up the files.
		(new Delete())->do([$this->id], SmartAlbumType::UNSORTED->value);
		$this->exists = false;
	}
}