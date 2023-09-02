<?php

namespace App\Livewire\Components\Pages\Gallery;

use App\Actions\Photo\Strategies\RotateStrategy;
use App\Contracts\Models\AbstractAlbum;
use App\Enum\Livewire\PhotoOverlayMode;
use App\Factories\AlbumFactory;
use App\Livewire\DTO\PhotoFlags;
use App\Livewire\Traits\InteractWithModal;
use App\Models\Configs;
use App\Models\Photo as PhotoModel;
use App\Policies\AlbumPolicy;
use App\Policies\PhotoPolicy;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Similar to the Album module, this takes care of displaying a single photo.
 */
class Photo extends Component
{
	use InteractWithModal;
	private AlbumFactory $albumFactory;

	#[Locked]
	public string $albumId;

	#[Locked]
	public string $photoId;

	#[Locked]
	public PhotoFlags $flags;

	public string $overlayType;

	/** @var PhotoModel Said photo to be displayed */
	public PhotoModel $photo;
	public ?AbstractAlbum $album = null;

	public ?PhotoModel $nextPhoto = null;
	public ?PhotoModel $previousPhoto = null;

	public function boot(): void
	{
		$this->albumFactory = resolve(AlbumFactory::class);
	}

	public function mount(string $albumId, string $photoId): void
	{
		$this->albumId = $albumId;
		$this->photoId = $photoId;

		$this->album = $this->albumFactory->findAbstractAlbumOrFail($this->albumId);

		// TODO: support password
		Gate::authorize(AlbumPolicy::CAN_ACCESS, [AbstractAlbum::class, $this->album]);

		/** @var PhotoModel $photoItem */
		$photoItem = PhotoModel::with('album')->findOrFail($this->photoId);
		$this->photo = $photoItem;
		$this->overlayType = Configs::getValueAsEnum('image_overlay_type', PhotoOverlayMode::class)->value;

		$this->flags = new PhotoFlags(
			can_autoplay: true,
			can_rotate: Configs::getValueAsBool('editor_enabled'),
		);

		/** @var int $idx */
		$idx = $this->album->photos->search(fn (PhotoModel $photo) => $photo->id === $this->photoId);
		$max = $this->album->photos->count();
		$wrapOver = Configs::getValueAsBool('photos_wraparound') && $max > 1;

		$idx_next = ($idx + 1) % $max;
		$idx_previous = ($idx - 1) % $max;
		if ($idx < $idx_next && $idx_previous < $idx) {
			$this->previousPhoto = $this->album->photos->get($idx_previous);
			$this->nextPhoto = $this->album->photos->get($idx_next);
		} else {
			// Possible wrap around
			$this->previousPhoto = ($wrapOver && $idx_previous > $idx) ? $this->album->photos->get($idx_previous) : null;
			$this->nextPhoto = ($wrapOver && $idx_next < $idx) ? $this->album->photos->get($idx_next) : null;
		}

		// $this->locked = Gate::check(AlbumPolicy::CAN_ACCESS, [AbstractAlbum::class, $this->album]);
	}

	/**
	 * Render the associated view.
	 *
	 * @return View
	 *
	 * @throws BindingResolutionException
	 */
	public function render(): View
	{
		return view('livewire.pages.gallery.photo');
	}

	public function back(): mixed
	{
		return $this->redirect(route('livewire-gallery-album', ['albumId' => $this->albumId]));
	}

	#[On('reloadPage')]
	public function reloadPage(): void
	{
	}

	public function set_star(): void
	{
		Gate::authorize(PhotoPolicy::CAN_EDIT, $this->photo);
		$this->photo->is_starred = !$this->photo->is_starred;
		$this->photo->save();
	}

	public function rotate_ccw(): void
	{
		Gate::authorize(PhotoPolicy::CAN_EDIT, $this->photo);
		$rotateStrategy = new RotateStrategy($this->photo, -1);
		$this->photo = $rotateStrategy->do();
		$this->render();
	}

	public function rotate_cw(): void
	{
		Gate::authorize(PhotoPolicy::CAN_EDIT, $this->photo);
		$rotateStrategy = new RotateStrategy($this->photo, 1);
		$this->photo = $rotateStrategy->do();
		$this->render();
	}

	public function delete(): void
	{
		$this->openModal('forms.photo.delete', ['photoId' => $this->photo->id, 'albumId' => $this->albumId]);

		// Gate::check(PhotoPolicy::CAN_EDIT, $this->photo);
		// $fileDeleter = $delete->do([$this->photo->id]);
		// App::terminating(fn () => $fileDeleter->do());
		// $this->back();
	}
}
