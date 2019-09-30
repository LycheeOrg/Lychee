<?php

namespace App;

use App\ModelFunctions\Helpers;
use Eloquent;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Storage;

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
	// we have exactly the same mapping as for app/Photo in order to avoid cases
	protected $kinds_dir = [
		'url' => 'big',
		'medium' => 'medium',
		'medium2x' => 'medium',
		'small' => 'small',
		'small2x' => 'small',
		'thumbUrl' => 'thumb',
		'thumb2x' => 'thumb',
	];

	protected $kinds_origin = [
		'url' => 'url',
		'medium' => 'url',
		'medium2x' => 'url',
		'small' => 'url',
		'small2x' => 'url',
		'thumbUrl' => 'thumbUrl',
		'thumb2x' => 'thumbUrl',
	];

	/**
	 * Generate a sim link.
	 * The salt is important in order to remove the deterministic side of the address.
	 *
	 * @param Photo  $photo
	 * @param string $kind
	 * @param string $salt
	 * @param $field
	 */
	private function create(Photo $photo, string $kind, string $salt, $field)
	{
		$urls = explode('.', $photo->$field);
		if (substr($kind, -2, 2) == '2x') {
			$url = $urls[0] . '@2x.' . $urls[1];
		} else {
			$url = $urls[0] . '.' . $urls[1];
		}

		if ($photo->type == 'raw') {
			$original = Storage::path('raw/' . $url);
		} else {
			$original = Storage::path($this->kinds_dir[$kind] . '/' . $url);
		}
		$extension = Helpers::getExtension($original);
		$file_name = hash('sha256', $salt . '|' . $original) . $extension;
		$sym = Storage::drive('symbolic')->path($file_name);

		try {
			// in theory we should be safe...
			symlink($original, $sym);
		} catch (Exception $exception) {
			unlink($sym);
			symlink($original, $sym);
		}
		$this->$kind = $file_name;
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

		// first the URL
		if ($photo->url != '') {
			$this->create($photo, 'url', strval($now), 'url');
		}

		// in case of video we need to use thumbUrl instead
		$kinds = [
			'medium', 'medium2x', 'small', 'small2x', 'thumbUrl', 'thumb2x',
		];

		if (strpos($photo->type, 'video') === 0) {
			foreach ($kinds as $kind) {
				if ($photo->$kind != '' && $photo->$kind != '0') {
					$this->create($photo, $kind, strval($now), 'thumbUrl');
				}
			}
		} else {
			foreach ($kinds as $kind) {
				if ($photo->$kind != '' && $photo->$kind != '0') {
					$this->create($photo, $kind, strval($now), $this->kinds_origin[$kind]);
				}
			}
		}
	}

	/**
	 * Given the return array, override the link provided.
	 *
	 * @param array $return
	 */
	public function override(array &$return)
	{
		foreach ($this->kinds_dir as $kind => $dir) {
			if ($this->$kind != '') {
				$return[$kind] = Storage::drive('symbolic')->url($this->$kind);
			}
		}
	}

	/**
	 * @param $kind
	 *
	 * @return string URL to symbolic link
	 */
	public function get($kind)
	{
		if ($this->$kind != '') {
			return Storage::drive('symbolic')->url($this->$kind);
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
		foreach ($this->kinds_dir as $kind => $dir) {
			if ($this->$kind != '') {
				$path = Storage::drive('symbolic')->path($this->$kind);
				try {
					Logs::warning(__FUNCTION__, __LINE__, $path);
					unlink($path);
				} catch (Exception $e) {
					Logs::error(__METHOD__, __LINE__, 'could not unlink ' . $path);
				}
			}
		}

		return parent::delete();
	}
}
