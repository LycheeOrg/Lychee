<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\SmartAlbums;

use App\Contracts\Exceptions\InternalLycheeException;
use App\Contracts\Models\AbstractAlbum;
use App\DTO\PhotoSortingCriterion;
use App\Enum\SmartAlbumType;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\Internal\FrameworkException;
use App\Exceptions\Internal\InvalidOrderDirectionException;
use App\Exceptions\Internal\InvalidQueryModelException;
use App\Exceptions\InvalidPropertyException;
use App\Models\AccessPermission;
use App\Models\Configs;
use App\Models\Extensions\SortingDecorator;
use App\Models\Extensions\Thumb;
use App\Models\Extensions\ToArrayThrowsNotImplemented;
use App\Models\Extensions\UTCBasedTimes;
use App\Models\Photo;
use App\Policies\PhotoQueryPolicy;
use App\SmartAlbums\Utils\MimicModel;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class BaseSmartAlbum.
 *
 * The common base class for all built-in smart albums which can neither
 * be created nor deleted, but always exist.
 * Smart albums are never explicit "parent albums" of photos.
 * Photos belong to these albums due to certain properties like being
 * starred, being recently added, etc.
 */
abstract class BaseSmartAlbum implements AbstractAlbum
{
	use MimicModel;
	use UTCBasedTimes;
	use ToArrayThrowsNotImplemented;

	protected PhotoQueryPolicy $photo_query_policy;
	protected string $id;
	protected string $title;
	protected ?Thumb $thumb = null;
	/** @var ?Collection<int,Photo> */
	protected ?Collection $photos = null;
	protected \Closure $smart_photo_condition;
	protected AccessPermission|null $public_permissions;

	/**
	 * @throws ConfigurationKeyMissingException
	 * @throws FrameworkException
	 */
	protected function __construct(SmartAlbumType $id, \Closure $smart_condition)
	{
		try {
			$this->photo_query_policy = resolve(PhotoQueryPolicy::class);
			$this->id = $id->value;
			$this->title = __('gallery.smart_album.' . strtolower($id->name)) ?? $id->name;
			$this->smart_photo_condition = $smart_condition;
			/** @var AccessPermission|null $perm */
			$perm = AccessPermission::query()->where('base_album_id', '=', $id->value)->first();
			$this->public_permissions = $perm;
			// @codeCoverageIgnoreStart
		} catch (BindingResolutionException $e) {
			throw new FrameworkException('Laravel\'s service container', $e);
		}
		// @codeCoverageIgnoreEnd
	}

	// From AbstractAlbum
	public function get_id(): string
	{
		return $this->id;
	}

	public function get_title(): string
	{
		return $this->title;
	}

	/**
	 * @return Collection<int,AccessPermission>
	 */
	public function get_access_permissions(): Collection
	{
		if ($this->public_permissions === null) {
			return resolve(Collection::class);
		}

		return collect([$this->public_permissions]);
	}

	/**
	 * @return Collection<int,Photo>
	 */
	public function get_photos(): Collection
	{
		return $this->getPhotosAttribute();
	}

	/**
	 * @return \App\Eloquent\FixedQueryBuilder<Photo>
	 *
	 * @throws InternalLycheeException
	 */
	public function photos(): Builder
	{
		$query = $this->photo_query_policy
			->applySearchabilityFilter(
				query: Photo::query()->with(['album', 'size_variants', 'size_variants.sym_links']),
				origin: null,
				include_nsfw: !Configs::getValueAsBool('hide_nsfw_in_smart_albums')
			)->where($this->smart_photo_condition);

		return $query;
	}

	/**
	 * @return Collection<int,Photo>
	 *
	 * @throws InvalidOrderDirectionException
	 * @throws InvalidQueryModelException
	 */
	protected function getPhotosAttribute(): Collection
	{
		// Cache query result for later use
		// (this mimics the behaviour of relations of true Eloquent models)
		if ($this->photos === null) {
			$sorting = PhotoSortingCriterion::createDefault();

			/** @var \Illuminate\Database\Eloquent\Collection<int,\App\Models\Photo>&iterable $photos */
			$photos = (new SortingDecorator($this->photos()))
				->orderPhotosBy($sorting->column, $sorting->order)
				->get();
			$this->photos = $photos;
		}

		return $this->photos;
	}

	/**
	 * Similar to the function above.
	 * The big difference is that we do not check if it is null or not.
	 *
	 * @return Collection<int,Photo>|null
	 */
	public function getPhotos(): ?Collection
	{
		return $this->photos;
	}

	/**
	 * Override the or.
	 *
	 * @return Thumb|null
	 *
	 * @throws InvalidPropertyException
	 * @throws InvalidQueryModelException
	 */
	public function get_thumb(): Thumb|null
	{
		return $this->getThumbAttribute();
	}

	/**
	 * @throws InvalidPropertyException
	 * @throws InvalidQueryModelException
	 */
	protected function getThumbAttribute(): ?Thumb
	{
		/*
			* Note, `photos()` already applies a "security filter" and
			* only returns photos which are accessible by the current
			* user.
			*/
		$this->thumb ??= Configs::getValueAsBool('SA_random_thumbs')
			// @codeCoverageIgnoreStart
			? Thumb::createFromRandomQueryable($this->photos())
			// @codeCoverageIgnoreEnd
			: $this->thumb = Thumb::createFromQueryable(
				$this->photos(),
				PhotoSortingCriterion::createDefault()
			);

		return $this->thumb;
	}

	public function public_permissions(): ?AccessPermission
	{
		return $this->public_permissions;
	}

	public function setPublic(): void
	{
		if ($this->public_permissions !== null) {
			return;
		}

		$this->public_permissions = AccessPermission::ofPublic();
		$this->public_permissions->base_album_id = $this->id;
		$this->public_permissions->save();
	}

	public function setPrivate(): void
	{
		if ($this->public_permissions === null) {
			return;
		}

		$perm = $this->public_permissions;
		$this->public_permissions = null;
		$perm->delete();
	}
}