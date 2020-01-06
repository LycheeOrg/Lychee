<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App;

use App\ModelFunctions\Helpers;
use Eloquent;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
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
 * @property string      $size
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
 * @property Carbon|null $takestamp
 * @property int         $star
 * @property string      $thumbUrl
 * @property string      $livePhotoUrl
 * @property int|null    $album_id
 * @property string      $checksum
 * @property string      $license
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string      $medium
 * @property string      $medium2x
 * @property string      $small
 * @property string      $small2x
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
class Photo extends Model
{
	/**
	 * This extends the date types from Model to allow coercion with Carbon object.
	 *
	 * @var array dates
	 */
	protected $dates = [
		'created_at',
		'updated_at',
		'takestamp',
	];

	protected $casts = [
		'public' => 'int',
		'star' => 'int',
		'downloadable' => 'int',
		'share_button_visible' => 'int',
	];

	/**
	 * Return the relationship between a Photo and its Album.
	 *
	 * @return BelongsTo
	 */
	public function album()
	{
		return $this->belongsTo('App\Album', 'album_id', 'id')->withDefault(['public' => '1']);
	}

	/**
	 * Return the relationship between a Photo and its Owner.
	 *
	 * @return BelongsTo
	 */
	public function owner()
	{
		return $this->belongsTo('App\User', 'owner_id', 'id')->withDefault([
			'id' => 0,
			'username' => 'Admin',
		]);
	}

	/**
	 * Searches for a match of the livePhotoContentID to build a pair
	 * of photo and video to form a live Photo
	 * Warning: Only return the first hit!
	 *
	 * @param string $livePhotoContentID
	 * @param string $albumID
	 *
	 * @return Photo|bool|Builder|Model|object
	 */
	public function findLivePhotoPartner(string $livePhotoContentID, string $albumID = null)
	{
		// Todo: We need to search for pairs (Video + Photo)
		// Photo+Photo or Video+Video does not work
		$sql = $this->where('livePhotoContentID', '=', $livePhotoContentID)
					->where('album_id', '=', $albumID)
					->whereNull('livePhotoUrl');

		return ($sql->count() == 0) ? false : $sql->first();
	}

	/**
	 * Check if a photo already exists in the database via its checksum.
	 *
	 * @param string $checksum
	 * @param $photoID
	 *
	 * @return Photo|bool|Builder|Model|object
	 */
	public function isDuplicate(string $checksum, $photoID = null)
	{
		$sql = $this->where('checksum', '=', $checksum)
			  ->orWhere('livePhotoChecksum', '=', $checksum);
		if (isset($photoID)) {
			$sql = $sql->where('id', '<>', $photoID);
		}

		return ($sql->count() == 0) ? false : $sql->first();
	}

	/**
	 * Returns photo-attributes into a front-end friendly format. Note that some attributes remain unchanged.
	 *
	 * @return array returns photo-attributes in a normalized structure
	 */
	public function prepareData()
	{
		// Init
		$photo = [];

		// Set unchanged attributes
		$photo['id'] = strval($this->id);
		$photo['title'] = $this->title;
		$photo['tags'] = $this->tags;
		$photo['star'] = $this->star == 1 ? '1' : '0';
		$photo['album'] = $this->album_id !== null ? strval($this->album_id) : null;
		$photo['width'] = strval($this->width);
		$photo['height'] = strval($this->height);
		$photo['type'] = $this->type;
		$photo['size'] = $this->size;
		$photo['iso'] = $this->iso;
		$photo['aperture'] = $this->aperture;
		$photo['make'] = $this->make;
		$photo['model'] = $this->model;
		$photo['shutter'] = $this->shutter;
		$photo['focal'] = $this->focal;
		$photo['lens'] = $this->lens;
		$photo['latitude'] = $this->latitude;
		$photo['longitude'] = $this->longitude;
		$photo['altitude'] = $this->altitude;
		$photo['imgDirection'] = $this->imgDirection;
		$photo['livePhotoContentID'] = $this->livePhotoContentID;

		$photo['sysdate'] = $this->created_at->format('d F Y');
		$photo['description'] = $this->description == null ? '' : $this->description;
		$photo['license'] = Configs::get_value('default_license'); // default

		// shutter speed needs to be processed. It is stored as a string `a/b s`
		if ($photo['shutter'] != '' && substr($photo['shutter'], 0, 2) != '1/') {
			preg_match('/(\d+)\/(\d+) s/', $photo['shutter'], $matches);
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
						$photo['shutter'] = '1/' . $b . ' s';
					} else {
						$photo['shutter'] = ($a / $b) . ' s';
					}
				}
			}
		}

		if ($photo['shutter'] == '1/1 s') {
			$photo['shutter'] = '1 s';
		}

		// check if license is none
		if ($this->license == 'none') {
			// check if it has an album
			if ($this->album_id != 0) {
				// this does not include sub albums setting. Do we want this ?
				// this will need to be changed if we want to add license backtracking
				$l = $this->album->license;
				if ($l != 'none') {
					$photo['license'] = $l;
				}
			}
		} else {
			$photo['license'] = $this->license;
		}

		// if this is a video
		if (strpos($this->type, 'video') === 0) {
			$photoUrl = $this->thumbUrl;

			// We need to format the framerate (stored as focal) -> max 2 decimal digits
			$photo['focal'] = round($photo['focal'], 2);
		} else {
			$photoUrl = $this->url;
		}
		$photoUrl2x = '';
		if ($photoUrl !== '') {
			$photoUrl2x = explode('.', $photoUrl);
			$photoUrl2x = $photoUrl2x[0] . '@2x.' . $photoUrl2x[1];
		}

		// Parse medium
		if ($this->medium != '') {
			$photo['medium'] = Storage::url('medium/' . $photoUrl);
			$photo['medium_dim'] = $this->medium;
		} else {
			$photo['medium'] = '';
			$photo['medium_dim'] = '';
		}

		if ($this->medium2x != '') {
			$photo['medium2x'] = Storage::url('medium/' . $photoUrl2x);
			$photo['medium2x_dim'] = $this->medium2x;
		} else {
			$photo['medium2x'] = '';
			$photo['medium2x_dim'] = '';
		}

		if ($this->small != '') {
			$photo['small'] = Storage::url('small/' . $photoUrl);
			$photo['small_dim'] = $this->small;
		} else {
			$photo['small'] = '';
			$photo['small_dim'] = '';
		}

		if ($this->small2x != '') {
			$photo['small2x'] = Storage::url('small/' . $photoUrl2x);
			$photo['small2x_dim'] = $this->small2x;
		} else {
			$photo['small2x'] = '';
			$photo['small2x_dim'] = '';
		}

		// Parse paths
		$photo['thumbUrl'] = Storage::url('thumb/' . $this->thumbUrl);

		if ($this->thumb2x == '1') {
			$thumbUrl2x = explode('.', $this->thumbUrl);
			$thumbUrl2x = $thumbUrl2x[0] . '@2x.' . $thumbUrl2x[1];
			$photo['thumb2x'] = Storage::url('thumb/' . $thumbUrl2x);
		} else {
			$photo['thumb2x'] = '';
		}

		$path_prefix = $this->type == 'raw' ? 'raw/' : 'big/';
		$photo['url'] = Storage::url($path_prefix . $this->url);

		if ($this->livePhotoUrl !== '' && $this->livePhotoUrl !== null) {
			$photo['livePhotoUrl'] = Storage::url($path_prefix . $this->livePhotoUrl);
		} else {
			$photo['livePhotoUrl'] = null;
		}

		// Use takestamp as sysdate when possible
		if (isset($this->takestamp) && $this->takestamp != null) {
			// Use takestamp
			$photo['cameraDate'] = '1';
			$photo['sysdate'] = $this->created_at->format('d F Y');
			$photo['takedate'] = $this->takestamp->format('d F Y \a\t H:i');
		} else {
			// Use sysstamp from the id
			$photo['cameraDate'] = '0';
			$photo['sysdate'] = $this->created_at->format('d F Y');
			$photo['takedate'] = '';
		}

		$photo['public'] = $this->get_public();

		return $photo;
	}

	/**
	 * Returns photo-attributes into a front-end friendly format. Note that some attributes remain unchanged.
	 *
	 * @return array returns photo-attributes in a normalized structure
	 */
	public function prepareLocationData()
	{
		// Init
		$photo = [];

		// Set unchanged attributes
		$photo['id'] = strval($this->id);
		$photo['title'] = $this->title;
		$photo['album'] = $this->album_id !== null ? strval($this->album_id) : null;
		$photo['latitude'] = $this->latitude;
		$photo['longitude'] = $this->longitude;

		// if this is a video
		if (strpos($this->type, 'video') === 0) {
			$photoUrl = $this->thumbUrl;
		} else {
			$photoUrl = $this->url;
		}

		$photoUrl2x = '';
		if ($photoUrl !== '') {
			$photoUrl2x = explode('.', $photoUrl);
			$photoUrl2x = $photoUrl2x[0] . '@2x.' . $photoUrl2x[1];
		}

		if ($this->small != '') {
			$photo['small'] = Storage::url('small/' . $photoUrl);
		} else {
			$photo['small'] = '';
		}

		if ($this->small2x != '') {
			$photo['small2x'] = Storage::url('small/' . $photoUrl2x);
		} else {
			$photo['small2x'] = '';
		}

		// Parse paths
		$photo['thumbUrl'] = Storage::url('thumb/' . $this->thumbUrl);

		if ($this->thumb2x == '1') {
			$thumbUrl2x = explode('.', $this->thumbUrl);
			$thumbUrl2x = $thumbUrl2x[0] . '@2x.' . $thumbUrl2x[1];
			$photo['thumb2x'] = Storage::url('thumb/' . $thumbUrl2x);
		} else {
			$photo['thumb2x'] = '';
		}

		$path_prefix = $this->type == 'raw' ? 'raw/' : 'big/';
		$photo['url'] = Storage::url($path_prefix . $this->url);

		if (isset($this->takestamp) && $this->takestamp != null) {
			// Use takestamp
			$photo['takedate'] = $this->takestamp->format('d F Y \a\t H:i');
		} else {
			$photo['takedate'] = '';
		}

		return $photo;
	}

	/**
	 * Downgrade the quality of the pictures.
	 *
	 * @param array $return
	 */
	public function downgrade(array &$return)
	{
		if (strpos($this->type, 'video') == 0) {
			if ($return['medium2x'] != '') {
				$return['url'] = '';
			} elseif ($return['medium'] != '') {
				$return['url'] = '';
			} else {
			}
		}
	}

	/**
	 * Get the public value of a picture
	 * if 0 : picture is private
	 * if 1 : picture is public alone
	 * if 2 : picture is public by album being public (if being in an album).
	 *
	 * @return string
	 */
	public function get_public()
	{
		$ret = $this->public == 1 ? '1' : '0';

		if ($this->album_id != null) {
			$ret = $this->album->public == '1' ? '2' : $ret;
		}

		return $ret;
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

		if (strpos($this->type, 'video') === 0) {
			$photoName = $this->thumbUrl;
		} else {
			$photoName = $this->url;
		}
		if ($photoName !== '') {
			$photoName2x = explode('.', $photoName);
			$photoName2x = $photoName2x[0] . '@2x.' . $photoName2x[1];

			// Delete Live Photo Video file
			// TODO: USE STORAGE FOR DELETE
			// check first if livePhotoUrl is available
			if ($this->livePhotoUrl !== null) {
				if (!Storage::exists($path_prefix . $this->livePhotoUrl)) {
					Logs::error(__METHOD__, __LINE__, 'Could not find file in ' . Storage::path($path_prefix . $this->livePhotoUrl));
					$error = true;
				} elseif (!Storage::delete($path_prefix . $this->livePhotoUrl)) {
					Logs::error(__METHOD__, __LINE__, 'Could not delete file in ' . Storage::path($path_prefix . $this->livePhotoUrl));
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
			$thumbUrl2x = explode('.', $this->thumbUrl);
			$thumbUrl2x = $thumbUrl2x[0] . '@2x.' . $thumbUrl2x[1];
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
	 *  Defines a bunch of helpers.
	 */

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
	 * @param $query
	 *
	 * @return mixed
	 */
	public static function select_stars(Builder $query)
	{
		return self::set_order($query->where('star', '=', 1));
	}

	/**
	 * @param $query
	 *
	 * @return mixed
	 */
	public static function select_public(Builder $query)
	{
		return self::set_order($query->where('public', '=', 1));
	}

	/**
	 * @param $query
	 *
	 * @return mixed
	 */
	public static function select_recent(Builder $query)
	{
		return self::set_order($query->where('created_at', '>=', Carbon::now()->subDays(intval(Configs::get_value('recent_age', '1')))->toDateTimeString()));
	}

	/**
	 * @param $query
	 *
	 * @return mixed
	 */
	public static function select_unsorted(Builder $query)
	{
		return self::set_order($query->where('album_id', '=', null));
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
		return self::select_stars($query);
	}

	/**
	 * @param $query
	 *
	 * @return mixed
	 */
	public function scopePublic($query)
	{
		return self::select_public($query);
	}

	/**
	 * @param $query
	 *
	 * @return mixed
	 */
	public function scopeRecent($query)
	{
		return self::select_recent($query);
	}

	/**
	 * @param $query
	 *
	 * @return mixed
	 */
	public function scopeUnsorted($query)
	{
		return self::select_unsorted($query);
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
}
