<?php

namespace App\Livewire\Components\Forms\Album;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Livewire\Params;
use App\Contracts\Models\AbstractAlbum;
use App\Exceptions\Internal\LycheeDomainException;
use App\Exceptions\Internal\LycheeLogicException;
use App\Factories\AlbumFactory;
use App\Livewire\Traits\InteractWithModal;
use App\Livewire\Traits\Notify;
use App\Models\Album;
use App\Policies\AlbumPolicy;
use App\Rules\AlbumIDRule;
use Illuminate\Contracts\View\View;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * This defines the Upload Form used in modals.
 */
class AddTrack extends Component
{
	use WithFileUploads;
	use Notify;
	use InteractWithModal;

	/**
	 * @var string|null albumId of where to upload the picture
	 */
	#[Locked] public ?string $albumID = null;
	public ?UploadedFile $file = null;

	private AlbumFactory $albumFactory;

	public function boot(): void
	{
		$this->albumFactory = resolve(AlbumFactory::class);
	}

	/**
	 * @return array<string,string|array<int,string|\Illuminate\Contracts\Validation\ValidationRule|\Illuminate\Validation\Rules\Enum>>
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['required', new AlbumIDRule(false)],
			'file' => 'required|file',
		];
	}

	/**
	 * Mount the component.
	 *
	 * @param array{parentID:?string} $params
	 *
	 * @return void
	 */
	public function mount(array $params = ['parentID' => null]): void
	{
		$this->albumID = $params[Params::PARENT_ID] ?? null;

		if ($this->albumID === null) {
			throw new LycheeLogicException('parentID is null');
		}

		$album = $this->albumFactory->findBaseAlbumOrFail($this->albumID, false);
		if (!$album instanceof Album) {
			throw new LycheeDomainException('This functionality is not available for tag albums.');
		}

		Gate::authorize(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, $album]);
	}

	public function render(): View
	{
		return view('livewire.forms.album.add-track');
	}

	public function submit(): void
	{
		$this->validate();
		/** @var Album $album */
		$album = $this->albumFactory->findBaseAlbumOrFail($this->albumID, false);

		Gate::authorize(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, $album]);
		$album->setTrack($this->file);
		$this->notify(__('lychee.SUCCESS'));
		$this->closeModal();
	}

	/**
	 * Close the modal containing the Upload panel.
	 *
	 * @return void
	 */
	public function close(): void
	{
		$this->closeModal();
	}
}
