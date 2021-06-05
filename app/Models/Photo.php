<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Models;

use App\Casts\DateTimeWithTimezoneCast;
use App\Casts\MustNotSetCast;
use App\Facades\AccessControl;
use App\Facades\Helpers;
use App\Models\Extensions\HasAttributesPatch;
use App\Models\Extensions\PhotoBooleans;
use App\Models\Extensions\PhotoCast;
use App\Models\Extensions\SizeVariants;
use App\Models\Extensions\UTCBasedTimes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * App\Photo.
 *
 * @property int          $id
 * @property string       $title
 * @property string|null  $description
 * @property string       $filename
 * @property string       $short_path
 * @property string       $full_path
 * @property string       $url
 * @property string       $tags
 * @property bool         $public
 * @property int          $owner_id
 * @property string       $type
 * @property int          $width
 * @property int          $height
 * @property int          $filesize
 * @property string       $iso
 * @property string       $aperture
 * @property string       $make
 * @property string       $model
 * @property string       $lens
 * @property string       $shutter
 * @property string       $focal
 * @property float|null   $latitude
 * @property float|null   $longitude
 * @property float|null   $altitude
 * @property float|null   $imgDirection
 * @property string|null  $location
 * @property Carbon|null  $taken_at
 * @property string|null  $taken_at_orig_tz
 * @property bool         $star
 * @property string       $thumb_filename
 * @property string|null  $live_photo_filename
 * @property string|null  $live_photo_short_path
 * @property string|null  $live_photo_full_path
 * @property string|null  $live_photo_url
 * @property int|null     $album_id
 * @property string       $checksum
 * @property string       $license
 * @property Carbon       $created_at
 * @property Carbon       $updated_at
 * @property int|null     $medium_width
 * @property int|null     $medium_height
 * @property int|null     $medium2x_width
 * @property int|null     $medium2x_height
 * @property int|null     $small_width
 * @property int|null     $small_height
 * @property int|null     $small2x_width
 * @property int|null     $small2x_height
 * @property bool         $thumb2x
 * @property string|null  $live_photo_content_id
 * @property string|null  $live_photo_checksum
 * @property Album|null   $album
 * @property User         $owner
 * @property SizeVariants $size_variants
 * @property bool         $downloadable
 * @property bool         $share_button_visible
 *
 * @method static Builder|Photo ownedBy($id)
 * @method static Builder|Photo public()
 * @method static Builder|Photo recent()
 * @method static Builder|Photo stars()
 * @method static Builder|Photo unsorted()
 * @method static Builder|Photo whereAlbumId($value)
 * @method static Builder|Photo whereAltitude($value)
 * @method static Builder|Photo whereAperture($value)
 * @method static Builder|Photo whereChecksum($value)
 * @method static Builder|Photo whereCreatedAt($value)
 * @method static Builder|Photo whereDescription($value)
 * @method static Builder|Photo whereFocal($value)
 * @method static Builder|Photo whereHeight($value)
 * @method static Builder|Photo whereId($value)
 * @method static Builder|Photo whereImgDirection($value)
 * @method static Builder|Photo whereLocation($value)
 * @method static Builder|Photo whereIso($value)
 * @method static Builder|Photo whereLatitude($value)
 * @method static Builder|Photo whereLens($value)
 * @method static Builder|Photo whereLicense($value)
 * @method static Builder|Photo whereLivePhotoChecksum($value)
 * @method static Builder|Photo whereLivePhotoContentID($value)
 * @method static Builder|Photo whereLivePhotoUrl($value)
 * @method static Builder|Photo whereLongitude($value)
 * @method static Builder|Photo whereMake($value)
 * @method static Builder|Photo whereMedium($value)
 * @method static Builder|Photo whereMedium2x($value)
 * @method static Builder|Photo whereModel($value)
 * @method static Builder|Photo whereOwnerId($value)
 * @method static Builder|Photo wherePublic($value)
 * @method static Builder|Photo whereShutter($value)
 * @method static Builder|Photo whereSize($value)
 * @method static Builder|Photo whereSmall($value)
 * @method static Builder|Photo whereSmall2x($value)
 * @method static Builder|Photo whereStar($value)
 * @method static Builder|Photo whereTags($value)
 * @method static Builder|Photo whereTakenAt($value)
 * @method static Builder|Photo whereThumb2x($value)
 * @method static Builder|Photo whereThumbFilename($value)
 * @method static Builder|Photo whereTitle($value)
 * @method static Builder|Photo whereType($value)
 * @method static Builder|Photo whereUpdatedAt($value)
 * @method static Builder|Photo whereFilename($value)
 * @method static Builder|Photo whereWidth($value)
 */
class Photo extends Model
{
	use PhotoBooleans;
	use PhotoCast;
	use UTCBasedTimes;
	use HasAttributesPatch;

	protected $casts = [
		'created_at' => 'datetime',
		'updated_at' => 'datetime',
		'taken_at' => DateTimeWithTimezoneCast::class,
		'size_variants' => MustNotSetCast::class,
		'short_path' => MustNotSetCast::class . ':filename',
		'full_path' => MustNotSetCast::class . ':filename',
		'url' => MustNotSetCast::class . ':filename',
		'live_photo_short_path' => MustNotSetCast::class . ':live_photo_filename',
		'live_photo_full_path' => MustNotSetCast::class . ':live_photo_filename',
		'live_photo_url' => MustNotSetCast::class . ':live_photo_filename',
		'downloadable' => MustNotSetCast::class,
		'share_button_visible' => MustNotSetCast::class,
	];

	/**
	 * @var string[] The list of attributes which exist as columns of the DB
	 *               relation but shall not be serialized to JSON
	 */
	protected $hidden = [
		'album',  // do not serialize relation in order to avoid infinite loops
		'owner',  // do not serialize relation
		'filename',  // serialize url instead
		'thumb_filename',  // serialized as part of size_variants
		'thumb2x',  // serialized as part of size_variants
		'small_width',  // serialized as part of size_variants
		'small_height',  // serialized as part of size_variants
		'small2x_width',  // serialized as part of size_variants
		'small2x_height',  // serialized as part of size_variants
		'medium_width',  // serialized as part of size_variants
		'medium_height',  // serialized as part of size_variants
		'medium2x_width',  // serialized as part of size_variants
		'medium2x_height',  // serialized as part of size_variants
		'live_photo_filename', // serialize live_photo_url instead
	];

	/**
	 * @var string[] The list of "virtual" attributes which do not exist as
	 *               columns of the DB relation but which shall be appended to
	 *               JSON from accessors
	 */
	protected $appends = [
		'url',
		'live_photo_url',
		'size_variants',
		'downloadable',
		'share_button_visible',
	];

	/**
	 * @var string[] The list of relations which should be loaded eagerly
	 */
	protected $with = [
		'album',  // the album is required anyway for access control checks
		'owner',  // same reason as for album
	];

	/**
	 * @var SizeVariants|null caches the size variants associated to this class, once they have been created by {@link getSizeVariantsAttribute()}
	 */
	protected ?SizeVariants $sizeVariants = null;

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

	/**
	 * Before calling the delete() method which will remove the entry from the database, we need to remove the files.
	 *
	 * @param bool $keep_original
	 *
	 * @return bool True on success, false otherwise
	 */
	public function predelete(bool $keep_original = false): bool
	{
		if ($this->isDuplicate($this->checksum, $this->id)) {
			Logs::notice(__METHOD__, __LINE__, $this->id . ' is a duplicate!');
			// it is a duplicate, we do not delete!
			return true;
		}

		$success = true;

		// Delete original file
		if ($keep_original === false) {
			// quick check...
			if (!Storage::exists($this->short_path)) {
				Logs::error(__METHOD__, __LINE__, 'Could not find file ' . $this->full_path);
				$success = false;
			} elseif (!Storage::delete($this->short_path)) {
				Logs::error(__METHOD__, __LINE__, 'Could not delete file ' . $this->full_path);
				$success = false;
			}
		}

		// Delete Live Photo Video file
		// check first if live_photo_filename is available
		if ($this->live_photo_filename !== null) {
			if (!Storage::exists($this->live_photo_short_path)) {
				Logs::error(__METHOD__, __LINE__, 'Could not find file ' . $this->live_photo_full_path);
				$success = false;
			} elseif (!Storage::delete($this->live_photo_short_path)) {
				Logs::error(__METHOD__, __LINE__, 'Could not delete file ' . $this->live_photo_full_path);
				$success = false;
			}
		}

		$success &= $this->size_variants->deleteFromStorage();

		return $success;
	}

	/**
	 * @param $query
	 *
	 * @return mixed
	 */
	public static function set_order(Builder $query)
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
	 * @param $query
	 *
	 * @return mixed
	 */
	public function scopeStars($query)
	{
		return $query->where('star', '=', 1);
	}

	/**
	 * @param $query
	 *
	 * @return mixed
	 */
	public function scopePublic($query)
	{
		return $query->where('public', '=', 1);
	}

	/**
	 * @param $query
	 *
	 * @return mixed
	 */
	public function scopeRecent($query)
	{
		return $query->where('created_at', '>=', Carbon::now()->subDays(intval(Configs::get_value('recent_age', '1')))->toDateTimeString());
	}

	/**
	 * @param $query
	 *
	 * @return mixed
	 */
	public function scopeUnsorted($query)
	{
		return $query->where('album_id', '=', null);
	}

	/**
	 * @param $query
	 * @param $id
	 *
	 * @return mixed
	 */
	public function scopeOwnedBy(Builder $query, $id)
	{
		return $id == 0 ? $query : $query->where('owner_id', '=', $id);
	}

	public function withTags($tags)
	{
		$sql = $this;
		foreach ($tags as $tag) {
			$sql = $sql->where('tags', 'like', '%' . $tag . '%');
		}

		return ($sql->count() == 0) ? false : $sql->first();
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
	 * @param string $shutter the value from the database passed in by
	 *                        the Eloquent framework
	 *
	 * @return string A properly formatted shutter value
	 */
	protected function getShutterAttribute(string $shutter): string
	{
		// shutter speed needs to be processed. It is stored as a string `a/b s`
		if ($shutter != '' && substr($shutter, 0, 2) != '1/') {
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
	 * @param string $focal the value from the database passed in by the
	 *                      Eloquent framework
	 *
	 * @return string
	 */
	protected function getFocalAttribute(string $focal): string
	{
		// We need to format the framerate (stored as focal) -> max 2 decimal digits
		return $this->isVideo() ? round($focal, 2) : $focal;
	}

	/**
	 * Accessor for the "virtual" attribute {@link Photo::$short_path}.
	 *
	 * The virtual attribute `short_path` is the relative path of the original
	 * file as it needs to be input into methods of
	 * {@link \Illuminate\Support\Facades\Storage}.
	 * It depends on the property {@link Photo::$filename} and is prepended by
	 * either `raw` or `big`.
	 *
	 * @return string the short path of the file
	 */
	protected function getShortPathAttribute(): string
	{
		$path_prefix = $this->type == 'raw' ? 'raw/' : 'big/';

		return $path_prefix . $this->filename;
	}

	/**
	 * Accessor for the "virtual" attribute {@link Photo::$full_path}.
	 *
	 * Returns the full path of the original file as it needs to be input into
	 * some low-level PHP functions like `unlink`.
	 * This is a convenient method and wraps {@link Photo::$short_path} into
	 * {@link \Illuminate\Support\Facades\Storage::path()}.
	 *
	 * @return string the full path of the file
	 */
	protected function getFullPathAttribute(): string
	{
		return Storage::path($this->short_path);
	}

	/**
	 * Accessor for the "virtual" attribute {@link Photo::$url}.
	 *
	 * Returns the URL of the original file as it is seen from a client's
	 * point of view.
	 * This is a convenient method and wraps {@link Photo::$short_path} into
	 * {@link \Illuminate\Support\Facades\Storage::url()}.
	 *
	 * @return string the url of the file
	 */
	protected function getUrlAttribute(): string
	{
		return Storage::url($this->short_path);
	}

	/**
	 * Accessor for the "virtual" attribute {@see Photo::$live_photo_short_path}.
	 *
	 * The virtual attribute `live_photo_short_path` is the relative path of
	 * the live photo file as it needs to be input into methods of
	 * {@link \Illuminate\Support\Facades\Storage}.
	 * It depends on the property {@link Photo::$live_photo_filename} and is
	 * prepended by `big`.
	 *
	 * @return string|null The short path of the live photo
	 */
	protected function getLivePhotoShortPathAttribute(): ?string
	{
		return empty($this->live_photo_filename) ? null : 'big/' . $this->live_photo_filename;
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
		return empty($this->live_photo_filename) ? null : Storage::path($this->live_photo_short_path);
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
		return empty($this->live_photo_filename) ? null : Storage::url($this->live_photo_short_path);
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
	 * @param bool $value the value from the database passed in by
	 *                    the Eloquent framework
	 *
	 * @return bool true if the photo is publicly visible
	 */
	protected function getPublicAttribute(bool $value): bool
	{
		return $value || ($this->album_id != null && $this->album->is_public());
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
	 * This method does some post-processing on the result of the
	 * standard implementation:
	 *
	 *  - If the image must not be downloaded at full resolution and a medium
	 *    sized variant exist, then url to the original size is removed.
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
			unset($result['url']);
		}

		return $result;
	}
}
