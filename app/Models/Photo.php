<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Models;

use App\Casts\DateTimeWithTimezoneCast;
use App\Facades\Helpers;
use App\Facades\AccessControl;
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
 * @method static Builder|Photo whereUrl($value)
 * @method static Builder|Photo whereWidth($value)
 */
class Photo extends Model
{
	use PhotoBooleans;
	use PhotoCast;
	use UTCBasedTimes;

	protected $casts = [
		'created_at' => 'datetime',
		'updated_at' => 'datetime',
		'taken_at' => DateTimeWithTimezoneCast::class,
	];

	/**
	 * @var string[] The list of attributes which exist as columns of the DB
	 *               relation but shall not be serialized to JSON
	 */
	protected $hidden = [
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
	 *               columns of the DB relation but shall be appended to JSON
	 *               from accessors
	 */
	protected $appends = [
		'url',            // see getUrlAttribute()
		'live_photo_url', // see getLivePhotoUrlAttribute()
		'size_variants',  // see getSizeVariantsAttribute()
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
			if (!Storage::exists($this->url)) {
				Logs::error(__METHOD__, __LINE__, 'Could not find file ' . Storage::path($this->url));
				$success = false;
			} elseif (!Storage::delete($this->url)) {
				Logs::error(__METHOD__, __LINE__, 'Could not delete file ' . Storage::path($this->url));
				$success = false;
			}
		}

		// Delete Live Photo Video file
		// TODO: USE STORAGE FOR DELETE
		// check first if live_photo_filename is available
		if ($this->live_photo_filename !== null) {
			if (!Storage::exists($this->live_photo_url)) {
				Logs::error(__METHOD__, __LINE__, 'Could not find file ' . Storage::path($this->live_photo_url));
				$success = false;
			} elseif (!Storage::delete($this->live_photo_url)) {
				Logs::error(__METHOD__, __LINE__, 'Could not delete file ' . Storage::path($this->live_photo_url));
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
	 * Mutator for virtual attribute `size_variants`.
	 *
	 * Always throws an exception as a safety measurement in case someone
	 * tries to set `size_variants` directly.
	 *
	 * @param SizeVariants $sizeVariants the new size variants
	 */
	protected function setSizeVariantsAttribute(SizeVariants $sizeVariants): void
	{
		throw new \BadMethodCallException('must not set size variants directly, instead use underlying attributes of relation directly');
	}

	/**
	 * Accessor for attribute `shutter`.
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
	 * Accessor for the "virtual" attribute `url`.
	 *
	 * The virtual attribute `url` is the relative url of the original
	 * image/video and depends on the attribute `filename` prepended by
	 * either "raw" or "big".
	 *
	 * @return string The relative url
	 */
	protected function getUrlAttribute(): string
	{
		$path_prefix = $this->type == 'raw' ? 'raw/' : 'big/';

		return $path_prefix . $this->filename;
	}

	/**
	 * Mutator for the attribute `url`.
	 *
	 * Always throws an exception as a safety measurement in case someone
	 * tries to set `url` directly.
	 *
	 * @param string $url
	 */
	protected function setUrlAttribute(string $url): void
	{
		throw new \BadMethodCallException('must not set \'url\' directly, use \'filename\' instead');
	}

	/**
	 * Accessor for the "virtual" attribute `live_photo_url`.
	 *
	 * The virtual attribute `live_photo_url` is the relative url of the live
	 * photo and equals the attribute `live_photo_filename` prepended by
	 * big".
	 *
	 * @return string|null The relative url
	 */
	protected function getLivePhotoUrlAttribute(): ?string
	{
		return empty($this->live_photo_filename) ? null : 'big/' . $this->live_photo_filename;
	}

	/**
	 * Mutator for the attribute `url`.
	 *
	 * Always throws an exception as a safety measurement in case someone
	 * tries to set `url` directly.
	 *
	 * @param string|null $url
	 */
	protected function setLivePhotoUrlAttribute(?string $url): void
	{
		throw new \BadMethodCallException('must not set \'live_photo_url\' directly, use \'live_photo_filename\' instead');
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
