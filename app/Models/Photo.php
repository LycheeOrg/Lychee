<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Models;

use App\Casts\DateTimeWithTimezoneCast;
use App\Casts\MustNotSetCast;
use App\Facades\AccessControl;
use App\Facades\Helpers;
use App\Models\Extensions\HasAttributesPatch;
use App\Models\Extensions\HasTimeBasedID;
use App\Models\Extensions\PhotoBooleans;
use App\Models\Extensions\PhotoCast;
use App\Models\Extensions\SizeVariants;
use App\Models\Extensions\UTCBasedTimes;
use App\Observers\PhotoObserver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * App\Photo.
 *
 * @property int          $id
 * @property string       $title
 * @property string|null  $description
 * @property string       $tags
 * @property int          $public
 * @property int          $owner_id
 * @property string|null  $type
 * @property int          $filesize
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
 * @property float|null   $imgDirection
 * @property string|null  $location
 * @property Carbon|null  $taken_at
 * @property string|null  $taken_at_orig_tz
 * @property bool         $star
 * @property string|null  $live_photo_short_path
 * @property string|null  $live_photo_full_path
 * @property string|null  $live_photo_url
 * @property int|null     $album_id
 * @property string       $checksum
 * @property string       $license
 * @property Carbon       $created_at
 * @property Carbon       $updated_at
 * @property string|null  $live_photo_content_id
 * @property string|null  $live_photo_checksum
 * @property Album|null   $album
 * @property User         $owner
 * @property SizeVariants $size_variants
 * @property Collection   $size_variants_raw
 * @property bool         $downloadable
 * @property bool         $share_button_visible
 *
 * @method static Builder ownedBy($id)
 * @method static Builder public()
 * @method static Builder recent()
 * @method static Builder stars()
 * @method static Builder unsorted()
 * @method static Builder whereAlbumId($value)
 * @method static Builder whereAltitude($value)
 * @method static Builder whereAperture($value)
 * @method static Builder whereChecksum($value)
 * @method static Builder whereCreatedAt($value)
 * @method static Builder whereDescription($value)
 * @method static Builder whereFocal($value)
 * @method static Builder whereId($value)
 * @method static Builder whereImgDirection($value)
 * @method static Builder whereLocation($value)
 * @method static Builder whereIso($value)
 * @method static Builder whereLatitude($value)
 * @method static Builder whereLens($value)
 * @method static Builder whereLicense($value)
 * @method static Builder whereLivePhotoChecksum($value)
 * @method static Builder whereLivePhotoContentID($value)
 * @method static Builder whereLivePhotoShortPath($value)
 * @method static Builder whereLongitude($value)
 * @method static Builder whereMake($value)
 * @method static Builder whereModel($value)
 * @method static Builder whereOwnerId($value)
 * @method static Builder wherePublic($value)
 * @method static Builder whereShutter($value)
 * @method static Builder whereFilesize($value)
 * @method static Builder whereStar($value)
 * @method static Builder whereTags($value)
 * @method static Builder whereTakenAt($value)
 * @method static Builder whereTitle($value)
 * @method static Builder whereType($value)
 * @method static Builder whereUpdatedAt($value)
 */
class Photo extends Model
{
	use PhotoBooleans;
	use PhotoCast;
	use UTCBasedTimes;
	use HasAttributesPatch;
	use HasTimeBasedID;

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
		'size_variants' => MustNotSetCast::class,
		'short_path' => MustNotSetCast::class . ':filename',
		'full_path' => MustNotSetCast::class . ':filename',
		'url' => MustNotSetCast::class . ':filename',
		'live_photo_full_path' => MustNotSetCast::class . ':live_photo_short_path',
		'live_photo_url' => MustNotSetCast::class . ':live_photo_short_path',
		'downloadable' => MustNotSetCast::class,
		'share_button_visible' => MustNotSetCast::class,
		// the following casts should normally not be necessary
		// but some code (where?, probably in the area of albums) assigns
		// string/integer values to these attributes
		// (instead of integer/booleans)
		// Consequently, the PHPunit tests fail, because the tests - for
		// example - check for `owner_id === 42` but gets `owner_id="42"` and
		// then the test fails.
		// Here we enforce correct types during JSON serialization.
		// TODO: Find the code which actually assigns values of wrong type and fix the error at its root
		'id' => 'integer',
		'owner_id' => 'integer',
		'star' => 'boolean',
		'filesize' => 'integer',
	];

	/**
	 * @var string[] The list of attributes which exist as columns of the DB
	 *               relation but shall not be serialized to JSON
	 */
	protected $hidden = [
		'album',  // do not serialize relation in order to avoid infinite loops
		'owner',  // do not serialize relation
		'size_variants_raw', // do not serialize collections of size variants, but the wrapper object
		'live_photo_short_path', // serialize live_photo_url instead
	];

	/**
	 * @var string[] The list of "virtual" attributes which do not exist as
	 *               columns of the DB relation but which shall be appended to
	 *               JSON from accessors
	 */
	protected $appends = [
		'live_photo_url',
		'size_variants',
		'downloadable',
		'share_button_visible',
	];

	/**
	 * @var string[] The list of relations which should be loaded eagerly
	 */
	//protected $with = ['size_variants_raw'];

	protected $attributes = [
		'tags' => '',
	];

	/**
	 * @var SizeVariants|null caches the size variants associated to this class, once they have been created by {@link getSizeVariantsAttribute()}
	 */
	protected ?SizeVariants $sizeVariants = null;

	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);
		$this->registerObserver(PhotoObserver::class);
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

	public function size_variants_raw(): HasMany
	{
		return $this->hasMany(SizeVariant::class);
	}

	/**
	 * @param Builder $query
	 *
	 * @return Builder
	 */
	public static function set_order(Builder $query): Builder
	{
		$sortingCol = Configs::get_value('sorting_Photos_col');
		if ($sortingCol !== 'title' && $sortingCol !== 'description') {
			$query = $query->orderBy($sortingCol, Configs::get_value('sorting_Photos_order'));
		}

		return $query->orderBy('photos.id', 'ASC');
	}

	/**
	 * Define scopes which we can directly use e.g. Photo::stars()->all().
	 */

	/**
	 * @param Builder $query
	 *
	 * @return Builder
	 */
	public function scopeStars(Builder $query): Builder
	{
		return $query->where('star', '=', true);
	}

	/**
	 * @param Builder $query
	 *
	 * @return Builder
	 */
	public function scopePublic(Builder $query): Builder
	{
		return $query->where('public', '=', true);
	}

	/**
	 * @param Builder $query
	 *
	 * @return Builder
	 */
	public function scopeRecent(Builder $query): Builder
	{
		return $query->where('created_at', '>=', $this->fromDateTime(
			Carbon::now()->subDays(intval(Configs::get_value('recent_age', '1')))
		));
	}

	/**
	 * @param Builder $query
	 *
	 * @return Builder
	 */
	public function scopeUnsorted(Builder $query): Builder
	{
		return $query->whereNull('album_id');
	}

	/**
	 * @param Builder $query
	 * @param int     $ownerID
	 *
	 * @return Builder
	 */
	public function scopeOwnedBy(Builder $query, int $ownerID): Builder
	{
		return $ownerID == 0 ? $query : $query->where('owner_id', '=', $ownerID);
	}

	/**
	 * Accessor for the virtual attribute `size_variants`.
	 *
	 * @return SizeVariants
	 */
	protected function getSizeVariantsAttribute(): SizeVariants
	{
		if ($this->sizeVariants === null) {
			$this->sizeVariants = new SizeVariants($this);
		}

		return $this->sizeVariants;
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
	 * @return string A properly formatted shutter value
	 */
	protected function getShutterAttribute(?string $shutter): string
	{
		if (empty($shutter)) {
			return '';
		}
		// shutter speed needs to be processed. It is stored as a string `a/b s`
		if (substr($shutter, 0, 2) != '1/') {
			preg_match('/(\d+)\/(\d+) s/', $shutter, $matches);
			if ($matches) {
				$a = intval($matches[1]);
				$b = intval($matches[2]);
				if ($b != 0) {
					try {
						$gcd = Helpers::gcd($a, $b);
						$a = $a / $gcd;
						$b = $b / $gcd;
					} catch (Exception $e) {
						// this should not happen as we covered the case $b = 0;
					}
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
			return $this->album->get_license();
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
	 * @return string
	 */
	protected function getFocalAttribute(?string $focal): string
	{
		if (empty($focal)) {
			return '';
		}
		// We need to format the framerate (stored as focal) -> max 2 decimal digits
		return $this->isVideo() ? round($focal, 2) : $focal;
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
	 * @return string the url of the file
	 */
	protected function getLivePhotoUrlAttribute(): ?string
	{
		return empty($this->live_photo_short_path) ? null : Storage::url($this->live_photo_short_path);
	}

	/**
	 * Accessor for the "virtual" attribute {@see Photo::$downloadable}.
	 *
	 * The photo is downloadable if the currently authenticated user is the
	 * owner or if the photo is part of a downloadable album or if it
	 * unsorted and unsorted photos are configured to be downloadable by
	 * default.
	 *
	 * @return bool true if the photo is downloadable
	 */
	protected function getDownloadableAttribute(): bool
	{
		$default = (bool) Configs::get_value('downloadable', '0');

		return AccessControl::is_current_user($this->owner_id) ||
			($this->album_id != null && $this->album->is_downloadable()) ||
			($this->album_id == null && $default);
	}

	/**
	 * Accessor for the attribute {@see Photo::$public}.
	 *
	 * An photo is public if it is publicly visible on its own right or
	 * because it is part of a public album.
	 *
	 * @param bool|null $value the value from the database passed in by
	 *                         the Eloquent framework
	 *
	 * @return int the visibility of the photo:
	 *             a) equals 0 if the photo is not publicly visible,
	 *             b) equals 1 if the photo is publicly visible on its own right
	 *             c) equals 2 if the photo is publicly visible because it is
	 *             part of a public album
	 */
	protected function getPublicAttribute(?bool $value): int
	{
		if ($this->album_id != null && $this->album->is_public()) {
			return 2;
		}

		return intval($value);
	}

	/**
	 * Accessor for the "virtual" attribute {@see Photo::$share_button_visible}.
	 *
	 * The share button is visible if the currently authenticated user is the
	 * owner or if the photo is part of a an album which has enabled the
	 * share button or if the photo is unsorted and unsorted photos are
	 * configured to be sharable by default.
	 *
	 * @return bool true if the share button is visible for this photo
	 */
	protected function getShareButtonVisibleAttribute(): bool
	{
		$default = (bool) Configs::get_value('share_button_visible', '0');

		return AccessControl::is_current_user($this->owner_id) ||
			($this->album_id != null && $this->album->is_share_button_visible()) ||
			($this->album_id == null && $default);
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
	 */
	public function toArray(): array
	{
		$result = parent::toArray();
		// Downgrades the accessible resolution of a photo
		// The decision logic here is a merge of three formerly independent
		// (and slightly different) approaches
		if (
			!AccessControl::is_current_user($this->owner_id) &&
			$this->isVideo() === false &&
			($result['size_variants']['medium2x'] !== null || $result['size_variants']['medium'] !== null) &&
			(
				($this->album_id != null && !$this->album->is_full_photo_visible()) ||
				($this->album_id == null && Configs::get_value('full_photo', '1') != '1')
			)
		) {
			unset($result['size_variants']['original']['url']);
		}

		return $result;
	}

	/**
	 * @return bool true if another DB entry exists for the same photo
	 */
	public function hasDuplicate(): bool
	{
		$checksum = $this->checksum;

		return self::query()
			->where(function ($q) use ($checksum) {
				$q->where('checksum', '=', $checksum)
					->orWhere('live_photo_checksum', '=', $checksum);
			})
			->where('id', '<>', $this->id)
			->exists();
	}

	public function replicate(array $except = null): Photo
	{
		$duplicate = parent::replicate($except);
		// save duplicate so that is gets an ID
		$duplicate->push();
		/** @var SizeVariant $sizeVariant */
		foreach ($this->size_variants_raw as $sizeVariant) {
			/** @var SizeVariant $dupSizeVariant */
			$dupSizeVariant = $duplicate->size_variants_raw()->make();
			$dupSizeVariant->size_variant = $sizeVariant->size_variant;
			$dupSizeVariant->short_path = $sizeVariant->short_path;
			$dupSizeVariant->width = $sizeVariant->width;
			$dupSizeVariant->height = $sizeVariant->height;
			if (!$dupSizeVariant->save()) {
				throw new \RuntimeException('could not persist size variant');
			}
		}
		$duplicate->refresh();

		return $duplicate;
	}
}
