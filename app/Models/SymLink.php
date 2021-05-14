<?php

namespace App\Models;

use Eloquent;
use Exception;
use Helpers;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * App\SymLink.
 *
 * @method static Builder|SymLink newModelQuery()
 * @method static Builder|SymLink newQuery()
 * @method static Builder|SymLink query()
 * @mixin Eloquent
 *
 * @property int         $id
 * @property int|null    $photo_id
 * @property string      $url
 * @property string      $medium
 * @property string      $medium2x
 * @property string      $small
 * @property string      $small2x
 * @property string      $thumbUrl
 * @property string      $thumb2x
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder|SymLink whereCreatedAt($value)
 * @method static Builder|SymLink whereId($value)
 * @method static Builder|SymLink whereMedium($value)
 * @method static Builder|SymLink whereMedium2x($value)
 * @method static Builder|SymLink wherePhotoId($value)
 * @method static Builder|SymLink whereSmall($value)
 * @method static Builder|SymLink whereSmall2x($value)
 * @method static Builder|SymLink whereThumb2x($value)
 * @method static Builder|SymLink whereThumbUrl($value)
 * @method static Builder|SymLink whereUpdatedAt($value)
 * @method static Builder|SymLink whereUrl($value)
 */
class SymLink extends Model
{
	/**
	 * Maps a size variant to a path prefix.
	 * This mapping must be the same as for App\Models\Photo in order to avoid cases.
	 */
	const VARIANT_2_PATH_PREFIX = [
		'original' => 'big',
		'medium' => 'medium',
		'medium2x' => 'medium',
		'small' => 'small',
		'small2x' => 'small',
		'thumb' => 'thumb',
		'thumb2x' => 'thumb',
	];

	/**
	 * Maps a size variant to the name of the attribute (field) of App\Models\Photo which stores the original
	 * filename.
	 * (Despite the attributes being named "url" they actually store filenames).
	 */
	const VARIANT_2_ORIGINAL_FILENAME_FIELD = [
		'original' => 'url',
		'medium' => 'url',
		'medium2x' => 'url',
		'small' => 'url',
		'small2x' => 'url',
		'thumb' => 'thumbUrl',
		'thumb2x' => 'thumbUrl',
	];

	/**
	 * Maps a size variant to the name of an attribute (field) of the class App\Models\Photo which can be exploited
	 * as an indicator whether this size variant exist.
	 */
	const VARIANT_2_INDICATOR_FIELD = [
		'original' => 'url',             // type: string|null
		'medium' => 'medium_width',      // type: int|null
		'medium2x' => 'medium2x_width',  // type: int|null
		'small' => 'small_width',        // type: int|null
		'small2x' => 'small2x_width',    // type: int|null
		'thumb' => 'thumbUrl',           // type: string|null
		'thumb2x' => 'thumb2x',          // type: integer, either 0 or 1
	];

	/**
	 * Maps a size variant to the name of the attribute (field) of this class/database table which stores the
	 * symlinked path.
	 * (Despite some attributes being named "url" they actually store relative paths).
	 */
	const VARIANT_2_SYM_PATH_FIELD = [
		'original' => 'url',
		'medium' => 'medium',
		'medium2x' => 'medium2x',
		'small' => 'small',
		'small2x' => 'small2x',
		'thumb' => 'thumbUrl',
		'thumb2x' => 'thumb2x',
	];

	/**
	 * Generate a sym link.
	 * The salt is important in order to remove the deterministic side of the address.
	 *
	 * @param Photo  $photo       The original photo
	 * @param string $sizeVariant An enum-like attribute which indicates what size variant shall be sym-linked.
	 *                            Allowed values are 'original', 'thumb', 'thumb2x', 'small', 'small2x', 'medium', 'medium2x'
	 * @param string $salt
	 */
	private function create(Photo $photo, string $sizeVariant, string $salt)
	{
		// in case of video and raw we always need to use the field 'thumbUrl' for anything which is not the original size
		$originalFieldName = ($sizeVariant != 'original' && ($photo->isVideo() || $photo->type == 'raw')) ?
			'thumbUrl' :
			self::VARIANT_2_ORIGINAL_FILENAME_FIELD[$sizeVariant];
		$originalFileName = (substr($sizeVariant, -2, 2) == '2x') ? Helpers::ex2x($photo->$originalFieldName) : $photo->$originalFieldName;

		if ($photo->type == 'raw' && $sizeVariant == 'original') {
			$originalPath = Storage::path('raw/' . $originalFileName);
		} else {
			$originalPath = Storage::path(self::VARIANT_2_PATH_PREFIX[$sizeVariant] . '/' . $originalFileName);
		}
		$extension = Helpers::getExtension($originalPath);
		$symFilename = hash('sha256', $salt . '|' . $originalPath) . $extension;
		$symPath = Storage::drive('symbolic')->path($symFilename);

		try {
			// in theory we should be safe...
			symlink($originalPath, $symPath);
		} catch (Exception $exception) {
			unlink($symPath);
			symlink($originalPath, $symPath);
		}
		$this->{self::VARIANT_2_SYM_PATH_FIELD[$sizeVariant]} = $symFilename;
	}

	/**
	 * Set up a link.
	 *
	 * @param Photo $photo
	 */
	public function set(Photo $photo)
	{
		$this->photo_id = $photo->id;
		$this->timestamps = false;
		// we set up the created_at
		$now = now();
		$this->created_at = $now;
		$this->updated_at = $now;

		foreach (self::VARIANT_2_INDICATOR_FIELD as $variant => $indicator_field) {
			if ($photo->{$indicator_field} != null && $photo->{$indicator_field} != 0 && $photo->{$indicator_field} != '') {
				$this->create($photo, $variant, strval($now));
			}
		}
	}

	/**
	 * Given the return array of a photo, override the link provided.
	 *
	 * @param array $return The serialization of a photo as returned by Photo#toReturnArray()
	 */
	public function override(array &$return)
	{
		foreach (self::VARIANT_2_SYM_PATH_FIELD as $variant => $field) {
			if ($this->$field != '') {
				switch ($variant) {
					case 'original':
						$return['url'] = Storage::drive('symbolic')->url($this->$field);
						break;
					default:
						$return['sizeVariants'][$variant]['url'] = Storage::drive('symbolic')->url($this->$field);
						break;
				}
			}
		}
	}

	/**
	 * @param string $sizeVariant
	 *
	 * @return string Relative path to symbolic link
	 */
	public function get(string $sizeVariant): string
	{
		$field = self::VARIANT_2_SYM_PATH_FIELD[$sizeVariant];
		if ($this->$field != '') {
			return Storage::drive('symbolic')->url($this->$field);
		} else {
			return '';
		}
	}

	/**
	 * before deleting we actually unlink the symlinks.
	 *
	 * @return bool|null
	 */
	public function delete()
	{
		foreach (self::VARIANT_2_SYM_PATH_FIELD as $variant => $field) {
			if ($this->$field != '') {
				$path = Storage::drive('symbolic')->path($this->$field);
				try {
					unlink($path);
				} catch (Exception $e) {
					Logs::error(__METHOD__, __LINE__, 'could not unlink ' . $path);
				}
			}
		}

		return parent::delete();
	}
}
