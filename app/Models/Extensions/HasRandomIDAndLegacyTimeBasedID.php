<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Models\Extensions;

use App\Exceptions\InsufficientEntropyException;
use App\Exceptions\Internal\NotImplementedException;
use App\Exceptions\Internal\TimeBasedIdException;
use App\Factories\IdFactory;
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
	 * Holds a pre-allocated key that should be consumed on the next INSERT.
	 * Set via {@link preallocateId()}.  Cleared after first use so that
	 * DB-collision retries generate a fresh random ID instead of re-trying
	 * the same value.
	 */
	protected ?string $pre_allocated_key = null;

	/**
	 * Get the value indicating whether the IDs are incrementing.
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
	 * @throws NotImplementedException
	 * @throws InvalidCastException
	 * @throws JsonEncodingException
	 */
	public function setAttribute($key, $value): mixed
	{
		if ($key === $this->getKeyName()) {
			throw new NotImplementedException('must not set primary key explicitly, primary key will be set on first insert');
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
		$retry_counter = 5;
		$last_exception = null;

		do {
			$retry = false;
			try {
				$retry_counter--;
				$this->generateKey();
				$attributes = $this->getAttributesForInsert();
				$result = $query->insert($attributes);
				// @codeCoverageIgnoreStart
			} catch (QueryException $e) {
				$last_exception = $e;
				$error_code = $e->getCode();
				if (in_array($error_code, [23000, 23505, '23000', '23505'], true)) {
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
		} while ($retry && $retry_counter > 0);

		if ($retry_counter === 0) {
			// @codeCoverageIgnoreStart
			throw new TimeBasedIdException('unable to persist model to DB after 5 unsuccessful attempts', $last_exception);
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
	 * @throws InsufficientEntropyException
	 */
	private function generateKey(): void
	{
		// If a key was pre-allocated (e.g. via preallocateId()), consume it on the
		// first INSERT attempt.  Clearing $pre_allocated_key after use ensures that
		// a DB-collision retry generates a fresh random ID rather than failing again
		// with the same value.
		if ($this->pre_allocated_key !== null) {
			$this->attributes[$this->getKeyName()] = $this->pre_allocated_key;
			$this->pre_allocated_key = null;

			return;
		}

		$id_factory = resolve(IdFactory::class);
		$this->attributes[$this->getKeyName()] = $id_factory->createRandomID();
	}
}
