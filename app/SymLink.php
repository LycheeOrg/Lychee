<?php

namespace App;

use App\ModelFunctions\Helpers;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Storage;

/**
 * App\SymLink.
 *
 * @method static Builder|SymLink newModelQuery()
 * @method static Builder|SymLink newQuery()
 * @method static Builder|SymLink query()
 * @mixin \Eloquent
 */
class SymLink extends Model
{
	/**
	 * Generate a sim link.
	 *
	 * @param Photo  $photo
	 * @param string $kind
	 * @param string $salt
	 */
	private function symlinking(Photo $photo, string $kind, string $salt)
	{
		$dir = $kind;
		$urls = explode('.', $photo->url);
		if (substr($kind, -2, 2) == '2x') {
			$dir = substr($kind, 0, -2);
			$url = $urls[0] . '@2x.' . $urls[1];
		} else {
			$url = $urls[0] . '.' . $urls[1];
		}

		$original = Storage::path($dir . '/' . $url);
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
		$this->$kind = Storage::drive('symbolic')->url($file_name);
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

		$kinds = [
			'big', 'medium', 'medium2x', 'small', 'small2x', 'thumb', 'thumb2x',
		];
		foreach ($kinds as $kind) {
			if ($photo->$kind != '') {
				$this->symlinking($photo, $kind, strval($now));
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
		$kinds = [
			'big', 'medium', 'medium2x', 'small', 'small2x', 'thumb', 'thumb2x',
		];
		foreach ($kinds as $kind) {
			if ($this->$kind != '') {
				$return[$kind] = $this->$kind;
			}
		}
	}

	/**
	 * before deleting we actually unlink the symlinks.
	 *
	 * @return bool|null
	 *
	 * @throws Exception
	 */
	public function delete()
	{
		$kinds = [
			'big', 'medium', 'medium2x', 'small', 'small2x', 'thumb', 'thumb2x',
		];
		foreach ($kinds as $kind) {
			if ($this->$kind != '') {
				$this->unlink(Storage::drive('symbolic')->path($this->$kind));
			}
		}

		return parent::delete();
	}
}
