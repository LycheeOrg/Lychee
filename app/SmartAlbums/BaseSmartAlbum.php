<?php

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

	protected PhotoQueryPolicy $photoQueryPolicy;
	protected string $id;
	protected string $title;
	protected ?Thumb $thumb = null;
	/** @var ?Collection<int,Photo> */
	protected ?Collection $photos = null;
	protected \Closure $smartPhotoCondition;
	protected AccessPermission|null $publicPermissions;

	/**
	 * @throws ConfigurationKeyMissingException
	 * @throws FrameworkException
	 */
	protected function __construct(SmartAlbumType $id, \Closure $smartCondition)
	{
		try {
			$this->photoQueryPolicy = resolve(PhotoQueryPolicy::class);
			$this->id = $id->value;
			$this->title = __('lychee.' . $id->name) ?? $id->name;
			$this->smartPhotoCondition = $smartCondition;
			/** @var AccessPermission|null $perm */
			$perm = AccessPermission::query()->where('base_album_id', '=', $id->value)->first();
			$this->publicPermissions = $perm;
		} catch (BindingResolutionException $e) {
			throw new FrameworkException('Laravel\'s service container', $e);
		}
	}

	/**
	 * @throws InternalLycheeException
	 */
	public function photos(): Builder
	{
		return $this->photoQueryPolicy
			->applySearchabilityFilter(
				Photo::query()->with(['album', 'size_variants', 'size_variants.sym_links'])
			)->where($this->smartPhotoCondition);
	}

	/**
	 * @return Collection<Photo>
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

			/** @var \Illuminate\Database\Eloquent\Collection&iterable<\App\Models\Photo> $photos */
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
	 * @return Collection|null
	 */
	public function getPhotos(): ?Collection
	{
		return $this->photos;
	}

	/**
	 * @throws InvalidPropertyException
	 * @throws InvalidQueryModelException
	 */
	protected function getThumbAttribute(): ?Thumb
	{
		if ($this->thumb === null) {
			/*
			 * Note, `photos()` already applies a "security filter" and
			 * only returns photos which are accessible by the current
			 * user.
			 */
			$this->thumb = Thumb::createFromQueryable(
				$this->photos(),
				PhotoSortingCriterion::createDefault()
			);
		}

		return $this->thumb;
	}

	public function setPublic(): void
	{
		if ($this->publicPermissions !== null) {
			return;
		}

		$this->publicPermissions = AccessPermission::ofPublic();
		$this->publicPermissions->base_album_id = $this->id;
		$this->publicPermissions->save();
	}

	public function setPrivate(): void
	{
		if ($this->publicPermissions === null) {
			return;
		}

		$perm = $this->publicPermissions;
		$this->publicPermissions = null;
		$perm->delete();
	}
}
