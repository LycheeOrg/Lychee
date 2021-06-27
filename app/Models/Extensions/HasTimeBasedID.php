<?php

namespace App\Models\Extensions;

use App\Models\Configs;
use App\Models\Logs;
use Illuminate\Database\Eloquent\Concerns\HasAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;

/**
 * Trait HasTimeBasedID.
 *
 * Inspired by https://emymbenoun.medium.com/how-to-use-uuids-instead-of-auto-increment-ids-in-your-laravel-app-2e6cc045f6c1.
 */
trait HasTimeBasedID
{
	public static function bootHasTimeBasedID()
	{
		static::creating(function (Model $model) {
			$pKey = $model->getKeyName();
			if (empty($model->$pKey)) {
				$model->generateID();
			}

			return true;
		});
	}

	/**
	 * Get the value indicating whether the IDs are incrementing.
	 *
	 * @return bool
	 */
	public function getIncrementing(): bool
	{
		return false;
	}

	/**
	 * Set whether IDs are incrementing.
	 *
	 * @param bool $value
	 */
	public function setIncrementing($value)
	{
		throw new \BadMethodCallException('must not call setIncrementing for a model which uses the trait HasTimeBasedID');
	}

	/**
	 * Set a given attribute on the model.
	 *
	 * Actually, this method should override
	 * {@link HasAttributes::setAttribute()} in order to prevent setting of
	 * the primary key by application code.
	 * However, {@link AlbumCast::toTagAlbum()} does it nonetheless, so we
	 * don't throw an exception until that method is fixed.
	 *
	 * TODO: Fix {@link AlbumCast::toTagAlbum()}.
	 *
	 * @param string $key   name of attribute which is being set
	 * @param mixed  $value value of attribute
	 *
	 * @return mixed
	 */
	public function setAttribute($key, $value)
	{
		// TODO: Uncomment the following lines, after AlbumCast::toTagAlbum() has been fixed
		/*if ($key == $this->getKeyName()) {
			throw new \InvalidArgumentException('must not set primary key explicitly, primary key will be set on first insert');
		}*/

		return parent::setAttribute($key, $value);
	}

	/**
	 * Saves the model to the database.
	 *
	 * @param array $options
	 *
	 * @return bool
	 */
	public function save(array $options = []): bool
	{
		$result = false;
		$retryCounter = 5;
		$lastException = null;
		do {
			$retry = false;
			try {
				$retryCounter--;
				$result = parent::save();
			} catch (QueryException $e) {
				$lastException = $e;
				$errorCode = $e->getCode();
				if ($errorCode == 23000 || $errorCode == 23505) {
					// houston, we have a duplicate entry problem
					// Our ids are based on current system time, so
					// wait randomly up to 1s before retrying.
					usleep(rand(0, 1000000));
					$retry = true;
					// Remove primary key which has been set by last attempt
					unset($this->attributes[$this->getKeyName()]);
				} else {
					throw $e;
				}
			}
		} while ($retry && $retryCounter > 0);

		if ($retryCounter === 0) {
			$msg = 'unable to persist model to DB after 5 unsuccessful attempts';
			Logs::error(__METHOD__, __LINE__, $msg);
			throw new \RuntimeException($msg, 0, $lastException);
		}

		return $result;
	}

	/**
	 * Generates an ID for the primary key from current microtime.
	 */
	public function generateID(): void
	{
		if (
			PHP_INT_MAX == 2147483647
			|| Configs::get_value('force_32bit_ids', '0') === '1'
		) {
			// For 32-bit installations, we can only afford to store the
			// full seconds in id.  The calling code needs to be able to
			// handle duplicate ids.  Note that this also exposes us to
			// the year 2038 problem.
			$id = sprintf('%010d', microtime(true));
		} else {
			// Ensure 4 digits after the decimal point, 15 characters
			// total (including the decimal point), 0-padded on the
			// left if needed (shouldn't be needed unless we move back in
			// time :-) )
			$id = sprintf('%015.4f', microtime(true));
			$id = str_replace('.', '', $id);
		}
		$this->attributes[$this->getKeyName()] = intval($id);
	}
}
