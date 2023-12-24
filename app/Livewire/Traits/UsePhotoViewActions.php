<?php

namespace App\Livewire\Traits;

use App\Actions\Photo\Strategies\RotateStrategy;
use App\Enum\Livewire\NotificationType;
use App\Http\Resources\Models\PhotoResource;
use App\Livewire\Forms\PhotoUpdateForm;
use App\Models\Configs;
use App\Models\Photo;
use App\Policies\PhotoPolicy;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Renderless;

/**
 * Collection of actions accessible from the JS Photo view.
 */
trait UsePhotoViewActions
{
	/**
	 * Update data from a form data.
	 *
	 * @param array $formData data
	 *
	 * @return PhotoResource|null null or error, updated Model otherwise
	 */
	#[Renderless]
	public function updatePhoto(array $formData): ?PhotoResource
	{
		$form = new PhotoUpdateForm(...$formData);

		$valdiation = $form->validate();
		if ($valdiation !== []) {
			$msg = '';
			foreach ($valdiation as $value) {
				$msg .= ($msg !== '' ? '<br>' : '') . implode('<br>', $value);
			}
			$this->notify($msg, NotificationType::ERROR);

			return null;
		}

		Gate::authorize(PhotoPolicy::CAN_EDIT, [Photo::class, $form->getPhoto()]);

		$form->save();

		$this->notify(__('lychee.CHANGE_SUCCESS'));

		return PhotoResource::make($form->getPhoto());
	}

	/**
	 * Flip the star of a given photo.
	 *
	 * @param array<0,string> $photoIDarg
	 *
	 * @return bool
	 */
	#[Renderless]
	public function toggleStar(array $photoIDarg): bool
	{
		if (count($photoIDarg) !== 1 || !is_string($photoIDarg[0])) {
			$this->notify('wrong ID', NotificationType::ERROR);

			return false;
		}
		$photo = Photo::query()->findOrFail($photoIDarg[0]);

		Gate::authorize(PhotoPolicy::CAN_EDIT, [Photo::class, $photo]);
		$photo->is_starred = !$photo->is_starred;
		$photo->save();

		return true;
	}

	/**
	 * Rotate selected photo Counter ClockWise.
	 *
	 * @param string $photoID
	 *
	 * @return void
	 */
	public function rotate_ccw(string $photoID): void
	{
		if (!Configs::getValueAsBool('editor_enabled')) {
			return;
		}

		$photo = Photo::query()->findOrFail($photoID);
		Gate::authorize(PhotoPolicy::CAN_EDIT, [Photo::class, $photo]);
		$rotateStrategy = new RotateStrategy($photo, -1);
		$photo = $rotateStrategy->do();
		// Force hard refresh of the page (to load the rotated image)
		$this->redirect(route('livewire-gallery-photo', ['albumId' => $this->albumId, 'photoId' => $photo->id]));
	}

	/**
	 * Rotate selected photo ClockWise.
	 *
	 * @param string $photoID
	 *
	 * @return void
	 */
	public function rotate_cw(string $photoID): void
	{
		if (!Configs::getValueAsBool('editor_enabled')) {
			return;
		}

		$photo = Photo::query()->findOrFail($photoID);
		Gate::authorize(PhotoPolicy::CAN_EDIT, [Photo::class, $photo]);
		$rotateStrategy = new RotateStrategy($photo, 1);
		$photo = $rotateStrategy->do();
		// Force hard refresh of the page (to load the rotated image)
		$this->redirect(route('livewire-gallery-photo', ['albumId' => $this->albumId, 'photoId' => $photo->id]));
	}

	/**
	 * Set all photos for given id as starred.
	 *
	 * @param array<int,string> $photoIDs
	 *
	 * @return void
	 */
	#[Renderless]
	public function setStar(array $photoIDs): void
	{
		Gate::authorize(PhotoPolicy::CAN_EDIT_ID, [Photo::class, $photoIDs]);
		Photo::whereIn('id', $photoIDs)->update(['is_starred' => true]);
	}

	/**
	 * Set all photos for given id as NOT starred.
	 *
	 * @param array $photoIDs
	 *
	 * @return void
	 */
	#[Renderless]
	public function unsetStar(array $photoIDs): void
	{
		Gate::authorize(PhotoPolicy::CAN_EDIT_ID, [Photo::class, $photoIDs]);
		Photo::whereIn('id', $photoIDs)->update(['is_starred' => false]);
	}
}