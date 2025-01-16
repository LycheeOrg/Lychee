<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Models\Extensions;

use App\Contracts\Exceptions\InternalLycheeException;
use App\Exceptions\Internal\FailedModelAssumptionException;
use App\Exceptions\Internal\NotImplementedException;
use App\Exceptions\ModelDBException;
use Illuminate\Contracts\Encryption\EncryptException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\InvalidCastException;
use Illuminate\Database\Eloquent\JsonEncodingException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Trait ForwardsToParentImplementation.
 *
 * This trait is supposed to be used by "child" classes of a parent-child
 * relation that follows the design pattern as described in
 * {@link \App\Models\BaseAlbumImpl}.
 * This trait assumes that the using "child" classes provides a relation
 * called `base_class` which returns an instance of
 * {@link BelongsTo} and refers
 * to the implementation of the "parent" class.
 * This trait forwards calls to properties and relations which are not
 * defined by the "child" class to the "parent" class and therewith
 * mimics a behaviour as if the properties and relations of the parent
 * class were actually inherited by the "child" class.
 * Moreover, this trait ensures that the parent class is saved/created before
 * the child class is saved/created and that the (inherited) timestamp of
 * the parent class is touched when the child class is modified.
 */
trait ForwardsToParentImplementation
{
	abstract protected function friendlyModelName(): string;

	/**
	 * Returns the relationship between this model and the implementation
	 * of the "parent" class.
	 *
	 * @return BelongsTo
	 */
	abstract public function base_class(): BelongsTo;

	/**
	 * "Constructor" of trait.
	 *
	 * This "constructor" ensures that
	 *
	 *  1. the relation to the base class is not touched if the child class is
	 *     saved, because the internal mechanism of Eloquent always touches
	 *     the relation after saving the child, but we need it in inverse
	 *     order.
	 *  2. the relation to the base class is hidden from the default
	 *     serialization to JSON, because we don't want the base class to
	 *     be serialized as a normal relation but to be inlined into the
	 *     JSON
	 *  3. the model has no timestamps and no automatically incrementing ID,
	 *     because both are inherited from the parent model.
	 */
	public function initializeForwardsToParentImplementation(): void
	{
		$this->touches = array_diff($this->touches, ['base_class']);
		$this->appends = array_diff($this->appends, ['base_class']);
		$this->makeHidden('base_class');
		$this->with[] = 'base_class';
		$this->timestamps = false;
		$this->incrementing = false;
	}

	/**
	 * Perform a model insert operation.
	 *
	 * @param Builder<static> $query
	 *
	 * @return bool
	 *
	 * @throws FailedModelAssumptionException
	 */
	protected function performInsert(Builder $query): bool
	{
		if (!$this->relationLoaded('base_class')) {
			throw new FailedModelAssumptionException('cannot create a child class whose base class is not loaded');
		}
		/** @var Model $base_class */
		$base_class = $this->getRelation('base_class');
		if ($base_class->exists) {
			throw new FailedModelAssumptionException('cannot create a child class whose base class already exists');
		}
		// Save and therewith create the base class
		if (!$base_class->save()) {
			return false;
		}
		// Inherit the key of the base class
		$this->attributes[$this->getKeyName()] = $base_class->getKey();

		return parent::performInsert($query);
	}

	/**
	 * Perform a model update operation.
	 *
	 * @param Builder<static> $query
	 *
	 * @return bool
	 */
	protected function performUpdate(Builder $query): bool
	{
		/** @var Model */
		$base_class = $this->base_class;
		// touch() also indirectly saves the base_class hence any other
		// attributes which require an update are also saved
		if (!$base_class->touch()) {
			return false;
		}

		return parent::performUpdate($query);
	}

	/**
	 * Delete the model from the database.
	 *
	 * @return bool always returns true
	 *
	 * @throws ModelDBException thrown on failure
	 */
	public function delete(): bool
	{
		/** @var ?Model $baseClass */
		$baseClass = $this->base_class;

		$parentException = null;
		try {
			// Sic! Don't use `!$parentDelete` in condition, because we also
			// need to proceed if `$parentDelete === null` .
			// If Eloquent returns `null` (instead of `true`), this also
			// indicates a success, and we must go on.
			// Eloquent, I love you .... not.
			$parentResult = parent::delete();
			if ($parentResult === false) {
				$parentException = new \RuntimeException('Eloquent\Model::delete() returned false');
			}
		} catch (\Throwable $e) {
			$parentException = $e;
		}
		if ($parentException !== null) {
			throw ModelDBException::create($this->friendlyModelName(), 'deleting', $parentException);
		}

		// We must explicitly check if the base_class still exists in order
		// to avoid an infinite recursion, as the base class will also call
		// delete() on this class
		if ($baseClass !== null && $baseClass->exists) {
			$baseException = null;
			try {
				$baseResult = $baseClass->delete();
				// Same stupidity as above, if Eloquent returns `null`,
				// this also indicates a good case.
				if ($baseResult === false) {
					$baseException = new \RuntimeException('Eloquent\Model::delete() returned false');
				}
			} catch (\Throwable $e) {
				$baseException = $e;
			}
			if ($baseException !== null) {
				throw ModelDBException::create($this->friendlyModelName(), 'deleting', $baseException);
			}
		}

		return true;
	}

	/**
	 * Indicates whether the model has timestamps.
	 *
	 * Returns always false, because the child model uses the timestamps of
	 * its parent model
	 *
	 * @return bool always false
	 */
	public function usesTimestamps(): bool
	{
		return false;
	}

	/**
	 * Indicates whether the ID of the model is incrementing.
	 *
	 * Returns always false, because the child model inherits the ID of its
	 * parent model.
	 *
	 * @return bool always false
	 */
	public function getIncrementing(): bool
	{
		return false;
	}

	/**
	 * Determine if the model or any of the given attribute(s) have been modified.
	 *
	 * Inspired by {@link \Illuminate\Database\Eloquent\Concerns\HasAttributes::isDirty()}.
	 *
	 * @param string[]|string|null $attributes
	 *
	 * @return bool
	 */
	public function isDirty($attributes = null): bool
	{
		$baseIsDirty = $this->relationLoaded('base_class') && $this->getRelation('base_class')->isDirty();

		return $baseIsDirty || $this->hasChanges(
			$this->getDirty(),
			is_array($attributes) ? $attributes : func_get_args()
		);
	}

	/**
	 * Convert the model instance to an array.
	 *
	 * @return array<string,mixed>
	 */
	public function toArray(): array
	{
		return array_merge(parent::toArray(), $this->base_class->toArray());
	}

	/**
	 * Get an attribute from the model.
	 *
	 * This method is heavily inspired by
	 * {@link \Illuminate\Database\Concerns\HasAttributes::getAttribute()}.
	 * This method is modified in three ways:
	 *
	 *  1. A preliminary check if the requested attribute equals `'base_class'`.
	 *     This is necessary to avoid infinite loops in combination with 2).
	 *  2. A final call which forwards to the implementation of the base class
	 *     at the end, if the default code of
	 *     {@link \Illuminate\Database\Concerns\HasAttributes::getAttribute()}
	 *     would have fallen through.
	 *  3. While the middle part is basically a copy of the original code,
	 *     we had to tweak it slightly.
	 *     The original code calls `getRelationValue`, if the `$key` is not
	 *     an attribute, but we had to inline the code of `getRelationValue`
	 *     here due to two reasons:
	 *
	 *      1. This trait also overwrites `getRelationValue` such that
	 *         `getRelationValue` checks for a relation on both the child
	 *         and the parent model.
	 *         But here, we only must check on the child model, so we must
	 *         not call `getRelationValue`.
	 *      2. If `getRelationValue` returns `null` it is impossible to
	 *         distinguish, if `null` has been returned because the relation
	 *         exists and equals null or if no relation of that name exists
	 *         at all.
	 *         However, only in the latter case we want to forward the call to
	 *         the parent.
	 *         In the former case, we must return null directly.
	 *
	 * @param string $key the name of the queried attribute or relation
	 *
	 * @return mixed the value of the attribute or relation
	 *
	 * @throws \LogicException
	 * @throws InvalidCastException
	 */
	public function getAttribute($key): mixed
	{
		if ($key === '') {
			return null;
		}

		// If the primary key is requested, we must use a shortcut.
		// If the primary key of the model is not yet set as it might be the
		// case for new models, the implementation otherwise would fall
		// through until the end and try to forward the call to the base class.
		// However, asking for the primary key of the base class is
		//
		//  1. insane, because it should be identical to the primary key of
		//     this class, and
		//  2. does not work, because we cannot load the base class without
		//     knowing the primary key.
		if ($key === $this->getKeyName()) {
			// Sic!
			// Don't use `$this->getKey()` because this would call
			// `getAttribute` again, and we would end up in an infinite loop.
			// Just get the attribute directly.
			return $this->getAttributeValue($key);
		}

		// Avoid infinite loops, see below
		if ($key === 'base_class') {
			return $this->getRelationValue($key);
		}

		// If the attribute exists in the attribute array or has a "get"
		// mutator we will get the attribute's value.
		// Otherwise, we will proceed as if the developers
		// are asking for a relationship's value. This covers both types of values.
		if (
			array_key_exists($key, $this->attributes) ||
			array_key_exists($key, $this->casts) ||
			$this->hasGetMutator($key) ||
			$this->isClassCastable($key)
		) {
			return $this->getAttributeValue($key);
		}

		// Here we will determine if the model base class itself contains this given key
		// since we don't want to treat any of those methods as relationships because
		// they are all intended as helper methods and none of these are relations.
		if (method_exists(Model::class, $key)) {
			return null;
		}

		// If the key already exists in the relationships array, it just means the
		// relationship has already been loaded, so we'll just return it out of
		// here because there is no need to query within the relations twice.
		if ($this->relationLoaded($key)) {
			return $this->relations[$key];
		}

		// If the "attribute" exists as a method on the model, we will just assume
		// it is a relationship and will load and return results from the query
		// and hydrate the relationship's value on the "relationships" array.
		/** @disregard */
		if (
			method_exists($this, $key) ||
			(static::$relationResolvers[get_class($this)][$key] ?? null)
		) {
			return $this->getRelationshipFromMethod($key);
		}

		// If we have fallen through until here, the using "child" class has
		// no matching property nor relation.
		// So we try the implementation of the "parent" class.
		// Note, that his will load the relation of the parent class, if it
		// has not been loaded yet.
		// To avoid infinite loops, we had to check for "base_class" early in
		// this method.
		return $this->base_class->getAttribute($key);
	}

	/**
	 * Get the value of a relationship.
	 *
	 * This method is heavily inspired by
	 * {@link \Illuminate\Database\Eloquent\Concerns\HasAttributes::getRelationValue()}.
	 *
	 * @param string $key the name of the queried relation
	 *
	 * @return mixed the value of the relation if it could be loaded
	 *
	 * @throws InternalLycheeException
	 */
	public function getRelationValue($key): mixed
	{
		// If the key already exists in the relationships array, this means the
		// relationship has already been loaded, so we'll just return it out of
		// here because there is no need to query the relations twice.
		if ($this->relationLoaded($key)) {
			return $this->getRelation($key);
		}

		// Avoid infinite loops
		// Here we assume that the using class provides a relation `base_class`
		// (no check if such a method exists) and we rely on the fact that
		// `getRelationshipFromMethod` throws an exception if no such method
		// exists.
		// Bailing out with an exception prevents the infinite loop.
		if ($key === 'base_class') {
			// If this is a newly created model, then we cannot resolve the
			// relation to the base class from the database, because no such
			// entity exists.
			// In particular, calling the relation requires that this instance
			// of a model already has a valid primary key which does not exist
			// for a freshly created model.
			$primaryKey = $this->getKey();
			if (!$this->exists) {
				if ($primaryKey !== null) {
					throw new FailedModelAssumptionException('the primary key must not be set if the model does not exist');
				}
				$baseModel = $this->base_class()->getRelated()->newInstance();
				$this->setRelation('base_class', $baseModel);

				return $baseModel;
			} else {
				// This model exists, but the relation to the base class
				// has not yet been loaded.
				// Load it now.
				if ($primaryKey === null) {
					throw new FailedModelAssumptionException('the model allegedly exists, but we don\'t have a primary key, cannot load base model');
				}

				return $this->getRelationshipFromMethod('base_class');
			}
		}

		// If the "attribute" exists as a method on the model, we will just assume
		// it is a relationship and will load and return results from the query
		// and hydrate the relationship's value on the "relationships" array.
		/** @disregard */
		if (
			method_exists($this, $key) ||
			(static::$relationResolvers[get_class($this)][$key] ?? null)
		) {
			return $this->getRelationshipFromMethod($key);
		}

		// If we have fallen through until here, the using "child" class has
		// no matching property nor relation.
		// So we try the implementation of the "parent" class.
		// Note, that his will load the relation of the parent class, if it
		// has not been loaded yet.
		// To avoid infinite loops, we had to check for "base_class" early in
		// this method.
		return $this->base_class->getRelationValue($key);
	}

	/**
	 * Set a given attribute on the model.
	 *
	 * This method is heavily inspired by
	 * {@link \Illuminate\Database\Concerns\HasAttributes::setAttribute()}.
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return mixed
	 *
	 * @throws InvalidCastException
	 * @throws JsonEncodingException
	 * @throws EncryptException
	 * @throws \InvalidArgumentException
	 * @throws NotImplementedException
	 */
	public function setAttribute($key, $value): mixed
	{
		// First we will check for the presence of a mutator for the set operation
		// which simply lets the developers tweak the attribute as it is set on
		// this model, such as "json_encoding" a listing of data for storage.
		if ($this->hasSetMutator($key)) {
			return $this->setMutatedAttributeValue($key, $value);
		}

		// If an attribute is listed as a "date", we'll convert it from a DateTime
		// instance into a form proper for storage in the database tables using
		// the connection grammar's date format. We will auto set the values.
		elseif ($this->isDateAttribute($key)) {
			$value = $this->fromDateTime($value);
		}

		if ($this->isClassCastable($key)) {
			$this->setClassCastableAttribute($key, $value);

			return $this;
		}

		if (!is_null($value) && $this->isJsonCastable($key)) {
			$value = $this->castAttributeAsJson($key, $value);
		}

		// If this attribute contains a JSON ->, we'll set the proper value in the
		// attribute's underlying array. This takes care of properly nesting an
		// attribute in the array's value in the case of deeply nested items.
		if (Str::contains($key, '->')) {
			return $this->fillJsonAttribute($key, $value);
		}

		if (!is_null($value) && $this->isEncryptedCastable($key)) {
			$value = $this->castAttributeAsEncryptedString($key, $value);
		}

		// If we have fallen through until here, we first check if the parent
		// class provides an attribute of that name and then set the attribute
		// on the parent class.
		// Only if the parent class does not provide such an attribute either,
		// we write it to the child class.
		$baseClass = $this->base_class;
		if (
			array_key_exists($key, $baseClass->getAttributes()) ||
			$baseClass->hasSetMutator($key)
		) {
			$baseClass->setAttribute($key, $value);
		} else {
			$this->attributes[$key] = $value;
		}

		return $this;
	}

	/**
	 * Unset the value for a given offset.
	 *
	 * @param mixed $offset
	 *
	 * @return void
	 */
	public function offsetUnset($offset): void
	{
		// Prevent that the base model is unset from the set of relations
		if ($offset === 'base_class') {
			return;
		}
		parent::offsetUnset($offset);
		if ($this->relationLoaded('base_class')) {
			$this->base_class->offsetUnset($offset);
		}
	}
}
