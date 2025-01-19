<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Models\Extensions;

use App\Constants\RandomID;
use App\Exceptions\InsufficientEntropyException;
use App\Exceptions\Internal\NotImplementedException;
use App\Exceptions\Internal\TimeBasedIdException;
use App\Models\Configs;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\InvalidCastException;
use Illuminate\Database\Eloquent\JsonEncodingException;
use Illuminate\Database\QueryException;

/**
 * Trait HasTimeBasedID.
 *
 * @template TModel of \Illuminate\Database\Eloquent\Model
 *
 * Inspired by https://emymbenoun.medium.com/how-to-use-uuids-instead-of-auto-increment-ids-in-your-laravel-app-2e6cc045f6c1.
 */
trait HasRandomIDAndLegacyTimeBasedID
{
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
	 *
	 * @throws NotImplementedException
	 *
	 * @codeCoverageIgnore setter should not be used
	 */
	public function setIncrementing($value)
	{
		throw new NotImplementedException('must not call setIncrementing for a model which uses the trait HasTimeBasedID');
	}

	/**
	 * Set a given attribute on the model.
	 *
	 * @param string $key   name of attribute which is being set
	 * @param mixed  $value value of attribute
	 *
	 * @return mixed
	 *
	 * @throws NotImplementedException
	 * @throws InvalidCastException
	 * @throws JsonEncodingException
	 */
	public function setAttribute($key, $value): mixed
	{
		if ($key === $this->getKeyName()) {
			throw new NotImplementedException('must not set primary key explicitly, primary key will be set on first insert');
		}
		if ($key === RandomID::LEGACY_ID_NAME) {
			throw new NotImplementedException('must not set legacy key explicitly, legacy key will be set on first insert');
		}

		return parent::setAttribute($key, $value);
	}

	/**
	 * Performs the `INSERT` operation of the model.
	 *
	 * This method also tries to create a unique, time-based ID.
	 * The method is mostly copied & pasted from {@link \Illuminate\Database\Eloquent\Model::performInsert()}
	 * with adoptions regarding key generation.
	 *
	 * @param Builder<static> $query
	 *
	 * @return bool true on success
	 *
	 * @throws TimeBasedIdException
	 * @throws InsufficientEntropyException
	 */
	protected function performInsert(Builder $query): bool
	{
		if ($this->fireModelEvent('creating') === false) {
			// @codeCoverageIgnoreStart
			return false;
			// @codeCoverageIgnoreEnd
		}

		// First we'll need to create a fresh query instance and touch the creation and
		// update timestamps on this model, which are maintained by us for developer
		// convenience. After, we will just continue saving these model instances.
		if ($this->usesTimestamps()) {
			$this->updateTimestamps();
		}

		$result = false;
		$retryCounter = 5;
		$lastException = null;

		do {
			$retry = false;
			try {
				$retryCounter--;
				$this->generateKey();
				$attributes = $this->getAttributesForInsert();
				$result = $query->insert($attributes);
				// @codeCoverageIgnoreStart
			} catch (QueryException $e) {
				$lastException = $e;
				$errorCode = $e->getCode();
				if ($errorCode === 23000 || $errorCode === 23505 || $errorCode === '23000' || $errorCode === '23505') {
					// houston, we have a duplicate entry problem
					// Our ids are based on current system time, so
					// wait randomly up to 1s before retrying.
					usleep(rand(0, 1000000));
					$retry = true;
				} else {
					throw $e;
				}
				// @codeCoverageIgnoreEnd
			}
		} while ($retry && $retryCounter > 0);

		if ($retryCounter === 0) {
			// @codeCoverageIgnoreStart
			throw new TimeBasedIdException('unable to persist model to DB after 5 unsuccessful attempts', $lastException);
			// @codeCoverageIgnoreEnd
		}

		// We will go ahead and set the exists property to true, so that it is set when
		// the created event is fired, just in case the developer tries to update it
		// during the event. This will allow them to do so and run an update here.
		$this->exists = true;
		$this->wasRecentlyCreated = true;
		$this->fireModelEvent('created', false);

		return $result;
	}

	/**
	 * Generates a primary key and a legacy key.
	 *
	 * The primary key are 144bit true randomness, encoded as Base64.
	 * The legacy key is based on the current micro-time.
	 *
	 * @return void
	 *
	 * @throws InsufficientEntropyException
	 */
	private function generateKey(): void
	{
		// URl-compatible variant of base64 encoding
		// `+` and `/` are replaced by `-` and `_`, resp.
		// The other characters (a-z, A-Z, 0-9) are legal within an URL.
		// As the number of bytes is divisible by 3, no trailing `=` occurs.
		try {
			$id = strtr(base64_encode(random_bytes(3 * RandomID::ID_LENGTH / 4)), '+/', '-_');
			// Last character whould not be a - for some version of android.
			// this will reduce the entropy and induce a slight bias but we are still
			// above the birthday bounds.
			if ($id[23] === '-') {
				$id[23] = '0';
			}
			// @codeCoverageIgnoreStart
		} catch (\Exception $e) {
			throw new InsufficientEntropyException($e);
		}
		// @codeCoverageIgnoreEnd
		if (
			PHP_INT_MAX === 2147483647 ||
			Configs::getValueAsBool('force_32bit_ids')
		) {
			// For 32-bit installations, we can only afford to store the
			// full seconds in id.  The calling code needs to be able to
			// handle duplicate ids.  Note that this also exposes us to
			// the year 2038 problem.
			// @codeCoverageIgnoreStart
			$legacyID = sprintf('%010d', microtime(true));
		// @codeCoverageIgnoreEnd
		} else {
			// Ensure 4 digits after the decimal point, 15 characters
			// total (including the decimal point), 0-padded on the
			// left if needed (shouldn't be needed unless we move back in
			// time :-) )
			$legacyID = sprintf('%015.4f', microtime(true));
			$legacyID = str_replace('.', '', $legacyID);
		}
		$this->attributes[$this->getKeyName()] = $id;
		$this->attributes[RandomID::LEGACY_ID_NAME] = intval($legacyID);
	}
}
