<?php

namespace App\Models;

use App\Actions\Photo\Delete;
use App\Casts\ArrayCast;
use App\Casts\DateTimeWithTimezoneCast;
use App\Casts\MustNotSetCast;
use App\Constants\RandomID;
use App\Exceptions\Internal\IllegalOrderOfOperationException;
use App\Exceptions\Internal\LycheeAssertionError;
use App\Exceptions\Internal\ZeroModuloException;
use App\Exceptions\MediaFileOperationException;
use App\Exceptions\ModelDBException;
use App\Facades\Helpers;
use App\Image\Files\BaseMediaFile;
use App\Models\Builders\PhotoBuilder;
use App\Models\Extensions\HasAttributesPatch;
use App\Models\Extensions\HasBidirectionalRelationships;
use App\Models\Extensions\HasRandomIDAndLegacyTimeBasedID;
use App\Models\Extensions\SizeVariants;
use App\Models\Extensions\ThrowsConsistentExceptions;
use App\Models\Extensions\ToArrayThrowsNotImplemented;
use App\Models\Extensions\UTCBasedTimes;
use App\Relations\HasManySizeVariants;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use function Safe\preg_match;

/**
 * App\Photo.
 *
 * @property string       $id
 * @property int          $legacy_id
 * @property string       $title
 * @property string|null  $description
 * @property string[]     $tags
 * @property bool         $is_public
 * @property int          $owner_id
 * @property string|null  $type
 * @property string|null  $iso
 * @property string|null  $aperture
 * @property string|null  $make
 * @property string|null  $model
 * @property string|null  $lens
 * @property string|null  $shutter
 * @property string|null  $focal
 * @property float|null   $latitude
 * @property float|null   $longitude
 * @property float|null   $altitude
 * @property float|null   $img_direction
 * @property string|null  $location
 * @property Carbon|null  $taken_at
 * @property string|null  $taken_at_orig_tz
 * @property bool         $is_starred
 * @property string|null  $live_photo_short_path
 * @property string|null  $live_photo_full_path
 * @property string|null  $live_photo_url
 * @property string|null  $album_id
 * @property string       $checksum
 * @property string       $original_checksum
 * @property string       $license
 * @property Carbon       $created_at
 * @property Carbon       $updated_at
 * @property string|null  $live_photo_content_id
 * @property string|null  $live_photo_checksum
 * @property Album|null   $album
 * @property User         $owner
 * @property SizeVariants $size_variants
 * @property int          $filesize
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
 * @method static PhotoBuilder|Photo whereIsPublic($value)
 * @method static PhotoBuilder|Photo whereIsStarred($value)
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
 *
 * @mixin \Eloquent
 */
class Photo extends Model
{
	use UTCBasedTimes;
	use HasAttributesPatch;
	use HasRandomIDAndLegacyTimeBasedID;
	use ThrowsConsistentExceptions;
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
		RandomID::LEGACY_ID_NAME => RandomID::LEGACY_ID_TYPE,
		'created_at' => 'datetime',
		'updated_at' => 'datetime',
		'taken_at' => DateTimeWithTimezoneCast::class,
		'live_photo_full_path' => MustNotSetCast::class . ':live_photo_short_path',
		'live_photo_url' => MustNotSetCast::class . ':live_photo_short_path',
		'owner_id' => 'integer',
		'is_starred' => 'boolean',
		'is_public' => 'boolean',
		'tags' => ArrayCast::class,
		'latitude' => 'float',
		'longitude' => 'float',
		'altitude' => 'float',
		'img_direction' => 'float',
	];

	/**
	 * @param $query
	 *
	 * @return PhotoBuilder
	 */
	public function newEloquentBuilder($query): PhotoBuilder
	{
		return new PhotoBuilder($query);
	}

	/**
	 * Return the relationship between a Photo and its Album.
	 *
	 * @return BelongsTo
	 */
	public function album(): BelongsTo
	{
		return $this->belongsTo(Album::class, 'album_id', 'id');
	}

	/**
	 * Return the relationship between a Photo and its Owner.
	 *
	 * @return BelongsTo
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
				if ($matches) {
					$a = intval($matches[1]);
					$b = intval($matches[2]);
					if ($b !== 0) {
						$gcd = Helpers::gcd($a, $b);
						$a = $a / $gcd;
						$b = $b / $gcd;
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
		} catch (ZeroModuloException $e) {
			// this should not happen as we covered the case $b = 0;
			throw LycheeAssertionError::createFromUnexpectedException($e);
		}
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
	 *
	 * @return string
	 */
	protected function getLicenseAttribute(?string $license): string
	{
		if ($license === null) {
			return Configs::getValueAsString('default_license');
		}

		if ($license !== 'none') {
			return $license;
		}
		if ($this->album_id !== null) {
			return $this->album->license;
		}

		return Configs::getValueAsString('default_license');
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
	 * @return ?string
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
	 * Accessor for the "virtual" attribute {@see Photo::$live_photo_full_path}.
	 *
	 * Returns the full path of the live photo as it needs to be input into
	 * some low-level PHP functions like `unlink`.
	 * This is a convenient method and wraps
	 * {@link Photo::$live_photo_short_path} into
	 * {@link \Illuminate\Support\Facades\Storage::path()}.
	 *
	 * @return string|null The full path of the live photo
	 */
	protected function getLivePhotoFullPathAttribute(): ?string
	{
		$path = $this->live_photo_short_path;

		return ($path === null || $path === '') ? null : Storage::path($path);
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

		return ($path === null || $path === '') ? null : Storage::url($path);
	}

	/**
	 * Checks if the photo represents a (real) photo (as opposed to video or raw).
	 *
	 * @return bool
	 *
	 * @throws IllegalOrderOfOperationException
	 */
	public function isPhoto(): bool
	{
		if ($this->type === null || $this->type === '') {
			throw new IllegalOrderOfOperationException('Photo::isPhoto() must not be called before Photo::$type has been set');
		}

		return BaseMediaFile::isSupportedImageMimeType($this->type);
	}

	/**
	 * Checks if the photo represents a video.
	 *
	 * @return bool
	 *
	 * @throws IllegalOrderOfOperationException
	 */
	public function isVideo(): bool
	{
		if ($this->type === null || $this->type === '') {
			throw new IllegalOrderOfOperationException('Photo::isVideo() must not be called before Photo::$type has been set');
		}

		return BaseMediaFile::isSupportedVideoMimeType($this->type);
	}

	/**
	 * Checks if the photo represents a raw media.
	 *
	 * The media record is "raw" if it is neither of a supported photo nor
	 * video type.
	 *
	 * @return bool
	 *
	 * @throws IllegalOrderOfOperationException
	 */
	public function isRaw(): bool
	{
		return !$this->isPhoto() && !$this->isVideo();
	}

	/**
	 * @throws ModelDBException
	 * @throws IllegalOrderOfOperationException
	 */
	public function replicate(?array $except = null): Photo
	{
		$duplicate = parent::replicate($except);
		// A photo has the following relations: (parent) album, owner and
		// size_variants.
		// While the duplicate may keep the relation to the same album and
		// each photo requires an individual set of size variants.
		// Se we unset the relation and explicitly duplicate the size variants.
		$duplicate->unsetRelation('size_variants');
		// save duplicate so that the photo gets an ID
		$duplicate->save();

		$areSizeVariantsOriginallyLoaded = $this->relationLoaded('size_variants');
		// Duplicate the size variants of this instance for the duplicate
		$duplicatedSizeVariants = $this->size_variants->replicate($duplicate);
		if ($areSizeVariantsOriginallyLoaded) {
			$duplicate->setRelation('size_variants', $duplicatedSizeVariants);
		}

		return $duplicate;
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
