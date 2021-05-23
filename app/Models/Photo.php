<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Models;

use App\Casts\DateTimeWithTimezoneCast;
use App\Models\Extensions\PhotoBooleans;
use App\Models\Extensions\PhotoCast;
use App\Models\Extensions\PhotoGetters;
use Helpers;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Storage;

/**
 * App\Photo.
 *
 * @property int         $id
 * @property string      $title
 * @property string|null $description
 * @property string      $url
 * @property string      $tags
 * @property int         $public
 * @property int         $owner_id
 * @property string      $type
 * @property int|null    $width
 * @property int|null    $height
 * @property int         $filesize
 * @property string      $iso
 * @property string      $aperture
 * @property string      $make
 * @property string      $model
 * @property string      $lens
 * @property string      $shutter
 * @property string      $focal
 * @property float|null  $latitude
 * @property float|null  $longitude
 * @property float|null  $altitude
 * @property float|null  imgDirection
 * @property string|null location
 * @property Carbon|null $taken_at
 * @property string|null $taken_at_orig_tz
 * @property int         $star
 * @property string      $thumbUrl
 * @property string      $livePhotoUrl
 * @property int|null    $album_id
 * @property string      $checksum
 * @property string      $license
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 * @property int|null    $medium_width
 * @property int|null    $medium_height
 * @property int|null    $medium2x_width
 * @property int|null    $medium2x_height
 * @property int|null    $small_width
 * @property int|null    $small_height
 * @property int|null    $small2x_width
 * @property int|null    $small2x_height
 * @property int         $thumb2x
 * @property string      $livePhotoContentID
 * @property string      $livePhotoChecksum
 * @property Album|null  $album
 * @property User        $owner
 *
 * @method static Builder|Photo newModelQuery()
 * @method static Builder|Photo newQuery()
 * @method static Builder|Photo ownedBy($id)
 * @method static Builder|Photo public ()
 * @method static Builder|Photo query()
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
 * @method static Builder|Photo wherelivePhotoChecksum($value)
 * @method static Builder|Photo wherelivePhotoContentID($value)
 * @method static Builder|Photo wherelivePhotoUrl($value)
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
 * @method static Builder|Photo whereTakestamp($value)
 * @method static Builder|Photo whereThumb2x($value)
 * @method static Builder|Photo whereThumbUrl($value)
 * @method static Builder|Photo whereTitle($value)
 * @method static Builder|Photo whereType($value)
 * @method static Builder|Photo whereUpdatedAt($value)
 * @method static Builder|Photo whereUrl($value)
 * @method static Builder|Photo whereWidth($value)
 * @mixin Eloquent
 */
class Photo extends PatchedBaseModel
{
	use PhotoBooleans;
	use PhotoCast;
	use PhotoGetters;
	const THUMBNAIL_DIM = 200;
	const THUMBNAIL2X_DIM = 400;
	const VARIANT_THUMB = 'thumb';
	const VARIANT_THUMB2X = 'thumb2x';
	const VARIANT_SMALL = 'small';
	const VARIANT_SMALL2X = 'small2x';
	const VARIANT_MEDIUM = 'medium';
	const VARIANT_MEDIUM2X = 'medium2x';
	const VARIANT_ORIGINAL = 'original';

	/**
	 * Maps a size variant to the path prefix (directory) where the file for that size variant is stored.
	 * Use this array to avoid the anti-pattern "magic constants" throughout the whole code.
	 */
	const VARIANT_2_PATH_PREFIX = [
		self::VARIANT_THUMB => 'thumb',
		self::VARIANT_THUMB2X => 'thumb',
		self::VARIANT_SMALL => 'small',
		self::VARIANT_SMALL2X => 'small',
		self::VARIANT_MEDIUM => 'medium',
		self::VARIANT_MEDIUM2X => 'medium',
		self::VARIANT_ORIGINAL => 'big',
	];

	protected $casts = [
		'public' => 'int',
		'star' => 'int',
		'downloadable' => 'int',
		'share_button_visible' => 'int',
		'created_at' => 'datetime',
		'updated_at' => 'datetime',
		'taken_at' => DateTimeWithTimezoneCast::class,
	];

	/**
	 * Return the relationship between a Photo and its Album.
	 *
	 * @return BelongsTo
	 */
	public function album()
	{
		return $this->belongsTo('App\Models\Album', 'album_id', 'id');
	}

	/**
	 * Return the relationship between a Photo and its Owner.
	 *
	 * @return BelongsTo
	 */
	public function owner()
	{
		return $this->belongsTo('App\Models\User', 'owner_id', 'id');
	}

	/**
	 * Before calling the delete() method which will remove the entry from the database, we need to remove the files.
	 *
	 * @param bool $keep_original
	 *
	 * @return bool
	 */
	public function predelete(bool $keep_original = false)
	{
		if ($this->isDuplicate($this->checksum, $this->id)) {
			Logs::notice(__METHOD__, __LINE__, $this->id . ' is a duplicate!');
			// it is a duplicate, we do not delete!
			return true;
		}

		$error = false;
		$path_prefix = $this->type == 'raw' ? 'raw/' : 'big/';
		if ($keep_original === false) {
			// quick check...
			if (!Storage::exists($path_prefix . $this->url)) {
				Logs::error(__METHOD__, __LINE__, 'Could not find file in ' . Storage::path($path_prefix . $this->url));
				$error = true;
			} elseif (!Storage::delete($path_prefix . $this->url)) {
				Logs::error(__METHOD__, __LINE__, 'Could not delete file in ' . Storage::path($path_prefix . $this->url));
				$error = true;
			}
		}

		if ((strpos($this->type, 'video') === 0) || ($this->type == 'raw')) {
			$photoName = $this->thumbUrl;
		} else {
			$photoName = $this->url;
		}
		if ($photoName !== '') {
			$photoName2x = Helpers::ex2x($photoName);

			// Delete Live Photo Video file
			// TODO: USE STORAGE FOR DELETE
			// check first if livePhotoUrl is available
			if ($this->livePhotoUrl !== null) {
				if (!Storage::exists('big/' . $this->livePhotoUrl)) {
					Logs::error(__METHOD__, __LINE__, 'Could not find file in ' . Storage::path('big/' . $this->livePhotoUrl));
					$error = true;
				} elseif (!Storage::delete('big/' . $this->livePhotoUrl)) {
					Logs::error(__METHOD__, __LINE__, 'Could not delete file in ' . Storage::path('big/' . $this->livePhotoUrl));
					$error = true;
				}
			}

			// Delete medium
			// TODO: USE STORAGE FOR DELETE
			if (Storage::exists('medium/' . $photoName) && !unlink(Storage::path('medium/' . $photoName))) {
				Logs::error(__METHOD__, __LINE__, 'Could not delete photo in uploads/medium/');
				$error = true;
			}

			// TODO: USE STORAGE FOR DELETE
			if (Storage::exists('medium/' . $photoName2x) && !unlink(Storage::path('medium/' . $photoName2x))) {
				Logs::error(__METHOD__, __LINE__, 'Could not delete high-res photo in uploads/medium/');
				$error = true;
			}

			// Delete small
			// TODO: USE STORAGE FOR DELETE
			if (Storage::exists('small/' . $photoName) && !unlink(Storage::path('small/' . $photoName))) {
				Logs::error(__METHOD__, __LINE__, 'Could not delete photo in uploads/small/');
				$error = true;
			}

			// TODO: USE STORAGE FOR DELETE
			if (Storage::exists('small/' . $photoName2x) && !unlink(Storage::path('small/' . $photoName2x))) {
				Logs::error(__METHOD__, __LINE__, 'Could not delete high-res photo in uploads/small/');
				$error = true;
			}
		}

		if ($this->thumbUrl != '') {
			// Get retina thumb url
			$thumbUrl2x = Helpers::ex2x($this->thumbUrl);
			// Delete thumb
			// TODO: USE STORAGE FOR DELETE
			if (Storage::exists('thumb/' . $this->thumbUrl) && !unlink(Storage::path('thumb/' . $this->thumbUrl))) {
				Logs::error(__METHOD__, __LINE__, 'Could not delete photo in uploads/thumb/');
				$error = true;
			}

			// Delete thumb@2x
			// TODO: USE STORAGE FOR DELETE
			if (Storage::exists('thumb/' . $thumbUrl2x) && !unlink(Storage::path('thumb/' . $thumbUrl2x))) {
				Logs::error(__METHOD__, __LINE__, 'Could not delete high-res photo in uploads/thumb/');
				$error = true;
			}
		}

		return !$error;
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
	 * Sets the datetime when the photo has been taken.
	 *
	 * This mutator also sets the internal attribute `taken_at_orig_tz`
	 * accordingly.
	 *
	 * @param Carbon|null $datetime The datetime when the photo
	 *                              has been taken
	 */
	public function setTakenAtAttribute(?Carbon $datetime)
	{
		//throw new \BadMethodCallException('Whoops! Mutator for attribute \'taken_at\' called');
		$this->attributes['taken_at'] = $datetime;
		$this->attributes['taken_at_orig_tz'] = $datetime == null ? null : $datetime->getTimezone()->getName();
		if ($datetime !== null && empty($this->attributes['taken_at_orig_tz'])) {
			var_dump($this->attributes['taken_at_orig_tz']);
			throw new \InvalidArgumentException('Attribute \'taken_at\' has empty timezone attribute: \'' . $datetime->getTimezone()->getName() . '\'');
		}
	}

	/**
	 * Mutator for attribute `taken_at_orig_tz`.
	 *
	 * Always throws an exception.
	 *
	 * @param string|null $timezone The timezone
	 */
	public function setTakenAtOrigTzAttribute(?string $timezone)
	{
		throw new \BadMethodCallException('Attribute \'taken_at_orig_tz\' must not be set explicitly, set \'taken_at\' instead');
	}
}
