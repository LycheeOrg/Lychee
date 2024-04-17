<?php

namespace App\Livewire\Components\Forms\Album;

use App\Contracts\Models\AbstractAlbum;
use App\Livewire\Traits\Notify;
use App\Livewire\Traits\UseValidator;
use App\Models\Album;
use App\Models\Photo;
use App\Policies\AlbumPolicy;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Livewire\Component;

class SetHeader extends Component
{
	use AuthorizesRequests;
	use UseValidator;
	use Notify;
	public const COMPACT_HEADER = 'compact';

	#[Locked] public string $albumID;
	public ?string $search = null; // ! wired
	/** @var array<int,array{id:string,title:string,thumb:string}> */
	#[Locked] public array $photoListSaved;
	public ?string $header_id;
	public ?string $title;

	/**
	 * This is the equivalent of the constructor for Livewire Components.
	 *
	 * @param string $album_id to update the attributes of
	 *
	 * @return void
	 */
	public function mount(string $album_id): void
	{
		/** @var Album $album */
		$album = Album::with(['header'])->findOrFail($album_id);

		Gate::authorize(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, $album]);

		$this->albumID = $album_id;

		$this->photoListSaved = Photo::with('size_variants')->where('album_id', '=', $album_id)->get()->map(fn (Photo $photo) => [
			'id' => $photo->id,
			'title' => $photo->title,
			'thumb' => $photo->size_variants->getThumb()?->url ?? URL::asset('img/no_images.svg'),
		])->all();

		$this->header_id = $album->header_id;
		$this->title = $album->header?->title;
	}

	/**
	 * Simply render the form.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.forms.album.set-header');
	}

	/**
	 * Give the tree of albums owned by the user.
	 *
	 * @return array<int,array{id:string,title:string,thumb:string}>
	 */
	public function getPhotoListProperty(): array
	{
		$filtered = collect($this->photoListSaved);
		if ($this->search !== null && trim($this->search) !== '') {
			return $filtered->filter(function (array $photo) {
				return Str::contains($photo['title'], ltrim($this->search), true);
			})->all();
		}

		return $filtered->all();
	}

	public function select(?string $photoId, ?string $title): void
	{
		Gate::authorize(AlbumPolicy::CAN_EDIT_ID, [AbstractAlbum::class, [$this->albumID]]);

		Album::where('id', '=', $this->albumID)->update(['header_id' => $photoId]);
		$this->header_id = $photoId;
		$this->title = $title;
	}

	public function clearHeaderId(): void
	{
		$this->select(null, null);
	}
}
