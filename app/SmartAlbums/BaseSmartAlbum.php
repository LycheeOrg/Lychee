<?php

namespace App\SmartAlbums;

use App\Contracts\AbstractAlbum;
use App\Contracts\InternalLycheeException;
use App\DTO\PhotoSortingCriterion;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\Internal\FrameworkException;
use App\Exceptions\Internal\InvalidOrderDirectionException;
use App\Exceptions\Internal\InvalidQueryModelException;
use App\Exceptions\InvalidPropertyException;
use App\Models\Configs;
use App\Models\Extensions\SortingDecorator;
use App\Models\Extensions\Thumb;
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
 *
 * @property string $id
 */
abstract class BaseSmartAlbum implements AbstractAlbum
{
	use MimicModel;
	use UTCBasedTimes;

	protected PhotoQueryPolicy $photoQueryPolicy;
	protected string $id;
	protected string $title;
	protected bool $isPublic;
	protected bool $isDownloadable;
	protected bool $isShareButtonVisible;
	protected ?Thumb $thumb;
	protected Collection $photos;
	protected \Closure $smartPhotoCondition;

	/**
	 * @throws ConfigurationKeyMissingException
	 * @throws FrameworkException
	 */
	protected function __construct(string $id, string $title, bool $isPublic, \Closure $smartCondition)
	{
		try {
			$this->photoQueryPolicy = resolve(PhotoQueryPolicy::class);
			$this->id = $id;
			$this->title = $title;
			$this->isPublic = $isPublic;
			$this->isDownloadable = Configs::getValueAsBool('downloadable');
			$this->isShareButtonVisible = Configs::getValueAsBool('share_button_visible');
			$this->thumb = null;
			$this->smartPhotoCondition = $smartCondition;
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

			$this->photos = (new SortingDecorator($this->photos()))
				->orderBy('photos.' . $sorting->column, $sorting->order)
				->get();
		}

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

	/**
	 * @throws InvalidPropertyException
	 * @throws InvalidQueryModelException
	 */
	public function toArray(): array
	{
		// The properties `thumb` and `photos` are intentionally treated
		// differently.
		//
		//  1. The result always includes `thumb`, hence we call the
		//     getter method to ensure that the property is initialized, if it
		//     has not already been accessed before.
		//  2. The result only includes the collection `photos`, if it has
		//     already explicitly been accessed earlier and thus is initialized.
		//
		// Rationale:
		//
		//  1. This resembles the behaviour of a real Eloquent model, if the
		//     attribute `thumb` was part of the `append`-property of model.
		//  2. This resembles the behaviour of a real Eloquent model for
		//     one-to-many relations.
		//     A relation is only included in the array representation, if the
		//     relation has been loaded.
		//     This avoids unnecessary hydration of photos if the album is
		//     only used within a listing of sub-albums.

		$result = [
			'id' => $this->id,
			'title' => $this->title,
			'is_public' => $this->isPublic,
			'is_downloadable' => $this->isDownloadable,
			'is_share_button_visible' => $this->isShareButtonVisible,
			'thumb' => $this->getThumbAttribute(),
		];

		if ($this->photos !== null) {
			$result['photos'] = $this->photos->toArray();
		}

		return $result;
	}
}
