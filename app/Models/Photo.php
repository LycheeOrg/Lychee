<?php

namespace App\Models;

use App\Actions\Photo\Delete;
use App\Casts\ArrayCast;
use App\Casts\DateTimeWithTimezoneCast;
use App\Casts\MustNotSetCast;
use App\Contracts\HasRandomID;
use App\Exceptions\Internal\IllegalOrderOfOperationException;
use App\Exceptions\Internal\ZeroModuloException;
use App\Exceptions\MediaFileOperationException;
use App\Exceptions\ModelDBException;
use App\Facades\AccessControl;
use App\Facades\Helpers;
use App\Image\MediaFile;
use App\Models\Extensions\HasAttributesPatch;
use App\Models\Extensions\HasBidirectionalRelationships;
use App\Models\Extensions\HasRandomIDAndLegacyTimeBasedID;
use App\Models\Extensions\SizeVariants;
use App\Models\Extensions\ThrowsConsistentExceptions;
use App\Models\Extensions\UseFixedQueryBuilder;
use App\Models\Extensions\UTCBasedTimes;
use App\Relations\HasManySizeVariants;
use App\Relations\LinkedPhotoCollection;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

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
 * @property bool         $is_downloadable
 * @property bool         $is_share_button_visible
 */
class Photo extends Model implements HasRandomID
{
	use UTCBasedTimes;
	use HasAttributesPatch;
	use HasRandomIDAndLegacyTimeBasedID;
	use ThrowsConsistentExceptions {
		ThrowsConsistentExceptions::delete as private internalDelete;
	}
	use HasBidirectionalRelationships;
	use UseFixedQueryBuilder;

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
		HasRandomID::LEGACY_ID_NAME => HasRandomID::LEGACY_ID_TYPE,
		'created_at' => 'datetime',
		'updated_at' => 'datetime',
		'taken_at' => DateTimeWithTimezoneCast::class,
		'live_photo_full_path' => MustNotSetCast::class . ':live_photo_short_path',
		'live_photo_url' => MustNotSetCast::class . ':live_photo_short_path',
		'is_downloadable' => MustNotSetCast::class,
		'is_share_button_visible' => MustNotSetCast::class,
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
	 * @var string[] The list of attributes which exist as columns of the DB
	 *               relation but shall not be serialized to JSON
	 */
	protected $hidden = [
		HasRandomID::LEGACY_ID_NAME,
		'album',  // do not serialize relation in order to avoid infinite loops
		'owner',  // do not serialize relation
		'owner_id',
		'live_photo_short_path', // serialize live_photo_url instead
	];

	/**
	 * @var string[] The list of "virtual" attributes which do not exist as
	 *               columns of the DB relation but which shall be appended to
	 *               JSON from accessors
	 */
	protected $appends = [
		'live_photo_url',
		'is_downloadable',
		'is_share_button_visible',
	];

	/**
	 * Creates a new instance of {@link LinkedPhotoCollection}.
	 *
	 * The only difference between an ordinary {@link Collection} and a
	 * {@link LinkedPhotoCollection} is that the latter also adds links to
	 * the previous and next photo if the collection is serialized to JSON.
	 * This method is called by all relations which need to create a
	 * collection of photos.
	 *
	 * @param array $models a list of {@link Photo} models
	 *
	 * @return LinkedPhotoCollection
	 */
	public function newCollection(array $models = []): LinkedPhotoCollection
	{
		return new LinkedPhotoCollection($models);
	}

	/**
	 * Return the relationship between a Photo and its Album.
	 *
	 * @return BelongsTo
	 */
	public function album(): BelongsTo
	{
		return $this->belongsTo('App\Models\Album', 'album_id', 'id');
	}

	/**
	 * Return the relationship between a Photo and its Owner.
	 *
	 * @return BelongsTo
	 */
	public function owner(): BelongsTo
	{
		return $this->belongsTo('App\Models\User', 'owner_id', 'id');
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
	 * @param ?string $shutter the value from the database passed in by
	 *                         the Eloquent framework
	 *
	 * @return ?string A properly formatted shutter value
	 */
	protected function getShutterAttribute(?string $shutter): ?string
	{
		try {
			if (empty($shutter)) {
				return null;
			}
			// shutter speed needs to be processed. It is stored as a string `a/b s`
			if (!str_starts_with($shutter, '1/')) {
				preg_match('/(\d+)\/(\d+) s/', $shutter, $matches);
				if ($matches) {
					$a = intval($matches[1]);
					$b = intval($matches[2]);
					if ($b != 0) {
						$gcd = Helpers::gcd($a, $b);
						$a = $a / $gcd;
						$b = $b / $gcd;
						if ($a == 1) {
							$shutter = '1/' . $b . ' s';
						} else {
							$shutter = ($a / $b) . ' s';
						}
					}
				}
			}

			if ($shutter == '1/1 s') {
				$shutter = '1 s';
			}

			return $shutter;
		} catch (ZeroModuloException $e) {
			// this should not happen as we covered the case $b = 0;
			assert(false, new \AssertionError('Unexpected ZeroModuloException', $e->getCode(), $e));
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
	 * @param string $license the value from the database passed in by
	 *                        the Eloquent framework
	 *
	 * @return string
	 */
	protected function getLicenseAttribute(string $license): string
	{
		if ($license !== 'none') {
			return $license;
		}
		if ($this->album_id != null) {
			return $this->album->license;
		}

		return Configs::get_value('default_license');
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
	 * @param ?string $focal the value from the database passed in by the
	 *                       Eloquent framework
	 *
	 * @return ?string
	 *
	 * @throws IllegalOrderOfOperationException
	 */
	protected function getFocalAttribute(?string $focal): ?string
	{
		if (empty($focal)) {
			return null;
		}
		// We need to format the framerate (stored as focal) -> max 2 decimal digits
		return $this->isVideo() ? (string) round($focal, 2) : $focal;
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
		return empty($this->live_photo_short_path) ? null : Storage::path($this->live_photo_short_path);
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
		return empty($this->live_photo_short_path) ? null : Storage::url($this->live_photo_short_path);
	}

	/**
	 * Accessor for the "virtual" attribute {@see Photo::$is_downloadable}.
	 *
	 * The photo is downloadable if the currently authenticated user is the
	 * owner or if the photo is part of a downloadable album or if it is
	 * unsorted and unsorted photos are configured to be downloadable by
	 * default.
	 *
	 * @return bool true if the photo is downloadable
	 */
	protected function getIsDownloadableAttribute(): bool
	{
		return AccessControl::is_current_user_or_admin($this->owner_id) ||
			($this->album_id != null && $this->album->is_downloadable) ||
			($this->album_id == null && Configs::get_value('downloadable', '0'));
	}

	/**
	 * Accessor for the "virtual" attribute {@see Photo::$is_share_button_visible}.
	 *
	 * The share button is visible if the currently authenticated user is the
	 * owner or if the photo is part of an album which has enabled the
	 * share button or if the photo is unsorted and unsorted photos are
	 * configured to be sharable by default.
	 *
	 * @return bool true if the share button is visible for this photo
	 */
	protected function getIsShareButtonVisibleAttribute(): bool
	{
		$default = (bool) Configs::get_value('share_button_visible', '0');

		return AccessControl::is_current_user_or_admin($this->owner_id) ||
			($this->album_id != null && $this->album->is_share_button_visible) ||
			($this->album_id == null && $default);
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
		if (empty($this->type)) {
			throw new IllegalOrderOfOperationException('Photo::isPhoto() must not be called before Photo::$type has been set');
		}

		return MediaFile::isSupportedImageMimeType($this->type);
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
		if (empty($this->type)) {
			throw new IllegalOrderOfOperationException('Photo::isVideo() must not be called before Photo::$type has been set');
		}

		return MediaFile::isSupportedVideoMimeType($this->type);
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
	 * Serializes the model into an array.
	 *
	 * This method is also invoked by Eloquent when someone invokes
	 * {@link Model::toJson()} or {@link Model::jsonSerialize()}.
	 *
	 * This method removes the URL to the full resolution of a photo, if the
	 * client is not allowed to see that.
	 *
	 * @return array
	 *
	 * @throws IllegalOrderOfOperationException
	 */
	public function toArray(): array
	{
		$result = parent::toArray();

		// Modify the attribute `public`
		// The current front-end implementation does not expect a boolean
		// but a tri-state integer acc. to the following interpretation
		//  - 0 => the photo is not publicly visible
		//  - 1 => the photo is publicly visible on its own right
		//  - 2 => the photo is publicly visible because its album is public
		if ($this->album_id != null && $this->album->is_public) {
			$result['is_public'] = 2;
		} else {
			$result['is_public'] = $result['is_public'] ? 1 : 0;
		}

		// Downgrades the accessible resolution of a photo
		// The decision logic here is a merge of three formerly independent
		// (and slightly different) approaches
		if (
			!AccessControl::is_current_user_or_admin($this->owner_id) &&
			$this->isVideo() === false &&
			($result['size_variants']['medium2x'] !== null || $result['size_variants']['medium'] !== null) &&
			(
				($this->album_id != null && !$this->album->grants_full_photo) ||
				($this->album_id == null && Configs::get_value('full_photo', '1') != '1')
			)
		) {
			unset($result['size_variants']['original']['url']);
		}

		return $result;
	}

	/**
	 * @throws ModelDBException
	 * @throws IllegalOrderOfOperationException
	 */
	public function replicate(array $except = null): Photo
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
